# InternTrack Admin Panel — XAMPP (PHP + MySQL) Backend

This is the same admin panel, now wired to a **PHP + MySQL** backend so it
runs on XAMPP instead of Node.js.

## 1. Where to put the files

XAMPP serves websites from its `htdocs` folder. Copy this entire folder
into it, for example:

- **Windows:** `C:\xampp\htdocs\interntrack\`
- **macOS:** `/Applications/XAMPP/htdocs/interntrack/`
- **Linux:** `/opt/lampp/htdocs/interntrack/`

After copying, `htdocs/interntrack/` should directly contain `login.html`,
`dashboard.html`, the `api/` folder, and so on — **not** nested inside
another subfolder.

```
htdocs/
└── interntrack/
    ├── login.html / .css / .js
    ├── dashboard.html / .css / .js
    ├── user-approval.html / .css / .js
    ├── categories.html / .css / .js
    ├── deadlines.html / .css / .js
    ├── reports.html / .css / .js
    ├── announcements.html / .css / .js
    ├── settings.html / .css / .js
    ├── auth-guard.js
    └── api/
        ├── config.php          ← database connection settings
        ├── setup.php           ← run this once in your browser
        ├── auth/
        │   ├── login.php
        │   ├── logout.php
        │   └── me.php
        ├── users/
        │   ├── list.php
        │   ├── approve.php
        │   ├── reject.php
        │   └── pending_counts.php
        ├── categories/
        │   ├── list.php
        │   ├── create.php
        │   └── delete.php
        ├── deadlines/
        │   ├── list.php
        │   └── update.php
        ├── announcements/
        │   ├── list.php
        │   ├── create.php
        │   └── delete.php
        ├── settings/
        │   ├── get.php
        │   └── update.php
        └── dashboard/
            ├── stats.php
            └── activity.php
```

## 2. Start Apache and MySQL

Open the **XAMPP Control Panel** and click **Start** next to both
**Apache** and **MySQL**. Both need to show green/"Running" before continuing.

## 3. Create the database (one click)

Open your browser and go to:

```
http://localhost/interntrack/api/setup.php
```

This creates the `interntrack` database, all its tables, and seeds it with
demo data. You should see a page like:

```
✔ Database 'interntrack' ready.
✔ Tables created.
✔ Seeded admin login -> admin@university.edu.my / password
✔ Seeded demo users.
✔ Seeded categories.
✔ Seeded deadlines.
✔ Seeded announcements.
✔ Seeded settings.

All done! Go to http://localhost/interntrack/login.html and log in with:
  Email:    admin@university.edu.my
  Password: password
```

It's safe to visit this page more than once — it checks before inserting
duplicate data.

**If you see "Database connection failed"**, open `api/config.php` and
check the `DB_USER` / `DB_PASS` values match your MySQL setup (see
Troubleshooting below).

## 4. Open the app

Go to:
```
http://localhost/interntrack/login.html
```
Log in with:
- Email: `admin@university.edu.my`
- Password: `password`

## How the front-end connects to the backend

This is the part that's specific to how XAMPP serves files, so it's worth
understanding:

### Same-origin, so no CORS headaches
Because Apache serves **both** the HTML pages and the PHP scripts from the
same `http://localhost/interntrack/...` origin, the browser treats them as
the same website. That means session cookies (used to keep you logged in)
work automatically — no extra configuration needed.

### Relative paths matter
Every page's JavaScript calls the API using a **relative path**, for example in `login.js`:

```js
const res = await fetch('api/auth/login.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  credentials: 'include',   // sends/receives the PHP session cookie
  body: JSON.stringify({ email, password }),
});
```

Because there's no leading slash, `api/auth/login.php` resolves **relative
to the page that called it** — so from
`http://localhost/interntrack/login.html` it correctly becomes
`http://localhost/interntrack/api/auth/login.php`.

If you ever rename the `interntrack` folder, or deploy this under a
different path, you do **not** need to change any JavaScript — relative
paths adjust automatically. (This is different from the earlier Node.js
version of this project, which used paths starting with `/api/...`, because
that server was mounted at the domain root instead of a subfolder.)

### Sessions
`api/config.php` calls PHP's built-in `session_start()`. When you log in,
`api/auth/login.php` stores the admin's ID in `$_SESSION['admin_id']`. PHP
automatically sends a `PHPSESSID` cookie to the browser, and the browser
sends it back on every request (because the JS uses `credentials: 'include'`).
Every protected `.php` file calls `require_auth()` from `config.php`, which
checks that session value and returns a 401 error if it's missing.

