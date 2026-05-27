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

// =========================================
// Budget Pie Chart
// =========================================

(function () {

  const select       = document.getElementById('budgetProjectSelect');
  const legendWrap   = document.getElementById('budgetLegend');
  let   budgetChart      = null;
  let   res_groups_cache = [];

  // Color palette — blues and complementary tones
  const COLORS = [
    '#1B3F72', '#2B5BAD', '#4A7CC7', '#7AAEDD',
    '#A8C4E8', '#6B7FA3', '#3D5A80', '#8FB4D4',
    '#1A5276', '#2874A6', '#5499C7', '#85C1E9'
  ];

  // ---- Load projects into dropdown ----
  fetch('WS/WS_Fetch_Projects.php')
    .then(function (r) { return r.json(); })
    .then(function (res) {
      select.innerHTML = '';
      if (res.success && res.data.length) {
        res.data.forEach(function (p) {
          const opt = document.createElement('option');
          opt.value       = p.ID;
          opt.textContent = p.Name;
          if (parseInt(p.ID) === 1) opt.selected = true;
          select.appendChild(opt);
        });
      }
      loadChart(select.value || 1);
    })
    .catch(function () {
      select.innerHTML = '<option value="1">Project 1</option>';
      loadChart(1);
    });

  select.addEventListener('change', function () {
    loadChart(this.value);
  });

  // ---- Load chart data ----
  function loadChart(projectId) {
    fetch('WS/WS_Fetch_Budget_Chart.php?project_id=' + projectId)
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (!res.success || !res.labels.length) {
          legendWrap.innerHTML = '<p style="color:#8FA0B4;font-size:.82rem;">No budget data found.</p>';
          return;
        }
        // Store group IDs directly from response
        res_groups_cache = res.groups || [];
        renderChart(res.labels, res.values, res.group_ids || []);
      })
      .catch(function () {
        legendWrap.innerHTML = '<p style="color:#B43232;font-size:.82rem;">Failed to load data.</p>';
      });
  }

  // ---- Render ----
  function renderChart(labels, values, groupIds) {
    const total  = values.reduce(function (a, b) { return a + b; }, 0);
    const colors = labels.map(function (_, i) { return COLORS[i % COLORS.length]; });

    // Destroy existing chart if re-loading
    if (budgetChart) { budgetChart.destroy(); }

    const ctx = document.getElementById('chartBudget2').getContext('2d');
    budgetChart = new Chart(ctx, {
      type: 'pie',
      data: {
        labels: labels,
        datasets: [{
          data: values,
          backgroundColor: colors,
          borderWidth: 2,
          borderColor: '#fff',
          hoverOffset: 8
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        onClick: function (e, elements) {
          if (!elements.length) return;
          const idx     = elements[0].index;
          const groupId = groupIds[idx];
          if (groupId && window.filterCostChartByGroup) {
            window.filterCostChartByGroup(groupId, select.value);
          }
        },
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: function (ctx) {
                const pct = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
                return ' ' + formatAmount(ctx.parsed) + ' (' + pct + '%)';
              }
            }
          }
        },
        animation: { duration: 600, easing: 'easeInOutQuart' }
      }
    });

    // Build legend
    legendWrap.innerHTML = '';
    labels.forEach(function (label, i) {
      const pct  = total > 0 ? ((values[i] / total) * 100).toFixed(1) : 0;
      const item = document.createElement('div');
      item.className = 'budget-legend-item';
      item.innerHTML =
        '<div class="budget-legend-top">' +
          '<span class="budget-legend-dot" style="background:' + colors[i] + '"></span>' +
          '<span class="budget-legend-name" title="' + label + '">' + label + '</span>' +
        '</div>' +
        '<strong>' + formatAmount(values[i]) + ' <span style="color:#8FA0B4;font-weight:300;">(' + pct + '%)</span></strong>';
      legendWrap.appendChild(item);
    });
  }

  function formatAmount(val) {
    return val.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
  }

})();

// =========================================
// Cost Group Breakdown Pie Chart
// =========================================

