<?php
ob_start();
include 'admin_header.php'; ?>
<?php
// Database connection
$pdo = new PDO("mysql:host=localhost;dbname=sportshoes", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Add order
if (isset($_POST['add_order'])) {
    $customer_name = $_POST['customer_name'];
    $order_code = $_POST['order_code'];
    $status = $_POST['status'];
    $subtotal = $_POST['subtotal'];
    $tax_fee = $_POST['tax_fee'];
    $total = $_POST['total'];

    $stmt = $pdo->prepare("INSERT INTO orders (customer_name, order_code, status, subtotal, tax_fee, total, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$customer_name, $order_code, $status, $subtotal, $tax_fee, $total]);
    header("Location: order_management.php");
    exit;
}

// Delete order
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    // Delete order details first
    $stmt_details = $pdo->prepare("DELETE FROM order_details WHERE order_id = ?");
    $stmt_details->execute([$id]);
    // Then delete order
    $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: order_management.php");
    exit;
}

// Update order
if (isset($_POST['update_order'])) {
    $id = $_POST['order_id'];
    $customer_name = $_POST['customer_name'];
    $status = $_POST['status'];
    $subtotal = $_POST['subtotal'];
    $tax_fee = $_POST['tax_fee'];
    $total = $_POST['total'];

    $stmt = $pdo->prepare("UPDATE orders SET customer_name=?, status=?, subtotal=?, tax_fee=?, total=?, updated_at=NOW() WHERE id=?");
    $stmt->execute([$customer_name, $status, $subtotal, $tax_fee, $total, $id]);
    header("Location: order_management.php");
    exit;
}
?>

<!-- Begin Page Content -->
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Order Management</h1>
    <button class="btn btn-primary" data-toggle="modal" data-target="#addOrderModal">Add Order</button>
    </div>

    <!-- Orders Table -->
    <div class="card shadow mb-4">
    <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Order List</h6></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer Name</th>
                            <th>Created At</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->query("SELECT * FROM orders ORDER BY id DESC");
                        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        if (count($orders) > 0) {
                            foreach ($orders as $row) {
                                echo '<tr>
                                    <td>' . $row['id'] . '</td>
                                    <td>' . htmlspecialchars($row['customer_name']) . '</td>
                                    <td>' . $row['created_at'] . '</td>
                                    <td>$' . number_format($row['total']) . '</td>
                                    <td>';
                                if ($row['status'] == 'Delivered') {
                                    echo '<span class="badge badge-success">Delivered</span>';
                                } elseif ($row['status'] == 'Processing') {
                                    echo '<span class="badge badge-warning">Processing</span>';
                                } else {
                                    echo '<span class="badge badge-secondary">' . htmlspecialchars($row['status']) . '</span>';
                                }
                                echo '</td>
                                    <td>
                                        <button class="btn btn-info btn-sm editBtn" 
                                            data-id="' . $row['id'] . '"
                                            data-customer="' . htmlspecialchars($row['customer_name']) . '"
                                            data-status="' . $row['status'] . '"
                                            data-subtotal="' . $row['subtotal'] . '"
                                            data-tax="' . $row['tax_fee'] . '"
                                            data-total="' . $row['total'] . '"
                                        >Edit</button>
                                        <a href="order_management.php?delete=' . $row['id'] . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure you want to delete?\')">Delete</a>
                                    </td>
                                </tr>';
                            }
                        } else {
                            echo '<tr><td colspan="6" class="text-center">No orders found</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Order Modal -->
<div class="modal fade" id="addOrderModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Order</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input name="customer_name" class="form-control mb-2" placeholder="Customer Name" required>
                <input name="order_code" class="form-control mb-2" placeholder="Order Code" required>
                <select name="status" class="form-control mb-2">
                    <option>Processing</option>
                    <option>Delivered</option>
                </select>
                <input name="subtotal" type="number" step="0.01" class="form-control mb-2" placeholder="Subtotal" required>
                <input name="tax_fee" type="number" step="0.01" class="form-control mb-2" placeholder="Tax" required>
                <input name="total" type="number" step="0.01" class="form-control mb-2" placeholder="Total" required>
            </div>
            <div class="modal-footer">
                <button type="submit" name="add_order" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Order Modal -->
<div class="modal fade" id="editOrderModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form method="POST" class="modal-content">
            <input type="hidden" name="order_id" id="edit_order_id">
            <div class="modal-header">
                <h5 class="modal-title">Update Order</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input name="customer_name" id="edit_customer_name" class="form-control mb-2" required>
                <select name="status" id="edit_status" class="form-control mb-2">
                    <option>Processing</option>
                    <option>Delivered</option>
                </select>
                <input name="subtotal" type="number" step="0.01" id="edit_subtotal" class="form-control mb-2" required>
                <input name="tax_fee" type="number" step="0.01" id="edit_tax_fee" class="form-control mb-2" required>
                <input name="total" type="number" step="0.01" id="edit_total" class="form-control mb-2" required>
            </div>
            <div class="modal-footer">
                <button type="submit" name="update_order" class="btn btn-success">Update</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Script -->
<script>
    document.querySelectorAll('.editBtn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('edit_order_id').value = this.dataset.id;
            document.getElementById('edit_customer_name').value = this.dataset.customer;
            document.getElementById('edit_status').value = this.dataset.status;
            document.getElementById('edit_subtotal').value = this.dataset.subtotal;
            document.getElementById('edit_tax_fee').value = this.dataset.tax;
            document.getElementById('edit_total').value = this.dataset.total;
            $('#editOrderModal').modal('show');
        });
    });
</script>
<?php include 'admin_footer.php'; ?>
<?php ob_end_flush(); ?>

