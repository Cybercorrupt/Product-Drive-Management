<?php
/*
 * scripts/seed.php — idempotent seeder.
 * Creates the schema (if missing) and inserts demo users, categories and products.
 * Usage:  php scripts/seed.php
 * Reads DB config from the same .env / environment as the app.
 */
require __DIR__ . '/../config/config.php';
require INCLUDES . '/db.php';

echo "Seeding {" . DB_NAME . "} on " . DB_HOST . ":" . DB_PORT . " ...\n";

$schema = file_get_contents(BASE_PATH . '/sql/schema.sql');
db()->exec($schema);
echo "Schema ensured.\n";

// Users (idempotent by email)
$users = [
    ['Site Administrator', 'admin@productdrive.test', 'admin123', 'admin'],
    ['Operator One', 'operator@productdrive.test', 'operator123', 'operator'],
];
foreach ($users as [$name, $email, $pw, $role]) {
    $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if (!$stmt->fetchColumn()) {
        db()->prepare('INSERT INTO users (name, email, password_hash, role, status) VALUES (?,?,?,?,?)')
            ->execute([$name, $email, password_hash($pw, PASSWORD_DEFAULT), $role, 'active']);
        echo "  + user {$email}\n";
    } else {
        echo "  = user {$email} exists\n";
    }
}
$adminId = (int)db()->query("SELECT id FROM users WHERE email='admin@productdrive.test'")->fetchColumn();

// Categories
$cats = [
    ['Tech & Hardware', 'Laptops, desktops and computing gear'],
    ['Mobile Devices', 'Smartphones and tablets'],
    ['Wearables', 'Smartwatches and fitness trackers'],
    ['Audio', 'Headphones, earbuds and speakers'],
];
$catIds = [];
foreach ($cats as [$name, $desc]) {
    $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($name));
    $slug = trim($slug, '-');
    $stmt = db()->prepare('SELECT id FROM categories WHERE slug = ?');
    $stmt->execute([$slug]);
    $id = $stmt->fetchColumn();
    if (!$id) {
        db()->prepare('INSERT INTO categories (name, slug, description) VALUES (?,?,?)')->execute([$name, $slug, $desc]);
        $id = db()->lastInsertId();
        echo "  + category {$name}\n";
    }
    $catIds[$name] = (int)$id;
}

// Products
$products = [
    ['Minimalist Pro Laptop', 'PD-1001', 'Tech & Hardware', 1499.00, 12, 'active', 'https://images.unsplash.com/photo-1738707060349-c92b5b15bf80?crop=entropy&cs=srgb&fm=jpg&q=85&w=400', '14-inch ultralight laptop with all-day battery.'],
    ['Light Blue Smartphone', 'PD-1002', 'Mobile Devices', 799.00, 4, 'active', 'https://images.unsplash.com/photo-1778896135791-a29fff9b8e45?crop=entropy&cs=srgb&fm=jpg&q=85&w=400', 'Flagship phone with a triple-camera system.'],
    ['Minimalist Smartwatch', 'PD-1003', 'Wearables', 249.00, 0, 'active', 'https://images.unsplash.com/photo-1760520338254-1bb9327ca51c?crop=entropy&cs=srgb&fm=jpg&q=85&w=400', 'Health tracking and notifications on your wrist.'],
    ['Wireless Earbuds', 'PD-1004', 'Audio', 129.00, 40, 'active', 'https://images.unsplash.com/photo-1757168120889-4317e57a4849?crop=entropy&cs=srgb&fm=jpg&q=85&w=400', 'Noise-cancelling earbuds with wireless charging.'],
    ['Standing Desk Converter', 'PD-1005', 'Tech & Hardware', 189.00, 2, 'draft', null, 'Adjustable sit-stand desk riser.'],
];
foreach ($products as [$name, $sku, $cat, $price, $stock, $status, $image, $desc]) {
    $stmt = db()->prepare('SELECT id FROM products WHERE sku = ?');
    $stmt->execute([$sku]);
    if (!$stmt->fetchColumn()) {
        db()->prepare('INSERT INTO products (name, sku, category_id, price, stock, status, image, description, created_by) VALUES (?,?,?,?,?,?,?,?,?)')
            ->execute([$name, $sku, $catIds[$cat] ?? null, $price, $stock, $status, $image, $desc, $adminId]);
        echo "  + product {$name}\n";
    } else {
        echo "  = product {$sku} exists\n";
    }
}

echo "Done.\n";
