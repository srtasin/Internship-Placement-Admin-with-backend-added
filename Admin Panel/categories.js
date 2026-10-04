document.addEventListener('DOMContentLoaded', async function () {
  const tbody = document.getElementById('category-table-body');
  const modal = document.getElementById('category-modal');

  await loadCategories();

  async function loadCategories() {
    const res = await fetch('api/categories/list.php', { credentials: 'include' });
    const categories = await res.json();

    tbody.innerHTML = categories.map(function (c) {
      return '<tr data-id="' + c.id + '">' +
        '<td>' + c.name + '</td>' +
        '<td>' + (c.description || '') + '</td>' +
        '<td>' + c.internship_count + '</td>' +
        '<td><button class="icon-btn-sm" data-action="edit" type="button">\u270E</button> ' +
        '<button class="icon-btn-sm" data-action="delete" type="button">\uD83D\uDDD1</button></td>' +
        '</tr>';
    }).join('') || '<tr><td colspan="4" class="muted">No categories yet.</td></tr>';
  }

  function openModal() { modal.classList.add('active'); }
  function closeModal() {
    modal.classList.remove('active');
    modal.querySelector('input').value = '';
    modal.querySelector('textarea').value = '';
  }

  document.getElementById('add-category-btn').addEventListener('click', openModal);
  document.getElementById('close-category-modal').addEventListener('click', closeModal);
  document.getElementById('cancel-category-modal').addEventListener('click', closeModal);
  modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeModal(); });

  document.getElementById('save-category-modal').addEventListener('click', async function () {
    const name = modal.querySelector('input').value.trim();
    const description = modal.querySelector('textarea').value.trim();

    if (!name) {
      alert('Category name is required.');
      return;
    }

    const res = await fetch('api/categories/create.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include',
      body: JSON.stringify({ name, description }),
    });

    if (!res.ok) {
      const err = await res.json();
      alert(err.error || 'Could not add category.');
      return;
    }

    closeModal();
    await loadCategories();
  });

  tbody.addEventListener('click', async function (e) {
    const btn = e.target.closest('button[data-action="delete"]');
    if (!btn) return;
    const row = btn.closest('tr');
    const id = row.dataset.id;

    if (!confirm('Delete this category?')) return;

    const res = await fetch('api/categories/delete.php?id=' + id, {
      method: 'DELETE',
      credentials: 'include',
    });

    if (res.ok) {
      row.remove();
    } else {
      alert('Could not delete category.');
    }
  });
});
