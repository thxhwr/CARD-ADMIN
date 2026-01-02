<?php
$myAccountNo = "youbr919@naver.com" ?? null;
if (!$myAccountNo) {
    echo "로그인이 필요합니다.";
    exit;
}

$searchInput   = isset($_GET['accountNo']) ? trim($_GET['accountNo']) : null;
$rootAccountNo = ($searchInput === null || $searchInput === '')
    ? $myAccountNo
    : $searchInput;

$errorMsg = '';
$root     = null;
$levels   = [];

$postFields = [
    'accountNo' => $rootAccountNo,
];

$ch = curl_init('https://api.thxdeal.com/api/member/testMemberSpon.php');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $postFields,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
]);

$response = curl_exec($ch);
if ($response === false) {
    $errorMsg = "API 호출 실패: " . curl_error($ch);
}
curl_close($ch);


if (!$errorMsg) {
    $data = json_decode($response, true);

    if (!is_array($data) || ($data['resCode'] ?? -1) !== 0 || empty($data['data'])) {
        // 없는 계정이라고 가정
        $errorMsg = "존재하지 않는 계정입니다.";
    } else {
        $root = $data['data'];
    }
}

if ($root) {
    // relDepth: 1 = 자식, 2 = 손자, 3 = 증손
    function collectDescendants3Gen(array $node, int $relDepth, array &$levels, int $maxDepth = 3)
    {
        if ($relDepth >= 1 && $relDepth <= $maxDepth) {
            if (!isset($levels[$relDepth])) {
                $levels[$relDepth] = [];
            }

            $levels[$relDepth][] = [
                'name'      => $node['name']      ?? '',
                'accountNo' => $node['accountNo'] ?? '',
                'userId'    => $node['userId']    ?? null,
                'dept'      => $node['dept']      ?? null,   // 줄 번호
                'deptNo'    => $node['deptNo']    ?? null,   // 그 줄에서 순서
            ];
        }

        if ($relDepth >= $maxDepth) {
            return;
        }

        if (!empty($node['children']) && is_array($node['children'])) {
            foreach ($node['children'] as $child) {
                collectDescendants3Gen($child, $relDepth + 1, $levels, $maxDepth);
            }
        }
    }

    collectDescendants3Gen($root, 0, $levels, 3);

    // 각 세대 안에서 deptNo 순으로 정렬
    foreach ($levels as $relDepth => &$nodes) {
        usort($nodes, function ($a, $b) {
            return ($a['deptNo'] ?? 0) <=> ($b['deptNo'] ?? 0);
        });
    }
    unset($nodes);

    ksort($levels);
}
?>
<?php include __DIR__ . "/head.php"; ?>
<div class="main">
  <?php include __DIR__ . "/side.php"; ?>
  <header class="topbar">
    <div class="topbar-left">
      <button class="sidebar-toggle-btn" id="sidebarToggle" aria-label="메뉴 열기">
        ☰
      </button>

      <div>
        <div class="topbar-title">후원 계보</div>
        <div class="topbar-subtitle">기준 계정을 루트로 아래 3대까지 후원 계보를 조회합니다.</div>
        <div class="breadcrumb">
          <span>홈</span>
          <span>후원 계보</span>
        </div>
      </div>
    </div>

    <div class="topbar-right">
      <!-- 필요하면 우측에 다른 액션 추가 -->
      <!-- 지금은 비워두거나 새로고침 버튼 정도만 -->
      <div class="topbar-actions">
        <button class="icon-button" type="button" title="새로고침" onclick="location.reload();">
          ⟳
        </button>
      </div>
    </div>
  </header>

  <main class="content">

    <!-- 기준 계정 + 검색 -->
    <section class="card">
      <div class="card-header">
        <div>
          <div class="card-title">기준 계정</div>
          <div class="card-subtitle">
            기준 계정:
            <strong><?= htmlspecialchars($rootAccountNo, ENT_QUOTES) ?></strong>
            <span class="text-sm text-muted"> (이 계정을 루트로 아래 3대까지만 표시)</span>
          </div>
        </div>

        <!-- 오른쪽에 검색 폼 -->
        <form class="tree-search-form" method="get">
          <input type="text"
                 name="accountNo"
                 class="form-input tree-search-input"
                 placeholder="계보를 보고 싶은 계정(accountNo)을 입력"
                 value="<?= htmlspecialchars($searchInput ?? '', ENT_QUOTES) ?>">
          <button type="submit" class="primary-button tree-search-button">
            검색
          </button>
        </form>
      </div>

      <!-- 에러 / 빈 데이터 안내 -->
      <?php if (!empty($errorMsg)): ?>
        <p class="tree-error-text">
          <?= htmlspecialchars($errorMsg, ENT_QUOTES) ?>
        </p>

      <?php elseif (!$root): ?>
        <p class="tree-empty-text">
          루트 정보를 가져오지 못했습니다.
        </p>

      <?php else:
        ?>

        <!-- 트리 영역 전체 감싸는 컨테이너 -->
        <div class="tree-container">

          <!-- 루트 (나 또는 검색한 계정) -->
          <div class="tree-level">
            <div class="tree-row">
              <div class="tree-node-card tree-node-root">
                <span class="node-index">(<?= htmlspecialchars($root['userId'] ?? ''); ?>)</span>
                <div class="tree-node-name">
                  <?= htmlspecialchars($root['name'] ?? '', ENT_QUOTES) ?>
                </div>
                <!-- <div class="tree-node-meta">
                  줄: <?= (int)($root['dept'] ?? 0) ?>
                  · 순서 <?= (int)($root['deptNo'] ?? 0) ?>
                </div> -->
                <div class="tree-node-account">
                  <?= htmlspecialchars($root['accountNo'] ?? '', ENT_QUOTES) ?>
                </div>
              </div>
            </div>
          </div>

          <!-- 루트 기준 아래 3대 -->
          <?php if (empty($levels)): ?>
            <p class="tree-empty-text">
              표시할 후원인 계보가 없습니다. (밑으로 3대가 없음)
            </p>
          <?php else: ?>
            <?php foreach ($levels as $relDepth => $nodes): ?>
                <div class="tree-level-label" style="text-align:center">
                  <?= (int)$relDepth + 1?>대
                </div>
              <div class="tree-level">

                <div class="tree-row">
                  <?php
                  foreach ($nodes as $n): ?>
                    <div class="tree-node-card">
                      <span class="node-index">(<?= htmlspecialchars($n['userId'] ?? ''); ?>)</span>
                      <div class="tree-node-name">
                        <?= htmlspecialchars($n['name'], ENT_QUOTES) ?>
                      </div>
                      <!-- <div class="tree-node-meta">
                        줄: <?= (int)($n['dept'] ?? 0) ?>
                        · 순서 <?= (int)($n['deptNo'] ?? 0) ?>
                      </div> -->
                      <div class="tree-node-account">
                        <?= htmlspecialchars($n['accountNo'], ENT_QUOTES) ?>
                      </div>
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
