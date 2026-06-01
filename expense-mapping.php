<?php session_start(); $isAdmin = isset($_SESSION['user_type_id']) && $_SESSION['user_type_id'] === 1;
if (!isset($_SESSION['user'])) { header('Location: login.php'); exit; }
if (!$isAdmin) { header('Location: index.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
  <title>Highrise – Expense Mapping</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="CSS/index.css" />
  <link rel="stylesheet" href="CSS/expense_mapping.css" />
</head>
<body>

  <div class="overlay" id="overlay"></div>

  <aside class="sidebar" id="sidebar">
    <div class="sidebar__brand">
      <img src="images/SMS_LOGO.jpg" alt="SMS Urban Management" style="width:44px;height:44px;object-fit:contain;border-radius:6px;background:#fff;padding:2px;" />
      <span class="brand__name" style="font-size:0.72rem;line-height:1.2;">SMS Urban<br>Management</span>
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
          <line x1="9"  y1="14" x2="15" y2="14"/>
        </svg>
        Update Data
      </a>



      <p class="nav__section-label">Account</p>



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

  <div class="main" id="main">

    <header class="topbar">
      <button class="hamburger" id="btnHamburger" aria-label="Open menu">
        <span></span><span></span><span></span>
      </button>
      <span class="topbar__title">Expense Mapping</span>
      <div class="topbar__spacer"></div>
      <button class="btn-save-all" id="btnSaveAll" disabled>
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
          <polyline points="17 21 17 13 7 13 7 21"/>
          <polyline points="7 3 7 8 15 8"/>
        </svg>
        Save All
      </button>
    </header>

    <div class="content">
      <h1 class="page-title">Expense Mapping</h1>
      <p class="page-sub">Match each real expense to its corresponding budget item</p>

      <!-- Search -->
      <div class="mapping-toolbar">
        <div class="search-wrap">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <input type="text" id="searchInput" class="search-input" placeholder="Search expense..." />
        </div>
        <div class="mapping-stats">
          <span class="stat-pill stat-pill--mapped" id="statMapped">0 mapped</span>
          <span class="stat-pill stat-pill--unmapped" id="statUnmapped">0 unmapped</span>
        </div>
      </div>

      <!-- Table -->
      <div class="mapping-table-wrap">
        <table class="mapping-table">
          <thead>
            <tr>
              <th>Real Expense Name</th>
              <th>Account No.</th>
              <th>Budget Item</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody id="mappingBody">
            <tr>
              <td colspan="4" class="loading-row">Loading...</td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>
  </div>

  <script src="JS/index.js"></script>
  <script src="JS/expense_mapping.js"></script>
</body>
</html>
