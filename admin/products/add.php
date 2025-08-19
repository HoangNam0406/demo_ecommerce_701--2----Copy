<?php
require_once '../../config/database.php';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $category_id = intval($_POST['category_id'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $descriptions = trim($_POST['descriptions'] ?? '');
    $created_at = $updated_at = date('Y-m-d H:i:s');
    // Xử lý upload thumbnail
    $thumbnail = '';
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('thumb_') . '.' . $ext;
        $target = '../../assets/images/product-images/' . $filename;
        if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $target)) {
            $thumbnail = $filename;
        } else {
            $errors[] = 'Lỗi upload ảnh đại diện.';
        }
    } else {
        $errors[] = 'Vui lòng chọn ảnh đại diện.';
    }
    if (!$name || !$price || !$category_id || !$thumbnail) {
        $errors[] = 'Vui lòng nhập đầy đủ thông tin.';
    }
    if (empty($errors)) {
        $stmt = $pdo->prepare('INSERT INTO products (name, price, descriptions, thumbnail, stock, category_id, created_at, updated_at) VALUES (:name, :price, :descriptions, :thumbnail, :stock, :category_id, :created_at, :updated_at)');
        $stmt->execute([
            'name' => $name,
            'price' => $price,
            'descriptions' => $descriptions,
            'thumbnail' => $thumbnail,
            'stock' => $stock,
            'category_id' => $category_id,
            'created_at' => $created_at,
            'updated_at' => $updated_at
        ]);
        $product_id = $pdo->lastInsertId();
        // Xử lý upload nhiều ảnh phụ
        if (!empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['tmp_name'] as $k => $tmp_name) {
                if ($_FILES['images']['error'][$k] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($_FILES['images']['name'][$k], PATHINFO_EXTENSION);
                    $filename = uniqid('img_') . '.' . $ext;
                    $target = '../../assets/images/product-images/' . $filename;
                    if (move_uploaded_file($tmp_name, $target)) {
                        $ins_stmt = $pdo->prepare('INSERT INTO product_images (image_url, product_id) VALUES (:url, :pid)');
                        $ins_stmt->execute(['url' => $filename, 'pid' => $product_id]);
                    }
                }
            }
        }
        header('Location: index.php?msg=added');
        exit;
    } else {
        $err = urlencode(implode(' ', $errors));
        header('Location: add_form.php?error=' . $err);
        exit;
    }
}
?>
