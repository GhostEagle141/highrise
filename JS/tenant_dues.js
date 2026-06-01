// =========================================
// Highrise – Tenant Dues JS
// =========================================

document.addEventListener('DOMContentLoaded', function () {

  const tbody        = document.getElementById('duesBody');
  const searchInput  = document.getElementById('searchInput');
  const filterMonth  = document.getElementById('filterMonth');
  const filterYear   = document.getElementById('filterYear');
  const btnApply     = document.getElementById('btnApply');
  const btnReset     = document.getElementById('btnReset');
  const noResults    = document.getElementById('noResults');

  let allTenants = [];

  const MONTHS = ['', 'January', 'February', 'March', 'April', 'May', 'June',
                  'July', 'August', 'September', 'October', 'November', 'December'];

  fetchData();

  btnApply.addEventListener('click', function () { fetchData(filterMonth.value, filterYear.value); });
  btnReset.addEventListener('click', function () {
    filterMonth.value = '';
    filterYear.value  = '';
    searchInput.value = '';
    fetchData();
  });
  searchInput.addEventListener('input', function () {
    const q = this.value.trim().toLowerCase();
    const filtered = allTenants.filter(t => t.tenant_name.toLowerCase().includes(q));
    renderTable(filtered);
  });

  function fetchData(month, year) {
    setLoading(true);
    let url = 'WS/WS_Fetch_Tenant_Dues.php';
    const p = [];
    if (month) p.push('month=' + month);
    if (year)  p.push('year='  + year);
    if (p.length) url += '?' + p.join('&');

    fetch(url)
      .then(function (r) { return r.json(); })
      .then(function (res) {
        setLoading(false);
        if (!res.success) { showError(res.error); return; }
        allTenants = res.data;
        if (res.active.month) filterMonth.value = res.active.month;
        if (res.active.year)  filterYear.value  = res.active.year;
        populateYearDropdown(res.available);
        updateSummary(allTenants);
        renderTable(allTenants);
      })
      .catch(function () { setLoading(false); showError('Network error.'); });
  }

  function populateYearDropdown(available) {
    const years   = [...new Set(available.map(a => a.year))];
    const current = filterYear.value;
    filterYear.innerHTML = '<option value="">All Years</option>';
    years.forEach(function (y) {
      const opt = document.createElement('option');
      opt.value = y; opt.textContent = y;
      if (y == current) opt.selected = true;
      filterYear.appendChild(opt);
    });
  }

  function renderTable(data) {
    tbody.innerHTML = '';
    noResults.style.display = data.length === 0 ? 'block' : 'none';

    data.forEach(function (t, i) {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${i + 1}</td>
        <td>${t.tenant_name}</td>
        <td class="amount-cell">${formatAmount(t.due_amount)}</td>
        <td>${t.due_date ? formatDate(t.due_date) : '—'}</td>
      `;
      tbody.appendChild(tr);
    });
  }

  function updateSummary(data) {
    const totalDue  = data.reduce(function (s, t) { return s + (parseFloat(t.due_amount)     || 0); }, 0);
    const totalPaid = data.reduce(function (s, t) { return s + (parseFloat(t.advance_amount) || 0); }, 0);
    const elPaid = document.getElementById('summaryTotalPaid');
    const elDue  = document.getElementById('summaryTotalDue');
    if (elPaid) elPaid.textContent = '$' + totalPaid.toLocaleString('en-US', { minimumFractionDigits: 2 });
    if (elDue)  elDue.textContent  = '$' + totalDue.toLocaleString('en-US',  { minimumFractionDigits: 2 });
  }

  function formatAmount(val) {
    const n = parseFloat(val);
    return isNaN(n) ? '—' : n.toLocaleString('en-US', { minimumFractionDigits: 2 });
  }

  function formatDate(d) {
    return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
  }

  function setLoading(on) {
    if (on) tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:30px;color:#8FA0B4;">Loading...</td></tr>';
  }

  function showError(msg) {
    tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;color:#B43232;padding:30px;">${msg}</td></tr>`;
  }

});
