<?php
include __DIR__ . "/head.php";
include __DIR__ . "/side.php";

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function callApi(string $url, array $postData): array {
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query($postData),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT        => 10,
  ]);
  $raw = curl_exec($ch);

  if ($raw === false) {
    $err = curl_error($ch);
    curl_close($ch);
    return [null, "API 호출 실패: {$err}", ''];
  }
  curl_close($ch);

  $json = json_decode($raw, true);
  if (!is_array($json)) {
    return [null, "응답 JSON 파싱 실패", $raw];
  }

  $resCode = (int)($json['resCode'] ?? $json['code'] ?? 1);
  if ($resCode !== 0) {
    $msg = $json['message'] ?? 'API 오류';
    if (!empty($json['data']['error'])) $msg .= " (" . $json['data']['error'] . ")";
    return [null, $msg, $raw];
  }

  return [$json, null, $raw];
}

$accountNo = trim($_GET['accountNo'] ?? '');
if ($accountNo === '') {
  echo "<div style='padding:18px;color:#ef4444;'>accountNo가 없습니다.</div>";
  exit;
}

$typeCode = trim($_GET['typeCode'] ?? 'TP');
if (!in_array($typeCode, ['TP','SP','LP'], true)) $typeCode = 'TP';

$logPage  = max(1, (int)($_GET['logPage'] ?? 1));
$logLimit = 15;

$API_MEMBER_DETAIL = 'https://api.thxdeal.com/api/member/memberDetail.php';   
$API_POINT_HISTORY = 'https://api.thxdeal.com/api/point/history.php';        


[$detailJson, $detailErr] = callApi($API_MEMBER_DETAIL, [
  'accountNo' => $accountNo
]);

$member = $detailJson['data'] ?? [];


[$historyJson, $historyErr] = callApi($API_POINT_HISTORY, [
  'accountNo' => $accountNo,
  'typeCode'  => $typeCode,
  'page'      => $logPage,
  'limit'     => $logLimit
]);

$logs = $historyJson['data'] ?? [];

$totalLogs = (int)($historyJson['total'] ?? $historyJson['totalLine'] ?? 0);
$totalLogPages = max(1, (int)ceil($totalLogs / $logLimit));


$name     = $member['NAME'] ?? '';
$phone    = $member['PHONE'] ?? '';
$address  = $member['ADDRESS'] ?? '';
$refId    = $member['REFERRER_ACCOUNT_NO'] ?? '';
$refName  = $member['REFERRER_NAME'] ?? '';
$created  = $member['CREATED_AT'] ?? '';
$joinStr  = $created ? date('Y-m-d', strtotime($created)) : '';

function makeUrl(array $override = []): string {
  $params = array_merge($_GET, $override);
  return basename($_SERVER['PHP_SELF']) . '?' . http_build_query($params);
}

$range = 2;
$start = max(1, $logPage - $range);
$end   = min($totalLogPages, $logPage + $range);
while (($end - $start) < ($range * 2) && $start > 1) $start--;
while (($end - $start) < ($range * 2) && $end < $totalLogPages) $end++;
?>

