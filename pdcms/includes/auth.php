<?php
// Session-based authentication with brute-force throttling (session-backed).

function attempt_login(string $email, string $password, &$error): bool {
    $ip  = $_SERVER['REMOTE_ADDR'] ?? 'cli';
    $key = 'la_' . md5($ip . '|' . strtolower($email));
    $rec = $_SESSION[$key] ?? ['count' => 0, 'until' => 0];

    if ($rec['count'] >= 5 && time() < $rec['until']) {
        $mins = max(1, (int)ceil(($rec['until'] - time()) / 60));
        $error = "Too many failed attempts. Please try again in {$mins} minute(s).";
        return false;
    }

    $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([strtolower(trim($email))]);
    $user = $stmt->fetch();

    $ok = $user && password_verify($password, $user['password_hash']);
    if (!$ok || $user['status'] !== 'active') {
        $rec['count']++;
        $rec['until'] = time() + 900; // 15 min lockout window
        $_SESSION[$key] = $rec;
        $error = ($user && $user['status'] !== 'active')
            ? 'This account is inactive. Contact an administrator.'
            : 'Invalid email or password.';
        return false;
    }

    unset($_SESSION[$key]);

    if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
        $nh = password_hash($password, PASSWORD_DEFAULT);
        db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([$nh, $user['id']]);
    }

    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id'    => (int)$user['id'],
        'name'  => $user['name'],
        'email' => $user['email'],
        'role'  => $user['role'],
    ];
    db()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?')->execute([$user['id']]);
    return true;
}

function do_logout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
