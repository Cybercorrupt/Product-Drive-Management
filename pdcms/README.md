# Product Drive CMS

A hardened, self-contained **PHP + MySQL/MariaDB + Bootstrap 5** content management system for
managing a product catalog. Built to run locally on **XAMPP** (or any PHP/MySQL stack) for preview
and to deploy to **cPanel** for production — no framework, no build step.

## Features
- **Authentication** — session login, bcrypt password hashing, brute-force lockout (5 tries / 15 min).
- **Admin dashboard** — catalog stats, recent products, low-stock alerts.
- **Product CRUD** — create / edit / delete, search + category/status filters, pagination.
- **Image upload** — validated (type, size, real-image check), stored in `/uploads`, live preview.
- **Category management** — CRUD with product counts.
- **User management (admin only)** — roles (admin/operator), status, self-lockout protection.
- **Profile** — update name/email and change password.
- **Hardening** — CSRF tokens on every form, PDO prepared statements, output escaping (XSS),
  RBAC, secure session cookies, security headers, protected config/upload dirs.

## Tech
- PHP 8.1+ (tested on 8.2) with PDO MySQL, GD, mbstring.
- MySQL 5.7+ / MariaDB 10.x.
- Bootstrap 5.3 + Bootstrap Icons (CDN) + vanilla JS.

## Quick start
1. Create a database and import `sql/schema.sql`.
2. Copy `.env.example` to `.env` and fill in DB credentials.
3. Seed demo data: `php scripts/seed.php` (or import `sql/seed.sql`).
4. Serve the folder (see `docs/XAMPP_SETUP.md`) and open `/login`.

## Demo accounts (change in production!)
| Role     | Email                        | Password    |
|----------|------------------------------|-------------|
| Admin    | admin@productdrive.test      | admin123    |
| Operator | operator@productdrive.test   | operator123 |

## Documentation
- `docs/XAMPP_SETUP.md` — local preview setup.
- `docs/CPANEL_DEPLOY.md` — production deployment, backup, HTTPS, rollback.
- `docs/TEST_CHECKLIST.md` — pre-deployment QA checklist.

## Structure
```
config/       config.php (reads env) + config.sample.php
includes/     db.php, functions.php, auth.php, layout_*.php
controllers/  auth, dashboard, products, categories, users, profile
views/        server-rendered templates
assets/       css/js
uploads/      user-uploaded images (writable, no PHP execution)
sql/          schema.sql + seed.sql
scripts/      seed.php
index.php     front controller · router.php (dev server) · .htaccess (Apache)
```