<div class="layout">
  <div class="main">
    <header class="topbar">
      <div class="topbar-left">
        <button class="sidebar-toggle-btn" id="sidebarToggle" aria-label="메뉴 열기">☰</button>
        <div>
          <div class="topbar-title">회원 상세</div>
          <div class="topbar-subtitle">좌측 회원정보 / 우측 포인트 내역</div>
          <div class="breadcrumb">
            <span>홈</span><span>회원 관리</span><span>회원 상세</span>
          </div>
        </div>
      </div>

      <div class="topbar-right">
        <div class="topbar-actions">
          <button class="icon-button" title="뒤로" onclick="history.back()">←</button>
          <button class="icon-button" title="새로고침" onclick="location.reload()">⟳</button>
        </div>
      </div>
    </header>

    <main class="content">

      <?php if ($detailErr): ?>
        <div class="alert alert-danger">회원정보: <?= h($detailErr) ?></div>
      <?php endif; ?>
      <?php if ($historyErr): ?>
        <div class="alert alert-danger">포인트내역: <?= h($historyErr) ?></div>
      <?php endif; ?>
      <div class="detail-wrap">
        <div class="detail-grid">
        
            <section class="card-like member-card">
            <div class="card-grad">
                <div class="card-title-lg">회원정보</div>
            </div>

            <div class="card-pad">
                <div class="fg">
                <label>이름</label>
                <input type="text" value="<?= h($name) ?>" readonly>
                </div>

                <div class="fg">
                <label>아이디</label>
                <input type="text" value="<?= h($accountNo) ?>" readonly>
                </div>
                <div class="fg">
                <label>연락처</label>
                <input type="text" value="<?= h($phone) ?>" readonly>
                </div>

                <div class="fg">
                <label>이메일</label>
                <input type="text" value="<?= h($accountNo) ?>" readonly>
                </div>

                <div class="fg">
                <label>주소</label>
                <input type="text" value="<?= h($address) ?>" readonly>
                </div>

                <div class="fg">
                <label>총 보유 TP</label>
                <input type="text" value="" readonly>
                </div>

                <div class="fg">
                <label>총 보유 SP</label>
                <input type="text" value="" readonly>
                </div>

                <div class="fg">
                <label>총 보유 LP</label>
                <input type="text" value="" readonly>
                </div>

                <div class="fg">
                <label>추천인</label>
                <input type="text" value="<?= h(trim($refId . ($refName ? " ({$refName})" : ""))) ?>" readonly>
                </div>

                <div class="fg">
                <label>가입일</label>
                <input type="text" value="<?= h($joinStr) ?>" readonly>
                </div>
            </div>
            </section>

            <!-- ================== 우측: 포인트 내역 ================== -->
            <section class="card-like point-card">
            <div class="card-pad">
                <div class="point-head">
                <div>
                    <div class="card-title-md">포인트 내역</div>
                    <div class="muted">선택한 타입의 입/출금(적립/사용) 로그</div>
                </div>

                <div class="tabs">
                    <a class="tab <?= $typeCode==='TP'?'active':'' ?>" href="<?= h(makeUrl(['typeCode'=>'TP','logPage'=>1])) ?>">페이</a>
                    <a class="tab <?= $typeCode==='SP'?'active':'' ?>" href="<?= h(makeUrl(['typeCode'=>'SP','logPage'=>1])) ?>">SP</a>
                    <a class="tab <?= $typeCode==='LP'?'active':'' ?>" href="<?= h(makeUrl(['typeCode'=>'LP','logPage'=>1])) ?>">LP</a>
                </div>
                </div>

                <div class="table-wrap">
                <table class="tbl">
                    <thead>
                    <tr>
                        <th style="width:150px;">일시</th>
                        <th style="width:90px;">구분</th>
                        <th style="width:110px;">금액</th>
                        <th>내용</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($historyErr): ?>
                    <tr><td colspan="4" class="empty">내역을 불러오지 못했습니다.</td></tr>
                    <?php elseif (empty($logs)): ?>
                    <tr><td colspan="4" class="empty">내역이 없습니다.</td></tr>
                    <?php else: ?>
                    <?php foreach ($logs as $r): ?>
                        <?php
                        $dt = $r['CREATED_AT'] ?? '';
                        $dtStr = $dt ? date('Y-m-d H:i', strtotime($dt)) : '';
                        $action = $r['ACTION_TYPE'] ?? ''; // 입금/출금 or 적립/사용 등
                        $amt = $r['AMOUNT'] ?? 0;
                        $desc = $r['DESCRIPTION'] ?? '';
                        ?>
                        <tr>
                        <td class="mono"><?= h($dtStr) ?></td>
                        <td><?= h($action) ?></td>
                        <td class="mono"><?= h(number_format((float)$amt)) ?></td>
                        <td><?= h($desc) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
                </div>

                <?php if (!$historyErr && $totalLogPages > 1): ?>
                <div class="pager">
                    <a class="pbtn <?= $logPage<=1?'disabled':'' ?>" href="<?= h(makeUrl(['logPage'=>1])) ?>">«</a>
                    <a class="pbtn <?= $logPage<=1?'disabled':'' ?>" href="<?= h(makeUrl(['logPage'=>max(1,$logPage-1)])) ?>">‹</a>

                    <?php if ($start > 1): ?><span class="dots">…</span><?php endif; ?>

                    <?php for ($p=$start; $p<=$end; $p++): ?>
                    <a class="pbtn <?= $p===$logPage?'active':'' ?>" href="<?= h(makeUrl(['logPage'=>$p])) ?>">
                        <?= (int)$p ?>
                    </a>
                    <?php endfor; ?>

                    <?php if ($end < $totalLogPages): ?><span class="dots">…</span><?php endif; ?>

                    <a class="pbtn <?= $logPage>=$totalLogPages?'disabled':'' ?>" href="<?= h(makeUrl(['logPage'=>min($totalLogPages,$logPage+1)])) ?>">›</a>
                    <a class="pbtn <?= $logPage>=$totalLogPages?'disabled':'' ?>" href="<?= h(makeUrl(['logPage'=>$totalLogPages])) ?>">»</a>

                    <div class="pager-meta">
                    총 <b><?= (int)$totalLogs ?></b>건 · <?= (int)$logPage ?>/<?= (int)$totalLogPages ?>p
                    </div>
                </div>
                <?php endif; ?>

            </div>
            </section>
        </div>
      </div>
    </main>
  </div>
