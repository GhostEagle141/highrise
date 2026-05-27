<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
  <title>Highrise – Update Data</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="CSS/index.css" />
  <link rel="stylesheet" href="CSS/update-data.css" />
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

      <a href="budget-details.php" class="nav__item">
        <svg class="nav__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <line x1="12" y1="1" x2="12" y2="23"/>
          <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
        </svg>
        Budget Details
      </a>

      <a href="update-data.php" class="nav__item active">
        <svg class="nav__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
          <line x1="12" y1="11" x2="12" y2="17"/>
          <line x1="9"  y1="14" x2="15" y2="14"/>
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
    </nav>

    <div class="sidebar__footer">
      <a href="WS/WS_User_Functions.php?Function_ID=2" class="nav__item nav__item--logout">
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
      <span class="topbar__title">Update Data</span>
      <div class="topbar__spacer"></div>
    </header>

    <div class="content">
      <h1 class="page-title">Update Data</h1>
      <p class="page-sub">Upload Excel sheets to sync the latest data</p>

      <!-- Financial Data -->
      <div class="upload-section-label">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <line x1="12" y1="1" x2="12" y2="23"/>
          <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
        </svg>
        Financial Data
      </div>
      <div class="upload-card">
        <div class="drop-zone" id="dropZone">
          <input type="file" id="fileInput" accept=".xlsx,.xls,.csv" hidden />
          <div class="drop-zone__idle" id="stateIdle">
            <div class="excel-icon">
              <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
                <rect width="40" height="40" rx="8" fill="#1B6B35"/>
                <rect x="6" y="8" width="18" height="24" rx="2" fill="white" opacity="0.15"/>
                <rect x="6" y="8" width="18" height="24" rx="2" stroke="white" stroke-width="1.2" opacity="0.4"/>
                <line x1="10" y1="14" x2="20" y2="14" stroke="white" stroke-width="1.2" opacity="0.6"/>
                <line x1="10" y1="18" x2="20" y2="18" stroke="white" stroke-width="1.2" opacity="0.6"/>
                <line x1="10" y1="22" x2="16" y2="22" stroke="white" stroke-width="1.2" opacity="0.6"/>
                <rect x="18" y="20" width="16" height="14" rx="2" fill="#217346"/>
                <text x="21" y="31" font-family="Inter,sans-serif" font-size="9" font-weight="700" fill="white">XLS</text>
              </svg>
            </div>
            <p class="drop-zone__heading">Drag & drop your file here</p>
            <p class="drop-zone__sub">Supports .xlsx, .xls, .csv</p>
            <button class="btn-browse" id="btnBrowse">Browse file</button>
          </div>
          <div class="drop-zone__file" id="stateFile" style="display:none;">
            <div class="file-info">
              <div class="file-info__icon">
                <svg width="24" height="24" viewBox="0 0 40 40" fill="none">
                  <rect width="40" height="40" rx="8" fill="#1B6B35"/>
                  <rect x="6" y="8" width="18" height="24" rx="2" fill="white" opacity="0.15"/>
                  <rect x="18" y="20" width="16" height="14" rx="2" fill="#217346"/>
                  <text x="21" y="31" font-family="Inter,sans-serif" font-size="9" font-weight="700" fill="white">XLS</text>
                </svg>
              </div>
              <div class="file-info__meta">
                <p class="file-info__name" id="fileName">—</p>
                <p class="file-info__size" id="fileSize">—</p>
              </div>
              <button class="btn-remove" id="btnRemove" aria-label="Remove file">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="18" y1="6" x2="6" y2="18"/>
                  <line x1="6"  y1="6" x2="18" y2="18"/>
                </svg>
              </button>
            </div>
          </div>
        </div>
        <div class="upload-actions">
          <button class="btn-upload" id="btnUpload" disabled>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="16 16 12 12 8 16"/>
              <line x1="12" y1="12" x2="12" y2="21"/>
              <path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/>
            </svg>
            Upload & Save
          </button>
          <p class="upload-hint">Data will be processed and saved immediately after upload</p>
        </div>
      </div>

      <!-- Budget -->
      <div class="upload-section-label" style="margin-top: 32px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <line x1="12" y1="1" x2="12" y2="23"/>
          <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
        </svg>
        Budget
      </div>
      <div class="upload-card">
        <div class="drop-zone" id="dropZoneBudget">
          <input type="file" id="fileInputBudget" accept=".xlsx,.xls,.csv" hidden />
          <div class="drop-zone__idle" id="stateIdleBudget">
            <div class="excel-icon">
              <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
                <rect width="40" height="40" rx="8" fill="#1B6B35"/>
                <rect x="6" y="8" width="18" height="24" rx="2" fill="white" opacity="0.15"/>
                <rect x="6" y="8" width="18" height="24" rx="2" stroke="white" stroke-width="1.2" opacity="0.4"/>
                <line x1="10" y1="14" x2="20" y2="14" stroke="white" stroke-width="1.2" opacity="0.6"/>
                <line x1="10" y1="18" x2="20" y2="18" stroke="white" stroke-width="1.2" opacity="0.6"/>
                <line x1="10" y1="22" x2="16" y2="22" stroke="white" stroke-width="1.2" opacity="0.6"/>
                <rect x="18" y="20" width="16" height="14" rx="2" fill="#217346"/>
                <text x="21" y="31" font-family="Inter,sans-serif" font-size="9" font-weight="700" fill="white">XLS</text>
              </svg>
            </div>
            <p class="drop-zone__heading">Drag & drop your file here</p>
            <p class="drop-zone__sub">Supports .xlsx, .xls, .csv</p>
            <button class="btn-browse" id="btnBrowseBudget">Browse file</button>
          </div>
          <div class="drop-zone__file" id="stateFileBudget" style="display:none;">
            <div class="file-info">
              <div class="file-info__icon">
                <svg width="24" height="24" viewBox="0 0 40 40" fill="none">
                  <rect width="40" height="40" rx="8" fill="#1B6B35"/>
                  <rect x="6" y="8" width="18" height="24" rx="2" fill="white" opacity="0.15"/>
                  <rect x="18" y="20" width="16" height="14" rx="2" fill="#217346"/>
                  <text x="21" y="31" font-family="Inter,sans-serif" font-size="9" font-weight="700" fill="white">XLS</text>
                </svg>
              </div>
              <div class="file-info__meta">
                <p class="file-info__name" id="fileNameBudget">—</p>
                <p class="file-info__size" id="fileSizeBudget">—</p>
              </div>
              <button class="btn-remove" id="btnRemoveBudget" aria-label="Remove file">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="18" y1="6" x2="6" y2="18"/>
                  <line x1="6"  y1="6" x2="18" y2="18"/>
                </svg>
              </button>
            </div>
          </div>
        </div>
        <div class="upload-actions">
          <button class="btn-upload" id="btnUploadBudget" disabled>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="16 16 12 12 8 16"/>
              <line x1="12" y1="12" x2="12" y2="21"/>
              <path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/>
            </svg>
            Upload & Save
          </button>
          <p class="upload-hint">Budget data will be updated immediately after upload</p>
        </div>
      </div>

    </div>

  </div>

  <script src="JS/index.js"></script>
  <script src="JS/update-data.js"></script>
</body>
</html>
