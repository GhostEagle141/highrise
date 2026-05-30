// =========================================
// Highrise – Dashboard Charts
// =========================================

document.addEventListener('DOMContentLoaded', function () {

  // ---- Shared options ----
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
    animation: { animateRotate: true, duration: 700, easing: 'easeInOutQuart' }
  };

  // ---- Budget donut (placeholder) ----
  const ctxBudget = document.getElementById('chartBudget').getContext('2d');
  window._budgetDonut = new Chart(ctxBudget, {
    type: 'doughnut',
    data: {
      labels: ['Spent', 'Remaining'],
      datasets: [{ data: [62, 38], backgroundColor: ['#1B3F72', '#A8C4E8'], borderWidth: 0, hoverOffset: 6 }]
    },
    options: sharedOptions
  });

  // ---- Tenants donut (placeholder) ----
  const ctxTenants = document.getElementById('chartTenants').getContext('2d');
  window._tenantDonut = new Chart(ctxTenants, {
    type: 'doughnut',
    data: {
      labels: ['Paid', 'Outstanding'],
      datasets: [{ data: [71, 29], backgroundColor: ['#2B5BAD', '#E8A87C'], borderWidth: 0, hoverOffset: 6 }]
    },
    options: sharedOptions
  });

});

// Called by stats widget after real data loads
window.updateDashboardDonuts = function (totalBudget, totalExpenses, paidTenants, unpaidTenants) {
  const spent     = totalExpenses;
  const remaining = Math.max(totalBudget - totalExpenses, 0);
  const pct       = totalBudget > 0 ? Math.round((spent / totalBudget) * 100) : 0;

  if (window._budgetDonut) {
    window._budgetDonut.data.datasets[0].data = [spent, remaining];
    window._budgetDonut.update();
    const bc = document.getElementById('budgetCenter');
    if (bc) bc.querySelector('.donut-center__value').textContent = pct + '%';
    const ls = document.querySelector('.legend-item:nth-child(1) strong');
    const lr = document.querySelector('.legend-item:nth-child(2) strong');
    if (ls) ls.textContent = '$' + spent.toLocaleString('en-US', { minimumFractionDigits: 0 });
    if (lr) lr.textContent = '$' + remaining.toLocaleString('en-US', { minimumFractionDigits: 0 });
  }

  const tenantPct = (paidTenants + unpaidTenants) > 0
    ? Math.round((paidTenants / (paidTenants + unpaidTenants)) * 100) : 0;

  if (window._tenantDonut) {
    window._tenantDonut.data.datasets[0].data = [paidTenants, unpaidTenants];
    window._tenantDonut.update();
    const tc = document.getElementById('tenantsCenter');
    if (tc) tc.querySelector('.donut-center__value').textContent = tenantPct + '%';
  }
};

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
    if (window.loadBudgetVsExpenses) window.loadBudgetVsExpenses(this.value);
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
          const label   = labels[idx];
          if (groupId && window.filterCostChartByGroup) {
            window.filterCostChartByGroup(groupId, select.value);
          }
          showPieOverlay(groupId, label, select.value, 'chartBudget2');
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

// ---- Chart overlays ----
function buildOverlay(id, canvasId, groupId, label, projectId) {
  let overlay = document.getElementById(id);
  if (!overlay) {
    overlay = document.createElement('div');
    overlay.id = id;
    overlay.style.cssText = [
      'position:absolute', 'top:12px', 'right:12px',
      'background:#1B3F72', 'color:#fff',
      'border-radius:10px', 'padding:12px 18px',
      'display:flex', 'align-items:center', 'gap:12px',
      'box-shadow:0 4px 20px rgba(14,28,47,0.2)',
      'z-index:10', 'font-family:Inter,sans-serif',
      'font-size:0.82rem'
    ].join(';');
    const card = document.getElementById(canvasId).closest('.chart-card');
    card.style.position = 'relative';
    card.appendChild(overlay);
  }

  const url = 'financial-details.php?cost_group_id=' + groupId +
    (projectId ? '&project_id=' + projectId : '');

  overlay.innerHTML =
    '<span style="opacity:0.8;">📊 ' + label + '</span>' +
    '<a href="' + url + '" style="' +
      'background:#fff;color:#1B3F72;border-radius:6px;' +
      'padding:6px 14px;font-weight:500;text-decoration:none;' +
      'white-space:nowrap;font-size:0.8rem;' +
    '">Go to Details \u2192</a>' +
    '<button onclick="var el=document.getElementById(\'' + id + '\');if(el)el.style.display=\'none\';" style="' +
      'background:none;border:none;color:rgba(255,255,255,0.6);' +
      'cursor:pointer;font-size:16px;padding:0 2px;line-height:1;' +
    '">\u00d7</button>';
  overlay.style.display = 'flex';
}

