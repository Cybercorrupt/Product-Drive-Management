<?php
// DB-backed application settings (key/value). Cached per request.

function &_settings_store() {
    static $s = ['loaded' => false, 'data' => []];
    return $s;
}

function settings_all(): array {
    $s = &_settings_store();
    if (!$s['loaded']) {
        $s['data'] = [];
        try {
            foreach (db()->query('SELECT k, v FROM settings')->fetchAll() as $r) {
                $s['data'][$r['k']] = $r['v'];
            }
        } catch (Throwable $e) { /* table may not exist yet */ }
        $s['loaded'] = true;
    }
    return $s['data'];
}

function setting_get(string $k, $default = null) {
    $a = settings_all();
    return array_key_exists($k, $a) && $a[$k] !== null && $a[$k] !== '' ? $a[$k] : $default;
}

function setting_has(string $k): bool {
    $a = settings_all();
    return isset($a[$k]) && $a[$k] !== '';
}

function setting_bool(string $k): bool {
    return in_array(strtolower((string)setting_get($k, '')), ['1', 'true', 'on', 'yes'], true);
}

function setting_set(string $k, $v): void {
    db()->prepare('INSERT INTO settings (k, v) VALUES (?, ?) ON DUPLICATE KEY UPDATE v = VALUES(v)')
        ->execute([$k, (string)$v]);
    $s = &_settings_store();
    $s['data'][$k] = (string)$v;
    $s['loaded'] = true;
}

// Masked preview of a stored secret (for display in Settings).
function setting_mask(string $k): string {
    $v = (string)setting_get($k, '');
    if ($v === '') return '';
    $len = strlen($v);
    $tail = substr($v, -4);
    return str_repeat('•', min(12, max(4, $len - 4))) . $tail;
}