(function () {

  const groupSelect  = document.getElementById('costGroupSelect');
  const legendWrap   = document.getElementById('costGroupLegend');
  let   costChart    = null;
  let   currentProject = 1;

  const COLORS = [
    '#1B3F72', '#2B5BAD', '#4A7CC7', '#7AAEDD',
    '#A8C4E8', '#6B7FA3', '#3D5A80', '#8FB4D4',
    '#1A5276', '#2874A6', '#5499C7', '#85C1E9'
  ];

  // ---- Initial load ----
  loadCostChart(null, 1);

  // ---- Dropdown change ----
  groupSelect.addEventListener('change', function () {
    loadCostChart(this.value, currentProject);
  });

  // ---- Expose function so budget chart can trigger it on click ----
  window.filterCostChartByGroup = function (groupId, projectId) {
    currentProject = projectId || currentProject;
    groupSelect.value = groupId;
    loadCostChart(groupId, currentProject);

    // Scroll to cost breakdown chart
    document.getElementById('chartCostGroup').closest('.chart-card').scrollIntoView({
      behavior: 'smooth', block: 'start'
    });
  };

  function loadCostChart(groupId, projectId) {
    currentProject = projectId || currentProject;
    let url = 'WS/WS_Fetch_Cost_Group_Chart.php?project_id=' + currentProject;
    if (groupId) url += '&cost_group_id=' + groupId;

    legendWrap.innerHTML = '<p style="color:#8FA0B4;font-size:.82rem;padding:10px 0;">Loading...</p>';

    fetch(url)
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (!res.success) { showCostError(res.error); return; }

        // Populate dropdown
        if (res.groups && res.groups.length) {
          groupSelect.innerHTML = '';
          res.groups.forEach(function (g) {
            const opt = document.createElement('option');
            opt.value       = g.id;
            opt.textContent = g.name;
            if (parseInt(g.id) === parseInt(res.active_group)) opt.selected = true;
            groupSelect.appendChild(opt);
          });
        }

        if (!res.labels.length) {
          legendWrap.innerHTML = '<p style="color:#8FA0B4;font-size:.82rem;">No data for this group.</p>';
          return;
        }

        renderCostChart(res.labels, res.values);
      })
      .catch(function () { showCostError('Network error.'); });
  }

  function renderCostChart(labels, values) {
    const total  = values.reduce(function (a, b) { return a + b; }, 0);
    const colors = labels.map(function (_, i) { return COLORS[i % COLORS.length]; });

    if (costChart) costChart.destroy();

    const ctx = document.getElementById('chartCostGroup').getContext('2d');
    costChart = new Chart(ctx, {
      type: 'pie',
      data: {
        labels: labels,
        datasets: [{
          data: values,
          backgroundColor: colors,
          borderWidth: 2,
          borderColor: '#fff',
          hoverOffset: 8
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: function (ctx) {
                const pct = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
                return ' ' + formatCostAmount(ctx.parsed) + ' (' + pct + '%)';
              }
            }
          }
        },
        animation: { duration: 600, easing: 'easeInOutQuart' }
      }
    });

    // Legend
    legendWrap.innerHTML = '';
    labels.forEach(function (label, i) {
      const pct  = total > 0 ? ((values[i] / total) * 100).toFixed(1) : 0;
      const item = document.createElement('div');
      item.className = 'budget-legend-item';
      item.innerHTML =
        '<div class="budget-legend-top">' +
          '<span class="budget-legend-dot" style="background:' + colors[i] + '"></span>' +
          '<span class="budget-legend-name" title="' + label + '">' + label + '</span>' +
        '</div>' +
        '<strong>' + formatCostAmount(values[i]) + ' <span style="color:#8FA0B4;font-weight:300;">(' + pct + '%)</span></strong>';
      legendWrap.appendChild(item);
    });
  }

  function showCostError(msg) {
    legendWrap.innerHTML = '<p style="color:#B43232;font-size:.82rem;">' + msg + '</p>';
  }

  function formatCostAmount(val) {
    return val.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
  }

})();
