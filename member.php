<?php include __DIR__ . "/head.php"; ?>
<?php include __DIR__ . "/member-list.php"; ?>

<div class="layout">
  <?php include __DIR__ . "/side.php"; ?>

  <div class="main">
    <header class="topbar">
      <div class="topbar-left">
        <button class="sidebar-toggle-btn" id="sidebarToggle" aria-label="메뉴 열기">☰</button>
        <div>
          <div class="topbar-title">회원 관리</div>
          <div class="topbar-subtitle">승인(APPROVED) 회원 목록 조회</div>
          <div class="breadcrumb">
            <span>홈</span>
            <span>회원 관리</span>
          </div>
        </div>
      </div>

      <div class="topbar-right">
        <form class="search-box" method="get" action="">
          <span class="search-icon">🔍</span>
          <input type="text" name="q" class="search-input" placeholder="이름 / 아이디 / 연락처 검색"
                 value="<?= htmlspecialchars($q ?? '', ENT_QUOTES) ?>" />
        </form>

        <div class="topbar-actions">
          <button class="icon-button" title="새로고침" onclick="location.href='member.php'">⟳</button>
        </div>
      </div>
    </header>

    <main class="content">
      <section class="card" style="margin-top:20px;">
        <div class="card-header">
          <div>
            <div class="card-title">회원 목록</div>
          </div>
        </div>

        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>아이디</th>
                <th>이름</th>
                <th>연락처</th>
                <th>가입일</th>
              </tr>
            </thead>

            <tbody>
              <?php if ($errorMsg): ?>
                <tr>
                  <td colspan="4" class="text-sm" style="padding:16px; color:#ef4444;">
                    <?= htmlspecialchars($errorMsg, ENT_QUOTES) ?>
                  </td>
                </tr>

              <?php elseif (empty($memberList)): ?>
                <tr>
                  <td colspan="4" class="text-sm" style="padding:16px; color:#6b7280;">
                    회원이 없습니다.
                  </td>
                </tr>

              <?php else: ?>
                <?php foreach ($memberList as $row): ?>
                  <?php
                    $accountNo = $row['ACCOUNT_NO'] ?? '';
                    $name      = $row['NAME'] ?? '';
                    $phone     = $row['PHONE'] ?? '';
                    $createdAt = $row['CREATED_AT'] ?? '';
                    $dateStr   = $createdAt ? date('y-m-d H:i', strtotime($createdAt)) : '';
                  ?>
                  <tr>
                    <td class="text-sm"><?= htmlspecialchars($accountNo, ENT_QUOTES) ?></td>
                    <td class="text-sm"><?= htmlspecialchars($name, ENT_QUOTES) ?></td>
                    <td class="text-sm"><?= htmlspecialchars($phone, ENT_QUOTES) ?></td>
                    <td class="text-sm"><?= htmlspecialchars($dateStr, ENT_QUOTES) ?></td>
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

            $range = 2;
            $start = max(1, $page - $range);
            $end   = min($totalPages, $page + $range);

            while (($end - $start) < ($range * 2) && $start > 1) $start--;
            while (($end - $start) < ($range * 2) && $end < $totalPages) $end++;

            $makeUrl = function(int $p) use ($baseParams) {
              $params = $baseParams;
              $params['page'] = $p;
              return 'member.php?' . http_build_query($params);
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
</script>

</body>
</html>
