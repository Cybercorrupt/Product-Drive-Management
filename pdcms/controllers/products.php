<?php
// Products controller (CRUD + search/filter + image upload)

function products_route(string $action, array $seg, string $method): void {
    require_login();
    switch ($action) {
        case '':       products_index(); break;
        case 'create': products_create(); break;
        case 'store':  products_store(); break;
        case 'edit':   products_edit($seg[2] ?? null); break;
        case 'update': products_update(); break;
        case 'delete': products_delete(); break;
        default: http_response_code(404); render('errors/404', ['title' => 'Not Found', 'active' => 'products']);
    }
}

function products_index(): void {
    $pdo = db();
    $q        = trim($_GET['q'] ?? '');
    $catId    = ($_GET['category'] ?? '') !== '' ? (int)$_GET['category'] : null;
    $status   = in_array($_GET['status'] ?? '', ['active', 'draft', 'archived'], true) ? $_GET['status'] : '';
    $page     = max(1, (int)($_GET['page'] ?? 1));
    $perPage  = 8;
    $offset   = ($page - 1) * $perPage;

    $where = [];
    $params = [];
    if ($q !== '')     { $where[] = '(p.name LIKE ? OR p.sku LIKE ?)'; $params[] = "%$q%"; $params[] = "%$q%"; }
    if ($catId)        { $where[] = 'p.category_id = ?'; $params[] = $catId; }
    if ($status !== ''){ $where[] = 'p.status = ?'; $params[] = $status; }
    $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM products p $whereSql");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();
    $pages = max(1, (int)ceil($total / $perPage));

    $sql = "SELECT p.*, c.name AS category_name
            FROM products p LEFT JOIN categories c ON c.id = p.category_id
            $whereSql ORDER BY p.created_at DESC LIMIT $perPage OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    $categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();

    render('products/index', [
        'title'      => 'Products',
        'active'     => 'products',
        'products'   => $products,
        'categories' => $categories,
        'q'          => $q,
        'catId'      => $catId,
        'status'     => $status,
        'page'       => $page,
        'pages'      => $pages,
        'total'      => $total,
    ]);
}

function products_create(): void {
    $categories = db()->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
    render('products/form', [
        'title'      => 'Add Product',
        'active'     => 'products',
        'product'    => null,
        'categories' => $categories,
    ]);
    clear_old();
}

function products_edit($id): void {
    $id = (int)$id;
    $stmt = db()->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    if (!$product) { flash('danger', 'Product not found.'); redirect('products'); }
    $categories = db()->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
    render('products/form', [
        'title'      => 'Edit Product',
        'active'     => 'products',
        'product'    => $product,
        'categories' => $categories,
    ]);
    clear_old();
}

function products_validate(array $in, &$errors): array {
    $name  = trim($in['name'] ?? '');
    $sku   = trim($in['sku'] ?? '');
    $price = $in['price'] ?? '';
    $stock = $in['stock'] ?? '';
    $cat   = ($in['category_id'] ?? '') !== '' ? (int)$in['category_id'] : null;
    $status= in_array($in['status'] ?? '', ['active', 'draft', 'archived'], true) ? $in['status'] : 'active';
    $desc  = trim($in['description'] ?? '');

    if ($name === '' || mb_strlen($name) > 190) $errors['name'] = 'Name is required (max 190 chars).';
    if ($sku !== '' && mb_strlen($sku) > 64)     $errors['sku']  = 'SKU must be 64 characters or fewer.';
    if ($price === '' || !is_numeric($price) || (float)$price < 0) $errors['price'] = 'Price must be a non-negative number.';
    if ($stock === '' || !ctype_digit((string)$stock)) $errors['stock'] = 'Stock must be a whole number (0 or more).';

    return [
        'name' => $name, 'sku' => ($sku === '' ? null : $sku),
        'price' => (float)$price, 'stock' => (int)$stock,
        'category_id' => $cat, 'status' => $status, 'description' => ($desc === '' ? null : $desc),
    ];
}

function products_maybe_drive(?string $localName): ?string {
    if ($localName && !preg_match('#^https?://#', $localName) && drive_is_enabled()) {
        $e = '';
        $r = drive_upload_local($localName, $e);
        if ($r) return $r['id'];
    }
    return null;
}

