<?php
require_once '../../config/database.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
// Get product information
$stmt = $pdo->prepare('SELECT * FROM products WHERE id = :id');
$stmt->execute(['id' => $id]);
$product = $stmt->fetch();
if (!$product) {
    header('Location: index.php?error=Product not found');
    exit;
}
// Handle product update before rendering HTML
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $category_id = intval($_POST['category_id'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $descriptions = trim($_POST['descriptions'] ?? '');
    $updated_at = date('Y-m-d H:i:s');
    $thumbnail = $product['thumbnail'];
    // Xóa thumbnail nếu chọn
    if (isset($_POST['delete_thumbnail']) && $product['thumbnail']) {
        $thumbnail = '';
        $thumb_path = '../../assets/images/product-images/' . $product['thumbnail'];
        if (file_exists($thumb_path)) @unlink($thumb_path);
    }
    // Upload thumbnail mới nếu có
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('thumb_') . '.' . $ext;
        $target = '../../assets/images/product-images/' . $filename;
        if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $target)) {
            $thumbnail = $filename;
        }
    }
    // Xóa ảnh phụ nếu chọn
    if (!empty($_POST['delete_images'])) {
        foreach ($_POST['delete_images'] as $img_id) {
            $img_id = (int)$img_id;
            $img_stmt = $pdo->prepare('SELECT image_url FROM product_images WHERE id = :id');
            $img_stmt->execute(['id' => $img_id]);
            $img = $img_stmt->fetch();
            if ($img && $img['image_url']) {
                $img_path = '../../assets/images/product-images/' . $img['image_url'];
                if (file_exists($img_path)) @unlink($img_path);
            }
            $del_stmt = $pdo->prepare('DELETE FROM product_images WHERE id = :id');
            $del_stmt->execute(['id' => $img_id]);
        }
    }
    // Upload thêm ảnh phụ mới
    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['tmp_name'] as $k => $tmp_name) {
            if ($_FILES['images']['error'][$k] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['images']['name'][$k], PATHINFO_EXTENSION);
                $filename = uniqid('img_') . '.' . $ext;
                $target = '../../assets/images/product-images/' . $filename;
                if (move_uploaded_file($tmp_name, $target)) {
                    $ins_stmt = $pdo->prepare('INSERT INTO product_images (image_url, product_id) VALUES (:url, :pid)');
                    $ins_stmt->execute(['url' => $filename, 'pid' => $id]);
                }
            }
        }
    }
    $stmt = $pdo->prepare('UPDATE products SET name = :name, price = :price, descriptions = :descriptions, thumbnail = :thumbnail, stock = :stock, category_id = :category_id, updated_at = :updated_at WHERE id = :id');
    $stmt->execute([
        'name' => $name,
        'price' => $price,
        'descriptions' => $descriptions,
        'thumbnail' => $thumbnail,
        'stock' => $stock,
        'category_id' => $category_id,
        'updated_at' => $updated_at,
        'id' => $id
    ]);
    header('Location: index.php?msg=updated');
    exit;
}
// Get categories
$cat_stmt = $pdo->query('SELECT * FROM categories ORDER BY name');
$categories = $cat_stmt->fetchAll();
include '../admin_header.php';
?>
<div class="container mt-5">
    <h2>Edit Product</h2>
    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">Product Name</label>
            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
        </div>
        <div class="form-group">
            <label for="price">Price</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">$</span>
                </div>
                <input type="text" class="form-control" id="price" name="price" value="<?= number_format($product['price'], 0, '.', ',') ?>" required min="0">
            </div>
        </div>
        <div class="form-group">
            <label for="category_id">Category</label>
            <select class="form-control" id="category_id" name="category_id" required>
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $product['category_id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Current Thumbnail Image:</label><br>
            <?php if ($product['thumbnail']): ?>
                <img src="../../assets/images/product-images/<?= htmlspecialchars($product['thumbnail']) ?>" width="80">
                <label class="ml-2"><input type="checkbox" name="delete_thumbnail" value="1"> Delete this image</label>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label for="thumbnail">Change Thumbnail Image (optional)</label>
            <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/*">
        </div>
        <div class="form-group">
            <label>Current Detail Images:</label><br>
            <?php
            $img_stmt = $pdo->prepare('SELECT * FROM product_images WHERE product_id = :id');
            $img_stmt->execute(['id' => $id]);
            $images = $img_stmt->fetchAll();
            ?>
            <div class="row">
                <?php foreach ($images as $img): ?>
                    <div class="col-md-2 text-center mb-3">
                        <img src="../../assets/images/product-images/<?= htmlspecialchars($img['image_url']) ?>" class="img-fluid mb-2" style="max-height:80px;">
                        <label><input type="checkbox" name="delete_images[]" value="<?= $img['id'] ?>"> Delete</label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="form-group">
            <label for="images">Add New Detail Images (can select multiple)</label>
            <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*">
        </div>
        <div class="form-group">
            <label for="stock">Stock</label>
            <input type="number" class="form-control" id="stock" name="stock" min="0" value="<?= $product['stock'] ?>">
        </div>
        <div class="form-group">
            <label for="descriptions">Description</label>
            <textarea class="form-control" id="descriptions" name="descriptions" rows="3"><?= htmlspecialchars($product['descriptions']) ?></textarea>
        </div>
    <button type="submit" class="btn btn-primary">Update</button>
    <a href="index.php" class="btn btn-secondary">Back</a>
    </form>
</div>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $category_id = intval($_POST['category_id'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $descriptions = trim($_POST['descriptions'] ?? '');
    $updated_at = date('Y-m-d H:i:s');
    $thumbnail = $product['thumbnail'];
    // Delete thumbnail if selected
    if (isset($_POST['delete_thumbnail']) && $product['thumbnail']) {
        $thumbnail = '';
    // Delete physical file if needed
        $thumb_path = '../../assets/images/product-images/' . $product['thumbnail'];
        if (file_exists($thumb_path)) @unlink($thumb_path);
    }
    // Upload new thumbnail if available
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('thumb_') . '.' . $ext;
        $target = '../../assets/images/product-images/' . $filename;
        if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $target)) {
            $thumbnail = $filename;
        }
    }
    // Delete detail images if selected
    if (!empty($_POST['delete_images'])) {
        foreach ($_POST['delete_images'] as $img_id) {
            $img_id = (int)$img_id;
            $img_stmt = $pdo->prepare('SELECT image_url FROM product_images WHERE id = :id');
            $img_stmt->execute(['id' => $img_id]);
            $img = $img_stmt->fetch();
            if ($img && $img['image_url']) {
                $img_path = '../../assets/images/product-images/' . $img['image_url'];
                if (file_exists($img_path)) @unlink($img_path);
            }
            $del_stmt = $pdo->prepare('DELETE FROM product_images WHERE id = :id');
            $del_stmt->execute(['id' => $img_id]);
        }
    }
    // Upload new detail images
    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['tmp_name'] as $k => $tmp_name) {
            if ($_FILES['images']['error'][$k] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['images']['name'][$k], PATHINFO_EXTENSION);
                $filename = uniqid('img_') . '.' . $ext;
                $target = '../../assets/images/product-images/' . $filename;
                if (move_uploaded_file($tmp_name, $target)) {
                    $ins_stmt = $pdo->prepare('INSERT INTO product_images (image_url, product_id) VALUES (:url, :pid)');
                    $ins_stmt->execute(['url' => $filename, 'pid' => $id]);
                }
            }
        }
    }
    $stmt = $pdo->prepare('UPDATE products SET name = :name, price = :price, descriptions = :descriptions, thumbnail = :thumbnail, stock = :stock, category_id = :category_id, updated_at = :updated_at WHERE id = :id');
    $stmt->execute([
        'name' => $name,
        'price' => $price,
        'descriptions' => $descriptions,
        'thumbnail' => $thumbnail,
        'stock' => $stock,
        'category_id' => $category_id,
        'updated_at' => $updated_at,
        'id' => $id
    ]);
    header('Location: index.php?msg=updated');
    exit;
}
?>
<?php include '../admin_footer.php'; ?>
