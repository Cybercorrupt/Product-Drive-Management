<?php /** Settings page (admin). Expects: $pending, $webhook */
$driveEnabled = setting_bool('drive_enabled');
$waEnabled    = setting_bool('wa_enabled');
$hasSA        = setting_has('drive_service_account');
?>
<div class="page-head">
    <div>
        <h2 class="h3 mb-0">Settings</h2>
        <p class="lead-sub">Integrations & configuration. Secrets are stored in the database and shown masked.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Google Drive -->
    <div class="col-lg-6">
        <div class="panel h-100" data-testid="drive-settings-panel">
            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="stat-card-ico" style="width:40px;height:40px;border-radius:10px;display:grid;place-items:center;background:rgba(37,99,235,.14);color:var(--bs-primary);"><i class="bi bi-google" style="font-size:1.1rem;"></i></span>
                <div>
                    <h3 class="h6 mb-0">Google Drive storage</h3>
                    <small style="color:var(--muted)">Service Account · files fall back to local when unavailable</small>
                </div>
                <span class="badge-status <?= $driveEnabled ? 'st-active' : 'st-draft' ?> ms-auto"><?= $driveEnabled ? 'Enabled' : 'Disabled' ?></span>
            </div>

            <form method="post" action="<?= url('settings/drive') ?>" data-testid="drive-form">
                <?= csrf_field() ?>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch" id="drive_enabled" name="drive_enabled" <?= $driveEnabled ? 'checked' : '' ?> data-testid="drive-enabled-toggle">
                    <label class="form-check-label" for="drive_enabled">Store product files on Google Drive</label>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="drive_folder_id">Drive folder ID</label>
                    <input type="text" class="form-control" id="drive_folder_id" name="drive_folder_id" value="<?= e(setting_get('drive_folder_id', '')) ?>" placeholder="e.g. 1AbCdEf..." data-testid="drive-folder-input">
                    <div class="input-hint">Create a folder in your Drive, open it, and copy the ID from the URL. <strong>Share that folder with the service account email as Editor.</strong></div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="drive_service_account">Service account JSON</label>
                    <textarea class="form-control mono" id="drive_service_account" name="drive_service_account" rows="4" placeholder='<?= $hasSA ? "•••• saved — paste new JSON to replace, or leave blank to keep" : "Paste the full service-account key JSON here" ?>' style="font-size:.78rem;" data-testid="drive-sa-input"></textarea>
                    <?php if ($hasSA): ?>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="drive_clear_sa" name="drive_clear_sa" data-testid="drive-clear-sa">
                            <label class="form-check-label" for="drive_clear_sa" style="font-size:.82rem;color:var(--muted)">Remove stored credentials</label>
                        </div>
                    <?php endif; ?>
                </div>
                <button class="btn btn-primary" type="submit" data-testid="drive-save-btn"><i class="bi bi-check-lg me-1"></i> Save Drive settings</button>
            </form>

            <hr style="border-color:var(--card-border)">
            <div class="d-flex flex-wrap gap-2">
                <form method="post" action="<?= url('settings/drive-test') ?>" data-testid="drive-test-form">
                    <?= csrf_field() ?>
                    <button class="btn btn-icon" style="width:auto;padding:.5rem .9rem;" type="submit" data-testid="drive-test-btn"><i class="bi bi-plug me-1"></i> Test connection</button>
                </form>
                <form method="post" action="<?= url('settings/drive-sync') ?>" data-testid="drive-sync-form">
                    <?= csrf_field() ?>
                    <button class="btn btn-icon" style="width:auto;padding:.5rem .9rem;" type="submit" data-testid="drive-sync-btn"><i class="bi bi-arrow-repeat me-1"></i> Sync local files (<?= (int)$pending ?>)</button>
                </form>
            </div>
        </div>
    </div>

    <!-- WhatsApp -->
    <div class="col-lg-6">
        <div class="panel h-100" data-testid="whatsapp-settings-panel">
            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="stat-card-ico" style="width:40px;height:40px;border-radius:10px;display:grid;place-items:center;background:rgba(16,185,129,.14);color:var(--bs-success);"><i class="bi bi-whatsapp" style="font-size:1.1rem;"></i></span>
                <div>
                    <h3 class="h6 mb-0">WhatsApp bot</h3>
                    <small style="color:var(--muted)">Meta WhatsApp Cloud API · product search & media</small>
                </div>
                <span class="badge-status <?= $waEnabled ? 'st-active' : 'st-draft' ?> ms-auto"><?= $waEnabled ? 'Enabled' : 'Disabled' ?></span>
            </div>

            <form method="post" action="<?= url('settings/whatsapp') ?>" data-testid="whatsapp-form">
                <?= csrf_field() ?>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch" id="wa_enabled" name="wa_enabled" <?= $waEnabled ? 'checked' : '' ?> data-testid="wa-enabled-toggle">
                    <label class="form-check-label" for="wa_enabled">Enable WhatsApp bot replies</label>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="wa_phone_number_id">Phone Number ID</label>
                    <input type="text" class="form-control" id="wa_phone_number_id" name="wa_phone_number_id" value="<?= e(setting_get('wa_phone_number_id', '')) ?>" placeholder="123456789012345" data-testid="wa-phone-input">
                    <div class="input-hint">From Meta → WhatsApp → API Setup (numeric ID, not the phone number).</div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="wa_access_token">Access Token</label>
                    <input type="password" class="form-control" id="wa_access_token" name="wa_access_token" placeholder="<?= setting_has('wa_access_token') ? setting_mask('wa_access_token') . '  (leave blank to keep)' : 'EAAB...' ?>" data-testid="wa-token-input">
                </div>
                <div class="row g-2">
                    <div class="col-7 mb-3">
                        <label class="form-label" for="wa_app_secret">App Secret</label>
                        <input type="password" class="form-control" id="wa_app_secret" name="wa_app_secret" placeholder="<?= setting_has('wa_app_secret') ? setting_mask('wa_app_secret') . ' (kept)' : 'for signature check' ?>" data-testid="wa-secret-input">
                    </div>
                    <div class="col-5 mb-3">
                        <label class="form-label" for="wa_graph_version">Graph version</label>
                        <input type="text" class="form-control" id="wa_graph_version" name="wa_graph_version" value="<?= e(setting_get('wa_graph_version', 'v23.0')) ?>" data-testid="wa-version-input">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="wa_verify_token">Verify Token</label>
                    <input type="text" class="form-control" id="wa_verify_token" name="wa_verify_token" value="<?= e(setting_get('wa_verify_token', '')) ?>" placeholder="your-own-random-string" data-testid="wa-verify-input">
                    <div class="input-hint">Choose any random string; enter the same value in Meta's webhook config.</div>
                </div>
                <button class="btn btn-primary" type="submit" data-testid="wa-save-btn"><i class="bi bi-check-lg me-1"></i> Save WhatsApp settings</button>
            </form>

            <hr style="border-color:var(--card-border)">
            <label class="form-label">Webhook URL (paste into Meta)</label>
            <div class="input-group mb-3">
                <input type="text" class="form-control mono" style="font-size:.78rem;" value="<?= e($webhook) ?>" readonly data-testid="wa-webhook-url">
                <button class="btn btn-icon" style="width:auto;" type="button" onclick="navigator.clipboard.writeText('<?= e($webhook) ?>')" title="Copy"><i class="bi bi-clipboard"></i></button>
            </div>
            <form method="post" action="<?= url('settings/whatsapp-test') ?>" class="input-group" data-testid="wa-test-form">
                <?= csrf_field() ?>
                <input type="text" name="wa_test_to" class="form-control" placeholder="Test to number e.g. 628123456789" data-testid="wa-test-input">
                <button class="btn btn-icon" style="width:auto;padding:.5rem .9rem;" type="submit" data-testid="wa-test-btn"><i class="bi bi-send me-1"></i> Send test</button>
            </form>
        </div>
    </div>

    <!-- Currency & format -->
    <div class="col-lg-6">
        <div class="panel h-100" data-testid="currency-settings-panel">
            <div class="d-flex align-items-center gap-2 mb-3">
                <span style="width:40px;height:40px;border-radius:10px;display:grid;place-items:center;background:rgba(217,119,6,.14);color:var(--bs-warning);"><i class="bi bi-cash-coin" style="font-size:1.1rem;"></i></span>
                <div>
                    <h3 class="h6 mb-0">Currency &amp; format</h3>
                    <small style="color:var(--muted)">How prices are displayed across the CMS &amp; bot</small>
                </div>
            </div>
            <form method="post" action="<?= url('settings/currency') ?>" data-testid="currency-form" id="currencyForm">
                <?= csrf_field() ?>
                <div class="row g-2">
                    <div class="col-4 mb-2">
                        <label class="form-label" for="currency_code">Code</label>
                        <input type="text" maxlength="6" class="form-control" id="currency_code" name="currency_code" value="<?= e(setting_get('currency_code', 'USD')) ?>" data-testid="currency-code-input">
                    </div>
                    <div class="col-4 mb-2">
                        <label class="form-label" for="currency_symbol">Symbol</label>
                        <input type="text" maxlength="6" class="form-control" id="currency_symbol" name="currency_symbol" value="<?= e(setting_get('currency_symbol', '$')) ?>" data-testid="currency-symbol-input">
                    </div>
                    <div class="col-4 mb-2">
                        <label class="form-label" for="currency_position">Position</label>
                        <select class="form-select" id="currency_position" name="currency_position" data-testid="currency-position-select">
                            <option value="before" <?= setting_get('currency_position', 'before') === 'before' ? 'selected' : '' ?>>Before</option>
                            <option value="after" <?= setting_get('currency_position') === 'after' ? 'selected' : '' ?>>After</option>
                        </select>
                    </div>
                    <div class="col-4 mb-2">
                        <label class="form-label" for="currency_decimals">Decimals</label>
                        <input type="number" min="0" max="4" class="form-control" id="currency_decimals" name="currency_decimals" value="<?= e(setting_get('currency_decimals', '2')) ?>" data-testid="currency-decimals-input">
                    </div>
                    <div class="col-4 mb-2">
                        <label class="form-label" for="currency_thousand_sep">Thousands</label>
                        <?php $ts = setting_get('currency_thousand_sep', ','); ?>
                        <select class="form-select" id="currency_thousand_sep" name="currency_thousand_sep" data-testid="currency-thousand-select">
                            <option value="," <?= $ts === ',' ? 'selected' : '' ?>>1,000</option>
                            <option value="." <?= $ts === '.' ? 'selected' : '' ?>>1.000</option>
                            <option value=" " <?= $ts === ' ' ? 'selected' : '' ?>>1 000</option>
                            <option value="none" <?= $ts === 'none' ? 'selected' : '' ?>>1000</option>
                        </select>
                    </div>
                    <div class="col-4 mb-2">
                        <label class="form-label" for="currency_decimal_sep">Decimal</label>
                        <?php $ds = setting_get('currency_decimal_sep', '.'); ?>
                        <select class="form-select" id="currency_decimal_sep" name="currency_decimal_sep" data-testid="currency-decimal-select">
                            <option value="." <?= $ds === '.' ? 'selected' : '' ?>>0.50</option>
                            <option value="," <?= $ds === ',' ? 'selected' : '' ?>>0,50</option>
                        </select>
                    </div>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="currency_space" name="currency_space" <?= setting_bool('currency_space') ? 'checked' : '' ?> data-testid="currency-space-toggle">
                    <label class="form-check-label" for="currency_space" style="font-size:.85rem;">Space between symbol and amount</label>
                </div>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="input-hint w-100">Quick presets:</span>
                    <button type="button" class="btn btn-icon" style="width:auto;padding:.35rem .7rem;font-size:.8rem;" data-cur-preset='{"code":"USD","symbol":"$","position":"before","decimals":"2","thousand":",","decimal":".","space":false}'>USD $</button>
                    <button type="button" class="btn btn-icon" style="width:auto;padding:.35rem .7rem;font-size:.8rem;" data-cur-preset='{"code":"IDR","symbol":"Rp","position":"before","decimals":"0","thousand":".","decimal":",","space":true}'>IDR Rp</button>
                    <button type="button" class="btn btn-icon" style="width:auto;padding:.35rem .7rem;font-size:.8rem;" data-cur-preset='{"code":"EUR","symbol":"€","position":"after","decimals":"2","thousand":".","decimal":",","space":true}'>EUR €</button>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-primary" type="submit" data-testid="currency-save-btn"><i class="bi bi-check-lg me-1"></i> Save currency</button>
                    <span class="badge-mono" data-testid="currency-preview">Preview: <span id="curPreview"><?= e(money(1234567.5)) ?></span></span>
                </div>
            </form>
        </div>
    </div>

    <!-- WhatsApp bot replies -->
    <div class="col-lg-6">
        <div class="panel h-100" data-testid="wa-replies-panel">
            <div class="d-flex align-items-center gap-2 mb-3">
                <span style="width:40px;height:40px;border-radius:10px;display:grid;place-items:center;background:rgba(16,185,129,.14);color:var(--bs-success);"><i class="bi bi-chat-dots" style="font-size:1.1rem;"></i></span>
                <div>
                    <h3 class="h6 mb-0">WhatsApp bot replies</h3>
                    <small style="color:var(--muted)">Customise what the bot sends back</small>
                </div>
            </div>
            <form method="post" action="<?= url('settings/wa-replies') ?>" data-testid="wa-replies-form">
                <?= csrf_field() ?>
                <div class="row g-2 align-items-end mb-2">
                    <div class="col-5">
                        <label class="form-label" for="wa_max_results">Max results</label>
                        <input type="number" min="1" max="10" class="form-control" id="wa_max_results" name="wa_max_results" value="<?= e(setting_get('wa_max_results', '3')) ?>" data-testid="wa-max-results-input">
                    </div>
                    <div class="col-7">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="wa_send_images" name="wa_send_images" <?= setting_get('wa_send_images', '1') !== '0' ? 'checked' : '' ?> data-testid="wa-send-images-toggle">
                            <label class="form-check-label" for="wa_send_images" style="font-size:.85rem;">Send product images</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="wa_send_videos" name="wa_send_videos" <?= setting_get('wa_send_videos', '1') !== '0' ? 'checked' : '' ?> data-testid="wa-send-videos-toggle">
                            <label class="form-check-label" for="wa_send_videos" style="font-size:.85rem;">Send product videos</label>
                        </div>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label" for="wa_welcome_message">Welcome / help message</label>
                    <textarea class="form-control" id="wa_welcome_message" name="wa_welcome_message" rows="2" data-testid="wa-welcome-input"><?= e(setting_get('wa_welcome_message', "👋 Welcome to {app}!\n\nSend a product *name* or *SKU* to search. Example: \"laptop\" or \"PD-1001\".")) ?></textarea>
                    <div class="input-hint">Token: <span class="badge-mono">{app}</span></div>
                </div>
                <div class="mb-2">
                    <label class="form-label" for="wa_no_results_message">No-results message</label>
                    <textarea class="form-control" id="wa_no_results_message" name="wa_no_results_message" rows="2" data-testid="wa-noresults-input"><?= e(setting_get('wa_no_results_message', "No products found for \"{query}\". Try a different name or SKU.")) ?></textarea>
                    <div class="input-hint">Token: <span class="badge-mono">{query}</span></div>
                </div>
                <div class="mb-2">
                    <label class="form-label" for="wa_reply_template">Product reply template</label>
                    <textarea class="form-control" id="wa_reply_template" name="wa_reply_template" rows="4" data-testid="wa-template-input"><?= e(setting_get('wa_reply_template', "*{name}*\n🏷️ SKU: {sku}\n💰 Price: {price}\n📦 Stock: {stock}\n\n{description}")) ?></textarea>
                    <div class="input-hint">Tokens: <span class="badge-mono">{name}</span> <span class="badge-mono">{sku}</span> <span class="badge-mono">{price}</span> <span class="badge-mono">{stock}</span> <span class="badge-mono">{category}</span> <span class="badge-mono">{description}</span></div>
                </div>
                <button class="btn btn-primary" type="submit" data-testid="wa-replies-save-btn"><i class="bi bi-check-lg me-1"></i> Save bot replies</button>
            </form>
        </div>
    </div>

    <!-- General -->
    <div class="col-12">
        <div class="panel" data-testid="general-settings-panel">
            <h3 class="h6 mb-3">General</h3>
            <form method="post" action="<?= url('settings/general') ?>" class="row g-2 align-items-end" data-testid="general-form">
                <?= csrf_field() ?>
                <div class="col-md-8">
                    <label class="form-label" for="app_url">Public base URL</label>
                    <input type="url" class="form-control" id="app_url" name="app_url" value="<?= e(setting_get('app_url', '')) ?>" placeholder="https://your-domain.com" data-testid="app-url-input">
                    <div class="input-hint">Used to build absolute links for images/videos sent over WhatsApp. Leave blank to auto-detect.</div>
                </div>
                <div class="col-md-4 d-grid">
                    <button class="btn btn-primary" type="submit" data-testid="general-save-btn"><i class="bi bi-check-lg me-1"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
