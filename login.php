<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
  <title>Highrise – Sign In</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="CSS/login.css" />
</head>
<body>

  <div class="page-wrapper">

    <div class="card">

      <!-- Logo -->
      <div class="brand">
        <div class="brand__logo">
          <!-- Building icon -->
          <svg width="22" height="26" viewBox="0 0 22 26" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <rect x="4" y="1" width="14" height="24" rx="1" fill="#4A7CC7"/>
            <rect x="1" y="9" width="4" height="16" rx="1" fill="#1B3F72"/>
            <rect x="17" y="9" width="4" height="16" rx="1" fill="#1B3F72"/>
            <rect x="7"  y="4"  width="2.5" height="3" rx="0.4" fill="white" opacity="0.6"/>
            <rect x="12" y="4"  width="2.5" height="3" rx="0.4" fill="white" opacity="0.6"/>
            <rect x="7"  y="9"  width="2.5" height="3" rx="0.4" fill="white" opacity="0.6"/>
            <rect x="12" y="9"  width="2.5" height="3" rx="0.4" fill="white" opacity="0.6"/>
            <rect x="7"  y="14" width="2.5" height="3" rx="0.4" fill="white" opacity="0.6"/>
            <rect x="12" y="14" width="2.5" height="3" rx="0.4" fill="white" opacity="0.6"/>
            <rect x="8.5" y="19" width="5" height="6" rx="0.4" fill="white" opacity="0.5"/>
          </svg>
        </div>
        <span class="brand__name">Highrise</span>
      </div>

      <div class="divider"></div>

      <h1 class="card__title">Sign in</h1>
      <p class="card__subtitle">Access your account</p>

      <!-- Form -->
      <div class="form-group">
        <label class="form-label" for="txtCredentials">Email or Phone</label>
        <div class="input-wrap">
          <svg class="input-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
          <input type="text" id="txtCredentials" class="form-input" placeholder="you@example.com" autocomplete="username" />
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="txtPassword">Password</label>
        <div class="input-wrap">
          <svg class="input-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <rect x="3" y="11" width="18" height="11" rx="2"/>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
          </svg>
          <input type="password" id="txtPassword" class="form-input" placeholder="••••••••" autocomplete="current-password" />
          <button type="button" class="toggle-pw" id="btnTogglePw" aria-label="Toggle password visibility">
            <svg id="iconEye" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
              <circle cx="12" cy="12" r="3"/>
            </svg>
          </button>
        </div>
      </div>

      <div class="form-options">
        <label class="remember-me">
          <input type="checkbox" id="chkRemember" />
          <span class="checkmark"></span>
          Remember me
        </label>
        <a href="forgotpassword.php" class="forgot-link">Forgot password?</a>
      </div>

      <button class="btn-login" id="btnLogin">
        <span class="btn-text">Sign In</span>
        <span class="btn-loader" aria-hidden="true"></span>
      </button>

      <p class="register-cta">
        New to Highrise? <a href="register.php">Create an account</a>
      </p>

    </div>

  </div>

  <script src="JS/login.js"></script>
</body>
</html>