### auth-guard.js
Every page except `login.html` loads `auth-guard.js` first. On page load it
calls `api/auth/me.php` — if that fails (no valid session), it immediately
redirects back to `login.html`. This stops someone from opening
`dashboard.html` directly without logging in first. It also wires up the
sidebar's "Sign Out" link to call `api/auth/logout.php`.

## Database structure

Everything lives in one MySQL database, `interntrack`, with 6 tables:

| Table | Purpose |
|---|---|
| `admins` | login credentials (bcrypt-hashed passwords via PHP's `password_hash()`) |
| `users` | students/companies/coordinators awaiting or given approval |
| `categories` | internship categories |
| `deadlines` | semester deadline entries |
| `announcements` | posted announcements |
| `settings` | key/value system settings |

You can browse/edit this data directly any time via **phpMyAdmin**
(`http://localhost/phpmyadmin` → select the `interntrack` database).

## API reference

All routes live under `/interntrack/api/` and (except login) require a
logged-in session.

| Method | Route | What it does |
|---|---|---|
| POST | `api/auth/login.php` | `{ email, password }` → logs in, sets session cookie |
| POST | `api/auth/logout.php` | clears the session |
| GET | `api/auth/me.php` | current logged-in admin, or 401 |
| GET | `api/users/list.php?role=student` | list users, optional role filter |
| GET | `api/users/pending_counts.php` | `{ student, company, coordinator }` pending counts |
| POST | `api/users/approve.php?id=3` | mark a user approved |
| POST | `api/users/reject.php?id=3` | mark a user rejected |
| GET | `api/categories/list.php` | list categories |
| POST | `api/categories/create.php` | `{ name, description }` → add one |
| DELETE | `api/categories/delete.php?id=3` | remove one |
| GET | `api/deadlines/list.php` | list deadlines |
| PUT | `api/deadlines/update.php?id=1` | `{ title, date_label, description }` → update one |
| GET | `api/announcements/list.php` | list announcements |
| POST | `api/announcements/create.php` | `{ title, description, audience }` → add one |
| DELETE | `api/announcements/delete.php?id=3` | remove one |
| GET | `api/settings/get.php` | get all settings |
| PUT | `api/settings/update.php` | update one or more settings |
| GET | `api/dashboard/stats.php` | summary counts for the dashboard cards |
| GET | `api/dashboard/activity.php` | recent approval/rejection activity |

## Troubleshooting

**"Database connection failed" on setup.php**
- Check MySQL is actually running (green in the XAMPP Control Panel).
- A fresh XAMPP install has MySQL user `root` with **no password** — this
  matches the defaults already in `api/config.php`. If you changed the
  root password yourself (e.g. in phpMyAdmin → User accounts), update
  `DB_PASS` in both `api/config.php` and `api/setup.php` to match.

**Pages load but data never appears / spinner forever**
- Open the browser DevTools (F12) → Console tab, and look for a red error.
  A `404` on a `.php` file usually means the folder wasn't copied into
  `htdocs` correctly, or you're missing a trailing file.
- A `500` error usually means a PHP error — check the Apache error log,
  normally at `xampp/apache/logs/error.log`.

**Stuck on login / redirected back to login immediately**
- Make sure cookies aren't blocked for `localhost` in your browser.
- Make sure you're accessing the site via `http://localhost/interntrack/...`
  and not by double-clicking the HTML file directly (that opens it as
  `file://...`, which has no PHP behind it at all — it has to go through Apache).

**Want to start over with fresh demo data?**
- Open phpMyAdmin, select the `interntrack` database, and click **Drop**
  (far left panel, "Drop" link) to delete it entirely.
- Visit `api/setup.php` again — it will recreate everything from scratch.

## What's still worth adding later

- **Multiple admin accounts** — right now there's only the one seeded login;
  there's no "create another admin" screen.
- **HTTPS** — fine for local development as-is, but for a real deployment
  you'd want Apache configured with an SSL certificate.
- **Reports page** — the detailed faculty/company breakdown is still static
  demo content; the "Generate PDF" button is simulated rather than a real
  PDF export.
- **Student/Company/Coordinator-facing portals** — this backend only covers
  the admin panel from the original Figma file.
