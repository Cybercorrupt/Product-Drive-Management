# Pre-Deployment Test Checklist

Run every item below on the preview URL before publishing to cPanel.
Do **not** mark a feature "done" just because the page opens — exercise the actual action.

## Auth & session
- [ ] `/dashboard` while logged out → redirects to `/login`.
- [ ] Login with wrong password → "Invalid email or password."
- [ ] 5 wrong attempts → temporary lockout message.
- [ ] Login with `admin@productdrive.test / admin123` → lands on dashboard.
- [ ] Session cookie is `HttpOnly` (and `Secure` over HTTPS).
- [ ] Logout → redirects to login; `/dashboard` no longer accessible.

## RBAC (roles)
- [ ] Operator logs in → **no** "Users" link in sidebar.
- [ ] Operator visits `/users` directly → redirected to dashboard (403 flash).
- [ ] Admin sees and can open "Users".

## Products CRUD
- [ ] Create product with all fields + image → appears in list with thumbnail.
- [ ] Create with empty name / negative price → validation error, no save.
- [ ] Duplicate SKU → "That SKU is already in use."
- [ ] Edit product; change image → old image replaced.
- [ ] Search by name and by SKU returns correct rows.
- [ ] Filter by category and by status works.
- [ ] Pagination appears when > 8 products.
- [ ] Delete product → row removed, image file deleted.

## Image upload security
- [ ] Upload a `.txt`/`.php` renamed to `.jpg` → rejected ("not a valid image").
- [ ] Upload > 3 MB → rejected.
- [ ] Direct request to `uploads/<file>.php` → not executed (403/plain).

## Categories
- [ ] Add category; appears with product count 0.
- [ ] Duplicate name → error.
- [ ] Edit category name.
- [ ] Delete category → its products become "Uncategorized" (not deleted).

## Users (admin)
- [ ] Create operator user (password ≥ 6) → can log in.
- [ ] Duplicate email → error.
- [ ] Admin cannot change own role / deactivate self / delete self.
- [ ] Edit user without password → password unchanged.

## Profile
- [ ] Update name/email → reflected in topbar.
- [ ] Change password with wrong current password → rejected.
- [ ] Change password correctly → can log in with new password.

## Hardening
- [ ] Submitting any form with a tampered/missing `_csrf` → 419 rejected.
- [ ] `<script>` in a product name is shown as text, not executed (XSS escaped).
- [ ] `/.env` and `/config/config.php` are not downloadable (403).
- [ ] `APP_DEBUG="false"` in production hides stack traces (custom 500 page).

## Responsive / UX
- [ ] Sidebar collapses to a drawer under 992px; hamburger works.
- [ ] Theme toggle switches light/dark and persists on reload.
- [ ] Flash messages auto-dismiss.
