<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
  <title>Highrise – Financial Details</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="CSS/index.css" />
  <link rel="stylesheet" href="CSS/tenant_dues.css" />
  <link rel="stylesheet" href="CSS/financial_details.css" />
</head>
<body>

  <div class="overlay" id="overlay"></div>

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

      <a href="index.php" class="nav__item">
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
        </svg>
        Tenant Dues
      </a>

      <a href="supplier-dues.php" class="nav__item">
        <svg class="nav__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <rect x="2" y="7" width="20" height="14" rx="2"/>
          <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
        </svg>
        Supplier Dues
      </a>

      <a href="financial-details.php" class="nav__item active">
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
          <line x1="9"  y1="14" x2="15" y2="14"/>
        </svg>
        Update Data
      </a>

      <a href="expense-mapping.php" class="nav__item">
        <svg class="nav__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <line x1="8" y1="6" x2="21" y2="6"/>
          <line x1="8" y1="12" x2="21" y2="12"/>
          <line x1="8" y1="18" x2="21" y2="18"/>
          <line x1="3" y1="6"  x2="3.01" y2="6"/>
          <line x1="3" y1="12" x2="3.01" y2="12"/>
          <line x1="3" y1="18" x2="3.01" y2="18"/>
        </svg>
        Expense Mapping
      </a>

      <p class="nav__section-label">Account</p>

      <a href="profile-edit.php" class="nav__item">
        <svg class="nav__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
          <circle cx="12" cy="7" r="4"/>
        </svg>
        Profile
      </a>
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

  <div class="main" id="main">

    <header class="topbar">
      <button class="hamburger" id="btnHamburger" aria-label="Open menu">
        <span></span><span></span><span></span>
      </button>
      <span class="topbar__title">Financial Details</span>
      <div class="topbar__spacer"></div>
    </header>

    <div class="content">
      <h1 class="page-title">Financial Details</h1>
      <p class="page-sub">All recorded expenses with budget group filtering</p>

      <!-- Filters -->
      <div class="date-toolbar" style="margin-top:20px;">
        <select id="filterGroup" class="filter-select">
          <option value="">All Cost Groups</option>
        </select>
        <select id="filterMonth" class="filter-select">
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
        <select id="filterYear" class="filter-select">
          <option value="">All Years</option>
        </select>
        <button class="btn-apply" id="btnApply">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
          Apply
        </button>
        <button class="btn-reset" id="btnReset">Reset</button>
      </div>

      <!-- Summary -->
      <div class="dues-summary" style="margin-top:16px;">
        <div class="summary-pill summary-pill--total">
          <span class="summary-pill__count" id="summaryCount">—</span>
          <span class="summary-pill__label">Records</span>
        </div>
        <div class="summary-pill summary-pill--paid">
          <span class="summary-pill__count" id="summaryTotal" style="font-size:1rem;">—</span>
          <span class="summary-pill__label">Total (USD)</span>
        </div>
      </div>

      <!-- Table -->
      <div class="table-wrap">
        <table class="dues-table" id="financialTable">
          <thead>
            <tr>
              <th>#</th>
              <th class="sortable" data-col="account_no">Account No. <span class="sort-icon">↕</span></th>
              <th class="sortable" data-col="expense_name">Expense Name <span class="sort-icon">↕</span></th>
              <th class="sortable amount-header" data-col="amount">Amount <span class="sort-icon">↕</span></th>
              <th class="sortable" data-col="currency">Currency <span class="sort-icon">↕</span></th>
              <th class="sortable amount-header" data-col="amount_usd">Amount (USD) <span class="sort-icon">↕</span></th>
              <th class="sortable" data-col="trans_date">Date <span class="sort-icon">↕</span></th>
            </tr>
          </thead>
          <tbody id="financialBody">
            <tr><td colspan="7" style="text-align:center;padding:30px;color:#8FA0B4;">Loading...</td></tr>
          </tbody>
        </table>
        <p class="no-results" id="noResults" style="display:none;">No records match your filters.</p>
      </div>

    </div>
  </div>

  <script src="JS/index.js"></script>
  <script src="JS/financial_details.js"></script>
</body>
</html>
