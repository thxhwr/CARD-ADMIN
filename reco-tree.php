<?php
$myAccountNo = "youbr919@naver.com" ?? null;
if (!$myAccountNo) { echo "로그인이 필요합니다."; exit; }

$searchInput = isset($_GET['accountNo']) ? trim($_GET['accountNo']) : '';
$shouldFetch = ($searchInput !== '');

$errorMsg = '';
$root = null;
$upline = [];
$levels = [1 => [], 2 => [], 3 => []];

if ($shouldFetch) {
    $rootAccountNo = strtolower(trim($searchInput));

    $postFields = ['accountNo' => $rootAccountNo];

    $ch = curl_init('https://api.thxdeal.com/api/member/memberRecoTree.php');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $postFields,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 10,
    ]);

    $response = curl_exec($ch);
    if ($response === false) {
        $errorMsg = "API 호출 실패: " . curl_error($ch);
    }
    curl_close($ch);

    if (!$errorMsg) {
        $data = json_decode($response, true);

        if (!is_array($data) || ($data['resCode'] ?? -1) !== 0 || empty($data['data'])) {
            $errorMsg = "존재하지 않는 계정입니다.";
        } else {
            $payload = $data['data'] ?? null;
            if (!$payload || empty($payload['target'])) {
                $errorMsg = "존재하지 않는 계정입니다.";
            } else {
                $root = $payload['target'];
                $upline = $payload['upline']['list'] ?? [];

                $lv1 = $payload['downline']['levels']['level1'] ?? [];
                $lv2 = $payload['downline']['levels']['level2'] ?? [];
                $lv3 = $payload['downline']['levels']['level3'] ?? [];

                foreach ($lv1 as $r) $levels[1][] = $r;
                foreach ($lv2 as $r) $levels[2][] = $r;
                foreach ($lv3 as $r) $levels[3][] = $r;
            }
        }
    }
}
?>

<?php include __DIR__ . "/head.php"; ?>

<style>
.tree-search-form{display:flex;gap:10px;align-items:center}
.tree-search-input{width:360px;max-width:52vw}

