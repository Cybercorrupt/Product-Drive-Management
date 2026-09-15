<?php
// WhatsApp Cloud API bot — public webhook (verify + receive) and message senders.
// Route: /whatsapp/webhook  (GET = Meta verification, POST = incoming messages)

function wa_settings(): array {
    return [
        'enabled' => setting_bool('wa_enabled'),
        'token'   => (string)setting_get('wa_access_token', ''),
        'phone'   => (string)setting_get('wa_phone_number_id', ''),
        'secret'  => (string)setting_get('wa_app_secret', ''),
        'verify'  => (string)setting_get('wa_verify_token', ''),
        'ver'     => (string)setting_get('wa_graph_version', 'v23.0'),
    ];
}

function wa_configured(): bool {
    $c = wa_settings();
    return $c['token'] !== '' && $c['phone'] !== '';
}

function whatsapp_route(string $action, array $seg, string $method): void {
    // Public endpoint — no login/CSRF. Always responds plainly and exits.
    if ($action !== 'webhook') { http_response_code(404); echo 'Not found'; return; }
    if ($method === 'GET') { wa_verify(); return; }
    if ($method === 'POST') { wa_receive(); return; }
    http_response_code(405); echo 'Method Not Allowed';
}

function wa_verify(): void {
    $c = wa_settings();
    $mode      = $_GET['hub_mode'] ?? $_GET['hub.mode'] ?? '';
    $token     = $_GET['hub_verify_token'] ?? $_GET['hub.verify_token'] ?? '';
    $challenge = $_GET['hub_challenge'] ?? $_GET['hub.challenge'] ?? '';
    header('Content-Type: text/plain; charset=utf-8');
    if ($mode === 'subscribe' && $c['verify'] !== '' && hash_equals($c['verify'], (string)$token)) {
        http_response_code(200);
        echo $challenge;
    } else {
        http_response_code(403);
        echo 'Forbidden';
    }
    exit;
}

function wa_receive(): void {
    $c = wa_settings();
    $raw = file_get_contents('php://input') ?: '';

    // Verify signature when an app secret is configured.
    if ($c['secret'] !== '') {
        $sig = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
        $expected = 'sha256=' . hash_hmac('sha256', $raw, $c['secret']);
        if ($sig === '' || !hash_equals($expected, $sig)) {
            http_response_code(401);
            echo 'Invalid signature';
            exit;
        }
    }

    // Acknowledge immediately (Meta retries on non-2xx / slow responses).
    http_response_code(200);
    header('Content-Type: application/json');
    echo '{"ok":true}';
    if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();

    if (!$c['enabled'] || !wa_configured()) return;

    $payload = json_decode($raw, true);
    if (!is_array($payload)) return;

    try {
        foreach ($payload['entry'] ?? [] as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                if (($change['field'] ?? '') !== 'messages') continue;
                foreach ($change['value']['messages'] ?? [] as $message) {
                    if (($message['type'] ?? '') !== 'text') continue;
                    $from = (string)($message['from'] ?? '');
                    $text = trim((string)($message['text']['body'] ?? ''));
                    if ($from === '' || $text === '') continue;
                    wa_handle_query($c, $from, $text);
                }
            }
        }
    } catch (Throwable $e) {
        error_log('WhatsApp webhook error: ' . $e->getMessage());
    }
    exit;
}

function wa_render(string $tpl, array $map): string {
    return strtr($tpl, $map);
}

function wa_handle_query(array $c, string $from, string $text): void {
    $lower = strtolower($text);
    $greetings = ['hi', 'hello', 'help', 'hai', 'halo', 'menu', 'start'];
    if (in_array($lower, $greetings, true)) {
        $welcome = (string)setting_get('wa_welcome_message',
            "👋 Welcome to {app}!\n\nSend a product *name* or *SKU* to search. I'll reply with details, images and any product video.\n\nExample: \"laptop\" or \"PD-1001\".");
        wa_send_text($c, $from, wa_render($welcome, ['{app}' => APP_NAME]));
        return;
    }

    $max = max(1, min(10, (int)setting_get('wa_max_results', 3)));
    $like = '%' . $text . '%';
    $stmt = db()->prepare(
        "SELECT p.*, cat.name AS category_name FROM products p
         LEFT JOIN categories cat ON cat.id = p.category_id
         WHERE p.status = 'active' AND (p.name LIKE ? OR p.sku LIKE ? OR p.description LIKE ?)
         ORDER BY p.name LIMIT $max"
    );
    $stmt->execute([$like, $like, $like]);
    $rows = $stmt->fetchAll();

    if (!$rows) {
        $noRes = (string)setting_get('wa_no_results_message', "No products found for \"{query}\". Try a different name or SKU.");
        wa_send_text($c, $from, wa_render($noRes, ['{query}' => $text]));
        return;
    }

    $tpl = (string)setting_get('wa_reply_template',
        "*{name}*\n🏷️ SKU: {sku}\n💰 Price: {price}\n📦 Stock: {stock}\n\n{description}");
    $sendImages = setting_get('wa_send_images', '1') !== '0';
    $sendVideos = setting_get('wa_send_videos', '1') !== '0';

    foreach ($rows as $p) {
        $caption = wa_render($tpl, [
            '{name}'        => $p['name'],
            '{sku}'         => (string)($p['sku'] ?? ''),
            '{price}'       => money($p['price']),
            '{stock}'       => (string)(int)$p['stock'],
            '{category}'    => (string)($p['category_name'] ?? ''),
            '{description}' => trim(strip_tags((string)($p['description'] ?? ''))),
        ]);
        $caption = mb_substr(trim($caption), 0, 1024);

        $img = product_image_abs($p);
        $vid = product_video_abs($p);

        if ($sendImages && $img && preg_match('#^https://#', $img)) {
            wa_send_image($c, $from, $img, $caption);
        } else {
            wa_send_text($c, $from, $caption);
        }
        if ($sendVideos && $vid && preg_match('#^https://#', $vid)) {
            wa_send_video($c, $from, $vid, 'Product video: ' . $p['name']);
        }
    }
}

function wa_graph_post(array $c, array $body, &$err = '') {
    $url = 'https://graph.facebook.com/' . $c['ver'] . '/' . $c['phone'] . '/messages';
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 25,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $c['token'],
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode($body, JSON_UNESCAPED_UNICODE),
    ]);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $cerr = curl_error($ch);
    curl_close($ch);
    if ($res === false || $code < 200 || $code >= 300) {
        $err = "Graph API $code: " . ($res ?: $cerr);
        error_log('WhatsApp send error: ' . $err);
        return false;
    }
    return json_decode($res, true) ?: [];
}

function wa_send_text(array $c, string $to, string $body, &$err = '') {
    return wa_graph_post($c, [
        'messaging_product' => 'whatsapp',
        'recipient_type' => 'individual',
        'to' => $to,
        'type' => 'text',
        'text' => ['preview_url' => false, 'body' => mb_substr($body, 0, 4000)],
    ], $err);
}
function wa_send_image(array $c, string $to, string $url, string $caption = '') {
    return wa_graph_post($c, [
        'messaging_product' => 'whatsapp', 'to' => $to, 'type' => 'image',
        'image' => ['link' => $url, 'caption' => mb_substr($caption, 0, 1024)],
    ]);
}
function wa_send_video(array $c, string $to, string $url, string $caption = '') {
    return wa_graph_post($c, [
        'messaging_product' => 'whatsapp', 'to' => $to, 'type' => 'video',
        'video' => ['link' => $url, 'caption' => mb_substr($caption, 0, 1024)],
    ]);
}
