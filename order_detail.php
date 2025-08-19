<?php
// order_detail.php: Display order details
session_start();
require_once 'config/app.php';
require_once 'config/database.php';

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
if (!$order_id) {
    echo '<div class="container py-5"><div class="alert alert-danger">Order not found.</div></div>';
    exit;
}

// Get order information
$stmt_order = $pdo->prepare('SELECT o.*, u.name, u.phone, u.address FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?');
$stmt_order->execute([$order_id]);
$order = $stmt_order->fetch();
if (!$order) {
    echo '<div class="container py-5"><div class="alert alert-danger">Order not found.</div></div>';
    exit;
}
// Get order details
$stmt_details = $pdo->prepare('SELECT od.*, p.name FROM order_details od JOIN products p ON od.product_id = p.id WHERE od.order_id = ?');
$stmt_details->execute([$order_id]);
$order_details = $stmt_details->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>
<div class="container py-5" style="padding-top:180px;">
    <h2 class="mb-4" style="margin-top:50px;">Order Details #<?php echo htmlspecialchars($order['order_code']); ?></h2>
    <div class="mb-3">
        <strong>Recipient:</strong> <?php echo htmlspecialchars($order['name']); ?> <br>
        <strong>Address:</strong> <?php echo htmlspecialchars($order['address']); ?> <br>
        <strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?> <br>
        <strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?> <br>
        <strong>Order Date:</strong> <?php echo htmlspecialchars($order['created_at']); ?> <br>
        <strong>Note:</strong> <?php echo nl2br(htmlspecialchars($order['note'])); ?>
    </div>
    <h5>Product List</h5>
    <ul class="list-group mb-3">
        <?php foreach ($order_details as $item): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['quantity']; ?>
                <span><?php echo number_format($item['price'], 0, '.', '.'); ?>₫</span>
            </li>
        <?php endforeach; ?>
    </ul>
    <div class="mb-3">
    <strong>Subtotal: <?php echo number_format($order['subtotal'], 0, '.', '.'); ?>₫</strong><br>
    <strong>Tax/Fee: <?php echo number_format($order['tax_fee'], 0, '.', '.'); ?>₫</strong><br>
    <strong>Total: <?php echo number_format($order['total'], 0, '.', '.'); ?>₫</strong>
    </div>
    <a href="index.php" class="btn btn-success">Continue Shopping</a>
</div>
<?php include 'footer.php'; ?>
