document.addEventListener('DOMContentLoaded', async function () {
  const list = document.getElementById('deadline-list');
  await loadDeadlines();

  async function loadDeadlines() {
    const res = await fetch('api/deadlines/list.php', { credentials: 'include' });
    const items = await res.json();

    list.innerHTML = items.map(cardHtml).join('') ||
      '<p class="muted">No deadlines set yet.</p>';
  }

  function cardHtml(d) {
    return '<div class="card deadline-card" data-id="' + d.id + '">' +
      '<div class="deadline-icon">\uD83D\uDCC5</div>' +
      '<div class="deadline-body">' +
        '<div class="row-between"><h4>' + d.title + '</h4><span class="badge badge-blue">Upcoming</span></div>' +
        '<p class="deadline-date">' + d.date_label + '</p>' +
        '<p class="deadline-desc">' + (d.description || '') + '</p>' +
      '</div>' +
      '<button class="icon-btn-sm" data-action="edit" type="button">\u270E</button>' +
      '</div>';
  }

  list.addEventListener('click', async function (e) {
    const btn = e.target.closest('button[data-action="edit"]');
    if (!btn) return;
    const card = btn.closest('.deadline-card');
    const id = card.dataset.id;

    const currentTitle = card.querySelector('h4').textContent;
    const currentDate = card.querySelector('.deadline-date').textContent;
    const currentDesc = card.querySelector('.deadline-desc').textContent;

    const title = prompt('Title:', currentTitle);
    if (title === null) return;
    const date_label = prompt('Date:', currentDate);
    if (date_label === null) return;
    const description = prompt('Description:', currentDesc);
    if (description === null) return;

    const res = await fetch('api/deadlines/update.php?id=' + id, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include',
      body: JSON.stringify({ title, date_label, description }),
    });

    if (res.ok) {
      await loadDeadlines();
    } else {
      alert('Could not update deadline.');
    }
  });
});
