document.addEventListener('DOMContentLoaded', async function () {
  const list = document.getElementById('announcement-list');
  const modal = document.getElementById('announcement-modal');

  await loadAnnouncements();

  async function loadAnnouncements() {
    const res = await fetch('api/announcements/list.php', { credentials: 'include' });
    const items = await res.json();

    list.innerHTML = items.map(cardHtml).join('') ||
      '<p class="muted">No announcements yet.</p>';

    document.querySelector('[data-stat="total-announcements"]').textContent = items.length;
  }

  function badgeClassFor(audience) {
    if (audience === 'Students') return 'badge-blue';
    if (audience === 'Coordinators') return 'badge-purple';
    if (audience === 'Companies') return 'badge-green';
    return 'badge-gray';
  }

  function cardHtml(a) {
    return '<div class="card announcement-card" data-id="' + a.id + '">' +
      '<div class="announcement-icon">\uD83D\uDD14</div>' +
      '<div class="announcement-body">' +
        '<p class="mini-title">' + a.title + '</p>' +
        '<div class="row-gap"><span class="badge ' + badgeClassFor(a.audience) + '">' + a.audience + '</span>' +
        '<span class="time">' + a.created_at + '</span></div>' +
      '</div>' +
      '<div><button class="icon-btn-sm" data-action="edit" type="button">\u270E</button> ' +
      '<button class="icon-btn-sm" data-action="delete" type="button">\uD83D\uDDD1</button></div>' +
      '</div>';
  }

  function openModal() { modal.classList.add('active'); }
  function closeModal() {
    modal.classList.remove('active');
    modal.querySelector('input').value = '';
    modal.querySelector('textarea').value = '';
    modal.querySelector('select').selectedIndex = 0;
  }

  document.getElementById('create-announcement-btn').addEventListener('click', openModal);
  document.getElementById('close-announcement-modal').addEventListener('click', closeModal);
  document.getElementById('cancel-announcement-modal').addEventListener('click', closeModal);
  modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeModal(); });

  document.getElementById('publish-announcement-btn').addEventListener('click', async function () {
    const title = modal.querySelector('input').value.trim();
    const description = modal.querySelector('textarea').value.trim();
    const audience = modal.querySelector('select').value;

    if (!title) {
      alert('Title is required.');
      return;
    }

    const res = await fetch('api/announcements/create.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include',
      body: JSON.stringify({ title, description, audience }),
    });

    if (!res.ok) {
      const err = await res.json();
      alert(err.error || 'Could not publish announcement.');
      return;
    }

    closeModal();
    await loadAnnouncements();
  });

  list.addEventListener('click', async function (e) {
    const btn = e.target.closest('button[data-action="delete"]');
    if (!btn) return;
    const card = btn.closest('.announcement-card');
    const id = card.dataset.id;

    if (!confirm('Delete this announcement?')) return;

    const res = await fetch('api/announcements/delete.php?id=' + id, {
      method: 'DELETE',
      credentials: 'include',
    });

    if (res.ok) {
      card.remove();
      document.querySelector('[data-stat="total-announcements"]').textContent =
        list.querySelectorAll('.announcement-card').length;
    } else {
      alert('Could not delete announcement.');
    }
  });
});
