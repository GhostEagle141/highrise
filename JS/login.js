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
    const name     = txtCreds.value.trim();
    const password = txtPassword.value.trim();
    let valid = true;

    clearError(txtCreds);
    clearError(txtPassword);

    if (!name)     { showError(txtCreds);    valid = false; }
    if (!password) { showError(txtPassword); valid = false; }
    if (!valid) return;

    setLoading(true);

    const formData = new FormData();
    formData.append('name',     name);
    formData.append('password', password);

    fetch('WS/WS_Login.php', { method: 'POST', body: formData })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        setLoading(false);
        if (res.success) {
          window.location.href = 'index.php';
        } else {
          showError(txtCreds);
          showError(txtPassword);
          showMessage(res.error || 'Invalid credentials.');
        }
      })
      .catch(function () {
        setLoading(false);
        showMessage('Network error. Please try again.');
      });
  });

  [txtCreds, txtPassword].forEach(function (input) {
    input.addEventListener('input', function () { clearError(input); clearMessage(); });
    input.addEventListener('keydown', function (e) { if (e.key === 'Enter') btnLogin.click(); });
  });

  function showError(input)  { input.classList.add('error'); }
  function clearError(input) { input.classList.remove('error'); }

  function showMessage(msg) {
    let el = document.getElementById('loginError');
    if (!el) {
      el = document.createElement('p');
      el.id = 'loginError';
      el.style.cssText = 'color:#B43232;font-size:0.82rem;margin-top:10px;text-align:center;';
      btnLogin.parentNode.insertBefore(el, btnLogin.nextSibling);
    }
    el.textContent = msg;
  }

  function clearMessage() {
    const el = document.getElementById('loginError');
    if (el) el.textContent = '';
  }

  function setLoading(on) {
    btnLogin.disabled = on;
    const txt = btnLogin.querySelector('.btn-text');
    if (txt) txt.textContent = on ? 'Signing in...' : 'Sign In';
  }

});
