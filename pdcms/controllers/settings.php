<?php
// Settings controller (admin only): Google Drive + WhatsApp + general.

function settings_route(string $action, array $seg, string $method): void {
    require_admin();
    switch ($action . ':' . $method) {
        case ':GET':                 settings_index(); break;
        case 'general:POST':         settings_save_general(); break;
        case 'drive:POST':           settings_save_drive(); break;
        case 'drive-test:POST':      settings_test_drive(); break;
        case 'drive-sync:POST':      settings_sync_drive(); break;
        case 'whatsapp:POST':        settings_save_whatsapp(); break;
        case 'wa-replies:POST':      settings_save_wa_replies(); break;
        case 'currency:POST':        settings_save_currency(); break;
        case 'whatsapp-test:POST':   settings_test_whatsapp(); break;
        default: http_response_code(404); render('errors/404', ['title' => 'Not Found', 'active' => 'settings']);
    }
}

function settings_pending_sync_count(): int {
    try {
        return (int)db()->query(
            "SELECT COUNT(*) FROM products
             WHERE (image IS NOT NULL AND image <> '' AND image NOT LIKE 'http%' AND (image_drive_id IS NULL OR image_drive_id=''))
                OR (video IS NOT NULL AND video <> '' AND video NOT LIKE 'http%' AND (video_drive_id IS NULL OR video_drive_id=''))"
        )->fetchColumn();
    } catch (Throwable $e) { return 0; }
}

function settings_index(): void {
    render('settings/index', [
        'title'   => 'Settings',
        'active'  => 'settings',
        'pending' => settings_pending_sync_count(),
        'webhook' => abs_base_url() . '/whatsapp/webhook',
    ]);
}

function settings_save_general(): void {
    csrf_verify();
    setting_set('app_url', trim($_POST['app_url'] ?? ''));
    flash('success', 'General settings saved.');
    redirect('settings');
}

function settings_save_currency(): void {
    csrf_verify();
    setting_set('currency_code', strtoupper(trim($_POST['currency_code'] ?? 'USD')) ?: 'USD');
    setting_set('currency_symbol', trim($_POST['currency_symbol'] ?? '$'));
    setting_set('currency_position', ($_POST['currency_position'] ?? 'before') === 'after' ? 'after' : 'before');
    setting_set('currency_decimals', (string)max(0, min(4, (int)($_POST['currency_decimals'] ?? 2))));
    $ts = $_POST['currency_thousand_sep'] ?? ',';
    setting_set('currency_thousand_sep', in_array($ts, [',', '.', ' ', 'none'], true) ? $ts : ',');
    $ds = $_POST['currency_decimal_sep'] ?? '.';
    setting_set('currency_decimal_sep', in_array($ds, ['.', ','], true) ? $ds : '.');
    setting_set('currency_space', isset($_POST['currency_space']) ? '1' : '0');
    flash('success', 'Currency & format saved. Example: ' . money(1234567.5));
    redirect('settings');
}

function settings_save_wa_replies(): void {
    csrf_verify();
    setting_set('wa_max_results', (string)max(1, min(10, (int)($_POST['wa_max_results'] ?? 3))));
    setting_set('wa_send_images', isset($_POST['wa_send_images']) ? '1' : '0');
    setting_set('wa_send_videos', isset($_POST['wa_send_videos']) ? '1' : '0');
    setting_set('wa_welcome_message', trim($_POST['wa_welcome_message'] ?? ''));
    setting_set('wa_no_results_message', trim($_POST['wa_no_results_message'] ?? ''));
    setting_set('wa_reply_template', trim($_POST['wa_reply_template'] ?? ''));
    flash('success', 'WhatsApp bot replies saved.');
    redirect('settings');
}

// Keep existing secret when the field is submitted blank.
function _save_secret(string $key, string $field): void {
    $val = trim($_POST[$field] ?? '');
    if ($val !== '') setting_set($key, $val);
}

function settings_save_drive(): void {
    csrf_verify();
    setting_set('drive_enabled', isset($_POST['drive_enabled']) ? '1' : '0');
    setting_set('drive_folder_id', trim($_POST['drive_folder_id'] ?? ''));
    _save_secret('drive_service_account', 'drive_service_account');
    if (isset($_POST['drive_clear_sa'])) setting_set('drive_service_account', '');
    flash('success', 'Google Drive settings saved.');
    redirect('settings');
}

function settings_test_drive(): void {
    csrf_verify();
    $err = '';
    if (drive_test($err)) {
        flash('success', 'Google Drive connection OK — service account authenticated' . (setting_get('drive_folder_id') ? ' and folder accessible.' : '.'));
    } else {
        flash('danger', 'Drive test failed: ' . $err);
    }
    redirect('settings');
}

function settings_sync_drive(): void {
    csrf_verify();
    if (!drive_is_enabled()) { flash('warning', 'Enable and configure Google Drive first.'); redirect('settings'); }
    $rows = db()->query(
        "SELECT id, image, image_drive_id, video, video_drive_id FROM products
         WHERE (image IS NOT NULL AND image <> '' AND image NOT LIKE 'http%' AND (image_drive_id IS NULL OR image_drive_id=''))
            OR (video IS NOT NULL AND video <> '' AND video NOT LIKE 'http%' AND (video_drive_id IS NULL OR video_drive_id=''))"
    )->fetchAll();
    $n = 0; $err = '';
    foreach ($rows as $r) {
        if ($r['image'] && !preg_match('#^https?://#', $r['image']) && empty($r['image_drive_id'])) {
            $res = drive_upload_local($r['image'], $err);
            if ($res) { db()->prepare('UPDATE products SET image_drive_id=? WHERE id=?')->execute([$res['id'], $r['id']]); $n++; }
        }
        if ($r['video'] && !preg_match('#^https?://#', $r['video']) && empty($r['video_drive_id'])) {
            $res = drive_upload_local($r['video'], $err);
            if ($res) { db()->prepare('UPDATE products SET video_drive_id=? WHERE id=?')->execute([$res['id'], $r['id']]); $n++; }
        }
    }
    flash($n ? 'success' : 'info', $n ? "Synced $n file(s) to Google Drive." : ($err ? 'Sync error: ' . $err : 'Nothing to sync.'));
    redirect('settings');
}

function settings_save_whatsapp(): void {
    csrf_verify();
    setting_set('wa_enabled', isset($_POST['wa_enabled']) ? '1' : '0');
    setting_set('wa_phone_number_id', trim($_POST['wa_phone_number_id'] ?? ''));
    setting_set('wa_graph_version', trim($_POST['wa_graph_version'] ?? 'v23.0') ?: 'v23.0');
    setting_set('wa_verify_token', trim($_POST['wa_verify_token'] ?? ''));
    _save_secret('wa_access_token', 'wa_access_token');
    _save_secret('wa_app_secret', 'wa_app_secret');
    flash('success', 'WhatsApp settings saved.');
    redirect('settings');
}

function settings_test_whatsapp(): void {
    csrf_verify();
    $to = preg_replace('/[^0-9]/', '', $_POST['wa_test_to'] ?? '');
    if ($to === '') { flash('danger', 'Enter a recipient phone number (with country code) to test.'); redirect('settings'); }
    if (!wa_configured()) { flash('danger', 'Set the Access Token and Phone Number ID first.'); redirect('settings'); }
    $err = '';
    $res = wa_send_text(wa_settings(), $to, 'Test message from ' . APP_NAME . ' ✅', $err);
    if ($res !== false) flash('success', 'Test message sent to ' . $to . '.');
    else flash('danger', 'WhatsApp test failed: ' . $err);
    redirect('settings');
}
