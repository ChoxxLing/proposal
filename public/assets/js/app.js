const API = '../api/index.php';
const state = {
  seminars: [],
  students: [],
  scanner: null,
  lastScan: '',
  lastScanAt: 0
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

function currentSeminarId(selector = '#attendanceSeminar') {
  return Number($(selector)?.value || 0);
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

  $('#smsLogRows').innerHTML = (data.sms_logs || []).map((log) => `
    <tr>
      <td>${esc(log.title)}</td>
      <td>${esc(log.phone)}</td>
      <td><span class="status">${esc(log.status)}</span></td>
      <td>${esc(log.sent_at)}</td>
    </tr>
  `).join('');
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
        <td><strong>${esc(student.last_name)}, ${esc(student.first_name)}</strong><small>${esc(student.student_no)} | ${esc(student.grade_level)} ${esc(student.section)}</small></td>
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

  const res = await fetch(`${API}?resource=attendance&action=report&seminar_id=${seminarId}`);
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

async function startScanner() {
  if (!('BarcodeDetector' in window)) {
    setMessage('#scanStatus', 'BarcodeDetector is not available in this browser. Paste the QR token instead.');
    return;
  }

  const detector = new BarcodeDetector({ formats: ['qr_code'] });
  const video = $('#scannerVideo');
  const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
  video.srcObject = stream;
  await video.play();
  setMessage('#scanStatus', 'Scanning...');

  state.scanner = window.setInterval(async () => {
    const codes = await detector.detect(video);
    if (!codes.length) return;

    const token = codes[0].rawValue.trim();
    const now = Date.now();
    if (token === state.lastScan && now - state.lastScanAt < 3000) return;
    state.lastScan = token;
    state.lastScanAt = now;

    try {
      await submitQrToken(token);
    } catch (error) {
      setMessage('#scanStatus', error.message);
    }
  }, 700);
}

async function refreshLiveData() {
  await Promise.all([loadDashboard(), loadReport()]);
}

function bindEvents() {
  $$('.nav-link').forEach((button) => {
    button.addEventListener('click', () => {
      $$('.nav-link').forEach((item) => item.classList.remove('active'));
      $$('.page').forEach((page) => page.classList.remove('active'));
      button.classList.add('active');
      $(`#page-${button.dataset.page}`).classList.add('active');
    });
  });

  $('#logoutBtn').addEventListener('click', async () => {
    await api('auth', 'logout', { method: 'POST' });
    window.location.href = 'index.php';
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

  $('#startScanner').addEventListener('click', startScanner);

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

  ['#dashboardSeminar', '#reportSeminar'].forEach((selector) => {
    $(selector).addEventListener('change', () => selector === '#reportSeminar' ? loadReport() : loadDashboard());
  });

  $('#downloadReport').addEventListener('click', () => {
    const seminarId = currentSeminarId('#reportSeminar');
    if (seminarId) window.location.href = `${API}?resource=reports&action=csv&seminar_id=${seminarId}`;
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
  bindEvents();
  applyRoleUi();
  await Promise.all([loadStudents(), loadSeminars()]);
  await loadDashboard();
  window.setInterval(loadDashboard, 5000);
}

init().catch((error) => alert(error.message));
