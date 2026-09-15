<?php
// Central configuration: loads env vars, defines constants. NO SECRETS committed here.
define('BASE_PATH', dirname(__DIR__));

// --- Minimal .env loader (KEY=VALUE, supports quotes and # comments) ---
$envPath = BASE_PATH . '/.env';
if (is_file($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $k = trim($k);
        $v = trim($v);
        $v = preg_replace('/^"(.*)"$/s', '$1', $v);
        $v = preg_replace("/^'(.*)'$/s", '$1', $v);
        if (getenv($k) === false) { putenv("$k=$v"); $_ENV[$k] = $v; }
    }
}

function env($key, $default = null) {
    $val = getenv($key);
    return ($val === false || $val === '') ? $default : $val;
}

// --- Application ---
define('APP_NAME',  env('APP_NAME', 'Product Drive CMS'));
define('APP_ENV',   env('APP_ENV', 'local'));            // local | production
define('APP_DEBUG', filter_var(env('APP_DEBUG', 'true'), FILTER_VALIDATE_BOOLEAN));
define('APP_BASE',  rtrim(env('APP_BASE', ''), '/'));    // set to "/subdir" if not on domain root

// --- Database ---
define('DB_HOST',    env('DB_HOST', '127.0.0.1'));
define('DB_PORT',    env('DB_PORT', '3306'));
define('DB_NAME',    env('DB_NAME', 'product_drive'));
define('DB_USER',    env('DB_USER', 'root'));
define('DB_PASS',    env('DB_PASS', ''));
define('DB_CHARSET', 'utf8mb4');

// --- Paths ---
define('INCLUDES',    BASE_PATH . '/includes');
define('VIEWS',       BASE_PATH . '/views');
define('CONTROLLERS', BASE_PATH . '/controllers');
define('UPLOAD_DIR',  BASE_PATH . '/uploads');
define('MAX_UPLOAD_BYTES', 3 * 1024 * 1024); // 3 MB

// --- Error handling driven by APP_DEBUG ---
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

date_default_timezone_set(env('APP_TZ', 'UTC'));
