<?php
/** User create/edit form (admin). Expects: $user (or null) */
$isEdit = $user !== null;
$val = function ($k, $d = '') use ($user) { $o = old($k, null); return $o !== null ? $o : ($user[$k] ?? $d); };
$self = $isEdit && (int)$user['id'] === (int)current_user()['id'];
?>
<div class="page-head">
    <div>
        <a href="<?= url('users') ?>" class="text-decoration-none" style="color:var(--muted);font-size:.85rem;"><i class="bi bi-arrow-left"></i> Back to users</a>
        <h2 class="h3 mb-0 mt-1"><?= $isEdit ? 'Edit User' : 'Add User' ?></h2>
    </div>
</div>

<form method="post" action="<?= $isEdit ? url('users/update') : url('users/store') ?>" data-testid="user-form">
    <?= csrf_field() ?>
    <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= (int)$user['id'] ?>"><?php endif; ?>
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="panel">
                <div class="mb-3">
                    <label class="form-label" for="u-name">Full name *</label>
                    <input type="text" class="form-control" id="u-name" name="name" maxlength="120" required value="<?= e($val('name')) ?>" data-testid="user-name-input">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="u-email">Email *</label>
                    <input type="email" class="form-control" id="u-email" name="email" required value="<?= e($val('email')) ?>" data-testid="user-email-input">
                </div>
                <div class="mb-1 pw-wrap">
                    <label class="form-label" for="u-pass"><?= $isEdit ? 'New password' : 'Password *' ?></label>
                    <input type="password" class="form-control" id="u-pass" name="password" <?= $isEdit ? '' : 'required' ?> data-testid="user-password-input">
                    <button type="button" class="pw-toggle" data-pw-toggle="u-pass" tabindex="-1" style="top:2.35rem;"><i class="bi bi-eye"></i></button>
                    <div class="input-hint"><?= $isEdit ? 'Leave blank to keep the current password.' : 'Minimum 6 characters.' ?></div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="panel">
                <div class="mb-3">
                    <label class="form-label" for="u-role">Role</label>
                    <select class="form-select" id="u-role" name="role" data-testid="user-role-select" <?= $self ? 'disabled' : '' ?>>
                        <option value="operator" <?= $val('role', 'operator') === 'operator' ? 'selected' : '' ?>>Operator</option>
                        <option value="admin" <?= $val('role') === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                    <?php if ($self): ?><input type="hidden" name="role" value="admin"><div class="input-hint">You cannot change your own role.</div><?php endif; ?>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="u-status">Status</label>
                    <select class="form-select" id="u-status" name="status" data-testid="user-status-select" <?= $self ? 'disabled' : '' ?>>
                        <option value="active" <?= $val('status', 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $val('status') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                    <?php if ($self): ?><input type="hidden" name="status" value="active"><div class="input-hint">You cannot deactivate yourself.</div><?php endif; ?>
                </div>
                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary py-2" data-testid="user-submit-btn"><i class="bi bi-check-lg me-1"></i> <?= $isEdit ? 'Save changes' : 'Create user' ?></button>
                    <a href="<?= url('users') ?>" class="btn btn-icon" style="width:auto;">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</form>
