<?php
// User management controller (admin only)

function users_route(string $action, array $seg, string $method): void {
    require_admin();
    switch ($action) {
        case '':       users_index(); break;
        case 'create': users_create(); break;
        case 'store':  users_store(); break;
        case 'edit':   users_edit($seg[2] ?? null); break;
        case 'update': users_update(); break;
        case 'delete': users_delete(); break;
        default: http_response_code(404); render('errors/404', ['title' => 'Not Found', 'active' => 'users']);
    }
}

function users_index(): void {
    $users = db()->query('SELECT id, name, email, role, status, last_login_at, created_at FROM users ORDER BY created_at DESC')->fetchAll();
    render('users/index', ['title' => 'Users', 'active' => 'users', 'users' => $users]);
}

function users_create(): void {
    render('users/form', ['title' => 'Add User', 'active' => 'users', 'user' => null]);
    clear_old();
}

function users_edit($id): void {
    $id = (int)$id;
    $stmt = db()->prepare('SELECT id, name, email, role, status FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    if (!$user) { flash('danger', 'User not found.'); redirect('users'); }
    render('users/form', ['title' => 'Edit User', 'active' => 'users', 'user' => $user]);
    clear_old();
}

function users_validate_common(array $in, &$errors): array {
    $name  = trim($in['name'] ?? '');
    $email = strtolower(trim($in['email'] ?? ''));
    $role  = in_array($in['role'] ?? '', ['admin', 'operator'], true) ? $in['role'] : 'operator';
    $status= in_array($in['status'] ?? '', ['active', 'inactive'], true) ? $in['status'] : 'active';

    if ($name === '' || mb_strlen($name) > 120) $errors['name'] = 'Name is required (max 120 chars).';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'A valid email is required.';

    return ['name' => $name, 'email' => $email, 'role' => $role, 'status' => $status];
}

function users_store(): void {
    csrf_verify();
    $errors = [];
    $data = users_validate_common($_POST, $errors);
    $password = (string)($_POST['password'] ?? '');
    if (strlen($password) < 6) $errors['password'] = 'Password must be at least 6 characters.';

    if ($errors) { set_old($_POST); foreach ($errors as $m) flash('danger', $m); redirect('users/create'); }

    try {
        db()->prepare('INSERT INTO users (name, email, password_hash, role, status) VALUES (?,?,?,?,?)')
            ->execute([$data['name'], $data['email'], password_hash($password, PASSWORD_DEFAULT), $data['role'], $data['status']]);
        flash('success', 'User created.');
    } catch (PDOException $e) {
        set_old($_POST);
        flash('danger', str_contains($e->getMessage(), 'Duplicate') ? 'That email is already registered.' : 'Could not create user.');
        redirect('users/create');
    }
    redirect('users');
}

function users_update(): void {
    csrf_verify();
    $id = (int)($_POST['id'] ?? 0);
    $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    if (!$existing) { flash('danger', 'User not found.'); redirect('users'); }

    $errors = [];
    $data = users_validate_common($_POST, $errors);
    $password = (string)($_POST['password'] ?? '');
    if ($password !== '' && strlen($password) < 6) $errors['password'] = 'New password must be at least 6 characters.';

    // Guard: do not let an admin lock themselves out (self demotion / self deactivate).
    if ($id === (int)current_user()['id']) {
        if ($data['role'] !== 'admin') $errors['role'] = 'You cannot change your own role.';
        if ($data['status'] !== 'active') $errors['status'] = 'You cannot deactivate your own account.';
    }

    if ($errors) { set_old($_POST); foreach ($errors as $m) flash('danger', $m); redirect('users/edit/' . $id); }

    try {
        if ($password !== '') {
            db()->prepare('UPDATE users SET name=?, email=?, role=?, status=?, password_hash=? WHERE id=?')
                ->execute([$data['name'], $data['email'], $data['role'], $data['status'], password_hash($password, PASSWORD_DEFAULT), $id]);
        } else {
            db()->prepare('UPDATE users SET name=?, email=?, role=?, status=? WHERE id=?')
                ->execute([$data['name'], $data['email'], $data['role'], $data['status'], $id]);
        }
        flash('success', 'User updated.');
    } catch (PDOException $e) {
        set_old($_POST);
        flash('danger', str_contains($e->getMessage(), 'Duplicate') ? 'That email is already registered.' : 'Could not update user.');
        redirect('users/edit/' . $id);
    }
    redirect('users');
}

function users_delete(): void {
    csrf_verify();
    $id = (int)($_POST['id'] ?? 0);
    if ($id === (int)current_user()['id']) { flash('danger', 'You cannot delete your own account.'); redirect('users'); }
    db()->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
    flash('success', 'User deleted.');
    redirect('users');
}
