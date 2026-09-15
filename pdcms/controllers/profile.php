<?php
// Profile / account settings controller

function profile_route(string $method): void {
    require_login();
    $method === 'POST' ? profile_update() : profile_show();
}

function profile_show(): void {
    $stmt = db()->prepare('SELECT id, name, email, role, status, last_login_at, created_at FROM users WHERE id = ?');
    $stmt->execute([current_user()['id']]);
    $user = $stmt->fetch();
    render('profile', ['title' => 'My Profile', 'active' => 'profile', 'user' => $user]);
    clear_old();
}

function profile_update(): void {
    csrf_verify();
    $id    = (int)current_user()['id'];
    $name  = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $current = (string)($_POST['current_password'] ?? '');
    $new     = (string)($_POST['new_password'] ?? '');
    $confirm = (string)($_POST['confirm_password'] ?? '');

    $errors = [];
    if ($name === '' || mb_strlen($name) > 120) $errors[] = 'Name is required (max 120 chars).';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';

    $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    $changePassword = ($new !== '' || $confirm !== '' || $current !== '');
    if ($changePassword) {
        if (!password_verify($current, $user['password_hash'])) $errors[] = 'Current password is incorrect.';
        if (strlen($new) < 6) $errors[] = 'New password must be at least 6 characters.';
        if ($new !== $confirm) $errors[] = 'New password and confirmation do not match.';
    }

    if ($errors) { set_old($_POST); foreach ($errors as $m) flash('danger', $m); redirect('profile'); }

    try {
        if ($changePassword) {
            db()->prepare('UPDATE users SET name=?, email=?, password_hash=? WHERE id=?')
                ->execute([$name, $email, password_hash($new, PASSWORD_DEFAULT), $id]);
        } else {
            db()->prepare('UPDATE users SET name=?, email=? WHERE id=?')->execute([$name, $email, $id]);
        }
        $_SESSION['user']['name'] = $name;
        $_SESSION['user']['email'] = $email;
        flash('success', 'Profile updated.');
    } catch (PDOException $e) {
        set_old($_POST);
        flash('danger', str_contains($e->getMessage(), 'Duplicate') ? 'That email is already in use.' : 'Could not update profile.');
    }
    redirect('profile');
}
