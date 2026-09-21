# Student Skill Exchange Platform

CSC 3215 Web Technologies — Group 5. Built with HTML, CSS, JavaScript, PHP, and MySQL.

## What's included

- **Auth**: registration (Skill Provider / Skill Seeker), login, logout, password hashing (`password_hash`/`password_verify`)
- **Profile**: view/edit info, change password, delete account
- **Skill Provider**: add skills, view/track own skills with request stats, accept/decline/complete exchange requests
- **Skill Seeker**: browse & search skills, send exchange requests, view request status, leave ratings & reviews
- **Admin**: manage users & roles, monitor all exchanges (with status filter), platform reports (users by role, requests by status, top-rated providers, most-requested skills)
- Session-based access control (`includes/auth.php`) so each role only sees its own pages
- All queries use prepared statements (mysqli) to prevent SQL injection

## Folder structure

```
skill-exchange-platform/
├── config/db.php          # DB connection settings
├── includes/               # auth.php, functions.php, header.php, footer.php
├── assets/css/style.css
├── sql/schema.sql          # run this first
├── index.php, register.php, login.php, logout.php, dashboard.php, profile.php
├── provider/                # dashboard, add_skill, skills, requests
├── seeker/                  # dashboard, browse_skills, my_requests, review
└── admin/                   # dashboard, users, exchanges, reports
```

## Setup (XAMPP / WAMP / LAMP)

1. Copy the `skill-exchange-platform` folder into your server's web root
   (e.g. `htdocs/` for XAMPP, `www/` for WAMP).
2. Start Apache and MySQL.
3. Open phpMyAdmin (or the `mysql` CLI) and import `sql/schema.sql` — this creates the
   `skill_exchange` database, all 5 tables, and seeds the 3 roles.
4. Check `config/db.php` — the defaults (`root` / no password / `localhost`) match a
   standard XAMPP install. Change them if your setup differs.
5. Visit `http://localhost/skill-exchange-platform/` in your browser.

## Creating the first Admin account

The public registration form only offers **Skill Provider** and **Skill Seeker** (Admin
shouldn't be self-service). To create an admin:

1. Register a normal account through the site.
2. In phpMyAdmin, open the `users` table and change that row's `roleId` to `3`
   (the Admin role from the seed data), **or** run:
   ```sql
   UPDATE users SET roleId = 3 WHERE email = 'youremail@example.com';
   ```
3. Log out and back in — you'll land on the Admin dashboard.

## Matching the proposal

- The database schema in `sql/schema.sql` mirrors the ER diagram exactly:
  `users`, `roles`, `skills`, `exchangeRequests`, `exchangeReviews` (only a `password`
  column was added to `users`, since authentication needs it).
- The page flow follows the activity diagram: Register/Login → Dashboard → Browse
  Skills → Send Request → Accept/Decline → Exchange → Review & Rating → Mark Completed.

## Suggested next steps for the demo/report

- Add a few seed users and skills via the UI so the browse/search page isn't empty.
- Take screenshots of each role's dashboard for your report.
- If your rubric wants it, you can extend `admin/reports.php` with a chart (e.g. Chart.js)
  instead of plain tables.
