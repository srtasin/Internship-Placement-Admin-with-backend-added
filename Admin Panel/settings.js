document.addEventListener('DOMContentLoaded', async function () {
  const systemName = document.getElementById('system-name');
  const universityName = document.getElementById('university-name');
  const adminEmail = document.getElementById('admin-email');
  const currentSemester = document.getElementById('current-semester');
  const sessionTimeout = document.getElementById('session-timeout');
  const passwordPolicy = document.getElementById('password-policy');
  const toggles = document.querySelectorAll('.toggle-row input[type="checkbox"]');
  const saveBtn = document.getElementById('save-settings-btn');
  const resetBtn = document.getElementById('reset-settings-btn');

  let defaults = {};

  await loadSettings();

  async function loadSettings() {
    const res = await fetch('api/settings/get.php', { credentials: 'include' });
    const s = await res.json();
    defaults = s;
    applySettings(s);
  }

  function applySettings(s) {
    systemName.value = s.system_name || '';
    universityName.value = s.university_name || '';
    adminEmail.value = s.admin_email || '';
    setSelect(currentSemester, s.current_semester);
    sessionTimeout.value = s.session_timeout || '30';
    setSelect(passwordPolicy, s.password_policy);

    toggles[0].checked = s.notif_email_applications === '1';
    toggles[1].checked = s.notif_sms_deadlines === '1';
    toggles[2].checked = s.notif_system_announcements === '1';
    toggles[3].checked = s.notif_weekly_digest === '1';
    toggles[4].checked = s.security_2fa === '1';
  }

  function setSelect(selectEl, value) {
    if (!value) return;
    for (const opt of selectEl.options) {
      if (opt.value === value || opt.textContent === value) {
        selectEl.value = opt.value || opt.textContent;
        return;
      }
    }
  }

  saveBtn.addEventListener('click', async function () {
    const payload = {
      system_name: systemName.value,
      university_name: universityName.value,
      admin_email: adminEmail.value,
      current_semester: currentSemester.value,
      session_timeout: sessionTimeout.value,
      password_policy: passwordPolicy.value,
      notif_email_applications: toggles[0].checked ? '1' : '0',
      notif_sms_deadlines: toggles[1].checked ? '1' : '0',
      notif_system_announcements: toggles[2].checked ? '1' : '0',
      notif_weekly_digest: toggles[3].checked ? '1' : '0',
      security_2fa: toggles[4].checked ? '1' : '0',
    };

    const originalText = saveBtn.textContent;
    saveBtn.disabled = true;

    const res = await fetch('api/settings/update.php', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include',
      body: JSON.stringify(payload),
    });

    if (res.ok) {
      defaults = await res.json();
      saveBtn.textContent = 'Saved \u2713';
      setTimeout(function () {
        saveBtn.textContent = originalText;
        saveBtn.disabled = false;
      }, 1500);
    } else {
      alert('Could not save settings.');
      saveBtn.disabled = false;
    }
  });

  resetBtn.addEventListener('click', function () {
    applySettings(defaults);
  });
});
