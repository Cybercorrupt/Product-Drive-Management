<?php
// Google Drive storage via a Service Account (JWT -> OAuth2 access token -> Drive REST).
// Lightweight: uses cURL + OpenSSL only, no Composer/SDK required (cPanel-friendly).

function drive_settings(): array {
    return [
        'enabled' => setting_bool('drive_enabled'),
        'folder'  => trim((string)setting_get('drive_folder_id', '')),
        'sa'      => trim((string)setting_get('drive_service_account', '')),
    ];
}

function drive_is_enabled(): bool {
    $c = drive_settings();
    return $c['enabled'] && $c['sa'] !== '';
}

function _b64url(string $d): string {
    return rtrim(strtr(base64_encode($d), '+/', '-_'), '=');
}

function drive_access_token(&$err = ''): ?string {
    static $cached = null;
    if ($cached) return $cached;
    $c = drive_settings();
    $json = json_decode($c['sa'], true);
    if (!$json || empty($json['client_email']) || empty($json['private_key'])) {
        $err = 'Invalid service account JSON (missing client_email / private_key).';
        return null;
    }
    $tokenUri = $json['token_uri'] ?? 'https://oauth2.googleapis.com/token';
    $now = time();
    $header = ['alg' => 'RS256', 'typ' => 'JWT'];
    $claim  = [
        'iss'   => $json['client_email'],
        'scope' => 'https://www.googleapis.com/auth/drive',
        'aud'   => $tokenUri,
        'iat'   => $now,
        'exp'   => $now + 3600,
    ];
    $segments = _b64url(json_encode($header)) . '.' . _b64url(json_encode($claim));
    $sig = '';
    if (!openssl_sign($segments, $sig, $json['private_key'], 'sha256WithRSAEncryption')) {
        $err = 'Failed to sign JWT — the private key is invalid.';
        return null;
    }
    $jwt = $segments . '.' . _b64url($sig);

    $ch = curl_init($tokenUri);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_POSTFIELDS => http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $jwt,
        ]),
    ]);
    $res = curl_exec($ch);
    $cerr = curl_error($ch);
    curl_close($ch);
    if ($res === false) { $err = 'Token request failed: ' . $cerr; return null; }
    $d = json_decode($res, true);
    if (empty($d['access_token'])) {
        $err = 'Token error: ' . ($d['error_description'] ?? $d['error'] ?? $res);
        return null;
    }
    $cached = $d['access_token'];
    return $cached;
}

function drive_test(&$err = ''): bool {
    $t = drive_access_token($err);
    if (!$t) return false;
    // Verify the target folder is reachable (shared with the service account).
    $c = drive_settings();
    if ($c['folder'] !== '') {
        $ch = curl_init('https://www.googleapis.com/drive/v3/files/' . rawurlencode($c['folder']) . '?fields=id,name&supportsAllDrives=true');
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $t]]);
        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code < 200 || $code >= 300) {
            $err = "Authenticated, but folder is not accessible ($code). Share the Drive folder with the service account email as Editor.";
            return false;
        }
    }
    return true;
}

// Upload a file that already exists in UPLOAD_DIR; returns ['id'=>...] or null.
function drive_upload_local(string $filename, &$err = ''): ?array {
    $path = UPLOAD_DIR . '/' . $filename;
    if (!is_file($path)) { $err = 'Local file missing: ' . $filename; return null; }
    $token = drive_access_token($err);
    if (!$token) return null;
    $c = drive_settings();

    $meta = ['name' => $filename];
    if ($c['folder'] !== '') $meta['parents'] = [$c['folder']];
    $mime = @mime_content_type($path) ?: 'application/octet-stream';
    $boundary = 'pdcms' . bin2hex(random_bytes(8));
    $body = "--$boundary\r\n"
          . "Content-Type: application/json; charset=UTF-8\r\n\r\n"
          . json_encode($meta) . "\r\n"
          . "--$boundary\r\n"
          . "Content-Type: $mime\r\n\r\n"
          . file_get_contents($path) . "\r\n"
          . "--$boundary--";

    $ch = curl_init('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart&fields=id&supportsAllDrives=true');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 120,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $token,
            'Content-Type: multipart/related; boundary=' . $boundary,
        ],
        CURLOPT_POSTFIELDS => $body,
    ]);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $cerr = curl_error($ch);
    curl_close($ch);
    if ($res === false || $code < 200 || $code >= 300) {
        $err = "Drive upload failed ($code): " . ($res ?: $cerr);
        return null;
    }
    $d = json_decode($res, true);
    $id = $d['id'] ?? null;
    if (!$id) { $err = 'Drive did not return a file id.'; return null; }
    drive_make_public($id, $token);
    return ['id' => $id];
}

function drive_make_public(string $id, string $token): void {
    $ch = curl_init('https://www.googleapis.com/drive/v3/files/' . rawurlencode($id) . '/permissions?supportsAllDrives=true');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token, 'Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode(['role' => 'reader', 'type' => 'anyone']),
    ]);
    curl_exec($ch);
    curl_close($ch);
}

function drive_delete(string $id): bool {
    $err = '';
    $token = drive_access_token($err);
    if (!$token) return false;
    $ch = curl_init('https://www.googleapis.com/drive/v3/files/' . rawurlencode($id) . '?supportsAllDrives=true');
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => 'DELETE',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token],
    ]);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $code >= 200 && $code < 300;
}

// Public view/download URL for a Drive file id.
function drive_view_url(string $id): string {
    return 'https://drive.google.com/uc?export=view&id=' . rawurlencode($id);
}
