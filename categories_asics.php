<?php 
session_start();
include 'header.php'; 
require_once __DIR__ . '/config/database.php';

// Handle add to cart when form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
  $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;
  $product_id = (int)$_POST['product_id'];
  $quantity = (int)($_POST['quantity'] ?? 1);
  $size = $_POST['size'] ?? '';
  // Get thumbnail from products table
  $stmt_img = $pdo->prepare('SELECT thumbnail FROM products WHERE id = ?');
  $stmt_img->execute([$product_id]);
  $img_row = $stmt_img->fetch();
  $image = $img_row && !empty($img_row['thumbnail']) ? 'assets/images/product-images/' . $img_row['thumbnail'] : '';
  // If product already in cart, update quantity; otherwise, insert new
  $stmt_check = $pdo->prepare('SELECT id, quantity FROM carts WHERE user_id = ? AND product_id = ? AND size = ?');
  $stmt_check->execute([$user_id, $product_id, $size]);
  $row = $stmt_check->fetch();
  if ($row) {
    $new_qty = $row['quantity'] + $quantity;
    $stmt_update = $pdo->prepare('UPDATE carts SET quantity = ?, image = ? WHERE id = ?');
    $stmt_update->execute([$new_qty, $image, $row['id']]);
  } else {
    $stmt_insert = $pdo->prepare('INSERT INTO carts (user_id, product_id, quantity, size, image) VALUES (?, ?, ?, ?, ?)');
    $stmt_insert->execute([$user_id, $product_id, $quantity, $size, $image]);
  }
  header('Location: cart.php');
  exit;
}

// Display all products in Asics brand (category_id = 1)
$products = [];
$stmt = $pdo->prepare('SELECT * FROM products WHERE category_id = 1 ORDER BY created_at DESC');
$stmt->execute();
$products = $stmt->fetchAll();
?>

<!--start page content-->
<div class="page-content">


   <!--start breadcrumb-->
   <div class="py-4 border-bottom">
    <div class="container">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0"> 
          <li class="breadcrumb-item"><a href="javascript:;">Home</a></li>
          <li class="breadcrumb-item"><a href="javascript:;">Shop</a></li>
          <li class="breadcrumb-item active" aria-current="page">Asics</li>
        </ol>
      </nav>
    </div>
   </div>
   <!--end breadcrumb-->


   <!--start product grid-->
   <section class="py-4">
    <div class="container">
      <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
        <?php if (!empty($products)): ?>
          <?php foreach ($products as $product): ?>
            <div class="col">
              <div class="card border shadow-none h-100 product-hover-card">
                <a href="product-details.php?id=<?= $product['id'] ?>">
                  <img src="assets/images/product-images/<?= htmlspecialchars($product['thumbnail']) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>" style="object-fit:cover; height:220px;">
                </a>
                <div class="card-body">
                  <h5 class="fw-bold mb-1" title="<?= htmlspecialchars($product['name']) ?>">
                    <?= htmlspecialchars($product['name']) ?>
                  </h5>
                  <div class="mb-2 text-danger fw-bold">
                    $<?= number_format($product['price'], 0, '.', '.') ?>
                  </div>
                  <div class="mb-2">Stock: <?= (int)$product['stock'] ?></div>
                  <div class="mb-2" style="font-size:13px; color:#555; min-height:40px;">
                    <?= nl2br(htmlspecialchars(mb_strimwidth($product['descriptions'], 0, 60, '...'))) ?>
                  </div>
                  <button type="button" class="btn btn-dark btn-ecomm btn-sm btn-add-to-cart" 
                    data-product-id="<?= $product['id'] ?>"
                    data-product-name="<?= htmlspecialchars($product['name']) ?>"
                    data-product-price="<?= $product['price'] ?>"
                    data-product-thumbnail="<?= htmlspecialchars($product['thumbnail']) ?>"
                  >Add to Cart</button>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-12"><div class="alert alert-danger">No Asics products found.</div></div>
        <?php endif; ?>
      </div>
    </div>
  </section>
   <!--start product details-->


  
  
 </div>
  <!--end page content-->
<style>
  .product-hover-card {
    transition: transform 0.2s cubic-bezier(.4,0,.2,1), box-shadow 0.2s;
  }
  .product-hover-card:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    z-index: 2;
  }
</style>
<!-- Hidden form to submit add to cart -->
<form id="addToCartForm" method="post" style="display:none;">
  <input type="hidden" name="add_to_cart" value="1">
  <input type="hidden" name="product_id" value="">
  <input type="hidden" name="quantity" value="1">
  <input type="hidden" name="size" value="">
</form>
<script>
$(document).ready(function() {
  $('.btn-add-to-cart').on('click', function() {
    $('#addToCartForm input[name="product_id"]').val($(this).data('product-id'));
    $('#addToCartForm input[name="quantity"]').val(1);
    $('#addToCartForm input[name="size"]').val('');
  $('#addToCartForm').removeAttr('action'); // Ensure submit to this file
    $('#addToCartForm').submit();
  });
});
</script>
<?php include 'footer.php'; ?>