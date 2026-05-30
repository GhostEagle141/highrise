// =========================================
// Highrise – Register User JS
// =========================================

document.addEventListener('DOMContentLoaded', function () {

  const btnRegister    = document.getElementById('btnRegister');
  const btnTogglePw    = document.getElementById('btnTogglePw');
  const txtName        = document.getElementById('txtName');
  const txtPassword    = document.getElementById('txtPassword');
  const txtConfirm     = document.getElementById('txtConfirm');
  const selectUserType = document.getElementById('selectUserType');
  const msgEl          = document.getElementById('registerMsg');

  // ---- Load user types ----
  fetch('WS/WS_Fetch_User_Types.php')
    .then(function (r) { return r.json(); })
    .then(function (res) {
      selectUserType.innerHTML = '<option value="">Select user type</option>';
      if (res.success && res.data.length) {
        res.data.forEach(function (t) {
          const opt = document.createElement('option');
          opt.value = t.ID; opt.textContent = t.Name;
          selectUserType.appendChild(opt);
        });
      }
    });

  // Enable button when fields have values
  [txtName, txtPassword, txtConfirm, selectUserType].forEach(function (el) {
    el.addEventListener('input', checkReady);
    el.addEventListener('change', checkReady);
  });

  function checkReady() {
    btnRegister.disabled = !(
      txtName.value.trim() &&
      txtPassword.value.trim() &&
      txtConfirm.value.trim() &&
      selectUserType.value
    );
  }

  // ---- Submit ----
  btnRegister.addEventListener('click', function () {
    const name       = txtName.value.trim();
    const password   = txtPassword.value.trim();
    const confirm    = txtConfirm.value.trim();
    const userTypeId = selectUserType.value;

    hideMsg();

    if (password !== confirm) {
      showMsg('Passwords do not match.', 'error');
      return;
    }
    if (password.length < 6) {
      showMsg('Password must be at least 6 characters.', 'error');
      return;
    }

    setLoading(true);
    const formData = new FormData();
    formData.append('name',         name);
    formData.append('password',     password);
    formData.append('user_type_id', userTypeId);

    fetch('WS/WS_Register.php', { method: 'POST', body: formData })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        setLoading(false);
        if (res.success) {
          showMsg('Account created successfully!', 'success');
          txtName.value     = '';
          txtPassword.value = '';
          txtConfirm.value  = '';
          selectUserType.value = '';
          btnRegister.disabled = true;
        } else {
          showMsg(res.error || 'Registration failed.', 'error');
        }
      })
      .catch(function () {
        setLoading(false);
        showMsg('Network error. Please try again.', 'error');
      });
  });

  function showMsg(msg, type) {
    msgEl.textContent  = msg;
    msgEl.style.color  = type === 'success' ? '#1B6B35' : '#B43232';
    msgEl.style.display = 'block';
  }
  function hideMsg() { msgEl.style.display = 'none'; }

  function setLoading(on) {
    btnRegister.disabled = on;
    const txt = btnRegister.querySelector('svg');
    btnRegister.childNodes[btnRegister.childNodes.length - 1].textContent = on ? ' Creating...' : ' Create Account';
  }

});
