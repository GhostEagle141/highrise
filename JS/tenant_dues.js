// =========================================
// Highrise – Tenant Dues JS
// =========================================

document.addEventListener('DOMContentLoaded', function () {

  const tbody        = document.getElementById('duesBody');
  const searchInput  = document.getElementById('searchInput');
  const filterStatus = document.getElementById('filterStatus');
  const filterMonth  = document.getElementById('filterMonth');
  const filterYear   = document.getElementById('filterYear');
  const btnApply     = document.getElementById('btnApply');
  const btnReset     = document.getElementById('btnReset');
  const noResults    = document.getElementById('noResults');
  const countPaid    = document.getElementById('countPaid');
  const countUnpaid  = document.getElementById('countUnpaid');
  const countTotal   = document.getElementById('countTotal');

  let allTenants = [];
  const today    = new Date();
  today.setHours(0, 0, 0, 0);

  const MONTHS = ['', 'January', 'February', 'March', 'April', 'May', 'June',
                  'July', 'August', 'September', 'October', 'November', 'December'];

  // ---- Initial load (latest data) ----
  fetchData();

  // ---- Events ----
  btnApply.addEventListener('click', function () {
    fetchData(filterMonth.value, filterYear.value);
  });

  btnReset.addEventListener('click', function () {
    filterMonth.value = '';
    filterYear.value  = '';
    fetchData();
  });

  searchInput.addEventListener('input', applyLocalFilters);
  filterStatus.addEventListener('change', applyLocalFilters);

  // ---- Fetch ----
  function fetchData(month, year) {
    setLoading(true);

    let url = 'WS/WS_Fetch_Tenant_Dues.php';
    const params = [];
    if (month) params.push('month=' + month);
    if (year)  params.push('year='  + year);
    if (params.length) url += '?' + params.join('&');

    fetch(url)
      .then(function (r) { return r.json(); })
      .then(function (res) {
        setLoading(false);
        if (!res.success) { showError(res.error); return; }

        allTenants = res.data;
        populateYearDropdown(res.available);

        // Set dropdowns to active period
        if (res.active.month) filterMonth.value = res.active.month;
        if (res.active.year)  filterYear.value  = res.active.year;

        updateSummary(allTenants);
        renderTable(allTenants);
      })
      .catch(function () {
        setLoading(false);
        showError('Network error. Could not load tenant dues.');
      });
  }

  // ---- Populate year dropdown from available dates ----
  function populateYearDropdown(available) {
    const years = [...new Set(available.map(function (a) { return a.year; }))];
    filterYear.innerHTML = '<option value="">All Years</option>';
    years.forEach(function (y) {
      const opt = document.createElement('option');
      opt.value       = y;
      opt.textContent = y;
      filterYear.appendChild(opt);
    });
  }

  // ---- Local filters (search + status) ----
  function applyLocalFilters() {
    const query  = searchInput.value.trim().toLowerCase();
    const status = filterStatus.value;

    const filtered = allTenants.filter(function (t) {
      const matchSearch = t.tenant_name.toLowerCase().includes(query);
      const overdue     = isOverdue(t);
      const matchStatus = status === 'all'    ? true
                        : status === 'unpaid' ? overdue
                        : status === 'paid'   ? !overdue
                        : true;
      return matchSearch && matchStatus;
    });

    renderTable(filtered);
  }

  // ---- Render ----
  function renderTable(data) {
    tbody.innerHTML = '';

    if (data.length === 0) {
      noResults.style.display = 'block';
      return;
    }
    noResults.style.display = 'none';

    data.forEach(function (t, idx) {
      const overdue = isOverdue(t);
      const tr      = document.createElement('tr');
      tr.className  = overdue ? 'row-unpaid' : '';

      tr.innerHTML = `
        <td>${idx + 1}</td>
        <td>
          ${t.tenant_name}
          ${overdue ? '<span class="overdue-flag" title="Payment overdue">!</span>' : ''}
        </td>
        <td>${formatAmount(t.due_amount)}</td>
        <td>${formatAmount(t.advance_amount)}</td>
        <td>${formatDate(t.due_date)}</td>
        <td>
          <span class="badge ${overdue ? 'badge--unpaid' : 'badge--paid'}">
            <span class="badge__dot"></span>
            ${overdue ? 'Overdue' : 'Clear'}
          </span>
        </td>
      `;
      tbody.appendChild(tr);
    });
  }

  // ---- Summary ----
  function updateSummary(data) {
    const unpaid = data.filter(isOverdue).length;
    countPaid.textContent   = data.length - unpaid;
    countUnpaid.textContent = unpaid;
    countTotal.textContent  = data.length;
  }

  // ---- Helpers ----
  function isOverdue(t) {
    if (!t.due_date) return false;
    const due = new Date(t.due_date);
    due.setHours(0, 0, 0, 0);
    return parseFloat(t.due_amount) !== 0 && due < today;
  }

  function formatAmount(val) {
    const num = parseFloat(val);
    if (isNaN(num)) return '—';
    return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function formatDate(dateStr) {
    if (!dateStr) return '—';
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
  }

  function setLoading(on) {
    tbody.innerHTML = on
      ? `<tr><td colspan="6" style="text-align:center;padding:30px;color:#8FA0B4;">Loading...</td></tr>`
      : '';
  }

  function showError(msg) {
    tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;color:#B43232;padding:30px;">${msg}</td></tr>`;
  }

});
