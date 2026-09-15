<?php /** Dashboard view. Expects: $stats, $recent, $lowStock */ ?>
<div class="page-head">
    <div>
        <h2 class="h3 mb-0">Overview</h2>
        <p class="lead-sub">Welcome back, <?= e(current_user()['name']) ?>. Here's your catalog at a glance.</p>
    </div>
    <div class="spacer"></div>
    <a href="<?= url('products/create') ?>" class="btn btn-primary" data-testid="dashboard-add-product"><i class="bi bi-plus-lg me-1"></i> New Product</a>
</div>

<div class="stat-grid" data-testid="stat-grid">
    <div class="stat-card" data-testid="stat-products">
        <div class="icon blue"><i class="bi bi-box"></i></div>
        <div class="value"><?= (int)$stats['products'] ?></div>
        <div class="label"><?= (int)$stats['active'] ?> active · total products</div>
    </div>
    <div class="stat-card" data-testid="stat-categories">
        <div class="icon cyan"><i class="bi bi-tags"></i></div>
        <div class="value"><?= (int)$stats['categories'] ?></div>
        <div class="label">Categories</div>
    </div>
    <div class="stat-card" data-testid="stat-users">
        <div class="icon green"><i class="bi bi-people"></i></div>
        <div class="value"><?= (int)$stats['users'] ?></div>
        <div class="label">Team members</div>
    </div>
    <div class="stat-card" data-testid="stat-stock-value">
        <div class="icon amber"><i class="bi bi-cash-stack"></i></div>
        <div class="value"><?= money($stats['stock_value']) ?></div>
        <div class="label"><?= (int)$stats['low_stock'] ?> low-stock items</div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="table-wrap" data-testid="recent-products-panel">
            <div class="d-flex align-items-center justify-content-between px-3 pt-3 pb-2">
                <h3 class="h6 mb-0">Recent products</h3>
                <a href="<?= url('products') ?>" class="btn btn-sm btn-icon" style="width:auto;padding:.3rem .7rem;">View all</a>
            </div>
            <?php if (!$recent): ?>
                <div class="empty-state"><div class="ico"><i class="bi bi-box"></i></div><h4 class="h6">No products yet</h4><p>Add your first product to get started.</p><a href="<?= url('products/create') ?>" class="btn btn-primary btn-sm">Add first product</a></div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($recent as $p): ?>
                        <tr data-testid="recent-row-<?= (int)$p['id'] ?>">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <?php if ($p['image']): ?><img src="<?= e(upload_url($p['image'])) ?>" class="thumb" alt=""><?php else: ?><div class="thumb thumb-empty"><i class="bi bi-image"></i></div><?php endif; ?>
                                    <div><strong><?= e($p['name']) ?></strong><?php if ($p['sku']): ?><br><span class="badge-mono"><?= e($p['sku']) ?></span><?php endif; ?></div>
                                </div>
                            </td>
                            <td><?= $p['category_name'] ? e($p['category_name']) : '<span class="text-secondary">—</span>' ?></td>
                            <td><?= money($p['price']) ?></td>
                            <td><?= (int)$p['stock'] ?></td>
                            <td><span class="badge-status st-<?= e($p['status']) ?>"><?= e($p['status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="table-wrap" data-testid="low-stock-panel">
            <div class="px-3 pt-3 pb-2"><h3 class="h6 mb-0"><i class="bi bi-exclamation-triangle text-warning me-1"></i> Low stock alerts</h3></div>
            <?php if (!$lowStock): ?>
                <div class="empty-state" style="padding:2rem 1rem;"><div class="ico" style="width:52px;height:52px;font-size:1.3rem;"><i class="bi bi-check2-circle"></i></div><p class="mb-0">Stock levels look healthy.</p></div>
            <?php else: ?>
            <ul class="list-group list-group-flush">
                <?php foreach ($lowStock as $p): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center" style="background:transparent;border-color:var(--card-border);color:var(--bs-body-color);">
                        <span><?= e($p['name']) ?></span>
                        <span class="badge-status <?= $p['stock'] == 0 ? 'st-archived' : 'st-draft' ?>"><?= (int)$p['stock'] ?> left</span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
    </div>
</div>
