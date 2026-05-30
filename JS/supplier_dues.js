// =========================================
// Highrise – Supplier Dues JS
// =========================================

document.addEventListener('DOMContentLoaded', function () {

  const tbody        = document.getElementById('supplierBody');
  const searchInput  = document.getElementById('searchInput');
  const filterMonth  = document.getElementById('filterMonth');
  const filterYear   = document.getElementById('filterYear');
  const btnApply     = document.getElementById('btnApply');
  const btnReset     = document.getElementById('btnReset');
  const noResults    = document.getElementById('noResults');
  const summaryCount = document.getElementById('summaryCount');
  const summaryPaid  = document.getElementById('summaryPaid');
  const summaryDue   = document.getElementById('summaryDue');

  let currentData = [];
  let sortCol     = 'due_date';
  let sortDir     = 'asc';
  let searchQuery = '';

  // ---- Initial load ----
  fetchData();

  // ---- Events ----
  btnApply.addEventListener('click', function () { fetchData(filterMonth.value, filterYear.value); });
  btnReset.addEventListener('click', function () {
    filterMonth.value = '';
    filterYear.value  = '';
    searchQuery = '';
    searchInput.value = '';
    fetchData();
  });
  searchInput.addEventListener('input', function () {
    searchQuery = this.value.trim().toLowerCase();
    renderTable(currentData);
  });

  // ---- Sorting ----
  document.querySelectorAll('.dues-table th.sortable').forEach(function (th) {
    th.addEventListener('click', function () {
      const col = th.dataset.col;
      sortDir = sortCol === col ? (sortDir === 'asc' ? 'desc' : 'asc') : 'asc';
      sortCol = col;
      document.querySelectorAll('.dues-table th.sortable').forEach(function (t) {
        t.classList.remove('sort-asc', 'sort-desc');
        t.querySelector('.sort-icon').textContent = '↕';
      });
      th.classList.add('sort-' + sortDir);
      th.querySelector('.sort-icon').textContent = sortDir === 'asc' ? '↑' : '↓';
      renderTable(currentData);
    });
  });

  // ---- Fetch ----
  function fetchData(month, year) {
    setLoading(true);
    let url = 'WS/WS_Fetch_Supplier_Dues.php';
    const p = [];
    if (month) p.push('month=' + month);
    if (year)  p.push('year='  + year);
    if (p.length) url += '?' + p.join('&');

    fetch(url)
      .then(function (r) { return r.json(); })
      .then(function (res) {
        setLoading(false);
        if (!res.success) { showError(res.error); return; }

        currentData = res.data;

        // Set active filters
        if (res.active.month) filterMonth.value = res.active.month;
        if (res.active.year)  filterYear.value  = res.active.year;

        // Populate year dropdown
        const years = [...new Set(res.available.map(function (a) { return a.year; }))];
        const curYear = filterYear.value;
        filterYear.innerHTML = '<option value="">All Years</option>';
        years.forEach(function (y) {
          const opt = document.createElement('option');
          opt.value = y; opt.textContent = y;
          if (y == curYear) opt.selected = true;
          filterYear.appendChild(opt);
        });

        renderTable(currentData);
      })
      .catch(function () { setLoading(false); showError('Network error.'); });
  }

  // ---- Render ----
  function renderTable(data) {
    let filtered = data;
    if (searchQuery) {
      filtered = data.filter(function (r) {
        return (r.supplier_name || '').toLowerCase().includes(searchQuery) ||
               (r.supplier_id   || '').toLowerCase().includes(searchQuery);
      });
    }

    filtered = sortData(filtered);
    tbody.innerHTML = '';
    noResults.style.display = 'none';

    if (!filtered.length) {
      noResults.style.display = 'block';
      updateSummary([]);
      return;
    }

    filtered.forEach(function (r, i) {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${i + 1}</td>
        <td style="font-family:monospace;font-size:0.82rem;">${r.supplier_id || '—'}</td>
        <td>${r.supplier_name || '—'}</td>
        <td><span class="currency-badge">${r.currency || '—'}</span></td>
        <td class="amount-cell">${fmt(r.paid_amount)}</td>
        <td class="amount-cell">${fmt(r.due_amount)}</td>
        <td class="amount-cell">${fmt(r.advance_amount)}</td>
        <td>${r.due_date ? formatDate(r.due_date) : '—'}</td>
      `;
      tbody.appendChild(tr);
    });

    updateSummary(filtered);
  }

  function updateSummary(data) {
    const totalPaid = data.reduce(function (s, r) { return s + parseFloat(r.paid_amount || 0); }, 0);
    const totalDue  = data.reduce(function (s, r) { return s + parseFloat(r.due_amount  || 0); }, 0);
    summaryCount.textContent = data.length.toLocaleString('en-US');
    summaryPaid.textContent  = '$' + totalPaid.toLocaleString('en-US', { minimumFractionDigits: 2 });
    summaryDue.textContent   = '$' + totalDue.toLocaleString('en-US',  { minimumFractionDigits: 2 });
  }

  function sortData(data) {
    return [...data].sort(function (a, b) {
      let av = a[sortCol] !== undefined ? a[sortCol] : '';
      let bv = b[sortCol] !== undefined ? b[sortCol] : '';
      const numCols = ['paid_amount', 'due_amount', 'advance_amount'];
      if (numCols.includes(sortCol)) {
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

  function fmt(val) {
    const n = parseFloat(val);
    if (isNaN(n)) return '—';
    return n.toLocaleString('en-US', { minimumFractionDigits: 2 });
  }

  function formatDate(d) {
    return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
  }

  function setLoading(on) {
    if (on) tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:30px;color:#8FA0B4;">Loading...</td></tr>';
  }

  function showError(msg) {
    tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;color:#B43232;padding:30px;">${msg}</td></tr>`;
  }

});
