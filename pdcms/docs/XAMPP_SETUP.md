# Local Preview Setup (XAMPP or equivalent)

This app is plain PHP — it runs on XAMPP, MAMP, Laragon, or the built-in PHP server.

## Option A — XAMPP (Windows/macOS/Linux)
1. **Install XAMPP** (PHP 8.1+). Start **Apache** and **MySQL** from the XAMPP control panel.
2. **Copy the project** into the web root:
   - Windows: `C:\xampp\htdocs\pdcms`
   - macOS: `/Applications/XAMPP/htdocs/pdcms`
3. **Create the database** via phpMyAdmin (http://localhost/phpmyadmin):
   - Create a database named `product_drive` (collation `utf8mb4_unicode_ci`).
   - Import `sql/schema.sql`, then `sql/seed.sql`.
   - (Or run `php scripts/seed.php` from a terminal in the project folder.)
4. **Configure env**: copy `.env.example` to `.env` and set:
   ```
   DB_HOST="127.0.0.1"
   DB_PORT="3306"
   DB_NAME="product_drive"
   DB_USER="root"
   DB_PASS=""            # XAMPP default MySQL root has no password
   APP_DEBUG="true"
   APP_BASE="/pdcms"      # because it's in a subfolder of htdocs
   ```
   > `APP_BASE` must match the subfolder. If you use a VirtualHost with the project
   > as the document root, set `APP_BASE=""`.
5. **Enable clean URLs**: the included `.htaccess` needs Apache `mod_rewrite`
   (enabled by default in XAMPP). 
6. **Open**: http://localhost/pdcms/login

### Recommended: dedicated VirtualHost (clean root URLs)
Add to `xampp/apache/conf/extra/httpd-vhosts.conf`:
```apache
<VirtualHost *:80>
    ServerName pdcms.local
    DocumentRoot "C:/xampp/htdocs/pdcms"
    <Directory "C:/xampp/htdocs/pdcms">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```
Add `127.0.0.1 pdcms.local` to your hosts file, set `APP_BASE=""`, restart Apache,
then open http://pdcms.local/login

## Option B — Built-in PHP server (no Apache)
From the project folder:
```bash
php scripts/seed.php          # one-time, seeds DB
php -S localhost:8000 router.php
```
Open http://localhost:8000/login  (uses `router.php` for clean URLs and static files).

## Uploads
Ensure the `uploads/` directory is writable by the web server:
```bash
chmod -R 755 uploads
```

## Troubleshooting
- **Blank page / 500**: set `APP_DEBUG="true"` in `.env` to see the error.
- **DB connection failed**: verify `.env` credentials and that MySQL is running.
- **404 on every page except index**: `mod_rewrite`/`AllowOverride All` not enabled.
- **CSS/JS missing in a subfolder**: `APP_BASE` doesn't match the subfolder name.
