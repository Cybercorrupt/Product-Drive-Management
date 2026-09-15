<?php /** Shared layout header. Expects: $title, $active. */ ?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? APP_NAME) ?> · <?= e(APP_NAME) ?></title>
    <script>
        // Apply saved theme before paint to avoid flash of wrong theme.
        (function () {
            var t = localStorage.getItem('pdcms-theme');
            if (!t) t = window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
            document.documentElement.setAttribute('data-bs-theme', t);
        })();
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="<?= asset('css/custom-theme.css') ?>" rel="stylesheet">
</head>
<body>
<div class="app-shell">
    <!-- Sidebar -->
    <aside class="sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="appSidebar" data-testid="app-sidebar">
        <div class="sidebar-brand">
            <span class="brand-badge"><i class="bi bi-box-seam-fill"></i></span>
            <div class="brand-text">
                <strong>Product Drive</strong>
                <small>CMS</small>
            </div>
            <button class="btn-close btn-close-white d-lg-none ms-auto" data-bs-dismiss="offcanvas" data-bs-target="#appSidebar" aria-label="Close"></button>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section">Main</div>
            <a href="<?= url('dashboard') ?>" class="nav-item <?= $active === 'dashboard' ? 'active' : '' ?>" data-testid="nav-dashboard">
                <i class="bi bi-grid-1x2"></i><span>Dashboard</span>
            </a>
            <div class="nav-section">Catalog</div>
            <a href="<?= url('products') ?>" class="nav-item <?= $active === 'products' ? 'active' : '' ?>" data-testid="nav-products">
                <i class="bi bi-box"></i><span>Products</span>
            </a>
            <a href="<?= url('categories') ?>" class="nav-item <?= $active === 'categories' ? 'active' : '' ?>" data-testid="nav-categories">
                <i class="bi bi-tags"></i><span>Categories</span>
            </a>
            <div class="nav-section">System</div>
            <?php if (is_admin()): ?>
            <a href="<?= url('users') ?>" class="nav-item <?= $active === 'users' ? 'active' : '' ?>" data-testid="nav-users">
                <i class="bi bi-people"></i><span>Users</span>
            </a>
            <?php endif; ?>
            <a href="<?= url('profile') ?>" class="nav-item <?= $active === 'profile' ? 'active' : '' ?>" data-testid="nav-profile">
                <i class="bi bi-person-gear"></i><span>Profile</span>
            </a>
        </nav>
        <div class="sidebar-foot">
            <span class="badge-secure"><i class="bi bi-shield-check"></i> Hardened build</span>
        </div>
    </aside>

    <!-- Main column -->
    <div class="main-col">
        <header class="topbar" data-testid="topbar">
            <button class="btn btn-icon d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#appSidebar" aria-label="Menu" data-testid="sidebar-toggle">
                <i class="bi bi-list"></i>
            </button>
            <h1 class="topbar-title"><?= e($title ?? APP_NAME) ?></h1>
            <div class="topbar-actions">
                <span class="csrf-chip" title="CSRF protection active" data-testid="csrf-indicator"><i class="bi bi-shield-lock"></i> CSRF</span>
                <a href="<?= url('products/create') ?>" class="btn btn-primary btn-sm d-none d-sm-inline-flex" data-testid="quick-add-product">
                    <i class="bi bi-plus-lg"></i> New Product
                </a>
                <button class="btn btn-icon" id="themeToggle" title="Toggle theme" data-testid="theme-toggle"><i class="bi bi-moon-stars"></i></button>
                <div class="dropdown">
                    <button class="user-chip" data-bs-toggle="dropdown" data-testid="user-menu">
                        <span class="avatar"><?= e(strtoupper(substr(current_user()['name'] ?? 'U', 0, 1))) ?></span>
                        <span class="user-meta d-none d-md-flex">
                            <strong><?= e(current_user()['name'] ?? '') ?></strong>
                            <small class="role-pill role-<?= e(current_user()['role']) ?>"><?= e(ucfirst(current_user()['role'])) ?></small>
                        </span>
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= url('profile') ?>" data-testid="menu-profile"><i class="bi bi-person"></i> My Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= url('logout') ?>" data-testid="menu-logout"><i class="bi bi-box-arrow-right"></i> Sign out</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <main class="content" data-testid="page-content">
            <div class="flash-stack" id="flashStack" data-testid="flash-stack">
                <?php foreach (get_flashes() as $fl): ?>
                    <div class="alert alert-<?= e($fl['type']) ?> alert-dismissible flash-alert" role="alert" data-testid="flash-<?= e($fl['type']) ?>">
                        <?= e($fl['msg']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endforeach; ?>
            </div>
