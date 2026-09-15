<?php
// Dashboard controller

function dashboard_index(): void {
    require_login();
    $pdo = db();

    $stats = [
        'products'   => (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn(),
        'active'     => (int)$pdo->query("SELECT COUNT(*) FROM products WHERE status='active'")->fetchColumn(),
        'categories' => (int)$pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn(),
        'users'      => (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
        'stock_value'=> (float)$pdo->query('SELECT COALESCE(SUM(price*stock),0) FROM products')->fetchColumn(),
        'low_stock'  => (int)$pdo->query('SELECT COUNT(*) FROM products WHERE stock <= 5')->fetchColumn(),
    ];

    $recent = $pdo->query(
        'SELECT p.*, c.name AS category_name
         FROM products p LEFT JOIN categories c ON c.id = p.category_id
         ORDER BY p.created_at DESC LIMIT 6'
    )->fetchAll();

    $lowStock = $pdo->query(
        'SELECT id, name, stock, sku FROM products WHERE stock <= 5 ORDER BY stock ASC LIMIT 6'
    )->fetchAll();

    render('dashboard', [
        'title'    => 'Dashboard',
        'active'   => 'dashboard',
        'stats'    => $stats,
        'recent'   => $recent,
        'lowStock' => $lowStock,
    ]);
}
