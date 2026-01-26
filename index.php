<?php 
include __DIR__ . "/head.php"; 

$apiUrl = 'https://api.thxdeal.com/api/point/total.php';


$postData = [ 
    'typeCodes'  => 'SP,TP,LP',          
];

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL            => $apiUrl,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query($postData),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 10,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/x-www-form-urlencoded'
    ],
]);

$response = curl_exec($ch);

if ($response === false) {
    $error = curl_error($ch);
    curl_close($ch);
    die('cURL Error: ' . $error);
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// JSON 파싱
$data = json_decode($response, true);
print_r($data);
if ($httpCode !== 200 || !$data || $data['code'] !== RES_SUCCESS) {
    // 실패 처리
    echo '<pre>';
    print_r($data);
    echo '</pre>';
    exit;
}

// 성공 시 결과
$totals = $data['data']['total'];

$sp = $totals['SP'] ?? 0;
$tp = $totals['TP'] ?? 0;
$lp = $totals['LP'] ?? 0;

?>
<div class="layout">
  <!-- ===== 사이드바 ===== -->
    <?php include __DIR__ . "/side.php"; ?>

  <!-- ===== 메인 영역 ===== -->
  <div class="main">
    <!-- 상단바 -->
    <header class="topbar">
      <div class="topbar-left">
        <!-- 모바일에서만 보이는 햄버거 버튼 -->
        <button class="sidebar-toggle-btn" id="sidebarToggle" aria-label="메뉴 열기">
          ☰
        </button>

        <div>
          <div class="topbar-title">대시보드</div>
          <div class="topbar-subtitle">오늘 기준 주요 지표를 확인하세요.</div>
          <div class="breadcrumb">
            <span>홈</span>
            <span>대시보드</span>
          </div>
        </div>
      </div>      
    </header>

    <!-- 컨텐츠 -->
    <main class="content">
      <!-- 요약 카드 -->
      <section class="summary-grid">
        <article class="summary-card">
          <div class="summary-label">총 TP</div>
          <div class="summary-value"><?= number_format($tp) ?></div>
        </article>

        <article class="summary-card">
          <div class="summary-label">총 SP</div>
          <div class="summary-value"><?= number_format($sp) ?></div>         
        </article>

        <article class="summary-card">
          <div class="summary-label">총 LP</div>
          <div class="summary-value"><?= number_format($lp) ?></div>
        </article>

        <article class="summary-card">
          <div class="summary-label">TP출금</div>
          <div class="summary-value">0</div>
        </article>
      </section>

      <!-- 주문 / 상품 영역 -->
      <!-- <section>
        <section class="card">
          <div class="card-header">
            <div>
              <div class="card-title">최근 주문</div>
              <div class="card-subtitle">최근 7일간 주문 내역 일부만 표시됩니다.</div>
            </div>
            <div class="card-actions">
              <button class="pill">전체보기</button>
            </div>
          </div>

          <div class="table-wrapper">
            <table>
              <thead>
              <tr>
                <th>주문일시</th>
                <th>주문번호</th>
                <th>주문자</th>
                <th>결제금액</th>
                <th>상태</th>
                <th>배송</th>
              </tr>
              </thead>
              <tbody>
              <tr>
                <td>2025-12-22<br><span class="text-sm text-muted">14:32</span></td>
                <td class="text-sm">20251222-00041</td>
                <td class="text-sm">홍길동</td>
                <td class="text-right">₩ 32,000</td>
                <td><span class="badge paid">결제완료</span></td>
                <td class="text-sm text-muted">준비중</td>
              </tr>
              <tr>
                <td>2025-12-22<br><span class="text-sm text-muted">13:18</span></td>
                <td class="text-sm">20251222-00040</td>
                <td class="text-sm">김영희</td>
                <td class="text-right">₩ 18,500</td>
                <td><span class="badge.ship-ready badge">배송준비</span></td>
                <td class="text-sm text-muted">택배</td>
              </tr>
              <tr>
                <td>2025-12-22<br><span class="text-sm text-muted">11:07</span></td>
                <td class="text-sm">20251222-00039</td>
                <td class="text-sm">이민수</td>
                <td class="text-right">₩ 74,900</td>
                <td><span class="badge pending">입금대기</span></td>
                <td class="text-sm text-muted">무통장</td>
              </tr>
              <tr>
                <td>2025-12-21<br><span class="text-sm text-muted">17:55</span></td>
                <td class="text-sm">20251221-00038</td>
                <td class="text-sm">박지수</td>
                <td class="text-right">₩ 42,500</td>
                <td><span class="badge cancel">주문취소</span></td>
                <td class="text-sm text-muted">고객요청</td>
              </tr>
              </tbody>
            </table>
          </div>
        </section> 
      </section> -->
    </main>
  </div>
</div>

<script>
  // 사이드바 토글 (모바일)
  const sidebarToggle = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('sidebar');

  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', function () {
      sidebar.classList.toggle('open');
    });

    // 사이드바 외부 클릭 시 닫기 (모바일용)
    document.addEventListener('click', function (e) {
      const target = e.target;
      const isClickInsideSidebar = sidebar.contains(target);
      const isClickToggle = sidebarToggle.contains(target);

      if (!isClickInsideSidebar && !isClickToggle && window.innerWidth <= 768) {
        sidebar.classList.remove('open');
      }
    });
  }
</script>

</body>
</html>
