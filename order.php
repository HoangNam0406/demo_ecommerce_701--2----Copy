<?php
// order.php: Handle COD order from cart
session_start();
require_once 'config/app.php';
require_once 'config/database.php';

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;

// Get user information
$stmt_user = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch();

// Get products in cart
$stmt_cart = $pdo->prepare('SELECT c.*, p.name, p.price FROM carts c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?');
$stmt_cart->execute([$user_id]);
$cart_items = $stmt_cart->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order']) && !empty($cart_items)) {
    // Get new address from form
    $new_address = trim($_POST['address'] ?? '');
    $new_phone = trim($_POST['phone'] ?? '');
    $note = $_POST['note'] ?? '';

    // If address or phone number changes, update in DB
    if ($new_address !== ($user['address'] ?? '') || $new_phone !== ($user['phone'] ?? '')) {
        $stmt_update = $pdo->prepare('UPDATE users SET address = ?, phone = ? WHERE id = ?');
        $stmt_update->execute([$new_address, $new_phone, $user_id]);
        // Update $user variable to display correctly after update
        $user['address'] = $new_address;
        $user['phone'] = $new_phone;
    }

    // Calculate total amount
    $subtotal = 0;
    foreach ($cart_items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    $tax_fee = 0; // Can add more if needed
    $total = $subtotal + $tax_fee;
    $order_code = 'ORD' . time() . rand(100,999);
    $status = 'Pending Confirmation';
    $now = date('Y-m-d H:i:s');

    // Create order, add customer_name
    $customer_name = $user['name'] ?? '';
    $stmt_order = $pdo->prepare('INSERT INTO orders (user_id, customer_name, order_code, note, status, subtotal, tax_fee, total, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt_order->execute([$user_id, $customer_name, $order_code, $note, $status, $subtotal, $tax_fee, $total, $now, $now]);
    $order_id = $pdo->lastInsertId();

    // Add order details
    foreach ($cart_items as $item) {
        $item_subtotal = $item['price'] * $item['quantity'];
        $stmt_detail = $pdo->prepare('INSERT INTO order_details (order_id, product_id, price, quantity, subtotal) VALUES (?, ?, ?, ?, ?)');
        $stmt_detail->execute([$order_id, $item['product_id'], $item['price'], $item['quantity'], $item_subtotal]);
    }

    // Clear cart
    $stmt_clear = $pdo->prepare('DELETE FROM carts WHERE user_id = ?');
    $stmt_clear->execute([$user_id]);

    // Redirect to order detail page
    header('Location: order_detail.php?order_id=' . $order_id);
    exit;
}

include 'header.php';
?>
<div class="container py-5" style="padding-top:180px;">
    <h2 class="mb-4" style="margin-top:50px;">Order Confirmation (COD)</h2>
    <?php if (empty($cart_items)): ?>
        <div class="alert alert-info">Your cart is empty.</div>
    <?php else: ?>
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Customer Name</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Address</label>
                <input type="text" class="form-control" name="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Phone</label>
                <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Note</label>
                <textarea class="form-control" name="note" rows="2"></textarea>
            </div>
            <h5>Product List</h5>
            <ul class="list-group mb-3">
                <?php foreach ($cart_items as $item): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?php echo htmlspecialchars($item['name']); ?> (Size: <?php echo htmlspecialchars($item['size']); ?>) x <?php echo $item['quantity']; ?>
                        <span><?php echo number_format($item['price'], 0, '.', '.'); ?>₫</span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="mb-3">
                <strong>Total: <?php echo number_format(array_sum(array_map(function($i){return $i['price']*$i['quantity'];}, $cart_items)), 0, '.', '.'); ?>₫</strong>
            </div>
            <button type="submit" name="place_order" class="btn btn-primary">Place Order (COD)</button>
        </form>
    <?php endif; ?>

    <!-- User's order history -->
    <hr>
    <h4 class="mt-5 mb-3">Your Order History</h4>
    <?php
    $stmt_orders = $pdo->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
    $stmt_orders->execute([$user_id]);
    $orders = $stmt_orders->fetchAll(PDO::FETCH_ASSOC);
    if (empty($orders)) {
        echo '<div class="alert alert-info">You have no orders yet.</div>';
    } else {
        echo '<table class="table table-bordered">';
        echo '<thead><tr><th>Order Code</th><th>Order Date</th><th>Status</th><th>Total</th><th>Detail</th></tr></thead><tbody>';
        foreach ($orders as $order) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($order['order_code']) . '</td>';
            echo '<td>' . htmlspecialchars($order['created_at']) . '</td>';
            echo '<td>' . htmlspecialchars($order['status']) . '</td>';
            echo '<td>' . number_format($order['total'], 0, '.', '.') . '₫</td>';
            echo '<td><a href="order_detail.php?order_id=' . $order['id'] . '" class="btn btn-sm btn-info">Detail</a></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    }
    ?>
</div>
<?php include 'footer.php'; ?>
