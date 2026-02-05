<?php include __DIR__ . "/head.php"; ?>

<div class="layout">
  <!-- ===== 사이드바 ===== -->
  <?php include __DIR__ . "/side.php"; ?>

  <!-- ===== 메인 영역 ===== -->
  <div class="main">
    <!-- 상단바 -->
    <header class="topbar">
      <div class="topbar-left">
        <button class="sidebar-toggle-btn" id="sidebarToggle" aria-label="메뉴 열기">☰</button>

        <div>
          <div class="topbar-title">회원 관리</div>
          <div class="topbar-subtitle">회원 목록 조회 및 등급/상태 관리를 할 수 있습니다.</div>
          <div class="breadcrumb">
            <span>홈</span>
            <span>회원 관리</span>
          </div>
        </div>
      </div>

      <div class="topbar-right">
        <div class="search-box">
          <span class="search-icon">🔍</span>
          <input
            type="text"
            class="search-input"
            id="searchInput"
            placeholder="이름, 아이디, 연락처 검색"
          />
        </div>

        <div class="topbar-actions">
          <button class="icon-button" title="새로고침" id="refreshBtn">⟳</button>
        </div>
      </div>
    </header>

    <!-- 컨텐츠 -->
    <main class="content">
      <section class="card" style="margin-top:20px;">
        <div class="card-header">
          <div>
            <div class="card-title">회원 목록</div>
            <div class="card-subtitle">승인된 회원만 표시됩니다.</div>
          </div>
        </div>

        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th><input type="checkbox" id="checkAll" /></th>
                <th>회원번호</th>
                <th>아이디 / 이름</th>
                <th>연락처</th>
                <th>가입일</th>
              </tr>
            </thead>


            <tbody id="memberTableBody"></tbody>
          </table>
        </div>
        <div class="pagination" id="pagination"
            style="display:flex; gap:6px; justify-content:flex-end; padding:12px 16px;">
        </div>
      </section>
    </main>
  </div>
</div>

<script>
  // ======================
  // 사이드바 토글 (모바일)
  // ======================
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

  const API_URL = '/member-list.php';

  const tableBody = document.getElementById('memberTableBody');
  const searchInput = document.getElementById('searchInput');
  const refreshBtn = document.getElementById('refreshBtn');
  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');
  const pageInfo = document.getElementById('pageInfo');
  const checkAll = document.getElementById('checkAll');
  const pagination = document.getElementById('pagination');

  let currentPage = 1;
  const limit = 20;
  let total = 0;

  function escapeHtml(str) {
    return String(str ?? '')
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  function formatDateTime(createdAt) {
    const s = String(createdAt || '');
    const [d, t] = s.split(' ');
    if (!d) return '-';
    if (!t) return d;
    return `${d}<br><span class="text-sm text-muted">${t}</span>`;
  }

  function setPagination() {
    const totalPages = Math.max(1, Math.ceil(total / limit));
    pageInfo.textContent = `${currentPage} / ${totalPages}`;

    prevBtn.disabled = currentPage <= 1;
    nextBtn.disabled = currentPage >= totalPages;
  }

  function renderTable(list) {
    tableBody.innerHTML = '';

    if (!list || list.length === 0) {
      tableBody.innerHTML = `
        <tr>
          <td colspan="5" class="text-center text-muted" style="padding:20px;">
            검색 결과가 없습니다.
          </td>
        </tr>
      `;
      return;
    }

    for (const m of list) {
      const accountNo = escapeHtml(m.ACCOUNT_NO);
      const name = escapeHtml(m.NAME);
      const phone = escapeHtml(m.PHONE);
      const createdAt = formatDateTime(m.CREATED_AT);

      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td><input type="checkbox" class="rowCheck" /></td>
        <td class="text-sm">${accountNo}</td>
        <td class="text-sm">
          ${accountNo}<br />
          <span class="text-muted text-sm">${name}</span>
        </td>
        <td class="text-sm">${phone || '-'}</td>
        <td class="text-sm">${createdAt}</td>
      `;
      tableBody.appendChild(tr);
    }
  }

  async function fetchMembers(page = 1) {
    const search = searchInput.value.trim();

    const body = new URLSearchParams({
      page: String(page),
      limit: String(limit),
      search
    });

    const res = await fetch(API_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body
    });

    const json = await res.json();

    // ✅ 너네 jsonResponse가 code/resCode 뭐 쓰는지 몰라서 둘 다 대응
    const code = (json.code ?? json.resCode ?? 1);

    if (code !== 0) {
      alert(json.message || '회원 목록을 불러오지 못했습니다.');
      return;
    }

    const list = json.data ?? json.list ?? [];
    total = Number(json.total ?? 0);

    currentPage = page;
    renderTable(list);
    setPagination();

    // 전체선택 체크 해제
    if (checkAll) checkAll.checked = false;

    renderPagination();
  }

  // ======================
  // 이벤트
  // ======================
  document.addEventListener('DOMContentLoaded', () => {
    fetchMembers(1);
  });

  // 엔터 검색
  searchInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') fetchMembers(1);
  });

  // 새로고침
  refreshBtn.addEventListener('click', () => fetchMembers(1));

  // 이전/다음
  prevBtn.addEventListener('click', () => {
    if (currentPage > 1) fetchMembers(currentPage - 1);
  });

  nextBtn.addEventListener('click', () => {
    const totalPages = Math.max(1, Math.ceil(total / limit));
    if (currentPage < totalPages) fetchMembers(currentPage + 1);
  });

  // 전체 선택
  if (checkAll) {
    checkAll.addEventListener('change', () => {
      document.querySelectorAll('.rowCheck').forEach(chk => chk.checked = checkAll.checked);
    });
  }

   function renderPagination() {
    pagination.innerHTML = '';

    const totalPages = Math.max(1, Math.ceil(total / limit));
    const maxVisible = 5; // 한 번에 보일 페이지 수
    let start = Math.max(1, currentPage - Math.floor(maxVisible / 2));
    let end = start + maxVisible - 1;

    if (end > totalPages) {
      end = totalPages;
      start = Math.max(1, end - maxVisible + 1);
    }

    // 이전 버튼
    const prevBtn = document.createElement('button');
    prevBtn.textContent = '이전';
    prevBtn.className = 'pill';
    prevBtn.disabled = currentPage === 1;
    prevBtn.onclick = () => fetchMembers(currentPage - 1);
    pagination.appendChild(prevBtn);

    // 페이지 번호
    for (let i = start; i <= end; i++) {
      const btn = document.createElement('button');
      btn.textContent = i;
      btn.className = 'pill';

      if (i === currentPage) {
        btn.style.background = '#333';
        btn.style.color = '#fff';
        btn.disabled = true;
      } else {
        btn.onclick = () => fetchMembers(i);
      }

      pagination.appendChild(btn);
    }

    // 다음 버튼
    const nextBtn = document.createElement('button');
    nextBtn.textContent = '다음';
    nextBtn.className = 'pill';
    nextBtn.disabled = currentPage === totalPages;
    nextBtn.onclick = () => fetchMembers(currentPage + 1);
    pagination.appendChild(nextBtn);
  }

</script>

</body>
</html>
