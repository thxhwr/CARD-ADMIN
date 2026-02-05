<?php
include __DIR__ . "/head.php";

// ✅ accountNo 받기
$accountNo = trim($_GET['accountNo'] ?? '');
if ($accountNo === '') {
  echo "<div style='padding:20px;color:#ef4444;'>accountNo가 없습니다.</div>";
  exit;
}

// ✅ 상세 API 호출 (서버 -> 서버라서 CORS 없음)
$apiUrl = 'https://api.thxdeal.com/api/member/memberDetail.php';

$postData = [
  'accountNo' => $accountNo
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
  $errorMsg = "API 호출 실패: " . curl_error($ch);
  curl_close($ch);
} else {
  curl_close($ch);
  $data = json_decode($response, true);

  if (!is_array($data)) {
    $errorMsg = "응답 JSON 파싱 실패";
  } else {
    $resCode = (int)($data['resCode'] ?? 1);
    if ($resCode !== 0) {
      $errorMsg = $data['message'] ?? 'API 오류';
      // detail API에서 내려준 error도 같이
      if (!empty($data['data']['error'])) $errorMsg .= " (" . $data['data']['error'] . ")";
    } else {
      $member = $data['data'] ?? [];
    }
  }
}

// 안전 초기화
$member = $member ?? [];
$errorMsg = $errorMsg ?? '';

// 화면 값 매핑 (현재 MEMBER_APPLY에서 오는 값 기준)
$name     = $member['NAME'] ?? '';
$phone    = $member['PHONE'] ?? '';
$address  = $member['ADDRESS'] ?? '';
$refId    = $member['REFERRER_ACCOUNT_NO'] ?? '';
$refName  = $member['REFERRER_NAME'] ?? '';
$created  = $member['CREATED_AT'] ?? '';
$joinStr  = $created ? date('Y-m-d', strtotime($created)) : '';

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>

<div class="layout">
  <?php include __DIR__ . "/side.php"; ?>

  <div class="main">
    <header class="topbar">
      <div class="topbar-left">
        <button class="sidebar-toggle-btn" id="sidebarToggle" aria-label="메뉴 열기">☰</button>
        <div>
          <div class="topbar-title">회원 상세</div>
          <div class="topbar-subtitle">회원정보 조회</div>
          <div class="breadcrumb">
            <span>홈</span>
            <span>회원 관리</span>
            <span>회원 상세</span>
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
      <?php if ($errorMsg): ?>
        <div style="padding:16px;border:1px solid #fecaca;background:#fff1f2;color:#b91c1c;border-radius:12px;">
          <?= h($errorMsg) ?>
        </div>
      <?php endif; ?>

      <section style="margin-top:14px; max-width:560px;">
        <div class="member-info-card">
          <div class="member-info-header">
            <h2>회원정보</h2>
          </div>

          <div class="member-info-body">
            <div class="form-group">
              <label>이름</label>
              <input type="text" value="<?= h($name) ?>" readonly>
            </div>

            <div class="form-group">
              <label>아이디</label>
              <input type="text" value="<?= h($accountNo) ?>" readonly>
            </div>

            <!-- 아래 항목들은 현재 MEMBER_APPLY에 없어서 빈값 처리(필요하면 테이블/API 확장) -->
            <div class="form-group">
              <label>성별</label>
              <input type="text" value="" readonly>
            </div>

            <div class="form-group">
              <label>생년월일</label>
              <input type="text" value="" readonly>
            </div>

            <div class="form-group">
              <label>연락처</label>
              <input type="text" value="<?= h($phone) ?>" readonly>
            </div>

            <div class="form-group">
              <label>이메일</label>
              <input type="text" value="<?= h($accountNo) ?>" readonly>
            </div>

            <div class="form-group">
              <label>주소</label>
              <input type="text" value="<?= h($address) ?>" readonly>
            </div>

            <div class="form-group">
              <label>총 보유페이</label>
              <input type="text" value="" readonly>
            </div>

            <div class="form-group">
              <label>총 보유SP</label>
              <input type="text" value="" readonly>
            </div>

            <div class="form-group">
              <label>총 보유LP</label>
              <input type="text" value="" readonly>
            </div>

            <div class="form-group">
              <label>추천인</label>
              <input type="text" value="<?= h(trim($refId . ($refName ? " ({$refName})" : ""))) ?>" readonly>
            </div>

            <div class="form-group">
              <label>가입일</label>
              <input type="text" value="<?= h($joinStr) ?>" readonly>
            </div>
          </div>
        </div>
      </section>
    </main>
  </div>
</div>

<style>
.member-info-card {
  background: #fff;
  border-radius: 18px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0,0,0,0.06);
}
.member-info-header {
  padding: 22px 24px;
  background: linear-gradient(135deg,#b7cdfc 0%,#cfd8ff 45%,#e4c6ff 100%);
}
.member-info-header h2 {
  margin: 0;
  font-size: 20px;
  font-weight: 700;
  color: #111827;
}
.member-info-body { padding: 22px 24px 26px; }
.form-group { margin-bottom: 18px; }
.form-group label {
  display: block;
  font-size: 14px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 8px;
}
.form-group input {
  width: 100%;
  height: 48px;
  padding: 0 14px;
  border-radius: 12px;
  border: 1.5px solid #d1d5db;
  font-size: 16px;
  background: #fff;
  color: #111827;
}
.form-group input[readonly] { background:#fff; }
</style>

<script>
  const sidebarToggle = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('sidebar');
  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', () => sidebar.classList.toggle('open'));
    document.addEventListener('click', (e) => {
      const t = e.target;
      if (!sidebar.contains(t) && !sidebarToggle.contains(t) && window.innerWidth <= 768) {
        sidebar.classList.remove('open');
      }
    });
  }
</script>

</body>
</html>
