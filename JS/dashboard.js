// =========================================
// Highrise – Dashboard Charts
// Example data — replace with DB values
// =========================================

document.addEventListener('DOMContentLoaded', function () {

  // ---- Data (swap these out with DB values) ----
  const budget = {
    spent:     74500,   // TODO: from DB
    remaining: 45500    // TODO: from DB
  };

  const tenants = {
    paid:        34,    // TODO: from DB
    outstanding: 14     // TODO: from DB
  };

  // ---- Shared chart defaults ----
  const sharedOptions = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '72%',
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: function (ctx) {
            return ' ' + ctx.label + ': ' + ctx.formattedValue;
          }
        }
      }
    },
    animation: {
      animateRotate: true,
      duration: 700,
      easing: 'easeInOutQuart'
    }
  };

  // ---- Budget chart ----
  const ctxBudget = document.getElementById('chartBudget').getContext('2d');
  new Chart(ctxBudget, {
    type: 'doughnut',
    data: {
      labels: ['Spent', 'Remaining'],
      datasets: [{
        data: [budget.spent, budget.remaining],
        backgroundColor: ['#1B3F72', '#A8C4E8'],
        borderWidth: 0,
        hoverOffset: 6
      }]
    },
    options: sharedOptions
  });

  // ---- Tenants chart ----
  const ctxTenants = document.getElementById('chartTenants').getContext('2d');
  new Chart(ctxTenants, {
    type: 'doughnut',
    data: {
      labels: ['Paid', 'Outstanding'],
      datasets: [{
        data: [tenants.paid, tenants.outstanding],
        backgroundColor: ['#2B5BAD', '#E8A87C'],
        borderWidth: 0,
        hoverOffset: 6
      }]
    },
    options: sharedOptions
  });

});
