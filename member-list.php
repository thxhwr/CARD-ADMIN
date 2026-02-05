<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/bootstrap.php';

// 관리자 로그인 체크 (있다면)
if (empty($_SESSION['admin_id'])) {
    echo json_encode([
        'code' => 401,
        'message' => '로그인이 필요합니다.'
    ]);
    exit;
}

// 프론트에서 받은 값
$page   = max(1, (int)($_POST['page'] ?? 1));
$limit  = min(100, max(10, (int)($_POST['limit'] ?? 20)));
$search = trim($_POST['search'] ?? '');

// 실제 API URL
$apiUrl = 'https://api.thxdeal.com/api/member/memberApprovedList.php';

// API로 전달할 데이터
$postData = [
    'page'   => $page,
    'limit'  => $limit,
    'search' => $search
];

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_POST            => true,
    CURLOPT_POSTFIELDS      => http_build_query($postData),
    CURLOPT_RETURNTRANSFER  => true,
    CURLOPT_TIMEOUT         => 10,
    CURLOPT_SSL_VERIFYPEER  => false, // 내부망/사설 SSL이면
]);

$response = curl_exec($ch);

if ($response === false) {
    echo json_encode([
        'code' => 500,
        'message' => 'API 호출 실패',
        'error' => curl_error($ch)
    ]);
    curl_close($ch);
    exit;
}

curl_close($ch);

// 그대로 프론트에 전달
echo $response;
