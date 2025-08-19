<?php include '../admin_header.php'; ?>
<div class="container mt-5">
    <h2>Add Category</h2>
    <form method="post" action="add.php">
        <div class="form-group">
            <label for="name">Category Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <button type="submit" class="btn btn-success">Add</button>
        <a href="index.php" class="btn btn-secondary">Back</a>
    </form>
</div>
<?php include '../admin_footer.php'; ?>
