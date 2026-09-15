<?php
// Categories controller

function categories_route(string $action, array $seg, string $method): void {
    require_login();
    switch ($action) {
        case '':       categories_index(); break;
        case 'store':  categories_store(); break;
        case 'update': categories_update(); break;
        case 'delete': categories_delete(); break;
        default: http_response_code(404); render('errors/404', ['title' => 'Not Found', 'active' => 'categories']);
    }
}

function categories_index(): void {
    $rows = db()->query(
        'SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS product_count
         FROM categories c ORDER BY c.name'
    )->fetchAll();

    $editId = ($_GET['edit'] ?? '') !== '' ? (int)$_GET['edit'] : null;
    $edit = null;
    if ($editId) {
        $stmt = db()->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt->execute([$editId]);
        $edit = $stmt->fetch() ?: null;
    }

    render('categories/index', [
        'title'      => 'Categories',
        'active'     => 'categories',
        'categories' => $rows,
        'edit'       => $edit,
    ]);
    clear_old();
}

function categories_store(): void {
    csrf_verify();
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    if ($name === '' || mb_strlen($name) > 120) {
        set_old($_POST);
        flash('danger', 'Category name is required (max 120 chars).');
        redirect('categories');
    }
    $slug = slugify($name);
    try {
        db()->prepare('INSERT INTO categories (name, slug, description) VALUES (?,?,?)')
            ->execute([$name, $slug, $desc ?: null]);
        flash('success', 'Category added.');
    } catch (PDOException $e) {
        flash('danger', str_contains($e->getMessage(), 'Duplicate') ? 'A category with that name already exists.' : 'Could not add category.');
    }
    redirect('categories');
}

function categories_update(): void {
    csrf_verify();
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    if ($name === '' || mb_strlen($name) > 120) {
        flash('danger', 'Category name is required (max 120 chars).');
        redirect('categories?edit=' . $id);
    }
    try {
        db()->prepare('UPDATE categories SET name=?, slug=?, description=? WHERE id=?')
            ->execute([$name, slugify($name), $desc ?: null, $id]);
        flash('success', 'Category updated.');
    } catch (PDOException $e) {
        flash('danger', str_contains($e->getMessage(), 'Duplicate') ? 'A category with that name already exists.' : 'Could not update category.');
    }
    redirect('categories');
}

function categories_delete(): void {
    csrf_verify();
    $id = (int)($_POST['id'] ?? 0);
    // products.category_id is ON DELETE SET NULL, so products are preserved.
    db()->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
    flash('success', 'Category deleted. Products in it were left uncategorized.');
    redirect('categories');
}
