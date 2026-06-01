// =========================================
// Highrise – Expense Mapping JS
// =========================================

document.addEventListener('DOMContentLoaded', function () {

  const tbody       = document.getElementById('mappingBody');
  const searchInput = document.getElementById('searchInput');
  const btnSaveAll  = document.getElementById('btnSaveAll');
  const statMapped  = document.getElementById('statMapped');
  const statUnmapped = document.getElementById('statUnmapped');

  let allExpenses = [];
  let budgetItems = [];
  let changedRows = {}; // expense id -> cost id

  // ---- Load data ----
  Promise.all([
    fetch('WS/WS_Fetch_Expenses_List.php').then(r => r.json()),
    fetch('WS/WS_Fetch_Costs_List.php').then(r => r.json())
  ]).then(function ([expRes, costRes]) {
    if (!expRes.success || !costRes.success) {
      tbody.innerHTML = '<tr><td colspan="4" class="loading-row">Failed to load data.</td></tr>';
      return;
    }
    allExpenses = expRes.data;
    budgetItems = costRes.data;
    renderTable(allExpenses);
    updateStats();
  }).catch(function () {
    tbody.innerHTML = '<tr><td colspan="4" class="loading-row">Network error.</td></tr>';
  });

  // ---- Search ----
  searchInput.addEventListener('input', function () {
    const q = this.value.trim().toLowerCase();
    const filtered = allExpenses.filter(e => e.name.toLowerCase().includes(q));
    renderTable(filtered);
  });

  // ---- Save All ----
  btnSaveAll.addEventListener('click', function () {
    // TODO: POST changedRows to WS/WS_Save_Expense_Mapping.php
    console.log('Mappings to save:', changedRows);
  });

  // ---- Render ----
  function renderTable(data) {
    tbody.innerHTML = '';

    if (!data.length) {
      tbody.innerHTML = '<tr><td colspan="4" class="loading-row">No expenses found.</td></tr>';
      return;
    }

    data.forEach(function (exp) {
      const tr = document.createElement('tr');
      tr.className  = 'row-unmapped';
      tr.dataset.id = exp.id;

      let options = '<option value="">— Not mapped —</option>';
      budgetItems.forEach(function (cost) {
        options += `<option value="${cost.id}">${cost.group_name} → ${cost.name}</option>`;
      });

      tr.innerHTML = `
        <td class="expense-name">${exp.name}</td>
        <td class="account-no">${exp.related_Account_no || '—'}</td>
        <td>
          <select class="budget-select" data-expense-id="${exp.id}">
            ${options}
          </select>
        </td>
        <td>
          <span class="status-badge status-badge--unmapped">
            <span class="status-badge__dot"></span>
            Unmapped
          </span>
        </td>
      `;

      const select = tr.querySelector('.budget-select');
      select.addEventListener('change', function () {
        const costId = this.value;
        changedRows[exp.id] = costId || null;

        const badge = tr.querySelector('.status-badge');
        if (costId) {
          tr.className       = 'row-mapped';
          this.classList.add('is-mapped');
          badge.className    = 'status-badge status-badge--mapped';
          badge.innerHTML    = '<span class="status-badge__dot"></span>Mapped';
        } else {
          tr.className          = 'row-unmapped';
          this.classList.remove('is-mapped');
          badge.className       = 'status-badge status-badge--unmapped';
          badge.innerHTML       = '<span class="status-badge__dot"></span>Unmapped';
        }

        btnSaveAll.disabled = Object.keys(changedRows).length === 0;
        updateStats();
      });

      tbody.appendChild(tr);
    });
  }

  function updateStats() {
    const mapped   = document.querySelectorAll('.row-mapped').length;
    const unmapped = document.querySelectorAll('.row-unmapped').length;
    statMapped.textContent   = mapped   + ' mapped';
    statUnmapped.textContent = unmapped + ' unmapped';
  }

});
