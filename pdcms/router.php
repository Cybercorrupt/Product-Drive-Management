<?php
// Router for the PHP built-in server (preview / XAMPP-equivalent).
// Serves existing static files directly; everything else goes to index.php.
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $path;

if ($path !== '/' && is_file($file)) {
    // Block direct access to sensitive files even via the dev server.
    $base = basename($file);
    if ($base === '.env' || str_ends_with($base, '.env') || $path === '/config/config.php') {
        http_response_code(403);
        echo 'Forbidden';
        return true;
    }
    return false; // let the built-in server serve the static asset
}

require __DIR__ . '/index.php';
