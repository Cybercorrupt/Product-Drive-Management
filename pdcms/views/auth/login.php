<?php /** Standalone auth/login page. */ ?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?> · <?= e(APP_NAME) ?></title>
    <script>(function(){var t=localStorage.getItem('pdcms-theme')||'dark';document.documentElement.setAttribute('data-bs-theme',t);})();</script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@0,400;0,500;0,600;0,700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="<?= asset('css/custom-theme.css') ?>" rel="stylesheet">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-visual">
        <div class="brand">
            <span class="brand-badge"><i class="bi bi-box-seam-fill"></i></span>
            <div class="brand-text"><strong style="color:#fff;font-family:'Outfit';font-size:1.1rem;">Product Drive</strong><br><small style="color:#93c5fd;letter-spacing:.18em;">CMS</small></div>
        </div>
        <div>
            <p class="tag mono">// hardened admin console</p>
            <h2>Manage your product catalog with confidence.</h2>
            <div class="feat mt-4">
                <span><i class="bi bi-shield-check"></i> CSRF, hashed passwords &amp; RBAC</span>
                <span><i class="bi bi-box"></i> Full product CRUD + image uploads</span>
                <span><i class="bi bi-people"></i> Role-based user management</span>
            </div>
        </div>
        <small class="mono" style="color:#475569;position:relative;z-index:1;">PHP <?= e(PHP_VERSION) ?> · MySQL/MariaDB · Bootstrap 5</small>
    </div>

    <div class="auth-form-side">
        <div class="auth-card">
            <h1 class="h3 mb-1">Welcome back</h1>
            <p class="text-secondary mb-4" style="color:var(--muted)!important;">Sign in to the Product Drive CMS.</p>

            <div id="flashStack">
                <?php foreach (get_flashes() as $fl): ?>
                    <div class="alert alert-<?= e($fl['type']) ?> alert-dismissible flash-alert" role="alert" data-testid="flash-<?= e($fl['type']) ?>">
                        <?= e($fl['msg']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endforeach; ?>
            </div>

            <form method="post" action="<?= url('login') ?>" data-testid="login-form" novalidate>
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label" for="email">Email address</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= e(old('email')) ?>" placeholder="admin@productdrive.test" required autofocus data-testid="login-email-input">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <div class="pw-wrap">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Your password" required data-testid="login-password-input">
                        <button type="button" class="pw-toggle" data-pw-toggle="password" tabindex="-1"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2" data-testid="login-submit-btn"><i class="bi bi-box-arrow-in-right me-1"></i> Sign in</button>
            </form>

            <div class="cred-hint mt-4" data-testid="demo-credentials">
                <div class="mb-1"><i class="bi bi-info-circle"></i> Demo accounts</div>
                admin@productdrive.test / admin123<br>
                operator@productdrive.test / operator123
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
