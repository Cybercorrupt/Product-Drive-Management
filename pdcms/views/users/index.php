<?php /** Users list (admin). Expects: $users */ ?>
<div class="page-head">
    <div>
        <h2 class="h3 mb-0">Users</h2>
        <p class="lead-sub">Manage who can access the CMS and their roles.</p>
    </div>
    <div class="spacer"></div>
    <a href="<?= url('users/create') ?>" class="btn btn-primary" data-testid="add-user-btn"><i class="bi bi-person-plus me-1"></i> Add User</a>
</div>

<div class="table-wrap" data-testid="users-table">
    <div class="table-responsive">
        <table class="table">
            <thead><tr><th>User</th><th>Role</th><th>Status</th><th>Last login</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr data-testid="user-row-<?= (int)$u['id'] ?>">
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar"><?= e(strtoupper(substr($u['name'], 0, 1))) ?></span>
                            <div><strong><?= e($u['name']) ?></strong><br><small style="color:var(--muted)"><?= e($u['email']) ?></small></div>
                        </div>
                    </td>
                    <td><span class="role-pill role-<?= e($u['role']) ?>"><?= e(ucfirst($u['role'])) ?></span></td>
                    <td><span class="badge-status st-<?= e($u['status']) ?>"><?= e($u['status']) ?></span></td>
                    <td><small style="color:var(--muted)"><?= $u['last_login_at'] ? e($u['last_login_at']) : 'Never' ?></small></td>
                    <td class="text-end">
                        <a href="<?= url('users/edit/' . (int)$u['id']) ?>" class="btn btn-icon" style="width:34px;height:34px;" title="Edit" data-testid="edit-user-<?= (int)$u['id'] ?>"><i class="bi bi-pencil"></i></a>
                        <?php if ((int)$u['id'] !== (int)current_user()['id']): ?>
                        <form method="post" action="<?= url('users/delete') ?>" class="d-inline" data-confirm="Delete this user account?" data-testid="delete-user-form-<?= (int)$u['id'] ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                            <button class="btn btn-icon text-danger" style="width:34px;height:34px;" title="Delete" data-testid="delete-user-<?= (int)$u['id'] ?>"><i class="bi bi-trash"></i></button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
