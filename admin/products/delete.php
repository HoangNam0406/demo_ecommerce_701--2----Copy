<?php
require_once '../../config/database.php';
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    // Xóa ảnh phụ
    $stmt = $pdo->prepare('DELETE FROM product_images WHERE product_id = :id');
    $stmt->execute(['id' => $id]);
    // Xóa sản phẩm
    $stmt = $pdo->prepare('DELETE FROM products WHERE id = :id');
    $stmt->execute(['id' => $id]);
}
header('Location: index.php?msg=deleted');
exit;
?>
