// =========================================
// Highrise – Financial Details JS
// =========================================

document.addEventListener('DOMContentLoaded', function () {

  const tbody        = document.getElementById('financialBody');
  const filterGroup  = document.getElementById('filterGroup');
  const filterMonth  = document.getElementById('filterMonth');
  const filterYear   = document.getElementById('filterYear');
  const btnApply     = document.getElementById('btnApply');
  const btnReset     = document.getElementById('btnReset');
  const noResults    = document.getElementById('noResults');
  const summaryCount = document.getElementById('summaryCount');
  const summaryTotal = document.getElementById('summaryTotal');

  // Read URL params
  const params    = new URLSearchParams(window.location.search);
  const initGroup = params.get('cost_group_id') || '';
  const initMonth = params.get('month')          || '';
  const initYear  = params.get('year')           || '';

  let currentData = [];
  let sortCol     = 'trans_date';
  let sortDir     = 'desc';
  let searchQuery = '';

  // ---- Add search bar dynamically ----
  const toolbar = document.querySelector('.date-toolbar');
  const searchWrap = document.createElement('div');
  searchWrap.className = 'search-wrap';
  searchWrap.style.flex = '1';
  searchWrap.innerHTML = `
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
      <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
    </svg>
    <input type="text" id="searchInput" class="search-input" placeholder="Search expense..." />
  `;
  toolbar.insertBefore(searchWrap, toolbar.firstChild);

  document.getElementById('searchInput').addEventListener('input', function () {
    searchQuery = this.value.trim().toLowerCase();
    renderTable(currentData);
  });

  // ---- Initial load ----
  fetch('WS/WS_Fetch_Financial_Details.php')
    .then(function (r) { return r.json(); })
    .then(function (res) {
      if (!res.success) { showError(res.error); return; }

      res.groups.forEach(function (g) {
        const opt = document.createElement('option');
        opt.value = g.id; opt.textContent = g.name;
        filterGroup.appendChild(opt);
      });

      const years = [...new Set(res.data.map(function (r) {
        return r.trans_date ? r.trans_date.substring(0, 4) : null;
      }).filter(Boolean))].sort().reverse();

      years.forEach(function (y) {
        const opt = document.createElement('option');
        opt.value = y; opt.textContent = y;
        filterYear.appendChild(opt);
      });

      if (initGroup) filterGroup.value = initGroup;
      if (initMonth) filterMonth.value = initMonth;
      if (initYear)  filterYear.value  = initYear;

      if (initGroup || initMonth || initYear) {
        loadData();
      } else {
        currentData = res.data;
        renderTable(currentData);
      }
    })
    .catch(function () { showError('Network error.'); });

  // ---- Filter events ----
  btnApply.addEventListener('click', loadData);
  btnReset.addEventListener('click', function () {
    filterGroup.value = '';
    filterMonth.value = '';
    filterYear.value  = '';
    searchQuery = '';
    document.getElementById('searchInput').value = '';
    loadData();
  });

  // ---- Sorting ----
  document.querySelectorAll('.dues-table th.sortable').forEach(function (th) {
    th.addEventListener('click', function () {
      const col = th.dataset.col;
      if (sortCol === col) {
        sortDir = sortDir === 'asc' ? 'desc' : 'asc';
      } else {
        sortCol = col;
        sortDir = 'asc';
      }
      document.querySelectorAll('.dues-table th.sortable').forEach(function (t) {
        t.classList.remove('sort-asc', 'sort-desc');
        t.querySelector('.sort-icon').textContent = '↕';
      });
      th.classList.add('sort-' + sortDir);
      th.querySelector('.sort-icon').textContent = sortDir === 'asc' ? '↑' : '↓';
      renderTable(currentData);
    });
  });

  // ---- Load with filters ----
  function loadData() {
    setLoading(true);
    let url = 'WS/WS_Fetch_Financial_Details.php';
    const p = [];
    if (filterGroup.value) p.push('cost_group_id=' + filterGroup.value);
    if (filterMonth.value) p.push('month='         + filterMonth.value);
    if (filterYear.value)  p.push('year='          + filterYear.value);
    if (p.length) url += '?' + p.join('&');

    fetch(url)
      .then(function (r) { return r.json(); })
      .then(function (res) {
        setLoading(false);
        if (!res.success) { showError(res.error); return; }
        currentData = res.data;
        renderTable(currentData);
      })
      .catch(function () { setLoading(false); showError('Network error.'); });
  }

  // ---- Render ----
  function renderTable(data) {
    // Apply search filter
    let filtered = data;
    if (searchQuery) {
      filtered = data.filter(function (r) {
        return (r.expense_name  || '').toLowerCase().includes(searchQuery) ||
               (r.account_no   || '').toLowerCase().includes(searchQuery) ||
               (r.currency     || '').toLowerCase().includes(searchQuery);
      });
    }

    // Apply sort
    filtered = sortData(filtered);

    tbody.innerHTML = '';
    noResults.style.display = 'none';

    if (!filtered.length) {
      noResults.style.display = 'block';
      summaryCount.textContent = '0';
      summaryTotal.textContent = '$0.00';
      return;
    }

    let totalUSD = 0;

    filtered.forEach(function (r, i) {
      const tr  = document.createElement('tr');
      const usd = parseFloat(r.amount_usd) || 0;
      totalUSD += usd;

      tr.innerHTML = `
        <td>${i + 1}</td>
        <td style="font-family:monospace;font-size:0.82rem;">${r.account_no || '—'}</td>
        <td>${r.expense_name || '—'}</td>
        <td class="amount-cell">${parseFloat(r.amount).toLocaleString('en-US', { minimumFractionDigits: 2 })}</td>
        <td><span class="currency-badge">${r.currency}</span></td>
        <td class="amount-cell">$${usd.toLocaleString('en-US', { minimumFractionDigits: 2 })}</td>
        <td>${r.trans_date ? formatDate(r.trans_date) : '—'}</td>
      `;
      tbody.appendChild(tr);
    });

    summaryCount.textContent = filtered.length.toLocaleString('en-US');
    summaryTotal.textContent = '$' + totalUSD.toLocaleString('en-US', { minimumFractionDigits: 2 });
  }

  function sortData(data) {
    return [...data].sort(function (a, b) {
      let av = a[sortCol] !== undefined ? a[sortCol] : '';
      let bv = b[sortCol] !== undefined ? b[sortCol] : '';
      if (sortCol === 'amount' || sortCol === 'amount_usd') {
        av = parseFloat(av) || 0;
        bv = parseFloat(bv) || 0;
        return sortDir === 'asc' ? av - bv : bv - av;
      }
      av = String(av).toLowerCase();
      bv = String(bv).toLowerCase();
      if (av < bv) return sortDir === 'asc' ? -1 :  1;
      if (av > bv) return sortDir === 'asc' ?  1 : -1;
      return 0;
    });
  }

  function formatDate(d) {
    const dt = new Date(d);
    return dt.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
  }

  function setLoading(on) {
    if (on) tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:30px;color:#8FA0B4;">Loading...</td></tr>';
  }

  function showError(msg) {
    tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;color:#B43232;padding:30px;">${msg}</td></tr>`;
  }

});
