const loginForm = document.getElementById('loginForm');
const loginMessage = document.getElementById('loginMessage');
const API = window.APP_API || '../api/index.php';
const ADMIN_URL = window.APP_ADMIN_URL || 'admin.php';

loginForm.addEventListener('submit', async (event) => {
  event.preventDefault();
  loginMessage.textContent = 'Signing in...';

  const res = await fetch(`${API}?resource=auth&action=login`, {
    method: 'POST',
    body: new FormData(loginForm)
  });
  const data = await res.json();

  if (!res.ok) {
    const hint = data.debug?.hint ? ` ${data.debug.hint}` : '';
    const found = data.debug ? ` email_found=${data.debug.email_found}` : '';
    loginMessage.textContent = `${data.error || 'Login failed.'}${found}${hint}`;
    return;
  }

  window.location.replace(ADMIN_URL);
});
