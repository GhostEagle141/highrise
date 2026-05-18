// =========================================
// Highrise – Tenant Dues JS
// Example data — replace with DB fetch
// =========================================

document.addEventListener('DOMContentLoaded', function () {

  // TODO: replace with fetch from WS/WS_Info_Fetch.php
  const tenants = [
    { id: 1,  name: 'Georges Khoury',    phone: '+961 70 123 456', due: '$1,200', date: '2025-05-02' },
    { id: 2,  name: 'Lara Haddad',       phone: '+961 71 234 567', due: '$950',   date: '2025-05-05' },
    { id: 3,  name: 'Rami Nassar',       phone: '+961 76 345 678', due: '$1,100', date: null          },
    { id: 4,  name: 'Maya Frem',         phone: '+961 78 456 789', due: '$800',   date: '2025-05-08' },
    { id: 5,  name: 'Charbel Abi Nader', phone: '+961 81 567 890', due: '$1,350', date: null          },
    { id: 6,  name: 'Nadia Saab',        phone: '+961 70 678 901', due: '$900',   date: '2025-05-03' },
    { id: 7,  name: 'Elie Gemayel',      phone: '+961 71 789 012', due: '$1,050', date: null          },
    { id: 8,  name: 'Joelle Bou Khalil', phone: '+961 76 890 123', due: '$750',   date: '2025-05-10' },
    { id: 9,  name: 'Tony Karam',        phone: '+961 78 901 234', due: '$1,200', date: '2025-05-01' },
    { id: 10, name: 'Sana Moukalled',    phone: '+961 81 012 345', due: '$880',   date: null          },
  ];

  const tbody       = document.getElementById('duesBody');
  const searchInput = document.getElementById('searchInput');
  const filterSelect = document.getElementById('filterSelect') || document.getElementById('filterStatus');
  const noResults   = document.getElementById('noResults');
  const countPaid   = document.getElementById('countPaid');
  const countUnpaid = document.getElementById('countUnpaid');
  const countTotal  = document.getElementById('countTotal');

  // Update summary pills
  const paid   = tenants.filter(t => t.date).length;
  const unpaid = tenants.filter(t => !t.date).length;
  countPaid.textContent   = paid;
  countUnpaid.textContent = unpaid;
  countTotal.textContent  = tenants.length;

  function renderTable(data) {
    tbody.innerHTML = '';

    if (data.length === 0) {
      noResults.style.display = 'block';
      return;
    }

    noResults.style.display = 'none';

    data.forEach(function (t) {
      const isPaid = !!t.date;
      const tr = document.createElement('tr');
      tr.className = isPaid ? '' : 'row-unpaid';

      tr.innerHTML = `
        <td>${t.id}</td>
        <td>${t.name}</td>
        <td>${t.phone}</td>
        <td>${t.due}</td>
        <td>${isPaid
          ? formatDate(t.date)
          : '<span class="no-date">Not paid</span>'
        }</td>
        <td>
          <span class="badge ${isPaid ? 'badge--paid' : 'badge--unpaid'}">
            <span class="badge__dot"></span>
            ${isPaid ? 'Paid' : 'Unpaid'}
          </span>
        </td>
      `;

      tbody.appendChild(tr);
    });
  }

  function filterData() {
    const query  = searchInput.value.trim().toLowerCase();
    const status = filterSelect.value;

    const filtered = tenants.filter(function (t) {
      const matchSearch = t.name.toLowerCase().includes(query) ||
                          t.phone.includes(query);
      const matchStatus = status === 'all'   ? true :
                          status === 'paid'   ? !!t.date :
                          status === 'unpaid' ? !t.date : true;
      return matchSearch && matchStatus;
    });

    renderTable(filtered);
  }

  function formatDate(dateStr) {
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
  }

  // Events
  searchInput.addEventListener('input', filterData);
  filterSelect.addEventListener('change', filterData);

  // Initial render
  renderTable(tenants);

});