function products_store(): void {
    csrf_verify();
    $errors = [];
    $data = products_validate($_POST, $errors);

    $uploadErr = '';
    $image = handle_image_upload('image', $uploadErr);
    if ($image === false) { $errors['image'] = $uploadErr; $image = null; }

    $videoErr = '';
    $video = handle_video_upload('video', $videoErr);
    if ($video === false) { $errors['video'] = $videoErr; $video = null; }

    if ($errors) {
        if ($image) delete_upload($image);
        if ($video) delete_upload($video);
        set_old($_POST);
        foreach ($errors as $msg) flash('danger', $msg);
        redirect('products/create');
    }

    $imageDriveId = products_maybe_drive($image);
    $videoDriveId = products_maybe_drive($video);

    try {
        $stmt = db()->prepare(
            'INSERT INTO products (name, sku, category_id, price, stock, status, image, image_drive_id, video, video_drive_id, description, created_by)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $data['name'], $data['sku'], $data['category_id'], $data['price'], $data['stock'],
            $data['status'], $image, $imageDriveId, $video, $videoDriveId, $data['description'], current_user()['id'],
        ]);
    } catch (PDOException $e) {
        if ($image) delete_upload($image);
        if ($video) delete_upload($video);
        if ($imageDriveId) drive_delete($imageDriveId);
        if ($videoDriveId) drive_delete($videoDriveId);
        set_old($_POST);
        flash('danger', str_contains($e->getMessage(), 'Duplicate') ? 'That SKU is already in use.' : 'Could not save product.');
        redirect('products/create');
    }

    $note = drive_is_enabled() && ($imageDriveId || $videoDriveId) ? ' (files stored on Google Drive)' : '';
    flash('success', 'Product created successfully.' . $note);
    redirect('products');
}

function products_update(): void {
    csrf_verify();
    $id = (int)($_POST['id'] ?? 0);
    $stmt = db()->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    if (!$existing) { flash('danger', 'Product not found.'); redirect('products'); }

    $errors = [];
    $data = products_validate($_POST, $errors);

    $image = $existing['image'];
    $imageDriveId = $existing['image_drive_id'];
    $newImage = null;
    if (!empty($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploadErr = '';
        $newImage = handle_image_upload('image', $uploadErr);
        if ($newImage === false) { $errors['image'] = $uploadErr; $newImage = null; }
    }

    $video = $existing['video'];
    $videoDriveId = $existing['video_drive_id'];
    $newVideo = null;
    if (!empty($_FILES['video']) && $_FILES['video']['error'] !== UPLOAD_ERR_NO_FILE) {
        $videoErr = '';
        $newVideo = handle_video_upload('video', $videoErr);
        if ($newVideo === false) { $errors['video'] = $videoErr; $newVideo = null; }
    }

    if ($errors) {
        if ($newImage) delete_upload($newImage);
        if ($newVideo) delete_upload($newVideo);
        set_old($_POST);
        foreach ($errors as $msg) flash('danger', $msg);
        redirect('products/edit/' . $id);
    }

    $newImageDriveId = null;
    $newVideoDriveId = null;
    if ($newImage) { $image = $newImage; $newImageDriveId = products_maybe_drive($newImage); $imageDriveId = $newImageDriveId; }
    if ($newVideo) { $video = $newVideo; $newVideoDriveId = products_maybe_drive($newVideo); $videoDriveId = $newVideoDriveId; }

    try {
        $stmt = db()->prepare(
            'UPDATE products SET name=?, sku=?, category_id=?, price=?, stock=?, status=?, image=?, image_drive_id=?, video=?, video_drive_id=?, description=? WHERE id=?'
        );
        $stmt->execute([
            $data['name'], $data['sku'], $data['category_id'], $data['price'], $data['stock'],
            $data['status'], $image, $imageDriveId, $video, $videoDriveId, $data['description'], $id,
        ]);
        if ($newImage && $existing['image']) { delete_upload($existing['image']); if ($existing['image_drive_id']) drive_delete($existing['image_drive_id']); }
        if ($newVideo && $existing['video']) { delete_upload($existing['video']); if ($existing['video_drive_id']) drive_delete($existing['video_drive_id']); }
    } catch (PDOException $e) {
        if ($newImage) delete_upload($newImage);
        if ($newVideo) delete_upload($newVideo);
        set_old($_POST);
        flash('danger', str_contains($e->getMessage(), 'Duplicate') ? 'That SKU is already in use.' : 'Could not update product.');
        redirect('products/edit/' . $id);
    }

    flash('success', 'Product updated successfully.');
    redirect('products');
}

function products_delete(): void {
    csrf_verify();
    $id = (int)($_POST['id'] ?? 0);
    $stmt = db()->prepare('SELECT image, image_drive_id, video, video_drive_id FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    if ($product) {
        db()->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
        delete_upload($product['image']);
        delete_upload($product['video']);
        if (!empty($product['image_drive_id'])) drive_delete($product['image_drive_id']);
        if (!empty($product['video_drive_id'])) drive_delete($product['video_drive_id']);
        flash('success', 'Product deleted.');
    } else {
        flash('danger', 'Product not found.');
    }
    redirect('products');
}
