// Included on every protected page (everything except login.html).
// Confirms a valid PHP session exists before the page is used; otherwise
// bounces back to the login screen. Also wires up the "Sign Out" link.
(async function () {
  try {
    const res = await fetch('api/auth/me.php', { credentials: 'include' });
    if (!res.ok) {
      window.location.href = 'login.html';
    }
  } catch (err) {
    window.location.href = 'login.html';
  }
})();

document.addEventListener('DOMContentLoaded', function () {
  const signOutLink = document.querySelector('.sidebar-footer .nav-item');
  if (signOutLink) {
    signOutLink.addEventListener('click', async function (e) {
      e.preventDefault();
      try {
        await fetch('api/auth/logout.php', { method: 'POST', credentials: 'include' });
      } finally {
        window.location.href = 'login.html';
      }
    });
  }
});
