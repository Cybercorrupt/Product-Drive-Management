<?php
// Front controller — all requests route through here.
require __DIR__ . '/config/config.php';

// --- Secure session ---
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
session_name('PDCMS_SESSID');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => $secure,
]);
session_start();

require INCLUDES . '/db.php';
require INCLUDES . '/functions.php';
require INCLUDES . '/settings.php';
require INCLUDES . '/google_drive.php';
require INCLUDES . '/auth.php';
foreach (glob(CONTROLLERS . '/*.php') as $c) require $c;

// --- Security headers ---
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: same-origin');
header('X-XSS-Protection: 0');

// --- Route parsing ---
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if (APP_BASE !== '' && strpos($uri, APP_BASE) === 0) {
    $uri = substr($uri, strlen(APP_BASE));
}
$route    = '/' . trim($uri, '/');
$segments = $route === '/' ? [] : explode('/', trim($route, '/'));
$page     = $segments[0] ?? '';
$action   = $segments[1] ?? '';
$method   = $_SERVER['REQUEST_METHOD'];

// --- Dispatch ---
try {
    switch ($page) {
        case '':
            is_logged_in() ? redirect('dashboard') : redirect('login');
            break;
        case 'login':
            $method === 'POST' ? auth_login_submit() : auth_login_show();
            break;
        case 'logout':
            auth_logout();
            break;
        case 'dashboard':
            dashboard_index();
            break;
        case 'products':
            products_route($action, $segments, $method);
            break;
        case 'categories':
            categories_route($action, $segments, $method);
            break;
        case 'users':
            users_route($action, $segments, $method);
            break;
        case 'profile':
            profile_route($method);
            break;
        case 'settings':
            settings_route($action, $segments, $method);
            break;
        case 'whatsapp':
            whatsapp_route($action, $segments, $method);
            break;
        default:
            http_response_code(404);
            render('errors/404', ['title' => 'Not Found', 'active' => '']);
    }
} catch (Throwable $ex) {
    http_response_code(500);
    if (APP_DEBUG) {
        echo '<pre style="padding:2rem;font:14px monospace;">' . e((string)$ex) . '</pre>';
    } else {
        error_log($ex->getMessage());
        render('errors/500', ['title' => 'Server Error', 'active' => '']);
    }
}
