# Integrations — Google Drive & WhatsApp

All credentials are configured in the CMS at **Settings** (admin only) and stored in the
`settings` database table (secrets shown masked). Nothing is hardcoded in the repository.

---

## 1. Google Drive (product file storage)

**Behavior:** when Drive is enabled and reachable, product images/videos are uploaded to your
Drive folder and served from there. If Drive is disabled or an upload fails, files are stored
locally in `uploads/` automatically. Use **Settings → Sync local files** to push existing local
files to Drive later.

### Create a Service Account
1. Go to https://console.cloud.google.com → create/select a project.
2. **APIs & Services → Library** → enable **Google Drive API**.
3. **APIs & Services → Credentials → Create credentials → Service account**. Name it, create.
4. Open the service account → **Keys → Add key → Create new key → JSON**. A JSON file downloads.
5. Copy the service account email (looks like `name@project.iam.gserviceaccount.com`).

### Share a Drive folder with the service account
1. In your Google Drive, create a folder (e.g. `ProductDrive`).
2. Open the folder; the URL is `https://drive.google.com/drive/folders/<FOLDER_ID>` — copy `<FOLDER_ID>`.
3. **Share** the folder with the service account email, role **Editor**.
   > This is required — a service account has no storage of its own; it writes into the shared folder.

### Configure in the CMS
- Settings → Google Drive:
  - Toggle **Store product files on Google Drive**.
  - Paste the **Drive folder ID**.
  - Paste the entire **service account JSON**.
  - Save, then click **Test connection** (authenticates and checks folder access).
  - Click **Sync local files** to upload any existing local product files.

Uploaded files are made link-viewable and served via
`https://drive.google.com/uc?export=view&id=<FILE_ID>`.

---

## 2. WhatsApp bot (Meta WhatsApp Cloud API)

Users message your WhatsApp Business number; the bot searches active products by name / SKU /
description and replies with product details, image, and any product video.

### Create the Meta app
1. https://developers.facebook.com/ → **Create App** → use case **WhatsApp**.
2. **WhatsApp → API Setup**: connect a WhatsApp Business Account, add/verify a phone number.
3. Copy the **Phone Number ID** (numeric — not the phone number).
4. Generate a token: temporary token works for testing; for production create a **System User**
   with `whatsapp_business_messaging`, `whatsapp_business_management` and generate a permanent token.
5. **App → Settings → Basic**: copy the **App Secret** (used to verify webhook signatures).
6. Choose your own random **Verify Token** string (e.g. `openssl rand -hex 32`).

### Configure in the CMS
- Settings → WhatsApp:
  - Toggle **Enable WhatsApp bot replies**.
  - Enter **Phone Number ID**, **Access Token**, **App Secret**, **Graph version** (e.g. `v23.0`),
    and your **Verify Token**. Save.
  - Copy the **Webhook URL** shown (e.g. `https://your-domain/whatsapp/webhook`).

### Point Meta at the webhook
1. Meta App → **WhatsApp → Configuration → Webhook → Edit**:
   - Callback URL: the Webhook URL from Settings.
   - Verify token: exactly the value you entered in Settings.
   - Click **Verify and save**.
2. **Subscribe** the app to the `messages` webhook field.

### Test
- In Settings, use **Send test** with your number (country code, no `+`, e.g. `628123456789`).
- Or message the business number: send `hi` for help, then a product name or SKU.

### Notes / limits
- Media links must be public HTTPS. Set **Settings → General → Public base URL** to your domain so
  locally-stored images/videos produce correct absolute links (Drive links are already absolute).
- Cloud API media limits: images up to 5 MB, MP4 video up to 16 MB.
- Free-form replies are allowed within the 24-hour customer-service window (user messages first).
  Outside it, an approved message template is required.
- On cPanel the webhook is `https://your-domain/whatsapp/webhook` (routed via `.htaccess`).
  Ensure no Basic Auth / WAF blocks Meta, and that SSL is active.

### Local migration / re-run
- Apply the DB changes for these features: `php scripts/migrate.php` (idempotent).