</div>

<style>
.detail-wrap {
  padding: 20px;
}

.detail-grid {
  display: grid;
  grid-template-columns: 420px 1fr;
  gap: 20px;
}

/* 공통 패널 */
.panel {
  background: #fff;
  border-radius: 14px;
  border: 1px solid #e5e7eb;
  overflow: hidden;
}

/* 헤더 */
.panel-header {
  padding: 16px 18px;
  border-bottom: 1px solid #eef2f7;
}

.panel-header.gradient {
  background: linear-gradient(135deg, #c7d7ff, #e7c9ff);
}

.panel-header h2 {
  margin: 0;
  font-size: 18px;
  font-weight: 700;
}

.panel-header .sub {
  margin-top: 6px;
  font-size: 13px;
  color: #6b7280;
}

.panel-body {
  padding: 18px;
}

/* 좌측 필드 */
.field {
  margin-bottom: 14px;
}

.field label {
  display: block;
  font-size: 14px;
  font-weight: 600;
  margin-bottom: 6px;
  color: #374151;
}

.field input {
  width: 100%;
  height: 42px;
  padding: 0 12px;
  border: 1px solid #9ca3af;
  border-radius: 4px;
  font-size: 15px;
  background: #fff;
}

/* 우측 탭 */
.point-tabs {
  display: flex;
  gap: 6px;
  margin-bottom: 14px;
}

.point-tabs .tab {
  padding: 6px 14px;
  border: 1px solid #d1d5db;
  background: #fff;
  border-radius: 4px;
  cursor: pointer;
}

.point-tabs .tab.active {
  background: #111827;
  color: #fff;
  border-color: #111827;
}

/* 테이블 */
.point-table {
  width: 100%;
  border-collapse: collapse;
}

.point-table th,
.point-table td {
  padding: 10px;
  border-bottom: 1px solid #e5e7eb;
  font-size: 14px;
}

.point-table th {
  text-align: left;
  color: #6b7280;
  font-weight: 600;
}

.point-table td.in {
  color: #2563eb;
  font-weight: 600;
}

/* 페이지네이션 */
.pagination {
  margin-top: 14px;
  font-size: 14px;
  color: #374151;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.pagination .count {
  color: #6b7280;
}