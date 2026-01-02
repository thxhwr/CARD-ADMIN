<?php
header('Content-Type: application/json; charset=utf-8');

$applyId = $_POST['applyId'] ?? null;

if (!$applyId) {
    echo json_encode([
        'ok' => false,
        'message' => 'APPLY_ID 누락'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$postFields = [
    'applyId' => $applyId,
];

$ch = curl_init('https://api.thxdeal.com/api/member/testMemberInsertFirst.php');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $postFields,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
]);
$response = curl_exec($ch);

if ($response === false) {
    echo json_encode([
        'ok' => false,
        'message' => 'cURL 오류: ' . curl_error($ch)
    ], JSON_UNESCAPED_UNICODE);
    curl_close($ch);
    exit;
}

curl_close($ch);

$data = json_decode($response, true);
if (!is_array($data)) {
    echo json_encode([
        'ok' => false,
        'message' => 'API 응답 파싱 실패'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (($data['resCode'] ?? 1) === 0) {
    echo json_encode([
        'ok' => true,
        'message' => '정상 처리 완료'
    ], JSON_UNESCAPED_UNICODE);
} else if($data['resCode'] == "4002"){
    echo json_encode([
        'ok' => false,
        'message' => $data['message'] ?? '처리 실패'
    ], JSON_UNESCAPED_UNICODE);
} else if($data['resCode'] == "1006"){
       echo json_encode([
        'ok' => false,
        'message' => $data['message'] ?? '처리 실패'
    ], JSON_UNESCAPED_UNICODE);
}
