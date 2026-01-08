<?php
// 사용자가 입력한 값
$id = $_POST['admin_id'] ?? '';
$pw = $_POST['admin_pw'] ?? '';

// 간단 유효성 검증
if ($id === '' || $pw === '') {
    header('Location: /login.php?error=1');
    exit;
}


$ALLOWED_ADMINS = [
  'thx.manager@gmail.com',
  'ksw9310@nate.com',
  'youbr919@naver.com',
];

if (!in_array($id, $ALLOWED_ADMINS, true)) {
    header('Location: /login.php?error=denied');
    exit;
}

// API로 보낼 데이터
$postFields = [
  'memberId' => $id,
  'memberPw' => $pw,
];

$ch = curl_init('https://api.thxdeal.com/api/login/memberLogin.php');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $postFields,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
]);

$response = curl_exec($ch);
if ($response === false) {
    curl_close($ch);
    header('Location: /login.php?error=1');
    exit;
}
curl_close($ch);

$data = json_decode($response, true);

// 로그인 성공
if (isset($data['resCode']) && $data['resCode'] == "0") {

    // ✅ 관리자 세션으로 분리해두는 게 좋아 (프론트 user 세션이랑 충돌 방지)
    $lifetime = 60 * 60 * 24 * 30;
    session_set_cookie_params([
        'lifetime' => $lifetime,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
    session_regenerate_id(true);

    // 관리자 로그인 플래그 (auth.php에서 이걸 체크)
    $_SESSION['admin_login'] = true;
    $_SESSION['admin_id']    = $id;

    // 필요하면 기존 데이터도 같이 저장 가능
    $_SESSION['user_Status'] = $data['data']['status'] ?? null;
    $_SESSION['user_No']     = $data['data']['accountNo'] ?? null;
    $_SESSION['user_Id']     = $data['data']['userId'] ?? null;
    $_SESSION['user_Card']   = $data['data']['localUserId'] ?? null;

    header('Location: /index.php');
    exit;

} else {
    header('Location: /login.php?error=1');
    exit;
}
