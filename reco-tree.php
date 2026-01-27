<?php
// admin_sponsor_tree.php 같은 파일명으로 사용
$myAccountNo = "youbr919@naver.com" ?? null; // TODO: 로그인 계정으로 교체
if (!$myAccountNo) {
    echo "로그인이 필요합니다.";
    exit;
}

$searchInput = isset($_GET['accountNo']) ? trim($_GET['accountNo']) : '';
$shouldFetch = ($searchInput !== ''); // 검색했을 때만 true

$errorMsg = '';
$root = null;
$levels = [];

if ($shouldFetch) {
    $rootAccountNo = $searchInput;

    $postFields = [
        'accountNo' => $rootAccountNo,
    ];

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
    
                // downline은 1대(직추천) 리스트
                $levels = [];
                $levels[1] = [];
    
                $list = $payload['downline']['list'] ?? [];
                foreach ($list as $row) {
                    $levels[1][] = [
                        'name'      => $row['name'] ?? '',
                        'accountNo' => $row['accountNo'] ?? '',
                        'userId'    => null,
                        'dept'      => null,
                        'deptNo'    => null,
                    ];
                }
            }
        }
    }   
}
?>

<?php include __DIR__ . "/head.php"; ?>

<style>
/* ✅ 퍼블리싱(필요하면 head.css로 이동) */
.tree-search-form{display:flex;gap:10px;align-items:center}
.tree-search-input{width:360px;max-width:52vw}
.tree-container{margin-top:18px}
.tree-level{margin-top:14px}
.tree-level-label{margin:18px 0 8px;font-weight:800;color:#111}
.tree-row{display:flex;flex-wrap:wrap;gap:12px;justify-content:center}
.tree-node-card{
  width:220px;
  border:1px solid rgba(0,0,0,.08);
  border-radius:14px;
  padding:12px 14px;
  background:#fff;
  box-shadow:0 6px 18px rgba(0,0,0,.06);
}
.tree-node-root{border:1px solid rgba(22,163,74,.35); box-shadow:0 10px 22px rgba(22,163,74,.12);}
.node-index{display:inline-block;font-size:12px;font-weight:800;opacity:.75;margin-bottom:6px}
.tree-node-name{font-size:15px;font-weight:900;margin-bottom:4px}
.tree-node-account{font-size:13px;opacity:.8;word-break:break-all}
.tree-hint{
  padding:14px 16px;
  border-radius:14px;
  background:rgba(2, 132, 199, .08);
  border:1px solid rgba(2, 132, 199, .18);
  color:#0f172a;
}
.tree-error-text{margin-top:12px;color:#ef4444;font-weight:800}
.tree-empty-text{margin-top:12px;color:#64748b;font-weight:700}
</style>

<div class="main">
  <?php include __DIR__ . "/side.php"; ?>

  <header class="topbar">
    <div class="topbar-left">
      <button class="sidebar-toggle-btn" id="sidebarToggle" aria-label="메뉴 열기">☰</button>

      <div>
        <div class="topbar-title">후원 계보</div>
        <div class="topbar-subtitle">계정을 검색하면 해당 계정을 루트로 아래 3대까지 후원 계보를 조회합니다.</div>
        <div class="breadcrumb">
          <span>홈</span>
          <span>후원 계보</span>
        </div>
      </div>
    </div>

    <div class="topbar-right">
      <div class="topbar-actions">
        <button class="icon-button" type="button" title="새로고침" onclick="location.href='<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) ?>';">⟳</button>
      </div>
    </div>
  </header>

  <main class="content">
    <section class="card">
      <div class="card-header">
        <div>
          <div class="card-title">계정 검색</div>
          <div class="card-subtitle">
            조회할 계정(accountNo)을 입력하고 검색하세요. (검색 후 아래 3대까지 표시)
          </div>
        </div>

        <form class="tree-search-form" method="get">
          <input type="text"
                 name="accountNo"
                 class="form-input tree-search-input"
                 placeholder="예) youbr919@naver.com"
                 value="<?= htmlspecialchars($searchInput ?? '', ENT_QUOTES) ?>">
          <button type="submit" class="primary-button tree-search-button">검색</button>
        </form>
      </div>

      <?php if (!$shouldFetch): ?>
        <!-- ✅ 검색 전: 안내만 -->
        <div class="tree-hint">
          상단 검색창에 계정(accountNo)을 입력하면 해당 계정의 후원 계보가 표시됩니다.
        </div>

      <?php elseif (!empty($errorMsg)): ?>
        <!-- ✅ 검색 후 에러 -->
        <p class="tree-error-text"><?= htmlspecialchars($errorMsg, ENT_QUOTES) ?></p>

      <?php elseif (!$root): ?>
        <!-- ✅ 검색 후인데 루트가 없음 -->
        <p class="tree-empty-text">루트 정보를 가져오지 못했습니다.</p>

      <?php else: ?>
        <!-- ✅ 검색 성공: 트리 표시 -->
        <div class="tree-container">

          <!-- 루트 -->
          <div class="tree-level">
            <div class="tree-row">
              <div class="tree-node-card tree-node-root">
                <span class="node-index">(<?= htmlspecialchars($root['userId'] ?? ''); ?>)</span>
                <div class="tree-node-name"><?= htmlspecialchars($root['name'] ?? '', ENT_QUOTES) ?></div>
                <div class="tree-node-account"><?= htmlspecialchars($root['accountNo'] ?? '', ENT_QUOTES) ?></div>
              </div>
            </div>
          </div>

          <!-- 하위 3대 -->
          <?php if (empty($levels)): ?>
            <p class="tree-empty-text">표시할 후원인 계보가 없습니다. (밑으로 3대가 없음)</p>
          <?php else: ?>
            <?php foreach ($levels as $relDepth => $nodes): ?>
              <div class="tree-level-label" style="text-align:center">
                <?= (int)$relDepth ?>대
              </div>
              <div class="tree-level">
                <div class="tree-row">
                  <?php foreach ($nodes as $n): ?>
                    <div class="tree-node-card">
                      <span class="node-index">(<?= htmlspecialchars($n['userId'] ?? ''); ?>)</span>
                      <div class="tree-node-name"><?= htmlspecialchars($n['name'] ?? '', ENT_QUOTES) ?></div>
                      <div class="tree-node-account"><?= htmlspecialchars($n['accountNo'] ?? '', ENT_QUOTES) ?></div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>

        </div>
      <?php endif; ?>

    </section>
  </main>
</div>
