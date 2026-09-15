<?php /** Products list. Expects: $products,$categories,$q,$catId,$status,$page,$pages,$total */ ?>
<div class="page-head">
    <div>
        <h2 class="h3 mb-0">Products</h2>
        <p class="lead-sub"><?= (int)$total ?> product<?= $total == 1 ? '' : 's' ?> in your catalog.</p>
    </div>
    <div class="spacer"></div>
    <a href="<?= url('products/create') ?>" class="btn btn-primary" data-testid="add-product-btn"><i class="bi bi-plus-lg me-1"></i> Add Product</a>
</div>

<form method="get" action="<?= url('products') ?>" class="panel mb-3" data-testid="product-filters">
    <div class="row g-2 align-items-end">
        <div class="col-md-5">
            <label class="form-label">Search</label>
            <input type="text" name="q" value="<?= e($q) ?>" class="form-control" placeholder="Name or SKU…" data-testid="product-search-input">
        </div>
        <div class="col-md-3">
            <label class="form-label">Category</label>
            <select name="category" class="form-select" data-autosubmit data-testid="product-filter-category">
                <option value="">All categories</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= (int)$c['id'] ?>" <?= $catId === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Status</label>
            <select name="status" class="form-select" data-autosubmit data-testid="product-filter-status">
                <option value="">Any</option>
                <?php foreach (['active','draft','archived'] as $s): ?>
                    <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 d-grid">
            <button class="btn btn-primary" type="submit" data-testid="product-search-btn"><i class="bi bi-search me-1"></i> Filter</button>
        </div>
    </div>
</form>

<div class="table-wrap" data-testid="products-table">
    <?php if (!$products): ?>
        <div class="empty-state">
            <div class="ico"><i class="bi bi-box"></i></div>
            <h4>No products found</h4>
            <p><?= ($q || $catId || $status) ? 'Try adjusting your filters.' : 'Add your first product to get started.' ?></p>
            <a href="<?= url('products/create') ?>" class="btn btn-primary btn-sm" data-testid="empty-add-product">Add Product</a>
        </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table">
            <thead><tr><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            <?php foreach ($products as $p): ?>
                <tr data-testid="product-row-<?= (int)$p['id'] ?>">
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <?php $pimg = product_image_url($p); if ($pimg): ?><img src="<?= e($pimg) ?>" class="thumb" alt="<?= e($p['name']) ?>"><?php else: ?><div class="thumb thumb-empty"><i class="bi bi-image"></i></div><?php endif; ?>
                            <div><strong><?= e($p['name']) ?></strong><?php if ($p['sku']): ?><br><span class="badge-mono"><?= e($p['sku']) ?></span><?php endif; ?><?php if (!empty($p['video']) || !empty($p['video_drive_id'])): ?> <span class="badge-mono" title="Has video"><i class="bi bi-camera-video"></i></span><?php endif; ?><?php if (!empty($p['image_drive_id']) || !empty($p['video_drive_id'])): ?> <span class="badge-mono" title="On Google Drive"><i class="bi bi-google"></i></span><?php endif; ?></div>
                        </div>
                    </td>
                    <td><?= $p['category_name'] ? e($p['category_name']) : '<span class="text-secondary">—</span>' ?></td>
                    <td><?= money($p['price']) ?></td>
                    <td><?= (int)$p['stock'] ?></td>
                    <td><span class="badge-status st-<?= e($p['status']) ?>"><?= e($p['status']) ?></span></td>
                    <td class="text-end">
                        <a href="<?= url('products/edit/' . (int)$p['id']) ?>" class="btn btn-icon" style="width:34px;height:34px;" title="Edit" data-testid="edit-product-<?= (int)$p['id'] ?>"><i class="bi bi-pencil"></i></a>
                        <form method="post" action="<?= url('products/delete') ?>" class="d-inline" data-confirm="Delete this product? This cannot be undone." data-testid="delete-form-<?= (int)$p['id'] ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                            <button class="btn btn-icon text-danger" style="width:34px;height:34px;" title="Delete" data-testid="delete-product-<?= (int)$p['id'] ?>"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php if ($pages > 1): ?>
<nav class="mt-3" data-testid="products-pagination">
    <ul class="pagination justify-content-center">
        <?php
        $qs = function ($p) use ($q, $catId, $status) {
            return url('products') . '?' . http_build_query(array_filter(['q' => $q, 'category' => $catId, 'status' => $status, 'page' => $p], fn($v) => $v !== null && $v !== ''));
        };
        for ($i = 1; $i <= $pages; $i++): ?>
            <li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="<?= e($qs($i)) ?>"><?= $i ?></a></li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
