# cPanel Production Deployment

Target: **PHP 8.1** + **MySQL/MariaDB 10.x** on shared cPanel hosting.

## 1. Prepare the database
1. cPanel → **MySQL® Databases**.
2. Create a database, e.g. `cpuser_productdrive`.
3. Create a user, e.g. `cpuser_pdcms`, with a **strong password**.
4. Add the user to the database with **ALL PRIVILEGES** (this database only — least privilege;
   do not reuse a super/root account).
5. cPanel → **phpMyAdmin** → select the database → **Import** → upload `sql/schema.sql`,
   then (optionally for first launch) `sql/seed.sql`.

## 2. Upload the files
1. Zip the project locally **excluding** `.env` and `uploads/*`.
2. cPanel → **File Manager** → upload & extract.
   - **Root domain**: extract into `public_html/` (document root).
   - **Subdomain**: create the subdomain first, then extract into its document root.
   - **Subfolder** (`example.com/shop`): extract into `public_html/shop` and set `APP_BASE="/shop"`.
3. For best security, keep only web-facing files in the doc root. The included `.htaccess`
   already denies direct access to `config/`, `includes/`, `controllers/`, `sql/`, `.env`.

## 3. Configure environment (secrets stay on the server only)
Create `.env` in the project root via File Manager (copy from `.env.example`):
```
APP_NAME="Product Drive CMS"
APP_ENV="production"
APP_DEBUG="false"
APP_BASE=""
APP_TZ="Asia/Jakarta"

DB_HOST="localhost"
DB_PORT="3306"
DB_NAME="cpuser_productdrive"
DB_USER="cpuser_pdcms"
DB_PASS="<the-strong-password-you-set>"
```
> Never commit `.env`. `APP_DEBUG="false"` disables error display in production.

## 4. Permissions
- Directories: `755`, files: `644`.
- `uploads/` must be writable: `755` (some hosts need `775`). It already ships with an
  `.htaccess` that disables PHP execution inside it.
- `.env` → `600` if your host allows it.

## 5. PHP version & extensions
cPanel → **MultiPHP Manager**: set the domain to **PHP 8.1** (or 8.2).
cPanel → **Select PHP Version / Extensions**: enable **pdo_mysql**, **gd**, **mbstring**, **fileinfo**, **curl**.

## 6. HTTPS
1. cPanel → **SSL/TLS Status** → **Run AutoSSL** (or install a Let's Encrypt cert).
2. In `.htaccess`, uncomment the "Force HTTPS in production" block to redirect HTTP→HTTPS.

## 7. Backup & rollback point
- **Before go-live** and before each change:
  - Database: phpMyAdmin → **Export** (Custom, gzip) → keep the `.sql.gz`.
    Or SSH: `mysqldump -u cpuser_pdcms -p cpuser_productdrive | gzip > backup_$(date +%F).sql.gz`
  - Files: File Manager → compress the project folder → download the archive.
- **Rollback**: restore the files archive and re-import the SQL dump.

## 8. Post-deploy smoke test
Run the checklist in `docs/TEST_CHECKLIST.md` against the live URL, then:
- Log in, change the admin password immediately (Profile screen).
- Create a real admin user and delete/deactivate the demo accounts.
- Confirm error/access logs: cPanel → **Errors** and **Raw Access** logs, or
  `~/logs/` / `error_log` in the project directory.

## 9. Cron (optional)
This app needs no cron by default. If you add scheduled tasks later, configure them in
cPanel → **Cron Jobs** calling a CLI PHP script.
