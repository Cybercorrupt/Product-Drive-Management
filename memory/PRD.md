# PRD — Product Drive CMS (Import & Hardening)

## Original problem statement
Import & harden a web-based PHP CMS ("Product Drive CMS"). The original GitHub repo
(`github.com/deexcode-project/Product-Drive-CMS`) returned 404 / was unavailable, so — per
the user's decision — the CMS was **rebuilt from scratch** on the same intended stack
(PHP + MySQL/MariaDB + Bootstrap + vanilla JS), runnable on an XAMPP-equivalent locally and
deployable to cPanel, with security hardening.

## Stack (kept as required)
- PHP 8.2 (PDO MySQL, GD, mbstring), MariaDB 10.11, Bootstrap 5.3, vanilla JS.
- Preview: PHP built-in server on port 3000 (supervisor `pdcms`) + MariaDB (supervisor `mariadb`).
- Production: cPanel (Apache + MySQL/MariaDB) via `.htaccess` front controller.

## User personas
- **Admin**: full access incl. user management.
- **Operator**: catalog operations (products, categories); no user management.

## Core requirements (static)
- Auth/login + secure sessions, admin dashboard, product CRUD, image upload, category mgmt,
  user management, profile.
- Hardening: CSRF, bcrypt hashing, prepared statements, output escaping, RBAC, brute-force
  lockout, secure cookies, security headers, protected config/uploads, no hardcoded secrets
  (env-based config), debug off in production.
- Reinstallable from docs on XAMPP; SQL builds DB without error; deployable to cPanel with
  backup/HTTPS/rollback docs.

## Implemented (2026-09-15)
- Full app at `/app/pdcms`: front controller routing, 6 controllers, server-rendered views.
- Auth with brute-force lockout; RBAC guards; CSRF on all POST forms; PDO prepared statements.
- Product CRUD + search/filter/pagination + validated image upload (type/size/real-image).
- Category CRUD (products set NULL on delete). User management (admin) with self-lockout guards.
- Profile with password change. Dark/light themed Bootstrap UI per design guidelines.
- `sql/schema.sql`, `sql/seed.sql`, idempotent `scripts/seed.php`.
- Docs: `README.md`, `docs/XAMPP_SETUP.md`, `docs/CPANEL_DEPLOY.md`, `docs/TEST_CHECKLIST.md`.
- Verified: login→dashboard (302), products/users render, operator blocked from /users,
  CSRF→419, unauth→login, `/.env`→403.

## Backlog (P1/P2)
- P1: activity/audit log, bulk product actions, CSV import/export.
- P2: product multi-image gallery, soft-delete/restore, per-user avatars, remember-me.
- Deploy phase: fill real cPanel domain/db creds, run AutoSSL, delete demo accounts.