function showBarOverlay(groupId, label, projectId) {
  buildOverlay('barChartOverlay', 'chartBudgetVsExp', groupId, label, projectId);
}

function showPieOverlay(groupId, label, projectId, canvasId) {
  buildOverlay('pieOverlay_' + canvasId, canvasId, groupId, label, projectId);
}

function hideBarOverlay() {
  const overlay = document.getElementById('barChartOverlay');
  if (overlay) overlay.style.display = 'none';
}

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
        onClick: function (e, elements) {
          if (!elements.length) return;
          const idx     = elements[0].index;
          const label   = labels[idx];
          const groupId = groupSelect.value;
          if (groupId) showPieOverlay(groupId, label, currentProject, 'chartCostGroup');
        },
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

// =========================================
// Budget vs Expenses Bar Chart
// =========================================

(function () {

  let barChart = null;

  // Listen for project change from budget pie chart dropdown
  document.addEventListener('DOMContentLoaded', function () {
    loadBarChart(1);
  });

  // Expose so project dropdown can trigger it
  window.loadBudgetVsExpenses = function (projectId) {
    loadBarChart(projectId);
  };

  function loadBarChart(projectId) {
    window._barProjectId = projectId;

    // Fetch group IDs and bar data together
    Promise.all([
      fetch('WS/WS_Fetch_Budget_vs_Expenses.php?project_id=' + projectId).then(function(r) { return r.json(); }),
      fetch('WS/WS_Fetch_Budget_Chart.php?project_id=' + projectId).then(function(r) { return r.json(); })
    ]).then(function (results) {
      const res  = results[0];
      const res2 = results[1];
      if (!res.success || !res.labels.length) return;
      // Store group map by name for reliable lookup
      window._barGroupIds    = res2.group_ids || [];
      window._barGroupLabels = res2.labels    || [];
      window._barGroupMap    = {};
      if (res2.groups) {
        res2.groups.forEach(function (g) {
          window._barGroupMap[g.name] = g.id;
        });
      }
      renderBarChart(res.labels, res.budget, res.expenses);
    }).catch(function () {});
  }

  function renderBarChart(labels, budget, expenses) {
    const expColors = expenses.map(function (e, i) {
      return e > budget[i] ? '#E24B4A' : '#4A7CC7';
    });

    if (barChart) barChart.destroy();

    const ctx = document.getElementById('chartBudgetVsExp').getContext('2d');
    barChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [
          {
            label: 'Budget',
            data: budget,
            backgroundColor: '#1B3F72',
            borderRadius: 4,
            borderSkipped: false,
            barPercentage: 0.5,
            categoryPercentage: 0.65,
            minBarLength: 4
          },
          {
            label: 'Expenses',
            data: expenses,
            backgroundColor: expColors,
            borderRadius: 4,
            borderSkipped: false,
            barPercentage: 0.5,
            categoryPercentage: 0.65,
            minBarLength: 4
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        onClick: function (e, elements) {
          if (!elements.length) { hideBarOverlay(); return; }
          const el      = elements[0];
          const idx     = el.index !== undefined ? el.index : el.dataIndex;
          const label   = labels[idx];
          const groupId = window._barGroupMap ? window._barGroupMap[label] : null;
          console.log('el:', el, 'idx:', idx, 'label:', label, 'map:', window._barGroupMap, 'groupId:', groupId);
          if (groupId) showBarOverlay(groupId, label, window._barProjectId || 1);
        },
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: function (ctx) {
                return ' ' + ctx.dataset.label + ': ' + ctx.parsed.y.toLocaleString('en-US', { minimumFractionDigits: 0 });
              },
              afterBody: function (items) {
                const i   = items[0].dataIndex;
                const diff = expenses[i] - budget[i];
                if (diff > 0) return ['⚠ Over by ' + diff.toLocaleString('en-US')];
                if (diff < 0) return ['✓ Under by ' + Math.abs(diff).toLocaleString('en-US')];
                return [];
              }
            }
          }
        },
        scales: {
          x: {
            grid: { display: false },
            ticks: {
              font: { size: 10 },
              color: '#8FA0B4',
              autoSkip: false,
              maxRotation: 40,
              callback: function (val, i) {
                const label = labels[i];
                return label.length > 15 ? label.substring(0, 15) + '…' : label;
              }
            }
          },
          y: {
            grid: { color: 'rgba(14,28,47,0.06)' },
            ticks: {
              font: { size: 11 },
              color: '#8FA0B4',
              callback: function (v) {
                if (v >= 1000000) return (v / 1000000).toFixed(1) + 'M';
                if (v >= 1000)    return Math.round(v / 1000) + 'k';
                return v;
              }
            }
          }
        }
      }
    });
  }

})();

