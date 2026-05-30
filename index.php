<?php session_start(); $isAdmin = isset($_SESSION['user_type_id']) && $_SESSION['user_type_id'] === 1; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
  <title>Highrise</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="CSS/index.css" />
  <link rel="stylesheet" href="CSS/dashboard.css" />
</head>
<body>

  <!-- Mobile overlay -->
  <div class="overlay" id="overlay"></div>

  <!-- Sidebar -->
  <aside class="sidebar" id="sidebar">

    <div class="sidebar__brand">
      <div class="brand__logo">
        <svg width="20" height="24" viewBox="0 0 22 26" fill="none">
          <rect x="4" y="1" width="14" height="24" rx="1" fill="#4A7CC7"/>
          <rect x="1" y="9" width="4"  height="16" rx="1" fill="#1B3F72"/>
          <rect x="17" y="9" width="4" height="16" rx="1" fill="#1B3F72"/>
          <rect x="7"  y="4"  width="2.5" height="3" rx="0.4" fill="white" opacity="0.7"/>
          <rect x="12" y="4"  width="2.5" height="3" rx="0.4" fill="white" opacity="0.7"/>
          <rect x="7"  y="9"  width="2.5" height="3" rx="0.4" fill="white" opacity="0.7"/>
          <rect x="12" y="9"  width="2.5" height="3" rx="0.4" fill="white" opacity="0.7"/>
          <rect x="7"  y="14" width="2.5" height="3" rx="0.4" fill="white" opacity="0.7"/>
          <rect x="12" y="14" width="2.5" height="3" rx="0.4" fill="white" opacity="0.7"/>
          <rect x="8.5" y="19" width="5" height="6"  rx="0.4" fill="white" opacity="0.5"/>
        </svg>
      </div>
      <span class="brand__name">Highrise</span>
    </div>

    <nav class="sidebar__nav">

      <p class="nav__section-label">Main</p>

      <a href="index.php" class="nav__item active">
        <svg class="nav__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
          <polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
        Main Dashboard
      </a>

      <a href="tenant-dues.php" class="nav__item">
        <svg class="nav__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
          <circle cx="9" cy="7" r="4"/>
          <line x1="23" y1="11" x2="23" y2="17"/>
          <line x1="20" y1="14" x2="26" y2="14"/>
        </svg>
        Tenant Dues
      </a>

      <a href="supplier-dues.php" class="nav__item">
        <svg class="nav__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <rect x="2" y="7" width="20" height="14" rx="2"/>
          <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
          <line x1="12" y1="12" x2="12" y2="16"/>
          <line x1="10" y1="14" x2="14" y2="14"/>
        </svg>
        Supplier Dues
      </a>

      <a href="financial-details.php" class="nav__item">
        <svg class="nav__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <line x1="12" y1="1" x2="12" y2="23"/>
          <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
        </svg>
        Financial Details
      </a>

      <a href="update-data.php" class="nav__item">
        <svg class="nav__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
          <line x1="12" y1="11" x2="12" y2="17"/>
          <line x1="9" y1="14" x2="15" y2="14"/>
        </svg>
        Update Data
      </a>

      <p class="nav__section-label">Account</p>

      <a href="profile-edit.php" class="nav__item">
        <svg class="nav__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
          <circle cx="12" cy="7" r="4"/>
        </svg>
        Profile
      </a>

      <?php if ($isAdmin): ?>
      <a href="register.php" class="nav__item">
        <svg class="nav__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
          <circle cx="8.5" cy="7" r="4"/>
          <line x1="20" y1="8" x2="20" y2="14"/>
          <line x1="23" y1="11" x2="17" y2="11"/>
        </svg>
        Register User
      </a>
      <?php endif; ?>

    </nav>

    <div class="sidebar__footer">
      <a href="login.php" class="nav__item nav__item--logout">
        <svg class="nav__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
          <polyline points="16 17 21 12 16 7"/>
          <line x1="21" y1="12" x2="9" y2="12"/>
        </svg>
        Sign Out
      </a>
    </div>

  </aside>

  <!-- Main content -->
  <div class="main" id="main">

    <!-- Top bar (mobile) -->
    <header class="topbar">
      <button class="hamburger" id="btnHamburger" aria-label="Open menu">
        <span></span>
        <span></span>
        <span></span>
      </button>
      <span class="topbar__title">Main Dashboard</span>
      <div class="topbar__spacer"></div>
    </header>

    <!-- Page content -->
    <div class="content">
      <div class="dashboard-header">
        <div>
          <h1 class="page-title">Welcome back</h1>
          <p class="page-sub">Here are your highlights</p>
        </div>
        <div class="dashboard-filters">
          <select id="dashProjectSelect" class="budget-project-select">
            <option value="">Loading...</option>
          </select>
          <select id="dashMonthSelect" class="budget-project-select">
            <option value="">All Months</option>
            <option value="1">January</option>
            <option value="2">February</option>
            <option value="3">March</option>
            <option value="4">April</option>
            <option value="5">May</option>
            <option value="6">June</option>
            <option value="7">July</option>
            <option value="8">August</option>
            <option value="9">September</option>
            <option value="10">October</option>
            <option value="11">November</option>
            <option value="12">December</option>
          </select>
          <select id="dashYearSelect" class="budget-project-select">
            <option value="">All Years</option>
          </select>
        </div>
      </div>

      <!-- Summary cards -->
      <div class="stat-grid">
        <div class="stat-card">
          <p class="stat-card__label">Total Budget</p>
          <p class="stat-card__value" id="statTotalBudget">—</p>
        </div>
        <div class="stat-card">
          <p class="stat-card__label">Spent</p>
          <p class="stat-card__value" id="statSpent">—</p>
        </div>
        <div class="stat-card">
          <p class="stat-card__label">Remaining</p>
          <p class="stat-card__value" id="statRemaining">—</p>
        </div>
        <div class="stat-card">
          <p class="stat-card__label">Total Tenants</p>
          <p class="stat-card__value" id="statTenants">—</p>
        </div>
      </div>

      <!-- Charts -->
      <div class="charts-grid">

        <div class="chart-card">
          <h2 class="chart-card__title">Budget Overview</h2>
          <p class="chart-card__sub">Spent vs remaining budget</p>
          <div class="chart-wrap">
            <canvas id="chartBudget"></canvas>
            <div class="donut-center" id="budgetCenter">
              <span class="donut-center__value">62%</span>
              <span class="donut-center__label">Spent</span>
            </div>
          </div>
          <div class="chart-legend">
            <div class="legend-item">
              <span class="legend-dot" style="background:#1B3F72"></span>
              <span>Spent <strong>$74,500</strong></span>
            </div>
            <div class="legend-item">
              <span class="legend-dot" style="background:#A8C4E8"></span>
              <span>Remaining <strong>$45,500</strong></span>
            </div>
          </div>
        </div>

        <div class="chart-card">
          <h2 class="chart-card__title">Tenant Payments</h2>
          <p class="chart-card__sub">Paid vs outstanding this month</p>
          <div class="chart-wrap">
            <canvas id="chartTenants"></canvas>
            <div class="donut-center" id="tenantsCenter">
              <span class="donut-center__value">71%</span>
              <span class="donut-center__label">Paid</span>
            </div>
          </div>
          <div class="chart-legend">
            <div class="legend-item">
              <span class="legend-dot" style="background:#2B5BAD"></span>
              <span>Paid <strong>34</strong></span>
            </div>
            <div class="legend-item">
              <span class="legend-dot" style="background:#E8A87C"></span>
              <span>Outstanding <strong>14</strong></span>
            </div>
          </div>
        </div>

      </div>

      <!-- Supplier charts -->
      <div class="charts-grid" style="margin-top:16px;">

        <!-- Total owed vs paid -->
        <div class="chart-card">
          <h2 class="chart-card__title">Supplier Balances</h2>
          <p class="chart-card__sub">Total paid vs outstanding to suppliers</p>
          <div class="chart-wrap">
            <canvas id="chartSupplierTotal"></canvas>
            <div class="donut-center" id="supplierTotalCenter">
              <span class="donut-center__value">—</span>
              <span class="donut-center__label">Paid</span>
            </div>
          </div>
          <div class="chart-legend">
            <div class="legend-item">
              <span class="legend-dot" style="background:#1B3F72"></span>
              <span>Paid <strong id="supplierTotalPaid">—</strong></span>
            </div>
            <div class="legend-item">
              <span class="legend-dot" style="background:#E8A87C"></span>
              <span>Due <strong id="supplierTotalDue">—</strong></span>
            </div>
          </div>
        </div>

        <!-- Per-supplier breakdown -->
        <div class="chart-card">
          <div class="chart-card-header">
            <div>
              <h2 class="chart-card__title">Supplier Detail</h2>
              <p class="chart-card__sub">Paid vs due for selected supplier</p>
            </div>
            <select id="supplierSelect" class="budget-project-select">
              <option value="">Loading...</option>
            </select>
          </div>
          <div class="chart-wrap">
            <canvas id="chartSupplierDetail"></canvas>
            <div class="donut-center" id="supplierDetailCenter">
              <span class="donut-center__value">—</span>
              <span class="donut-center__label">Paid</span>
            </div>
          </div>
          <div class="chart-legend">
            <div class="legend-item">
              <span class="legend-dot" style="background:#2B5BAD"></span>
              <span>Paid <strong id="supplierDetailPaid">—</strong></span>
            </div>
            <div class="legend-item">
              <span class="legend-dot" style="background:#E8A87C"></span>
              <span>Due <strong id="supplierDetailDue">—</strong></span>
            </div>
          </div>
        </div>

      </div>

      <!-- Budget vs Expenses bar chart -->
      <div class="chart-card" style="margin-top:16px; grid-column: 1 / -1;">
        <div class="chart-card-header">
          <div>
            <h2 class="chart-card__title">Budget vs Expenses</h2>
            <p class="chart-card__sub">Actual expenses compared to budget per cost code</p>
          </div>
        </div>
        <div style="position:relative; width:100%; height:420px;">
          <canvas id="chartBudgetVsExp" role="img" aria-label="Grouped bar chart comparing budget vs actual expenses per cost code">Budget vs expenses data.</canvas>
        </div>
        <div style="display:flex; gap:16px; font-size:12px; color:var(--text-muted); margin-top:14px; flex-wrap:wrap;">
          <span style="display:flex;align-items:center;gap:5px;"><span style="width:10px;height:10px;border-radius:2px;background:#1B3F72;"></span>Budget</span>
          <span style="display:flex;align-items:center;gap:5px;"><span style="width:10px;height:10px;border-radius:2px;background:#4A7CC7;"></span>Expenses</span>
          <span style="display:flex;align-items:center;gap:5px;"><span style="width:10px;height:10px;border-radius:2px;background:#E24B4A;"></span>Over budget</span>
        </div>
      </div>

      <!-- Budget pie chart -->
      <div class="charts-grid" style="margin-top: 16px;">
        <div class="chart-card" style="grid-column: 1 / -1;">
          <div class="chart-card-header">
            <div>
              <h2 class="chart-card__title">Budget by Cost Group</h2>
              <p class="chart-card__sub">Total budget allocation per group</p>
            </div>
            <select id="budgetProjectSelect" class="budget-project-select">
              <option value="">Loading...</option>
            </select>
          </div>
          <div class="budget-chart-wrap">
            <div class="budget-pie-wrap">
              <canvas id="chartBudget2"></canvas>
            </div>
            <div class="budget-legend" id="budgetLegend"></div>
          </div>
        </div>
      </div>

      <!-- Cost group breakdown pie chart -->
      <div class="charts-grid" style="margin-top: 16px;">
        <div class="chart-card" style="grid-column: 1 / -1;">
          <div class="chart-card-header">
            <div>
              <h2 class="chart-card__title">Cost Breakdown</h2>
              <p class="chart-card__sub">Budget amounts per cost within selected group</p>
            </div>
            <select id="costGroupSelect" class="budget-project-select">
              <option value="">Loading...</option>
            </select>
          </div>
          <div class="budget-chart-wrap">
            <div class="budget-pie-wrap">
              <canvas id="chartCostGroup"></canvas>
            </div>
            <div class="budget-legend" id="costGroupLegend"></div>
          </div>
        </div>
      </div>

    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
  <script src="JS/index.js"></script>
  <script src="JS/dashboard.js"></script>
</body>
</html>
