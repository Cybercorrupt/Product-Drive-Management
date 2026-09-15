<?php
/** Product create/edit form. Expects: $product (or null), $categories */
$isEdit = $product !== null;
$val = function ($key, $default = '') use ($product) {
    $o = old($key, null);
    if ($o !== null) return $o;
    return $product[$key] ?? $default;
};
?>
<div class="page-head">
    <div>
        <a href="<?= url('products') ?>" class="text-decoration-none" style="color:var(--muted);font-size:.85rem;"><i class="bi bi-arrow-left"></i> Back to products</a>
        <h2 class="h3 mb-0 mt-1"><?= $isEdit ? 'Edit Product' : 'Add Product' ?></h2>
    </div>
</div>

<form method="post" action="<?= $isEdit ? url('products/update') : url('products/store') ?>" enctype="multipart/form-data" data-testid="product-form">
    <?= csrf_field() ?>
    <?php if ($isEdit): ?><input type="hidden" name="id" value="<?= (int)$product['id'] ?>"><?php endif; ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="panel">
                <div class="mb-3">
                    <label class="form-label" for="name">Product name *</label>
                    <input type="text" class="form-control" id="name" name="name" maxlength="190" required value="<?= e($val('name')) ?>" data-testid="product-name-input">
                </div>
                <div class="row g-3">
                    <div class="col-sm-6 mb-3">
                        <label class="form-label" for="sku">SKU</label>
                        <input type="text" class="form-control" id="sku" name="sku" maxlength="64" value="<?= e($val('sku')) ?>" placeholder="e.g. PD-1001" data-testid="product-sku-input">
                        <div class="input-hint">Optional, must be unique.</div>
                    </div>
                    <div class="col-sm-6 mb-3">
                        <label class="form-label" for="category_id">Category</label>
                        <select class="form-select" id="category_id" name="category_id" data-testid="product-category-select">
                            <option value="">Uncategorized</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= (int)$c['id'] ?>" <?= (string)$val('category_id') === (string)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-sm-4 mb-3">
                        <label class="form-label" for="price">Price *</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="price" name="price" required value="<?= e($val('price', '0.00')) ?>" data-testid="product-price-input">
                    </div>
                    <div class="col-sm-4 mb-3">
                        <label class="form-label" for="stock">Stock *</label>
                        <input type="number" min="0" step="1" class="form-control" id="stock" name="stock" required value="<?= e($val('stock', '0')) ?>" data-testid="product-stock-input">
                    </div>
                    <div class="col-sm-4 mb-3">
                        <label class="form-label" for="status">Status</label>
                        <select class="form-select" id="status" name="status" data-testid="product-status-select">
                            <?php foreach (['active','draft','archived'] as $s): ?>
                                <option value="<?= $s ?>" <?= $val('status', 'active') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mb-1">
                    <label class="form-label" for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4" data-testid="product-description-input"><?= e($val('description')) ?></textarea>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="panel">
                <label class="form-label">Product image</label>
                <div class="dropzone" data-testid="product-image-dropzone">
                    <img class="preview" id="imgPreview" src="<?= $isEdit && product_image_url($product) ? e(product_image_url($product)) : '' ?>" style="<?= $isEdit && product_image_url($product) ? '' : 'display:none;' ?>" alt="">
                    <?php if (!($isEdit && product_image_url($product))): ?><div class="preview thumb-empty" id="imgPlaceholder" style="display:grid;"><i class="bi bi-image"></i></div><?php endif; ?>
                    <div class="dz-text">
                        <strong>Click to upload</strong>
                        <small>JPG, PNG, WEBP, GIF · max 3 MB</small>
                    </div>
                    <input type="file" name="image" accept="image/*" class="d-none" data-image-input data-preview="imgPreview" data-testid="product-image-input">
                </div>
                <?php if ($isEdit && $product['image']): ?><div class="input-hint">Leave empty to keep the current image.</div><?php endif; ?>
            </div>
            <div class="panel mt-3">
                <label class="form-label">Product video <small style="color:var(--muted);font-weight:400;">(optional)</small></label>
                <div class="dropzone" data-testid="product-video-dropzone">
                    <div class="preview thumb-empty" style="display:grid;"><i class="bi bi-camera-video"></i></div>
                    <div class="dz-text">
                        <strong id="videoName"><?= $isEdit && $product['video'] ? 'Video attached' : 'Click to upload' ?></strong>
                        <small>MP4, WEBM, MOV · max 16 MB</small>
                    </div>
                    <input type="file" name="video" accept="video/*" class="d-none" id="videoInput" data-testid="product-video-input">
                </div>
                <?php if ($isEdit && product_video_abs($product)): ?>
                    <div class="input-hint mt-2"><a href="<?= e(product_video_abs($product)) ?>" target="_blank" rel="noopener">View current video</a> · leave empty to keep.</div>
                <?php endif; ?>
                <?php if (drive_is_enabled()): ?><div class="input-hint mt-2"><i class="bi bi-google"></i> Google Drive is enabled — files will be stored there.</div><?php endif; ?>
            </div>
            <div class="d-grid gap-2 mt-3">
                <button type="submit" class="btn btn-primary py-2" data-testid="product-submit-btn"><i class="bi bi-check-lg me-1"></i> <?= $isEdit ? 'Save changes' : 'Create product' ?></button>
                <a href="<?= url('products') ?>" class="btn btn-icon" style="width:auto;">Cancel</a>
            </div>
        </div>
    </div>
</form>
