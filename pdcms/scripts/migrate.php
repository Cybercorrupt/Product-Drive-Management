<?php
/*
 * scripts/migrate.php — idempotent schema migration for integrations.
 * Adds the settings table and product media columns (Drive ids + video).
 * Usage:  php scripts/migrate.php
 */
require __DIR__ . '/../config/config.php';
require INCLUDES . '/db.php';

$pdo = db();
echo "Migrating {" . DB_NAME . "} ...\n";

$pdo->exec("CREATE TABLE IF NOT EXISTS settings (
    k VARCHAR(120) NOT NULL,
    v TEXT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (k)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
echo "  settings table ensured\n";

function column_exists(PDO $pdo, string $table, string $col): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?");
    $stmt->execute([$table, $col]);
    return (bool)$stmt->fetchColumn();
}

$adds = [
    'image_drive_id' => "ALTER TABLE products ADD COLUMN image_drive_id VARCHAR(255) NULL AFTER image",
    'video'          => "ALTER TABLE products ADD COLUMN video VARCHAR(255) NULL AFTER image_drive_id",
    'video_drive_id' => "ALTER TABLE products ADD COLUMN video_drive_id VARCHAR(255) NULL AFTER video",
];
foreach ($adds as $col => $sql) {
    if (!column_exists($pdo, 'products', $col)) {
        $pdo->exec($sql);
        echo "  + products.$col\n";
    } else {
        echo "  = products.$col exists\n";
    }
}

// Default settings (only insert if missing)
$defaults = [
    'drive_enabled' => '0',
    'wa_enabled' => '0',
    'wa_graph_version' => 'v23.0',
    'app_url' => '',
];
foreach ($defaults as $k => $v) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM settings WHERE k = ?');
    $stmt->execute([$k]);
    if (!$stmt->fetchColumn()) {
        $pdo->prepare('INSERT INTO settings (k, v) VALUES (?, ?)')->execute([$k, $v]);
        echo "  + setting $k\n";
    }
}

echo "Done.\n";
