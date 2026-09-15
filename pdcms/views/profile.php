<?php /** Profile. Expects: $user */ ?>
<div class="page-head">
    <div>
        <h2 class="h3 mb-0">My Profile</h2>
        <p class="lead-sub">Update your account details and password.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="panel text-center" data-testid="profile-card">
            <span class="avatar" style="width:72px;height:72px;font-size:1.8rem;margin:0 auto .8rem;"><?= e(strtoupper(substr($user['name'], 0, 1))) ?></span>
            <h3 class="h5 mb-0"><?= e($user['name']) ?></h3>
            <p style="color:var(--muted)" class="mb-2"><?= e($user['email']) ?></p>
            <span class="role-pill role-<?= e($user['role']) ?>"><?= e(ucfirst($user['role'])) ?></span>
            <hr style="border-color:var(--card-border)">
            <div class="text-start small" style="color:var(--muted)">
                <div class="d-flex justify-content-between mb-1"><span>Status</span><span class="badge-status st-<?= e($user['status']) ?>"><?= e($user['status']) ?></span></div>
                <div class="d-flex justify-content-between mb-1"><span>Member since</span><span><?= e(substr((string)$user['created_at'], 0, 10)) ?></span></div>
                <div class="d-flex justify-content-between"><span>Last login</span><span><?= $user['last_login_at'] ? e(substr((string)$user['last_login_at'], 0, 16)) : 'Now' ?></span></div>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <form method="post" action="<?= url('profile') ?>" class="panel" data-testid="profile-form">
            <?= csrf_field() ?>
            <h3 class="h6 mb-3">Account details</h3>
            <div class="row g-3">
                <div class="col-md-6 mb-2">
                    <label class="form-label" for="p-name">Name</label>
                    <input type="text" class="form-control" id="p-name" name="name" required maxlength="120" value="<?= e(old('name', $user['name'])) ?>" data-testid="profile-name-input">
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label" for="p-email">Email</label>
                    <input type="email" class="form-control" id="p-email" name="email" required value="<?= e(old('email', $user['email'])) ?>" data-testid="profile-email-input">
                </div>
            </div>
            <hr style="border-color:var(--card-border)">
            <h3 class="h6 mb-1">Change password</h3>
            <p class="input-hint mb-3">Leave these blank to keep your current password.</p>
            <div class="row g-3">
                <div class="col-md-4 mb-2 pw-wrap">
                    <label class="form-label" for="p-current">Current password</label>
                    <input type="password" class="form-control" id="p-current" name="current_password" data-testid="profile-current-password">
                </div>
                <div class="col-md-4 mb-2 pw-wrap">
                    <label class="form-label" for="p-new">New password</label>
                    <input type="password" class="form-control" id="p-new" name="new_password" data-testid="profile-new-password">
                </div>
                <div class="col-md-4 mb-2 pw-wrap">
                    <label class="form-label" for="p-confirm">Confirm password</label>
                    <input type="password" class="form-control" id="p-confirm" name="confirm_password" data-testid="profile-confirm-password">
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary" data-testid="profile-submit-btn"><i class="bi bi-check-lg me-1"></i> Save changes</button>
            </div>
        </form>
    </div>
</div>
