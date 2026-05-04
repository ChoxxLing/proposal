const loginForm = document.getElementById('loginForm');
const loginMessage = document.getElementById('loginMessage');

loginForm.addEventListener('submit', async (event) => {
  event.preventDefault();
  loginMessage.textContent = 'Signing in...';

  const res = await fetch('../api/index.php?resource=auth&action=login', {
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

  window.location.href = 'index.php';
});
