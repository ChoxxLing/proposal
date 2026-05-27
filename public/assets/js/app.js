const API = window.APP_API || '../api/index.php';
const PUBLIC_URL = window.APP_PUBLIC_URL || 'index.php';
const state = {
  seminars: [],
  students: [],
  scanner: null,
  scannerStream: null,
  scannerBusy: false,
  lastScan: '',
  lastScanAt: 0,
  logoutInProgress: false
};

const $ = (selector) => document.querySelector(selector);
const $$ = (selector) => Array.from(document.querySelectorAll(selector));
const esc = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
  '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
}[char]));

async function api(resource, action = 'index', options = {}) {
  const res = await fetch(`${API}?resource=${resource}&action=${action}`, options);
  const data = await res.json();
  if (!res.ok) throw new Error(data.error || 'Request failed.');
  return data;
}

function setMessage(selector, message) {
  const el = $(selector);
  if (el) el.textContent = message;
}

function setScanStatus(message, type = '') {
  const el = $('#scanStatus');
  if (!el) return;
  el.textContent = message;
  el.classList.remove('ok', 'warn');
  if (type) el.classList.add(type);
}

function currentSeminarId(selector = '#attendanceSeminar') {
  return Number($(selector)?.value || 0);
}

function activatePage(pageName) {
  $$('.nav-link').forEach((item) => item.classList.toggle('active', item.dataset.page === pageName));
  $$('.page').forEach((page) => page.classList.toggle('active', page.id === `page-${pageName}`));
}

function fillSeminarSelects() {
  const previous = {};
  ['#dashboardSeminar', '#attendanceSeminar', '#reportSeminar'].forEach((selector) => {
    previous[selector] = $(selector)?.value || '';
  });

  const options = ['<option value="">Select seminar</option>']
    .concat(state.seminars
      .filter((seminar) => seminar.status !== 'archived')
      .map((seminar) => `<option value="${seminar.id}">${esc(seminar.title)} - ${esc(seminar.seminar_date)}</option>`))
    .join('');

  ['#dashboardSeminar', '#attendanceSeminar', '#reportSeminar'].forEach((selector) => {
    const select = $(selector);
    if (select) {
      select.innerHTML = options;
      select.value = previous[selector];
    }
  });
}

function renderStudentOptions() {
  const select = $('#manualStudent');
  if (!select) return;
  select.innerHTML = state.students
    .map((student) => `<option value="${student.id}">${esc(student.last_name)}, ${esc(student.first_name)} (${esc(student.student_no)})</option>`)
    .join('');
}

async function loadDashboard() {
  const seminarId = $('#dashboardSeminar')?.value || '';
  const data = await api('dashboard', 'index', { method: 'GET' });
  state.seminars = data.seminars || [];
  fillSeminarSelects();

  const scoped = seminarId ? await fetchStats(seminarId) : data.stats;
  $('#metricTotal').textContent = scoped.total_students;
  $('#metricPresent').textContent = scoped.present;
  $('#metricAbsent').textContent = scoped.absent;
  $('#metricRate').textContent = `${scoped.attendance_rate}%`;

  $('#sessionRows').innerHTML = (data.sessions || []).map((session) => {
    const date = new Date(`${session.seminar_date}T00:00:00`);
    const day = Number.isNaN(date.getTime()) ? '--' : String(date.getDate()).padStart(2, '0');
    const month = Number.isNaN(date.getTime()) ? '' : date.toLocaleString('en', { month: 'short' }).toUpperCase();
    const rate = Number(session.attendance_rate || 0);
    const rateText = Number.isInteger(rate) ? String(rate) : rate.toFixed(2).replace(/\.?0+$/, '');

    return `
      <button class="session-row" type="button" data-session-id="${session.id}">
        <span class="session-date"><strong>${esc(day)}</strong><small>${esc(month)}</small></span>
        <span class="session-main">
          <strong>${esc(session.title)}</strong>
          <small>${esc(session.seminar_date)} | ${esc(session.start_time)} - ${esc(session.end_time)}</small>
        </span>
        <span class="session-progress">
          <small>Attendance</small>
          <span><em>${esc(rateText)}%</em><strong>${esc(session.present)}/${esc(session.total_students)}</strong></span>
        </span>
        <span class="session-arrow" aria-hidden="true">&rsaquo;</span>
      </button>
    `;
  }).join('') || '<p class="muted">No seminar sessions yet.</p>';
}