// =========================================
// Dashboard Stats Widgets
// =========================================

(function () {

  const dashProjectSelect = document.getElementById('dashProjectSelect');
  const dashMonthSelect   = document.getElementById('dashMonthSelect');
  const dashYearSelect    = document.getElementById('dashYearSelect');

  const statTotalBudget = document.getElementById('statTotalBudget');
  const statSpent       = document.getElementById('statSpent');
  const statRemaining   = document.getElementById('statRemaining');
  const statTenants     = document.getElementById('statTenants');

  // ---- Load projects ----
  fetch('WS/WS_Fetch_Projects.php')
    .then(function (r) { return r.json(); })
    .then(function (res) {
      dashProjectSelect.innerHTML = '';
      if (res.success && res.data.length) {
        res.data.forEach(function (p) {
          const opt = document.createElement('option');
          opt.value       = p.ID;
          opt.textContent = p.Name;
          if (parseInt(p.ID) === 1) opt.selected = true;
          dashProjectSelect.appendChild(opt);
        });
      }
      loadStats();
    });

  // ---- Events ----
  dashProjectSelect.addEventListener('change', loadStats);
  dashMonthSelect.addEventListener('change', loadStats);
  dashYearSelect.addEventListener('change', loadStats);

  // ---- Fetch stats ----
  function loadStats() {
    const projectId = dashProjectSelect.value || 1;
    const month     = dashMonthSelect.value;
    const year      = dashYearSelect.value;

    let url = 'WS/WS_Fetch_Dashboard_Stats.php?project_id=' + projectId;
    if (month) url += '&month=' + month;
    if (year)  url += '&year='  + year;

    statTotalBudget.textContent = '...';
    statSpent.textContent       = '...';
    statRemaining.textContent   = '...';
    statTenants.textContent     = '...';

    fetch(url)
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (!res.success) return;

        statTotalBudget.textContent = '$' + fmt(res.total_budget);
        statSpent.textContent       = '$' + fmt(res.total_expenses);
        statTenants.textContent     = res.total_tenants;

        const rem = res.remaining;
        statRemaining.textContent  = (rem < 0 ? '-$' : '$') + fmt(Math.abs(rem));
        statRemaining.style.color  = rem < 0 ? '#B43232' : 'var(--text-primary)';

        // Update donut charts with real data
        if (window.updateDashboardDonuts) {
          window.updateDashboardDonuts(
            res.total_budget,
            res.total_expenses,
            res.paid_tenants   || 0,
            res.unpaid_tenants || 0
          );
        }

        // Update supplier charts
        if (window.loadSupplierCharts) {
          window.loadSupplierCharts(month, year);
        }

        // Populate year dropdown from available dates
        populateYears(res.available);
      });
  }

  function populateYears(available) {
    const years    = [...new Set(available.map(function (a) { return a.year; }))];
    const current  = dashYearSelect.value;
    dashYearSelect.innerHTML = '<option value="">All Years</option>';
    years.forEach(function (y) {
      const opt = document.createElement('option');
      opt.value       = y;
      opt.textContent = y;
      if (y == current) opt.selected = true;
      dashYearSelect.appendChild(opt);
    });
  }

  function fmt(val) {
    return parseFloat(val).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

})();

