<?php
// Xử lý xóa danh mục
require_once '../../config/database.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare('DELETE FROM categories WHERE id = :id');
    $stmt->execute(['id' => $id]);
}
header('Location: index.php?msg=deleted');
exit;
?>
