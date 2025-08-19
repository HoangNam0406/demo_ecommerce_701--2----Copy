
<?php 
include 'header.php'; 
require_once __DIR__ . '/config/database.php';

// Display all products in Mizuno brand (category_id = 3)
$products = [];
$stmt = $pdo->prepare('SELECT * FROM products WHERE category_id = 3 ORDER BY created_at DESC');
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
          <li class="breadcrumb-item active" aria-current="page">Mizuno</li>
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
          <div class="col-12"><div class="alert alert-danger">No Mizuno products found.</div></div>
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
// Handle Add to Cart button
$(document).ready(function() {
  // When Add to Cart is clicked
  $('.btn-add-to-cart').on('click', function() {
    var productId = $(this).data('product-id');
    $('#addToCartForm input[name="product_id"]').val(productId);
    // If you choose a size, please add it. Leave it blank by default.
    $('#addToCartForm input[name="size"]').val('');
    $('#addToCartForm').attr('action', 'product-details.php?id=' + productId); // submit về product-details.php để xử lý logic thêm vào giỏ
    $('#addToCartForm').submit();
  });
});
</script>
<?php include 'footer.php'; ?>