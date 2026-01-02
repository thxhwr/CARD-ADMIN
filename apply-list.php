<?php include __DIR__ . "/head.php"; ?>
<?php include __DIR__ . "/list-get.php"; ?>
<div class="layout">
  <!-- ===== 사이드바 ===== -->
 <?php include __DIR__ . "/side.php"; ?>

  <!-- ===== 메인 영역 ===== -->
  <div class="main">
    <!-- 상단바 -->
    <header class="topbar">
      <div class="topbar-left">
        <!-- 모바일 햄버거 버튼 -->
        <button class="sidebar-toggle-btn" id="sidebarToggle" aria-label="메뉴 열기">
          ☰
        </button>

        <div>
          <div class="topbar-title">신청 관리</div>
          <div class="topbar-subtitle">신청 목록 조회 및 등급/상태 관리를 할 수 있습니다.</div>
          <div class="breadcrumb">
            <span>홈</span>
            <span>신청 관리</span>
          </div>
        </div>
      </div>

      <div class="topbar-right">
        <div class="search-box">
         <form class="search-box" method="get" action="">
        <span class="search-icon">🔍</span>
        <input type="text" name="q" class="search-input" placeholder="이름 / 아이디 검색"
                value="<?= htmlspecialchars($q ?? '', ENT_QUOTES) ?>" />
        </form>
        </div>

        <div class="topbar-actions">
          <button class="icon-button" title="새로고침" onclick="location.href='apply-list.php'">
            ⟳
          </button>
          <div class="user-chip">
            <div class="user-avatar"></div>
            <div>
              <div class="user-name">관리자</div>
              <div class="user-role">마스터</div>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- 컨텐츠 -->
    <main class="content">
      <!-- 회원 목록 테이블 -->
      <section class="card" style="margin-top:20px;">
        <div class="card-header">
          <div>
            <div class="card-title">신청 목록</div>
          </div>
        </div>

        <div class="table-wrapper">
          <table>
            <thead>
            <tr>
                <th>회원번호</th>
                <th>아이디 / 이름</th>
                <th>추천인 아이디 / 이름</th>
                <th>신청일</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
                <?php if ($errorMsg): ?>
                <tr>
                    <td colspan="5" class="text-sm" style="padding:16px; color:#ef4444;">
                    <?= htmlspecialchars($errorMsg, ENT_QUOTES) ?>
                    </td>
                </tr>

                <?php elseif (empty($appList)): ?>
                <tr>
                    <td colspan="5" class="text-sm" style="padding:16px; color:#6b7280;">
                    신청 내역이 없습니다.
                    </td>
                </tr>

                <?php else: ?>
                
                <?php foreach ($appList as $row): ?>
                    <?php
                        $applyId   = $row['APPLY_ID'] ?? '';
                        $accountNo = $row['ACCOUNT_NO'] ?? '';
                        $name      = $row['NAME'] ?? '';
                        $createdAt = $row['CREATED_AT'] ?? '';
                        $status    = $row['STATUS'] ?? '';
                        $REFERRER_USER_ID = $row['REFERRER_USER_ID'];
                        $REFERRER_NAME = $row['REFERRER_NAME'];

                        $dateStr = $createdAt ? date('y-m-d H:i', strtotime($createdAt)) : '';
                    ?>
                    <tr>
                        <td class="text-sm"><?= htmlspecialchars($applyId, ENT_QUOTES) ?></td>

                        <td class="text-sm">
                          <?= htmlspecialchars($accountNo, ENT_QUOTES) ?><br />
                          <span class="text-muted text-sm"><?= htmlspecialchars($name, ENT_QUOTES) ?></span>
                        </td>
                        <td class="text-sm">
                          <?= htmlspecialchars($REFERRER_USER_ID, ENT_QUOTES) ?><br />
                           <span class="text-muted text-sm"><?= htmlspecialchars($REFERRER_NAME, ENT_QUOTES) ?></span>
                        </td>
                        <td><?= htmlspecialchars($dateStr, ENT_QUOTES) ?></td>

                        <td>
                        <button type="button"
                                class="btn-confirm"
                                data-id="<?= htmlspecialchars($applyId, ENT_QUOTES) ?>"
                                data-account="<?= htmlspecialchars($accountNo, ENT_QUOTES) ?>"
                                style="background:#111;color:#fff;padding:10px 14px;border-radius:10px;"
                                >
                            승인
                        </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>

                <?php endif; ?>
                </tbody>
          </table>
        </div>
        <?php if (!$errorMsg && $totalPages > 1): ?>
          <?php
            $baseParams = [];
            if ($q !== '') $baseParams['q'] = $q;

            $range = 2; // 현재 기준 앞뒤로 2페이지씩 노출
            $start = max(1, $page - $range);
            $end   = min($totalPages, $page + $range);

            // 페이지 개수 5개 정도 유지하려고 보정
            while (($end - $start) < ($range * 2) && $start > 1) $start--;
            while (($end - $start) < ($range * 2) && $end < $totalPages) $end++;

            $makeUrl = function(int $p) use ($baseParams) {
              $params = $baseParams;
              $params['page'] = $p;
              return 'apply-list.php?' . http_build_query($params);
            };
          ?>

          <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:14px;flex-wrap:wrap;">
            <div class="text-sm" style="color:#6b7280;">
              총 <strong><?= (int)$totalLine ?></strong>건 · <?= (int)$page ?> / <?= (int)$totalPages ?> 페이지
            </div>

            <nav style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
              <a href="<?= $makeUrl(1) ?>"
                style="padding:8px 10px;border:1px solid #e5e7eb;border-radius:10px;text-decoration:none;color:#111;<?= $page<=1?'pointer-events:none;opacity:.4;':'' ?>">
                « 처음
              </a>
              <a href="<?= $makeUrl(max(1, $page-1)) ?>"
                style="padding:8px 10px;border:1px solid #e5e7eb;border-radius:10px;text-decoration:none;color:#111;<?= $page<=1?'pointer-events:none;opacity:.4;':'' ?>">
                ‹ 이전
              </a>

              <?php if ($start > 1): ?>
                <span style="padding:0 6px;color:#9ca3af;">…</span>
              <?php endif; ?>

              <?php for ($p = $start; $p <= $end; $p++): ?>
                <a href="<?= $makeUrl($p) ?>"
                  style="padding:8px 12px;border:1px solid #e5e7eb;border-radius:10px;text-decoration:none;<?= $p===$page?'background:#111;color:#fff;border-color:#111;':'color:#111;' ?>">
                  <?= $p ?>
                </a>
              <?php endfor; ?>

              <?php if ($end < $totalPages): ?>
                <span style="padding:0 6px;color:#9ca3af;">…</span>
              <?php endif; ?>

              <a href="<?= $makeUrl(min($totalPages, $page+1)) ?>"
                style="padding:8px 10px;border:1px solid #e5e7eb;border-radius:10px;text-decoration:none;color:#111;<?= $page>=$totalPages?'pointer-events:none;opacity:.4;':'' ?>">
                다음 ›
              </a>
              <a href="<?= $makeUrl($totalPages) ?>"
                style="padding:8px 10px;border:1px solid #e5e7eb;border-radius:10px;text-decoration:none;color:#111;<?= $page>=$totalPages?'pointer-events:none;opacity:.4;':'' ?>">
                끝 »
              </a>
            </nav>
          </div>
        <?php endif; ?>

      </section>

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

    document.addEventListener('click', function (e) {
      const target = e.target;
      const isClickInsideSidebar = sidebar.contains(target);
      const isClickToggle = sidebarToggle.contains(target);

      if (!isClickInsideSidebar && !isClickToggle && window.innerWidth <= 768) {
        sidebar.classList.remove('open');
      }
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.btn-confirm').forEach(btn => {
    btn.addEventListener('click', () => {
      const applyId = btn.dataset.id;

      if (!applyId) {
        alert('신청 ID 없음');
        return;
      }

      if (!confirm('해당 신청을 처리하시겠습니까?')) return;

      fetch('./approveApply.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'applyId=' + encodeURIComponent(applyId)
      })
      .then(res => res.json())
      .then(data => {
        if (data.ok) {
          // ✅ 성공 문구
          alert(data.message || '승인 완료');

          // ✅ 새로고침
          location.reload();
        } else {
          alert(data.message || '처리 실패');
        }
      })
    });
  });
});

</script>

</body>
</html>
