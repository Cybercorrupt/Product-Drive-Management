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

## Integrations added (2026-09-15)
- **Admin Settings page** (`/settings`, admin-only): DB-backed `settings` table, secrets shown masked.
- **Google Drive storage** (Service Account via JWT+cURL, no SDK): product image/video uploaded to a
  shared Drive folder when enabled; automatic **local fallback**; "Test connection" + "Sync local files"
  actions. Files served via `drive.google.com/uc?export=view&id=...`. Helper: `includes/google_drive.php`.
- **WhatsApp bot** (Meta Cloud API): public webhook `/whatsapp/webhook` (GET verify + POST with
  `X-Hub-Signature-256` HMAC check); searches active products by name/SKU/description and replies with
  details + image + optional video. Config + test-send in Settings. Controller: `controllers/whatsapp.php`.
- **Product video** upload (MP4/WEBM/MOV ≤16MB) added to product form; new columns
  `products.image_drive_id/video/video_drive_id` (migrate via `scripts/migrate.php`).
- Docs: `docs/INTEGRATIONS.md`. Verified: Settings CRUD + secret masking/retention, CSRF 419,
  webhook verify/signature, RBAC, local-fallback product create — testing agent 36/36 pass.
- NOTE: live Google Drive & WhatsApp calls require real credentials the user will add later; only the
  config UI, webhook verify/signature, and local fallback were exercised.
