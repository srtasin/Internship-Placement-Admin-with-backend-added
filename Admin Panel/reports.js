document.addEventListener('DOMContentLoaded', async function () {
  try {
    const res = await fetch('api/dashboard/stats.php', { credentials: 'include' });
    const stats = await res.json();
    const totalEl = document.querySelector('[data-stat="report-total-students"]');
    if (totalEl) totalEl.textContent = stats.totalStudents;
  } catch (err) {
    console.error('Failed to load report stats', err);
  }

  const modal = document.getElementById('report-modal');
  function open() { modal.classList.add('active'); }
  function close() { modal.classList.remove('active'); }

  document.getElementById('generate-report-btn').addEventListener('click', open);
  document.getElementById('close-report-modal').addEventListener('click', close);
  document.getElementById('download-report-btn').addEventListener('click', close);
  modal.addEventListener('click', function (e) { if (e.target === modal) close(); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
});
