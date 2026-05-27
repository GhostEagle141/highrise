// =========================================
// Highrise – Index JS
// =========================================

document.addEventListener('DOMContentLoaded', function () {

  const sidebar     = document.getElementById('sidebar');
  const overlay     = document.getElementById('overlay');
  const btnHamburger = document.getElementById('btnHamburger');

  function openMenu() {
    sidebar.classList.add('open');
    overlay.classList.add('visible');
    btnHamburger.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function closeMenu() {
    sidebar.classList.remove('open');
    overlay.classList.remove('visible');
    btnHamburger.classList.remove('open');
    document.body.style.overflow = '';
  }

  btnHamburger.addEventListener('click', function () {
    sidebar.classList.contains('open') ? closeMenu() : openMenu();
  });

  // Tap overlay to close
  overlay.addEventListener('click', closeMenu);

  // Close on nav item tap (mobile UX)
  sidebar.querySelectorAll('.nav__item').forEach(function (item) {
    item.addEventListener('click', function () {
      if (window.innerWidth < 768) closeMenu();
    });
  });

});

// Prevent browser from opening files dragged anywhere on the page
window.addEventListener('dragover', function (e) { e.preventDefault(); });
window.addEventListener('drop',     function (e) { e.preventDefault(); });
