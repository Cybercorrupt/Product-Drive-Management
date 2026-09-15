<?php
// Authentication controller

function auth_login_show(): void {
    if (is_logged_in()) redirect('dashboard');
    echo view('auth/login', ['title' => 'Sign In']);
    clear_old();
}

function auth_login_submit(): void {
    csrf_verify();
    $email    = trim($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    set_old(['email' => $email]);

    if ($email === '' || $password === '') {
        flash('danger', 'Email and password are required.');
        redirect('login');
    }

    $error = '';
    if (attempt_login($email, $password, $error)) {
        clear_old();
        flash('success', 'Welcome back, ' . current_user()['name'] . '.');
        redirect('dashboard');
    }
    flash('danger', $error);
    redirect('login');
}

function auth_logout(): void {
    do_logout();
    redirect('login');
}
