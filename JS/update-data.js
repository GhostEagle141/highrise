// =========================================
// Highrise – Update Data JS
// =========================================

document.addEventListener('DOMContentLoaded', function () {

  const dropZone  = document.getElementById('dropZone');
  const fileInput = document.getElementById('fileInput');
  const btnBrowse = document.getElementById('btnBrowse');
  const btnRemove = document.getElementById('btnRemove');
  const btnUpload = document.getElementById('btnUpload');
  const stateIdle = document.getElementById('stateIdle');
  const stateFile = document.getElementById('stateFile');
  const fileName  = document.getElementById('fileName');
  const fileSize  = document.getElementById('fileSize');

  const ALLOWED = ['xlsx', 'xls', 'csv'];

  // --- Browse button ---
  btnBrowse.addEventListener('click', function (e) {
    e.stopPropagation();
    fileInput.click();
  });

  fileInput.addEventListener('change', function () {
    if (fileInput.files.length) handleFile(fileInput.files[0]);
  });

  // --- Drag & drop ---
  dropZone.addEventListener('dragover', function (e) {
    e.preventDefault();
    dropZone.classList.add('dragover');
  });

  ['dragleave', 'dragend'].forEach(function (evt) {
    dropZone.addEventListener(evt, function () {
      dropZone.classList.remove('dragover');
    });
  });

  dropZone.addEventListener('drop', function (e) {
    e.preventDefault();
    dropZone.classList.remove('dragover');
    if (e.dataTransfer.files.length) handleFile(e.dataTransfer.files[0]);
  });

  // --- Remove file ---
  btnRemove.addEventListener('click', function () {
    clearFile();
  });

  // --- Upload ---
  btnUpload.addEventListener('click', function () {
    // TODO: hook up to backend upload handler
    showToast('File uploaded successfully');
    clearFile();
  });

  // --- Helpers ---
  function handleFile(file) {
    const ext = file.name.split('.').pop().toLowerCase();
    if (!ALLOWED.includes(ext)) {
      showToast('Please upload an .xlsx, .xls, or .csv file', true);
      return;
    }
    fileName.textContent = file.name;
    fileSize.textContent = formatSize(file.size);
    stateIdle.style.display = 'none';
    stateFile.style.display = 'block';
    dropZone.classList.add('has-file');
    btnUpload.disabled = false;
  }

  function clearFile() {
    fileInput.value = '';
    stateIdle.style.display = 'block';
    stateFile.style.display = 'none';
    dropZone.classList.remove('has-file');
    btnUpload.disabled = true;
  }

  function formatSize(bytes) {
    if (bytes < 1024)        return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
  }

  function showToast(message, isError) {
    let toast = document.querySelector('.toast');
    if (!toast) {
      toast = document.createElement('div');
      toast.className = 'toast';
      document.body.appendChild(toast);
    }
    toast.textContent = message;
    toast.style.background = isError ? '#8B2020' : '#1B6B35';
    toast.classList.add('show');
    setTimeout(function () { toast.classList.remove('show'); }, 3000);
  }

});
