<?php
// Display edit form and handle category update
require_once '../../config/database.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    if ($name !== '' && $id > 0) {
        $stmt = $pdo->prepare('UPDATE categories SET name = :name WHERE id = :id');
        $stmt->execute(['name' => $name, 'id' => $id]);
        header('Location: index.php?msg=updated');
        exit;
    }
}
// Get category info to display in form
$stmt = $pdo->prepare('SELECT * FROM categories WHERE id = :id');
$stmt->execute(['id' => $id]);
$category = $stmt->fetch();
if (!$category) {
    header('Location: index.php?error=notfound');
    exit;
}
include '../admin_header.php';
?>
<div class="container mt-5">
    <h2>Edit Category</h2>
    <form method="post">
        <div class="form-group">
            <label for="name">Category Name</label>
            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($category['name']) ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="index.php" class="btn btn-secondary">Back</a>
    </form>
</div>
<?php include '../admin_footer.php'; ?>