async function fetchStats(seminarId) {
  const res = await fetch(`${API}?resource=dashboard&action=index&seminar_id=${encodeURIComponent(seminarId)}`);
  const data = await res.json();
  if (!res.ok) throw new Error(data.error || 'Unable to load dashboard.');
  return data.stats;
}

async function loadStudents() {
  const search = $('#studentSearch')?.value || '';
  const res = await fetch(`${API}?resource=students&action=index&search=${encodeURIComponent(search)}`);
  const data = await res.json();
  if (!res.ok) throw new Error(data.error || 'Unable to load students.');
  state.students = data.students || [];
  renderStudentOptions();

  $('#studentRows').innerHTML = state.students.map((student) => {
    const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=${encodeURIComponent(student.qr_token)}`;
    return `
      <tr>
        <td><strong>${esc(student.last_name)}, ${esc(student.first_name)}</strong><small>${esc(student.student_no)} | Batch ${esc(student.batch_num)} ${esc(student.section)}</small></td>
        <td>${esc(student.parent_name)}</td>
        <td>${esc(student.parent_phone)}</td>
        <td><img class="qr" src="${qrUrl}" alt="QR for ${esc(student.student_no)}"><small>${esc(student.qr_token)}</small></td>
      </tr>
    `;
  }).join('');
}

async function loadSeminars() {
  const data = await api('seminars', 'index');
  state.seminars = data.seminars || [];
  fillSeminarSelects();
  const canManage = window.APP_USER?.role === 'admin';

  $('#seminarRows').innerHTML = state.seminars.map((seminar) => `
    <tr>
      <td><strong>${esc(seminar.title)}</strong><small>${esc(seminar.description)}</small></td>
      <td>${esc(seminar.seminar_date)}<small>${esc(seminar.start_time)} - ${esc(seminar.end_time)}</small></td>
      <td>${esc(seminar.venue)}</td>
      <td><span class="status">${esc(seminar.status)}</span></td>
      <td class="row-actions ${canManage ? '' : 'is-hidden'}">
        <button type="button" data-edit-seminar="${seminar.id}">Edit</button>
        <button type="button" data-sms-seminar="${seminar.id}">SMS</button>
        <button type="button" data-archive-seminar="${seminar.id}">Archive</button>
      </td>
    </tr>
  `).join('');
}

async function loadReport() {
  const seminarId = currentSeminarId('#reportSeminar');
  if (!seminarId) {
    $('#reportRows').innerHTML = '';
    return;
  }

  const status = $('#reportStatus')?.value || '';
  const statusParam = status ? `&status=${encodeURIComponent(status)}` : '';
  const res = await fetch(`${API}?resource=attendance&action=report&seminar_id=${seminarId}${statusParam}`);
  const data = await res.json();
  if (!res.ok) throw new Error(data.error || 'Unable to load report.');

  $('#reportRows').innerHTML = data.rows.map((row) => `
    <tr>
      <td><strong>${esc(row.last_name)}, ${esc(row.first_name)}</strong><small>${esc(row.student_no)}</small></td>
      <td>${esc(row.parent_name)}<small>${esc(row.parent_phone)}</small></td>
      <td><span class="status ${row.status === 'present' ? 'ok' : 'warn'}">${esc(row.status)}</span></td>
      <td>${esc(row.check_in_method)}</td>
      <td>${esc(row.checked_in_at)}</td>
    </tr>
  `).join('');
}

async function submitQrToken(token) {
  const seminarId = currentSeminarId();
  if (!seminarId) throw new Error('Select a seminar first.');

  const body = new URLSearchParams({ seminar_id: seminarId, qr_token: token });
  const data = await api('attendance', 'qr', { method: 'POST', body });
  const name = `${data.student.first_name} ${data.student.last_name}`;
  setMessage('#scanStatus', data.duplicate ? `${name} was already checked in.` : `${name} checked in successfully.`);
  setMessage('#manualMessage', data.duplicate ? `${name} was already checked in.` : `${name} checked in successfully.`);
  await refreshLiveData();
}

function scannerErrorMessage(error) {
  if (!error) return 'Unable to start the camera scanner.';
  if (error.name === 'NotAllowedError' || error.name === 'SecurityError') {
    return 'Camera permission was blocked. Allow camera access in your browser settings, then try again.';
  }
  if (error.name === 'NotFoundError' || error.name === 'DevicesNotFoundError') {
    return 'No camera was found on this device.';
  }
  if (error.name === 'NotReadableError' || error.name === 'TrackStartError') {
    return 'The camera is already in use by another app or browser tab.';
  }
  return error.message || 'Unable to start the camera scanner.';
}

function stopScanner(message = 'Camera stopped.') {
  if (state.scanner) {
    window.clearInterval(state.scanner);
    state.scanner = null;
  }

  if (state.scannerStream) {
    state.scannerStream.getTracks().forEach((track) => track.stop());
    state.scannerStream = null;
  }

  const video = $('#scannerVideo');
  if (video) {
    video.pause();
    video.srcObject = null;
  }

  const button = $('#startScanner');
  if (button) {
    button.textContent = 'Start';
    button.classList.remove('danger');
    button.classList.add('secondary');
  }

  state.scannerBusy = false;
  state.lastScan = '';
  state.lastScanAt = 0;
  setScanStatus(message);
}

async function openCameraStream() {
  try {
    return await navigator.mediaDevices.getUserMedia({
      video: { facingMode: { ideal: 'environment' } },
      audio: false
    });
  } catch (error) {
    if (['OverconstrainedError', 'ConstraintNotSatisfiedError', 'NotFoundError'].includes(error.name)) {
      return navigator.mediaDevices.getUserMedia({ video: true, audio: false });
    }
    throw error;
  }
}

async function handleScannedToken(token) {
  const value = String(token || '').trim();
  if (!value || state.scannerBusy) return;

  const now = Date.now();
  if (value === state.lastScan && now - state.lastScanAt < 3000) return;

  state.scannerBusy = true;
  state.lastScan = value;
  state.lastScanAt = now;
  setScanStatus('QR code detected. Recording attendance...');

  try {
    await submitQrToken(value);
    setScanStatus('QR code scanned and attendance updated.', 'ok');
  } catch (error) {
    setScanStatus(error.message, 'warn');
  } finally {
    window.setTimeout(() => {
      state.scannerBusy = false;
      if (state.scanner) setScanStatus('Scanning...');
    }, 1200);
  }
}

function scanWithJsQr(video, canvas) {
  if (!window.jsQR || !canvas || video.readyState < HTMLMediaElement.HAVE_ENOUGH_DATA) return;

  const width = video.videoWidth;
  const height = video.videoHeight;
  if (!width || !height) return;

  canvas.width = width;
  canvas.height = height;
  const context = canvas.getContext('2d', { willReadFrequently: true });
  context.drawImage(video, 0, 0, width, height);
  const imageData = context.getImageData(0, 0, width, height);
  const code = window.jsQR(imageData.data, width, height, { inversionAttempts: 'dontInvert' });
  if (code?.data) handleScannedToken(code.data);
}

async function startScanner() {
  if (state.scanner) {
    stopScanner();
    return;
  }

  if (!currentSeminarId()) {
    setScanStatus('Select a seminar first.', 'warn');
    return;
  }

  if (!window.isSecureContext) {
    setScanStatus('Camera access is blocked on insecure pages. On your phone, open https://<computer-ip>:8443/admin.php and allow camera permission.', 'warn');
    return;
  }

  if (!navigator.mediaDevices?.getUserMedia) {
    setScanStatus('This browser does not support camera access. Paste the QR token manually instead.', 'warn');
    return;
  }

  const hasNativeScanner = 'BarcodeDetector' in window;
  const hasFallbackScanner = 'jsQR' in window;

  if (!hasNativeScanner && !hasFallbackScanner) {
    setScanStatus('This browser cannot read QR codes here. Paste the QR token manually instead.', 'warn');
    return;
  }

  const video = $('#scannerVideo');
  const canvas = $('#scannerCanvas');
  let detector = null;

  try {
    if (hasNativeScanner) {
      detector = new BarcodeDetector({ formats: ['qr_code'] });
    }

    state.scannerStream = await openCameraStream();
  } catch (error) {
    setScanStatus(scannerErrorMessage(error), 'warn');
    return;
  }

  try {
    video.srcObject = state.scannerStream;
    await video.play();
  } catch (error) {
    stopScanner(scannerErrorMessage(error));
    $('#scanStatus')?.classList.add('warn');
    return;
  }

  const button = $('#startScanner');
  if (button) {
    button.textContent = 'Stop';
    button.classList.remove('secondary');
    button.classList.add('danger');
  }

  setScanStatus(hasNativeScanner ? 'Scanning with native QR detector...' : 'Scanning with fallback QR detector...');

  state.scanner = window.setInterval(async () => {
    try {
      if (detector) {
        let codes = [];
        try {
          codes = await detector.detect(video);
        } catch (error) {
          if (!hasFallbackScanner) throw error;
          detector = null;
          setScanStatus('Native QR detector is unavailable. Switching to fallback scanner...');
          return;
        }
        if (codes.length) {
          await handleScannedToken(codes[0].rawValue);
          return;
        }
      } else {
        scanWithJsQr(video, canvas);
      }
    } catch (error) {
      setScanStatus(scannerErrorMessage(error), 'warn');
    }
  }, 300);
}

async function refreshLiveData() {
  await Promise.all([loadDashboard(), loadReport()]);
}

async function logoutAndReturn() {
  if (state.logoutInProgress) return;
  state.logoutInProgress = true;
  stopScanner('');

  try {
    await api('auth', 'logout', { method: 'POST' });
  } catch (error) {
    console.warn(error);
  } finally {
    window.location.replace(PUBLIC_URL);
  }
}

function setupBackButtonLogout() {
  if (!window.history?.pushState) return;

  window.history.replaceState({ adminDashboard: true }, '', window.location.href);
  window.history.pushState({ adminDashboard: true }, '', window.location.href);

  window.addEventListener('popstate', () => {
    logoutAndReturn();
  });
}

function bindEvents() {
  $$('.nav-link').forEach((button) => {
    button.addEventListener('click', () => {
      activatePage(button.dataset.page);
    });
  });

  $('#logoutBtn').addEventListener('click', () => {
    const dialog = $('#logoutDialog');
    if (dialog && !dialog.open) {
      dialog.showModal();
      return;
    }

    if (!dialog) logoutAndReturn();
  });

  $('#cancelLogout')?.addEventListener('click', () => {
    $('#logoutDialog')?.close();
  });

  $('#confirmLogout')?.addEventListener('click', logoutAndReturn);

  $('#logoutDialog')?.addEventListener('click', (event) => {
    if (event.target === event.currentTarget) event.currentTarget.close();
  });

  $('#studentForm').addEventListener('submit', async (event) => {
    event.preventDefault();
    await api('students', 'store', { method: 'POST', body: new FormData(event.target) });
    event.target.reset();
    setMessage('#studentMessage', 'Student registered and QR token generated.');
    await loadStudents();
  });

  $('#csvForm').addEventListener('submit', async (event) => {
    event.preventDefault();
    const data = await api('students', 'import', { method: 'POST', body: new FormData(event.target) });
    setMessage('#csvMessage', `Imported ${data.result.created}, skipped ${data.result.skipped}.`);
    await loadStudents();
  });

  $('#studentSearch').addEventListener('input', () => loadStudents());

  $('#seminarForm').addEventListener('submit', async (event) => {
    event.preventDefault();
    const form = event.target;
    const action = form.elements.id.value ? 'update' : 'store';
    await api('seminars', action, { method: 'POST', body: new FormData(form) });
    form.reset();
    setMessage('#seminarMessage', 'Seminar saved.');
    await loadSeminars();
  });

  $('#seminarRows').addEventListener('click', async (event) => {
    const editId = event.target.dataset.editSeminar;
    const smsId = event.target.dataset.smsSeminar;
    const archiveId = event.target.dataset.archiveSeminar;

    if (editId) {
      const seminar = state.seminars.find((item) => String(item.id) === editId);
      Object.entries(seminar).forEach(([key, value]) => {
        if ($('#seminarForm').elements[key]) $('#seminarForm').elements[key].value = value ?? '';
      });
    }

    if (smsId) {
      await api('seminars', 'send_sms', { method: 'POST', body: new URLSearchParams({ id: smsId }) });
      setMessage('#seminarMessage', 'SMS notifications queued and logged.');
      await loadDashboard();
    }

    if (archiveId) {
      await api('seminars', 'archive', { method: 'POST', body: new URLSearchParams({ id: archiveId }) });
      await loadSeminars();
    }
  });

  $('#viewAllSessions').addEventListener('click', () => {
    activatePage('reports');
  });

  $('#sessionRows').addEventListener('click', async (event) => {
    const row = event.target.closest('[data-session-id]');
    if (!row) return;

    activatePage('reports');
    $('#reportSeminar').value = row.dataset.sessionId;
    await loadReport();
  });

  $('#startScanner').addEventListener('click', startScanner);
  window.addEventListener('beforeunload', () => stopScanner(''));

  $('#manualForm').addEventListener('submit', async (event) => {
    event.preventDefault();
    const seminarId = currentSeminarId();
    const studentId = $('#manualStudent').value;
    const data = await api('attendance', 'manual', {
      method: 'POST',
      body: new URLSearchParams({ seminar_id: seminarId, student_id: studentId })
    });
    setMessage('#manualMessage', data.duplicate ? 'Student was already checked in.' : 'Manual check-in recorded.');
    await refreshLiveData();
  });

  $('#tokenForm').addEventListener('submit', async (event) => {
    event.preventDefault();
    await submitQrToken(new FormData(event.target).get('qr_token'));
    event.target.reset();
  });

  $('#closeSeminar').addEventListener('click', async () => {
    const seminarId = currentSeminarId();
    const data = await api('attendance', 'close', {
      method: 'POST',
      body: new URLSearchParams({ seminar_id: seminarId })
    });
    setMessage('#manualMessage', `${data.absentees_recorded} absentees recorded.`);
    await refreshLiveData();
  });

  ['#dashboardSeminar', '#reportSeminar', '#reportStatus'].forEach((selector) => {
    $(selector).addEventListener('change', () => selector === '#dashboardSeminar' ? loadDashboard() : loadReport());
  });

  $('#downloadReport').addEventListener('click', () => {
    const seminarId = currentSeminarId('#reportSeminar');
    const status = $('#reportStatus')?.value || '';
    const statusParam = status ? `&status=${encodeURIComponent(status)}` : '';
    if (seminarId) window.location.href = `${API}?resource=reports&action=csv&seminar_id=${seminarId}${statusParam}`;
  });
}

function applyRoleUi() {
  if (window.APP_USER?.role === 'admin') return;
  ['#studentForm', '#csvForm', '#seminarForm'].forEach((selector) => {
    const el = $(selector);
    if (el) el.classList.add('is-hidden');
  });
  $('#closeSeminar')?.classList.add('is-hidden');
  $$('.row-actions').forEach((el) => el.classList.add('is-hidden'));
}

async function init() {
  setupBackButtonLogout();
  bindEvents();
  applyRoleUi();
  await Promise.all([loadStudents(), loadSeminars()]);
  await loadDashboard();
  window.setInterval(loadDashboard, 5000);
}

init().catch((error) => alert(error.message));
