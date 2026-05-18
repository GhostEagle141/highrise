// =========================================
// Highrise – Login JS
// =========================================

document.addEventListener('DOMContentLoaded', function () {

  const btnLogin    = document.getElementById('btnLogin');
  const btnTogglePw = document.getElementById('btnTogglePw');
  const txtPassword = document.getElementById('txtPassword');
  const txtCreds    = document.getElementById('txtCredentials');

  const eyeOpen = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
    <circle cx="12" cy="12" r="3"/>
  </svg>`;

  const eyeOff = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
    <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
    <line x1="1" y1="1" x2="23" y2="23"/>
  </svg>`;

  // Toggle password visibility
  btnTogglePw.addEventListener('click', function () {
    const isHidden = txtPassword.type === 'password';
    txtPassword.type = isHidden ? 'text' : 'password';
    btnTogglePw.innerHTML = isHidden ? eyeOff : eyeOpen;
    btnTogglePw.classList.toggle('active', isHidden);
  });

  // Login
  btnLogin.addEventListener('click', function () {
    const creds    = txtCreds.value.trim();
    const password = txtPassword.value.trim();
    let valid = true;

    clearError(txtCreds);
    clearError(txtPassword);

    if (!creds)    { showError(txtCreds);    valid = false; }
    if (!password) { showError(txtPassword); valid = false; }
    if (!valid) return;

    window.location.href = 'index.php';
  });

  // Clear error as user types
  [txtCreds, txtPassword].forEach(function (input) {
    input.addEventListener('input', function () { clearError(input); });
  });

  function showError(input)  { input.classList.add('error'); }
  function clearError(input) { input.classList.remove('error'); }

});
