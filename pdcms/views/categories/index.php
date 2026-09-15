<?php /** Categories. Expects: $categories, $edit (or null) */ ?>
<div class="page-head">
    <div>
        <h2 class="h3 mb-0">Categories</h2>
        <p class="lead-sub">Organize your products into groups.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="panel" data-testid="category-form-panel">
            <h3 class="h6 mb-3"><?= $edit ? 'Edit category' : 'Add category' ?></h3>
            <form method="post" action="<?= $edit ? url('categories/update') : url('categories/store') ?>" data-testid="category-form">
                <?= csrf_field() ?>
                <?php if ($edit): ?><input type="hidden" name="id" value="<?= (int)$edit['id'] ?>"><?php endif; ?>
                <div class="mb-3">
                    <label class="form-label" for="cat-name">Name *</label>
                    <input type="text" class="form-control" id="cat-name" name="name" maxlength="120" required value="<?= e($edit['name'] ?? old('name')) ?>" data-testid="category-name-input">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="cat-desc">Description</label>
                    <textarea class="form-control" id="cat-desc" name="description" rows="2" data-testid="category-desc-input"><?= e($edit['description'] ?? old('description')) ?></textarea>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" type="submit" data-testid="category-submit-btn"><i class="bi bi-check-lg me-1"></i> <?= $edit ? 'Save' : 'Add' ?></button>
                    <?php if ($edit): ?><a href="<?= url('categories') ?>" class="btn btn-icon" style="width:auto;">Cancel</a><?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="table-wrap" data-testid="categories-table">
            <?php if (!$categories): ?>
                <div class="empty-state"><div class="ico"><i class="bi bi-tags"></i></div><h4>No categories yet</h4><p>Create your first category on the left.</p></div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Name</th><th>Products</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                    <?php foreach ($categories as $c): ?>
                        <tr data-testid="category-row-<?= (int)$c['id'] ?>">
                            <td><strong><?= e($c['name']) ?></strong><?php if ($c['description']): ?><br><small style="color:var(--muted)"><?= e($c['description']) ?></small><?php endif; ?></td>
                            <td><span class="badge-mono"><?= (int)$c['product_count'] ?></span></td>
                            <td class="text-end">
                                <a href="<?= url('categories?edit=' . (int)$c['id']) ?>" class="btn btn-icon" style="width:34px;height:34px;" title="Edit" data-testid="edit-category-<?= (int)$c['id'] ?>"><i class="bi bi-pencil"></i></a>
                                <form method="post" action="<?= url('categories/delete') ?>" class="d-inline" data-confirm="Delete this category? Products will become uncategorized." data-testid="delete-category-form-<?= (int)$c['id'] ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                    <button class="btn btn-icon text-danger" style="width:34px;height:34px;" title="Delete" data-testid="delete-category-<?= (int)$c['id'] ?>"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
