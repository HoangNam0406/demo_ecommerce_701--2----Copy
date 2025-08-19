<?php
ob_start();
include 'admin_header.php';

// Connect to database using PDO
try {
    $pdo = new PDO("mysql:host=localhost;dbname=sportshoes", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle user deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: user_management.php");
    exit();
}

// Handle add/edit user
if (isset($_POST['save'])) {
    $name = $_POST['username']; // Get from username input
    $email = $_POST['email'];
    $role = $_POST['role'];

    if (isset($_POST['id']) && $_POST['id'] !== '') {
    // Edit
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?");
        $stmt->execute([$name, $email, $role, $id]);
    } else {
    // Add
        $stmt = $pdo->prepare("INSERT INTO users (name, email, role) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $role]);
    }

    header("Location: user_management.php");
    exit();
}

// Get user data for editing
$edit_user = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $edit_user = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get user list
$stmt = $pdo->query("SELECT * FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Begin Page Content -->
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">User Management</h1>
    </div>

    <!-- Add/Edit Form -->
    <div class="card mb-4">
    <div class="card-header"><?= $edit_user ? 'Edit User' : 'Add User' ?></div>
        <div class="card-body">
            <form method="post">
                <?php if ($edit_user): ?>
                    <input type="hidden" name="id" value="<?= $edit_user['id'] ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" required value="<?= $edit_user['name'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required value="<?= $edit_user['email'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" class="form-control" required>
                        <option value="Admin" <?= (isset($edit_user['role']) && $edit_user['role']=='Admin')?'selected':''; ?>>Admin</option>
                        <option value="User" <?= (isset($edit_user['role']) && $edit_user['role']=='User')?'selected':''; ?>>User</option>
                    </select>
                </div>
                <button type="submit" name="save" class="btn btn-success"><?= $edit_user ? 'Update' : 'Add New' ?></button>
                <?php if ($edit_user): ?>
                    <a href="user_management.php" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- User List Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">User List</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $row): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['role']) ?></td>
                            <td>
                                <a href="?edit=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- /.container-fluid -->

<?php include 'admin_footer.php'; ?>
<?php ob_end_flush(); ?>