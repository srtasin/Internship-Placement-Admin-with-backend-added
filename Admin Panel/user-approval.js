document.addEventListener('DOMContentLoaded', async function () {
  // ---- Tab switching (purely visual, unchanged) ----
  var tabs = document.querySelectorAll('.tab[data-tab]');
  var panels = document.querySelectorAll('.tab-panel');
  tabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
      tabs.forEach(function (t) { t.classList.remove('active'); });
      panels.forEach(function (p) { p.classList.remove('active'); });
      tab.classList.add('active');
      document.getElementById('tab-' + tab.dataset.tab).classList.add('active');
    });
  });

  await loadUsers('student', 'tab-students');
  await loadUsers('company', 'tab-companies');
  await loadUsers('coordinator', 'tab-coordinators');
  await loadPendingCounts();

  async function loadUsers(role, panelId) {
    const res = await fetch('api/users/list.php?role=' + role, { credentials: 'include' });
    const users = await res.json();
    const panel = document.getElementById(panelId);
    const tbody = panel.querySelector('tbody');

    tbody.innerHTML = users.map(rowHtml).join('') ||
      '<tr><td colspan="5" class="muted">No ' + role + ' registrations yet.</td></tr>';

    const countBadge = document.querySelector('.tab[data-tab="' + roleToTab(role) + '"] .tab-count');
    if (countBadge) countBadge.textContent = users.length;
  }

  function roleToTab(role) {
    return role === 'student' ? 'students' : role === 'company' ? 'companies' : 'coordinators';
  }

  function roleLabel(role) {
    return role.charAt(0).toUpperCase() + role.slice(1);
  }

  function rowHtml(user) {
    const statusBadge = user.status === 'approved'
      ? '<span class="badge badge-green">Approved</span>'
      : user.status === 'rejected'
        ? '<span class="badge badge-gray">Rejected</span>'
        : '<span class="badge badge-yellow">Pending</span>';

    const actions = user.status === 'pending'
      ? '<button class="btn btn-primary btn-xs" data-action="approve" data-id="' + user.id + '">\u2713 Approve</button> ' +
        '<button class="btn btn-danger-outline btn-xs" data-action="reject" data-id="' + user.id + '">\u2715 Reject</button>'
      : '<span class="muted">Processed</span>';

    return '<tr data-row-id="' + user.id + '">' +
      '<td>' + user.name + '</td>' +
      '<td>' + user.email + '</td>' +
      '<td><span class="badge badge-blue">' + roleLabel(user.role) + '</span></td>' +
      '<td>' + statusBadge + '</td>' +
      '<td>' + actions + '</td>' +
      '</tr>';
  }

  async function loadPendingCounts() {
    const res = await fetch('api/users/pending_counts.php', { credentials: 'include' });
    const counts = await res.json();
    setText('[data-pending="student"]', counts.student);
    setText('[data-pending="company"]', counts.company);
    setText('[data-pending="coordinator"]', counts.coordinator);
  }

  function setText(selector, value) {
    var el = document.querySelector(selector);
    if (el) el.textContent = value;
  }

  // ---- Approve / Reject (delegated, since rows are rebuilt) ----
  document.querySelector('.content').addEventListener('click', async function (e) {
    const btn = e.target.closest('button[data-action]');
    if (!btn) return;

    const id = btn.dataset.id;
    const action = btn.dataset.action; // 'approve' or 'reject'
    btn.disabled = true;

    try {
      const res = await fetch('api/users/' + action + '.php?id=' + id, {
        method: 'POST',
        credentials: 'include',
      });
      const updated = await res.json();
      if (!res.ok) {
        alert(updated.error || 'Action failed.');
        btn.disabled = false;
        return;
      }

      const row = document.querySelector('tr[data-row-id="' + id + '"]');
      row.outerHTML = rowHtml(updated);
      loadPendingCounts();
    } catch (err) {
      alert('Could not reach the server.');
      btn.disabled = false;
    }
  });
});
