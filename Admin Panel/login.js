document.getElementById('login-form').addEventListener('submit', async function (e) {
  e.preventDefault();

  const email = document.getElementById('email').value.trim();
  const password = document.getElementById('password').value;
  const submitBtn = e.target.querySelector('button[type="submit"]');
  const originalText = submitBtn.textContent;

  submitBtn.textContent = 'Signing in...';
  submitBtn.disabled = true;

  try {
    // Relative path: resolves to .../api/auth/login.php next to this page
    const res = await fetch('api/auth/login.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include', // send/receive the PHP session cookie
      body: JSON.stringify({ email, password }),
    });

    const data = await res.json();

    if (!res.ok) {
      alert(data.error || 'Login failed.');
      submitBtn.textContent = originalText;
      submitBtn.disabled = false;
      return;
    }

    window.location.href = 'dashboard.html';
  } catch (err) {
    alert('Could not reach the server. Is Apache/MySQL running in XAMPP?');
    submitBtn.textContent = originalText;
    submitBtn.disabled = false;
  }
});
