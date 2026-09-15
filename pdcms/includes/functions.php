<?php
// ---------- Output escaping ----------
function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// ---------- URL helpers ----------
function url($path = ''): string {
    return (APP_BASE === '' ? '' : APP_BASE) . '/' . ltrim($path, '/');
}
function asset($p): string { return url('assets/' . ltrim($p, '/')); }
function upload_url($file): string {
    if (!$file) return '';
    if (preg_match('#^https?://#', $file)) return $file;
    return url('uploads/' . rawurlencode($file));
}
function redirect($path): void {
    $to = preg_match('#^https?://#', $path) ? $path : url($path);
    header('Location: ' . $to);
    exit;
}

// ---------- Flash messages ----------
function flash($type, $msg): void { $_SESSION['flash'][] = ['type' => $type, 'msg' => $msg]; }
function get_flashes(): array { $f = $_SESSION['flash'] ?? []; unset($_SESSION['flash']); return $f; }

// ---------- Old input (form repopulation) ----------
function set_old(array $data): void { $_SESSION['old'] = $data; }
function old($key, $default = '') { return $_SESSION['old'][$key] ?? $default; }
function clear_old(): void { unset($_SESSION['old']); }

// ---------- CSRF ----------
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">'; }
function csrf_verify(): void {
    $t = $_POST['_csrf'] ?? '';
    if (!$t || !hash_equals($_SESSION['csrf'] ?? '', $t)) {
        http_response_code(419);
        die('Invalid or expired security token (CSRF). Please go back and try again.');
    }
}

// ---------- Auth / RBAC ----------
function current_user() { return $_SESSION['user'] ?? null; }
function is_logged_in(): bool { return !empty($_SESSION['user']); }
function is_admin(): bool { return (current_user()['role'] ?? '') === 'admin'; }
function require_login(): void {
    if (!is_logged_in()) { flash('warning', 'Please sign in to continue.'); redirect('login'); }
}
function require_admin(): void {
    require_login();
    if (!is_admin()) { http_response_code(403); flash('danger', 'You do not have permission to access that page.'); redirect('dashboard'); }
}

// ---------- Views / rendering ----------
function view($path, array $vars = []): string {
    extract($vars);
    ob_start();
    include VIEWS . '/' . $path . '.php';
    return ob_get_clean();
}
function render($path, array $vars = []): void {
    $content = view($path, $vars);
    $active  = $vars['active'] ?? '';
    $title   = $vars['title'] ?? APP_NAME;
    include INCLUDES . '/layout_header.php';
    echo $content;
    include INCLUDES . '/layout_footer.php';
}

// ---------- Image upload ----------
function handle_image_upload(string $field, &$error) {
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) return null;
    $f = $_FILES[$field];
    if ($f['error'] !== UPLOAD_ERR_OK) { $error = 'Upload failed (error code ' . $f['error'] . ').'; return false; }
    if ($f['size'] > MAX_UPLOAD_BYTES) { $error = 'Image exceeds the 3 MB limit.'; return false; }
    $info = @getimagesize($f['tmp_name']);
    if ($info === false) { $error = 'The selected file is not a valid image.'; return false; }
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $mime = $info['mime'] ?? '';
    if (!isset($allowed[$mime])) { $error = 'Only JPG, PNG, WEBP and GIF images are allowed.'; return false; }
    if (!is_dir(UPLOAD_DIR)) @mkdir(UPLOAD_DIR, 0755, true);
    $name = bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($f['tmp_name'], UPLOAD_DIR . '/' . $name)) { $error = 'Could not save the uploaded file.'; return false; }
    return $name;
}
function delete_upload($file): void {
    if ($file && !preg_match('#^https?://#', $file)) {
        $p = UPLOAD_DIR . '/' . $file;
        if (is_file($p)) @unlink($p);
    }
}

// ---------- Misc ----------
// ---------- Video upload ----------
function handle_video_upload(string $field, &$error) {
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) return null;
    $f = $_FILES[$field];
    if ($f['error'] !== UPLOAD_ERR_OK) { $error = 'Video upload failed (error code ' . $f['error'] . ').'; return false; }
    $max = 16 * 1024 * 1024; // WhatsApp video limit
    if ($f['size'] > $max) { $error = 'Video exceeds the 16 MB limit.'; return false; }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $f['tmp_name']);
    finfo_close($finfo);
    $allowed = ['video/mp4' => 'mp4', 'video/webm' => 'webm', 'video/quicktime' => 'mov'];
    if (!isset($allowed[$mime])) { $error = 'Only MP4, WEBM or MOV videos are allowed.'; return false; }
    if (!is_dir(UPLOAD_DIR)) @mkdir(UPLOAD_DIR, 0755, true);
    $name = bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($f['tmp_name'], UPLOAD_DIR . '/' . $name)) { $error = 'Could not save the video.'; return false; }
    return $name;
}

// ---------- Absolute URLs (needed for WhatsApp media links) ----------
function abs_base_url(): string {
    $u = trim((string)setting_get('app_url', ''));
    if ($u !== '') return rtrim($u, '/');
    $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
           || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') ? 'https' : 'http';
    $host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $proto . '://' . $host . (APP_BASE !== '' ? APP_BASE : '');
}
function abs_url(string $path): string {
    return preg_match('#^https?://#', $path) ? $path : (abs_base_url() . '/' . ltrim($path, '/'));
}

// ---------- Product media resolvers (Drive-aware) ----------
function product_image_url($p): string {
    if (!empty($p['image_drive_id'])) return drive_view_url($p['image_drive_id']);
    if (!empty($p['image'])) return upload_url($p['image']);
    return '';
}
function product_image_abs($p): string {
    if (!empty($p['image_drive_id'])) return drive_view_url($p['image_drive_id']);
    if (!empty($p['image'])) return preg_match('#^https?://#', $p['image']) ? $p['image'] : abs_url('uploads/' . rawurlencode($p['image']));
    return '';
}
function product_video_abs($p): string {
    if (!empty($p['video_drive_id'])) return drive_view_url($p['video_drive_id']);
    if (!empty($p['video'])) return preg_match('#^https?://#', $p['video']) ? $p['video'] : abs_url('uploads/' . rawurlencode($p['video']));
    return '';
}

function money($n): string {
    $symbol = (string)setting_get('currency_symbol', '$');
    $pos    = setting_get('currency_position', 'before');
    $dec    = (int)setting_get('currency_decimals', 2);
    $ts     = setting_get('currency_thousand_sep', ',');
    $ds     = setting_get('currency_decimal_sep', '.');
    if ($ts === 'none') $ts = '';
    $num = number_format((float)$n, max(0, min(4, $dec)), $ds, $ts);
    if ($symbol === '') return $num;
    $space = setting_bool('currency_space') ? "\u{00A0}" : '';
    return $pos === 'after' ? $num . $space . $symbol : $symbol . $space . $num;
}
function slugify($t): string {
    $t = strtolower(trim($t));
    $t = preg_replace('/[^a-z0-9]+/', '-', $t);
    return trim($t, '-') ?: 'item';
}
