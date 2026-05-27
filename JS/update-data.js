// =========================================
// Highrise – Update Data JS
// =========================================

document.addEventListener('DOMContentLoaded', function () {

  const ALLOWED = ['xlsx', 'xls', 'csv'];

  initUploadZone({
    dropZone:   document.getElementById('dropZone'),
    fileInput:  document.getElementById('fileInput'),
    btnBrowse:  document.getElementById('btnBrowse'),
    btnRemove:  document.getElementById('btnRemove'),
    btnUpload:  document.getElementById('btnUpload'),
    stateIdle:  document.getElementById('stateIdle'),
    stateFile:  document.getElementById('stateFile'),
    fileName:   document.getElementById('fileName'),
    fileSize:   document.getElementById('fileSize'),
    uploadUrl:  'WS/WS_Upload_Financial.php',
    successMsg: 'Financial data uploaded successfully',
    _file:      null   // stores the actual File object
  });

  initUploadZone({
    dropZone:   document.getElementById('dropZoneBudget'),
    fileInput:  document.getElementById('fileInputBudget'),
    btnBrowse:  document.getElementById('btnBrowseBudget'),
    btnRemove:  document.getElementById('btnRemoveBudget'),
    btnUpload:  document.getElementById('btnUploadBudget'),
    stateIdle:  document.getElementById('stateIdleBudget'),
    stateFile:  document.getElementById('stateFileBudget'),
    fileName:   document.getElementById('fileNameBudget'),
    fileSize:   document.getElementById('fileSizeBudget'),
    uploadUrl:  'WS/WS_Upload_Budget.php',
    successMsg: 'Budget data uploaded successfully',
    _file:      null
  });

  function initUploadZone(z) {

    z.btnBrowse.addEventListener('click', function (e) {
      e.stopPropagation();
      z.fileInput.click();
    });

    // File picked via browse
    z.fileInput.addEventListener('change', function () {
      if (z.fileInput.files.length) handleFile(z.fileInput.files[0], z);
    });

    z.dropZone.addEventListener('dragover', function (e) {
      e.preventDefault();
      z.dropZone.classList.add('dragover');
    });

    ['dragleave', 'dragend'].forEach(function (evt) {
      z.dropZone.addEventListener(evt, function () {
        z.dropZone.classList.remove('dragover');
      });
    });

    // File dropped
    z.dropZone.addEventListener('drop', function (e) {
      e.preventDefault();
      z.dropZone.classList.remove('dragover');
      if (e.dataTransfer.files.length) handleFile(e.dataTransfer.files[0], z);
    });

    z.btnRemove.addEventListener('click', function () { clearFile(z); });

    z.btnUpload.addEventListener('click', function () {
      if (!z._file) {
        showToast('No file selected.', true);
        return;
      }

      const formData = new FormData();
      formData.append('file', z._file);   // use stored File object

      z.btnUpload.disabled    = true;
      z.btnUpload.textContent = 'Uploading...';

      fetch(z.uploadUrl, { method: 'POST', body: formData })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (data.success) {
            showToast(data.message || z.successMsg, false);
            clearFile(z);
          } else {
            showToast(data.error || 'Upload failed.', true);
            z.btnUpload.disabled = false;
            restoreBtn(z);
          }
        })
        .catch(function () {
          showToast('Network error. Please try again.', true);
          z.btnUpload.disabled = false;
          restoreBtn(z);
        });
    });
  }

  function handleFile(file, z) {
    const ext = file.name.split('.').pop().toLowerCase();
    if (!ALLOWED.includes(ext)) {
      showToast('Please upload an .xlsx, .xls, or .csv file', true);
      return;
    }
    z._file = file;   // store the File object here
    z.fileName.textContent = file.name;
    z.fileSize.textContent = formatSize(file.size);
    z.stateIdle.style.display = 'none';
    z.stateFile.style.display = 'block';
    z.dropZone.classList.add('has-file');
    z.btnUpload.disabled = false;
  }

  function clearFile(z) {
    z._file = null;
    z.fileInput.value = '';
    z.stateIdle.style.display = 'block';
    z.stateFile.style.display = 'none';
    z.dropZone.classList.remove('has-file');
    z.btnUpload.disabled = true;
    restoreBtn(z);
  }

  function restoreBtn(z) {
    z.btnUpload.innerHTML = `
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <polyline points="16 16 12 12 8 16"/>
        <line x1="12" y1="12" x2="12" y2="21"/>
        <path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/>
      </svg>
      Upload & Save`;
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
