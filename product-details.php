<?php
session_start();
require_once 'config/app.php';
require_once 'config/database.php';
// Handle add to cart when form is submitted
$error_message = '';
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
  $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
  $product_id = (int)$_POST['product_id'];
  $quantity = (int)($_POST['quantity'] ?? 1);
  $size = $_POST['size'] ?? '';
  if (empty($size)) {
    $error_message = 'Please select a size before adding to cart!';
  } else if ($user_id == 0) {
    $error_message = 'You must be logged in to add items to your cart.';
  } else {
    // Get thumbnail from products table
    $stmt_img = $pdo->prepare('SELECT thumbnail FROM products WHERE id = ?');
    $stmt_img->execute([$product_id]);
    $img_row = $stmt_img->fetch();
    $image = $img_row && !empty($img_row['thumbnail']) ? 'assets/images/product-images/' . $img_row['thumbnail'] : 'assets/images/featured-products/01.webp';
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
}
include 'header.php';
// Get product data from database using PDO
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
$stmt->execute([$product_id]);
$product = $stmt->fetch();
?>
<!--end top header-->


<!--start page content-->
<div class="page-content">


  <!--start breadcrumb-->
  <div class="py-4 border-bottom">
    <div class="container">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item"><a href="javascript:;">Home</a></li>
          <li class="breadcrumb-item"><a href="javascript:;">Shop</a></li>
          <li class="breadcrumb-item active" aria-current="page">Page Details</li>
        </ol>
      </nav>
    </div>
  </div>
  <!--end breadcrumb-->


  <!--start product details-->
  <section class="py-4">
    <div class="container">
      <div class="row g-4">
        <div class="col-12 col-xl-7">
          <div class="product-images">
            <div class="product-zoom-images">
              <div class="row row-cols-2 g-3">
                <div class="col">
                  <div class="img-thumb-container overflow-hidden position-relative" data-fancybox="gallery" data-src="<?php echo isset($product['thumbnail']) && $product['thumbnail'] ? 'assets/images/product-images/' . htmlspecialchars($product['thumbnail']) : 'assets/images/product-images/01.jpg'; ?>">
                    <img src="<?php echo isset($product['thumbnail']) && $product['thumbnail'] ? 'assets/images/product-images/' . htmlspecialchars($product['thumbnail']) : 'assets/images/product-images/01.jpg'; ?>" class="img-fluid" alt="">
                  </div>
                </div>
              </div><!--end row-->
            </div>
          </div>
        </div>
        <div class="col-12 col-xl-5">
          <div class="product-info">
            <h4 class="product-title fw-bold mb-1"><?php echo htmlspecialchars($product['name'] ?? ''); ?></h4>
            <p class="mb-0"><?php echo htmlspecialchars($product['description'] ?? ''); ?></p>
            <div class="product-rating">
              <div class="hstack gap-2 border p-1 mt-3 width-content">
                <div><span class="rating-number">4.8</span><i class="bi bi-star-fill ms-1 text-warning"></i></div>
                <div class="vr"></div>
                <div>162 Ratings</div>
              </div>
            </div>
            <hr>
            <div class="product-price d-flex align-items-center gap-3">
              <div class="h4 fw-bold">$<?php echo number_format($product['price'] ?? 0); ?></div>
              <?php if (!empty($product['old_price'])): ?>
              <div class="h5 fw-light text-muted text-decoration-line-through">$<?php echo number_format($product['old_price']); ?></div>
              <?php endif; ?>
              <div class="h4 fw-bold text-danger"><?php if (!empty($product['discount'])) echo '(' . $product['discount'] . '% off)'; ?></div>
            </div>
            <p class="fw-bold mb-0 mt-1 text-success">inclusive of all taxes</p>



            <div class="size-chart mt-4">
              <h6 class="fw-bold mb-3">Select Size</h6>
              <div class="d-flex align-items-center gap-2 flex-wrap">
                <div class="">
                  <button type="button">39</button>
                </div>
                <div class="">
                  <button type="button">40</button>
                </div>
                <div class="">
                  <button type="button">41</button>
                </div>
                <div class="">
                  <button type="button">42</button>
                </div>
                <div class="">
                  <button type="button">43</button>
                </div>
                <div class="">
                  <button type="button">44</button>
                </div>
              </div>
            </div>


            <!-- Display error message if any -->
            <?php if (!empty($error_message)): ?>
              <div class="alert alert-danger mt-3" id="sizeErrorMsg"><?php echo $error_message; ?></div>
            <?php else: ?>
              <div class="alert alert-danger mt-3 d-none" id="sizeErrorMsg"></div>
            <?php endif; ?>
            <div class="cart-buttons mt-3">
              <div class="buttons d-flex flex-column flex-lg-row gap-3 mt-4">
                <button type="button" id="addToBagBtn" class="btn btn-lg btn-dark btn-ecomm px-5 py-3 col-lg-6"><i class="bi bi-basket2 me-2"></i>Add to Bag</button>
              </div>
            </div>

            <hr class="my-3">
            <div class="product-info">
              <h6 class="fw-bold mb-3">Product Details</h6>
              <p class="mb-1">
                <?php echo !empty($product['detail']) ? nl2br(htmlspecialchars($product['detail'])) : 'Product details will be displayed here.'; ?>
              </p>
            </div>

          </div>
        </div>
      </div><!--end row-->
    </div>
  </section>
  <!--start product details-->


  <!--start product details-->
  <section class="section-padding">
    <div class="container">
      <div class="separator pb-3">
        <div class="line"></div>
        <h3 class="mb-0 h3 fw-bold">Similar Products</h3>
        <div class="line"></div>
      </div>
      <div class="similar-products">
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 row-cols-xxl-5 g-4">
          <?php
            // Get similar products in the same category, excluding current product
            $category_id = $product['category_id'] ?? 0;
            $stmt_similar = $pdo->prepare('SELECT * FROM products WHERE category_id = ? AND id != ? LIMIT 10');
            $stmt_similar->execute([$category_id, $product_id]);
            $similar_products = $stmt_similar->fetchAll();
            foreach ($similar_products as $sp):
          ?>
          <div class="col">
            <a href="product-details.php?id=<?= $sp['id'] ?>">
              <div class="card rounded-0 product-hover-card">
                <img src="<?= isset($sp['thumbnail']) && $sp['thumbnail'] ? 'assets/images/product-images/' . htmlspecialchars($sp['thumbnail']) : 'assets/images/product-images/01.jpg' ?>" alt="" class="card-img-top rounded-0">
                <div class="card-body border-top">
                  <h5 class="mb-0 fw-bold product-short-title"><?= htmlspecialchars($sp['name']) ?></h5>
                  <p class="mb-0 product-short-name"><?= isset($sp['description']) ? htmlspecialchars($sp['description']) : '' ?></p>
                  <div class="product-price d-flex align-items-center gap-3 mt-2">
                    <div class="h6 fw-bold">$<?= number_format($sp['price']) ?></div>
                    <?php if (!empty($sp['old_price'])): ?>
                      <div class="h6 fw-light text-muted text-decoration-line-through">$<?= number_format($sp['old_price']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($sp['discount'])): ?>
                      <div class="h6 fw-bold text-danger">(<?= $sp['discount'] ?>% off)</div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </a>
          </div>
          <?php endforeach; ?>
        </div>
        <!--end row-->
      </div>
    </div>
  </section>
  <!--end product details-->


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
<?php include 'footer.php'; ?>
<!-- Add hidden form to submit when clicking ADD TO BAG -->
<form id="addToCartForm" method="post" style="display:none;">
  <input type="hidden" name="add_to_cart" value="1">
  <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
  <input type="hidden" name="quantity" value="1">
  <input type="hidden" name="size" value=""><!-- Will be updated by JS if a size is selected -->
</form>
<script>
// Handle ADD TO BAG button
$(document).ready(function() {
  // When selecting size, set active
  $('.size-chart button').on('click', function() {
    $('.size-chart button').removeClass('active');
    $(this).addClass('active');
    // Hide error message when a size is selected
    $('#sizeErrorMsg').addClass('d-none').text('');
  });

  // When clicking Add to Bag
  $('#addToBagBtn').on('click', function() {
    var size = $('.size-chart button.active').text() || '';
    if (!size) {
      $('#sizeErrorMsg').removeClass('d-none').text('Please select a size before adding to cart!');
      return;
    }
    // Check login status via PHP variable
    var isLoggedIn = <?php echo isset($_SESSION['user_id']) && $_SESSION['user_id'] ? 'true' : 'false'; ?>;
    if (!isLoggedIn) {
      $('#sizeErrorMsg').removeClass('d-none').text('You must be logged in to add items to your cart.');
      return;
    }
    $('#addToCartForm input[name="size"]').val(size);
    // If you want to select quantity dynamically, you can get it from another input
    $('#addToCartForm').submit();
  });
});
</script>