// =========================================
// Supplier Charts
// =========================================

(function () {

  let totalChart    = null;
  let detailChart   = null;
  const supplierSel = document.getElementById('supplierSelect');

  const DONUT_OPTS = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '72%',
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: function (ctx) {
            return ' ' + ctx.label + ': ' + ctx.parsed.toLocaleString('en-US', { minimumFractionDigits: 2 });
          }
        }
      }
    },
    animation: { duration: 600, easing: 'easeInOutQuart' }
  };

  // ---- Expose so stats widget can trigger reload ----
  window.loadSupplierCharts = function (month, year) {
    let url = 'WS/WS_Fetch_Supplier_Stats.php';
    const p = [];
    if (month) p.push('month=' + month);
    if (year)  p.push('year='  + year);
    const sid = supplierSel.value;
    if (sid)   p.push('supplier_id=' + encodeURIComponent(sid));
    if (p.length) url += '?' + p.join('&');

    fetch(url)
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (!res.success) return;

        // Populate supplier dropdown once
        if (supplierSel.options.length <= 1) {
          supplierSel.innerHTML = '<option value="">All Suppliers</option>';
          res.suppliers.forEach(function (s) {
            const opt = document.createElement('option');
            opt.value = s.Supplier_ID;
            opt.textContent = s.Name;
            supplierSel.appendChild(opt);
          });
        }

        renderTotalChart(res.total_paid, res.total_due);
        renderDetailChart(res.supplier.paid, res.supplier.due);
      });
  };

  // ---- Supplier dropdown change ----
  supplierSel.addEventListener('change', function () {
    const month = document.getElementById('dashMonthSelect') ? document.getElementById('dashMonthSelect').value : '';
    const year  = document.getElementById('dashYearSelect')  ? document.getElementById('dashYearSelect').value  : '';
    window.loadSupplierCharts(month, year);
  });

  // ---- Render total donut ----
  function renderTotalChart(paid, due) {
    const total = paid + due;
    const pct   = total > 0 ? Math.round((paid / total) * 100) : 0;

    if (totalChart) {
      totalChart.data.datasets[0].data = [paid, due];
      totalChart.update();
    } else {
      const ctx = document.getElementById('chartSupplierTotal').getContext('2d');
      totalChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: ['Paid', 'Due'],
          datasets: [{ data: [paid, due], backgroundColor: ['#1B3F72', '#E8A87C'], borderWidth: 0, hoverOffset: 6 }]
        },
        options: DONUT_OPTS
      });
    }

    const center = document.getElementById('supplierTotalCenter');
    if (center) center.querySelector('.donut-center__value').textContent = pct + '%';
    const elPaid = document.getElementById('supplierTotalPaid');
    const elDue  = document.getElementById('supplierTotalDue');
    if (elPaid) elPaid.textContent = '$' + fmt(paid);
    if (elDue)  elDue.textContent  = '$' + fmt(due);
  }

  // ---- Render detail donut ----
  function renderDetailChart(paid, due) {
    const total = paid + due;
    const pct   = total > 0 ? Math.round((paid / total) * 100) : 0;

    if (detailChart) {
      detailChart.data.datasets[0].data = [paid, due];
      detailChart.update();
    } else {
      const ctx = document.getElementById('chartSupplierDetail').getContext('2d');
      detailChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: ['Paid', 'Due'],
          datasets: [{ data: [paid, due], backgroundColor: ['#2B5BAD', '#E8A87C'], borderWidth: 0, hoverOffset: 6 }]
        },
        options: DONUT_OPTS
      });
    }

    const center = document.getElementById('supplierDetailCenter');
    if (center) center.querySelector('.donut-center__value').textContent = pct + '%';
    const elPaid = document.getElementById('supplierDetailPaid');
    const elDue  = document.getElementById('supplierDetailDue');
    if (elPaid) elPaid.textContent = '$' + fmt(paid);
    if (elDue)  elDue.textContent  = '$' + fmt(due);
  }

  function fmt(val) {
    return parseFloat(val).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  // Initial load
  document.addEventListener('DOMContentLoaded', function () {
    window.loadSupplierCharts('', '');
  });

})();
