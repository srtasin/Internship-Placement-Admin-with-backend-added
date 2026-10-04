document.addEventListener('DOMContentLoaded', async function () {
  try {
    const [statsRes, activityRes] = await Promise.all([
      fetch('api/dashboard/stats.php', { credentials: 'include' }),
      fetch('api/dashboard/activity.php', { credentials: 'include' }),
    ]);
    const stats = await statsRes.json();
    const activity = await activityRes.json();

    setText('[data-stat="students"]', stats.totalStudents);
    setText('[data-stat="companies"]', stats.totalCompanies);
    setText('[data-stat="coordinators"]', stats.totalCoordinators);
    setText('[data-stat="pending"]', stats.pendingApprovals);
    setText('[data-stat="announcements"]', stats.totalAnnouncements);

    const feed = document.getElementById('activity-feed');
    if (feed) {
      feed.innerHTML = activity.length
        ? activity.map(function (a) {
            var badgeClass = a.role === 'student' ? 'badge-blue'
              : a.role === 'company' ? 'badge-green' : 'badge-purple';
            var verb = a.status === 'approved' ? 'approved' : 'rejected';
            return '<div class="activity-row">' +
              '<div class="activity-left"><span class="activity-dot">\u25CF</span> ' + a.name + ' was ' + verb + '</div>' +
              '<div class="activity-right"><span class="badge ' + badgeClass + '">' + a.role + '</span></div>' +
              '</div>';
          }).join('')
        : '<p class="muted">No recent activity yet.</p>';
    }
  } catch (err) {
    console.error('Failed to load dashboard data', err);
  }

  function setText(selector, value) {
    var el = document.querySelector(selector);
    if (el) el.textContent = value;
  }
});