.tree-container{margin-top:18px}
.tree-level{margin-top:14px}
.tree-level-label{margin:18px 0 8px;font-weight:900;color:#111;text-align:center}
.tree-row{display:flex;flex-wrap:wrap;gap:12px;justify-content:center}

.tree-node-card{
  width:240px;
  border:1px solid rgba(0,0,0,.08);
  border-radius:14px;
  padding:12px 14px;
  background:#fff;
  box-shadow:0 6px 18px rgba(0,0,0,.06);
}
.tree-node-root{border:1px solid rgba(22,163,74,.35); box-shadow:0 10px 22px rgba(22,163,74,.12);}
.node-index{display:inline-block;font-size:12px;font-weight:900;opacity:.75;margin-bottom:6px}
.tree-node-name{font-size:15px;font-weight:900;margin-bottom:4px}
.tree-node-account{font-size:13px;opacity:.8;word-break:break-all}
.tree-node-meta{margin-top:6px;font-size:12px;opacity:.7;line-height:1.35}

.tree-hint{
  padding:14px 16px;
  border-radius:14px;
  background:rgba(2,132,199,.08);
  border:1px solid rgba(2,132,199,.18);
  color:#0f172a;
}
.tree-error-text{margin-top:12px;color:#ef4444;font-weight:900}
.tree-empty-text{margin-top:12px;color:#64748b;font-weight:800}
</style>

<div class="main">
  <?php include __DIR__ . "/side.php"; ?>

  <header class="topbar">
    <div class="topbar-left">
      <button class="sidebar-toggle-btn" id="sidebarToggle" aria-label="메뉴 열기">☰</button>
      <div>
        <div class="topbar-title">추천 계보</div>
        <div class="topbar-subtitle">계정을 검색하면 상위 3대 + 하위 3대 추천 계보를 표시합니다.</div>
        <div class="breadcrumb">
          <span>홈</span>
          <span>추천 계보</span>
        </div>
      </div>
    </div>
    <div class="topbar-right">
      <div class="topbar-actions">
        <button class="icon-button" type="button" title="초기화"
          onclick="location.href='<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) ?>';">⟳</button>
      </div>
    </div>
  </header>

  <main class="content">
    <section class="card">
      <div class="card-header">
        <div>
          <div class="card-title">계정 검색</div>
          <div class="card-subtitle">accountNo 입력 후 검색하면 계보가 표시됩니다.</div>
        </div>

        <form class="tree-search-form" method="get">
          <input type="text"
                 name="accountNo"
                 class="form-input tree-search-input"
                 placeholder="예) thx7929@gmail.com"
                 value="<?= htmlspecialchars($searchInput ?? '', ENT_QUOTES) ?>">
          <button type="submit" class="primary-button tree-search-button">검색</button>
        </form>
      </div>

      <?php if (!$shouldFetch): ?>
        <div class="tree-hint">검색 후 추천 계보가 표시됩니다.</div>

      <?php elseif (!empty($errorMsg)): ?>
        <p class="tree-error-text"><?= htmlspecialchars($errorMsg, ENT_QUOTES) ?></p>

      <?php elseif (!$root): ?>
        <p class="tree-empty-text">루트 정보를 가져오지 못했습니다.</p>

      <?php else: ?>

        <div class="tree-container">

            <?php if (!empty($upline[0])): ?>
                <div class="tree-level-label">상위 1대</div>
                <div class="tree-level">
                    <div class="tree-row">
                    <div class="tree-node-card">
                        <div class="tree-node-name">
                        <?= htmlspecialchars($upline[0]['name'] ?? '', ENT_QUOTES) ?>
                        </div>
                        <div class="tree-node-account">
                        <?= htmlspecialchars($upline[0]['accountNo'] ?? '', ENT_QUOTES) ?>
                        </div>
                    </div>
                    </div>
                </div>
            <?php endif; ?>

          <div class="tree-level-label">기준</div>
          <div class="tree-level">
            <div class="tree-row">
              <div class="tree-node-card tree-node-root">
                <span class="node-index">루트</span>
                <div class="tree-node-name"><?= htmlspecialchars($root['name'] ?? '', ENT_QUOTES) ?></div>
                <div class="tree-node-account"><?= htmlspecialchars($root['accountNo'] ?? '', ENT_QUOTES) ?></div>
                <div class="tree-node-meta">
                  추천인: <?= htmlspecialchars($root['referrerAccountNo'] ?? '', ENT_QUOTES) ?><br>
                </div>
              </div>
            </div>
          </div>

          <?php
            $hasAny = !empty($levels[1]) || !empty($levels[2]) || !empty($levels[3]);
          ?>

          <?php if (!$hasAny): ?>
            <p class="tree-empty-text">하위 추천 계보가 없습니다.</p>
          <?php else: ?>
            <?php for ($lvl = 1; $lvl <= 3; $lvl++): ?>
              <?php if (empty($levels[$lvl])) continue; ?>
              <div class="tree-level-label"><?= $lvl ?>대</div>
              <div class="tree-level">
                <div class="tree-row">
                  <?php foreach ($levels[$lvl] as $n): ?>
                    <div class="tree-node-card">
                      <div class="tree-node-name"><?= htmlspecialchars($n['name'] ?? '', ENT_QUOTES) ?></div>
                      <div class="tree-node-account"><?= htmlspecialchars($n['accountNo'] ?? '', ENT_QUOTES) ?></div>
                      <div class="tree-node-meta">
                        추천인: <?= htmlspecialchars($n['referrerAccountNo'] ?? '', ENT_QUOTES) ?><br>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endfor; ?>
          <?php endif; ?>

        </div>

      <?php endif; ?>
    </section>
  </main>
</div>
