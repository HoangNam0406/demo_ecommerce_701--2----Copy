<?php include '../admin_header.php'; ?>
<?php
require_once '../../config/database.php';
// Get categories for filtering and selection when adding/editing products
$cat_stmt = $pdo->query('SELECT * FROM categories ORDER BY name');
$categories = $cat_stmt->fetchAll();
// Get product list with category name
$sql = 'SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC';
$stmt = $pdo->query($sql);
$products = $stmt->fetchAll();
$msg = $_GET['msg'] ?? '';
$error = $_GET['error'] ?? '';
?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Product Management</h1>
    <?php if ($msg === 'added'): ?>
        <div class="alert alert-success">Product added successfully!</div>
    <?php elseif ($msg === 'updated'): ?>
        <div class="alert alert-success">Product updated successfully!</div>
    <?php elseif ($msg === 'deleted'): ?>
        <div class="alert alert-success">Product deleted successfully!</div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Product List</h6>
            <a href="add_form.php" class="btn btn-success btn-sm float-right">Add Product</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Product Name</th>
                            <th>Price</th>
                            <th>Category</th>
                            <th>Thumbnail</th>
                            <th>Stock</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr><td colspan="8" class="text-center">No products found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($products as $p): ?>
                                <tr>
                                    <td><?= $p['id'] ?></td>
                                    <td><?= htmlspecialchars($p['name']) ?></td>
                                    <td>$<?= number_format($p ['price'], 0, ',', '.') ?></td>
                                    <td><?= htmlspecialchars($p['category_name']) ?></td>
                                    <td><?php if ($p['thumbnail']): ?><img src="../../assets/images/product-images/<?= htmlspecialchars($p['thumbnail']) ?>" width="60"><?php endif; ?></td>
                                    <td><?= $p['stock'] ?></td>
                                    <td><?= $p['created_at'] ?></td>
                                    <td>
                                        <a href="edit.php?id=<?= $p['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="delete.php?id=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete?')">Delete</a>
                                        <a href="images.php?id=<?= $p['id'] ?>" class="btn btn-info btn-sm">Images</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include '../admin_footer.php'; ?>
