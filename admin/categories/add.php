<?php
// Xử lý thêm danh mục
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    if ($name !== '') {
        $stmt = $pdo->prepare('INSERT INTO categories (name) VALUES (:name)');
        $stmt->execute(['name' => $name]);
        header('Location: index.php?msg=added');
        exit;
    } else {
        header('Location: index.php?error=empty');
        exit;
    }
}
?>
