<?php include '../admin_header.php'; ?>
<?php
require_once '../../config/database.php';
// Get categories for selection
$cat_stmt = $pdo->query('SELECT * FROM categories ORDER BY name');
$categories = $cat_stmt->fetchAll();
?>
<div class="container mt-5">
    <h2>Add Product</h2>
    <form method="post" action="add.php" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">Product Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="price">Price</label>
            <input type="number" class="form-control" id="price" name="price" required min="0">
        </div>
        <div class="form-group">
            <label for="category_id">Category</label>
            <select class="form-control" id="category_id" name="category_id" required>
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="thumbnail">Thumbnail Image</label>
            <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/*" required>
        </div>
        <div class="form-group">
            <label for="stock">Stock</label>
            <input type="number" class="form-control" id="stock" name="stock" min="0" value="0">
        </div>
        <div class="form-group">
            <label for="descriptions">Description</label>
            <textarea class="form-control" id="descriptions" name="descriptions" rows="3"></textarea>
        </div>
        <div class="form-group">
            <label for="images">Detail Images (can select multiple)</label>
            <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*">
        </div>
        <button type="submit" class="btn btn-success">Add</button>
        <a href="index.php" class="btn btn-secondary">Back</a>
    </form>
</div>
<?php include '../admin_footer.php'; ?>
