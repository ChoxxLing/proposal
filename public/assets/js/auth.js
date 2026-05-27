const loginForm = document.getElementById('loginForm');
const loginMessage = document.getElementById('loginMessage');
const recoveryDialog = document.getElementById('recoveryDialog');
const recoveryOpen = document.getElementById('recoveryOpen');
const recoveryClose = document.getElementById('recoveryClose');
const API = window.APP_API || '../api/index.php';
const ADMIN_URL = window.APP_ADMIN_URL || 'admin.php';
let failedAttempts = 0;

function openRecoveryDialog() {
  if (recoveryDialog && !recoveryDialog.open) recoveryDialog.showModal();
}

function showRecoveryOption() {
  if (recoveryOpen) recoveryOpen.classList.remove('is-hidden');
}

recoveryOpen?.addEventListener('click', openRecoveryDialog);
recoveryClose?.addEventListener('click', () => recoveryDialog?.close());

recoveryDialog?.addEventListener('click', (event) => {
  if (event.target === recoveryDialog) recoveryDialog.close();
});

loginForm.addEventListener('submit', async (event) => {
  event.preventDefault();
  loginMessage.textContent = 'Signing in...';

  const res = await fetch(`${API}?resource=auth&action=login`, {
    method: 'POST',
    body: new FormData(loginForm)
  });
  const data = await res.json();

  if (!res.ok) {
    failedAttempts += 1;
    loginMessage.textContent = data.error || 'Incorrect email and password';

    if (failedAttempts >= 3) {
      showRecoveryOption();
      openRecoveryDialog();
    }

    return;
  }

  failedAttempts = 0;
  window.location.replace(ADMIN_URL);
});
