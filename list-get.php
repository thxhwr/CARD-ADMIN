<?php
$q = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;

$postFields = [
  'search' => $q,
  'page'   => $page,
  'list'   => $limit,
];

$ch = curl_init('https://api.thxdeal.com/api/member/testMemberAppList.php');
curl_setopt_array($ch, [
  CURLOPT_POST           => true,
  CURLOPT_POSTFIELDS     => $postFields,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_SSL_VERIFYPEER => false,
]);
$response = curl_exec($ch);
$curlErr  = $response === false ? curl_error($ch) : null;
curl_close($ch);

$appList = [];
$totalLine = 0;
$totalPages = 1;
$errorMsg = null;

if ($curlErr) {
  $errorMsg = "API 호출 실패: " . $curlErr;
} else {
  $json = json_decode($response, true);

  if (!is_array($json) || !isset($json['resCode'])) {
    $errorMsg = "응답 파싱 실패";
  } elseif ((int)$json['resCode'] !== 0) {
    $errorMsg = $json['message'] ?? '요청 실패';
  } else {
    $appList   = $json['data'] ?? [];
    $totalLine = (int)($json['totalLine'] ?? 0);

    $totalPages = max(1, (int)ceil($totalLine / $limit));
    if ($page > $totalPages) $page = $totalPages; // 혹시 page가 넘어가면 보정
  }
}
?>
