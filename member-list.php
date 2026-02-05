<?php
$q = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;

$memberList = [];
$totalLine = 0;
$totalPages = 1;
$errorMsg = '';

$apiUrl = 'https://api.thxdeal.com/api/member/memberApprovedList.php';

$postData = [
  'page'  => $page,
  'limit' => $limit,
  'search'=> $q,
];

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
  CURLOPT_POST           => true,
  CURLOPT_POSTFIELDS     => http_build_query($postData),
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_SSL_VERIFYPEER => false,
  CURLOPT_TIMEOUT        => 10,
]);

$response = curl_exec($ch);

if ($response === false) {
  $errorMsg = 'API 호출 실패: ' . curl_error($ch);
  curl_close($ch);
  return;
}
curl_close($ch);

$data = json_decode($response, true);
if (!is_array($data)) {
  $errorMsg = '응답 JSON 파싱 실패';
  return;
}


$code = $data['code'] ?? $data['resCode'] ?? 1;
if ((int)$code !== 0) {
  $errorMsg = $data['message'] ?? 'API 오류';
  return;
}

$memberList = $data['data'] ?? $data['list'] ?? [];
$totalLine  = (int)($data['total'] ?? 0);
$totalPages = max(1, (int)ceil($totalLine / $limit));
