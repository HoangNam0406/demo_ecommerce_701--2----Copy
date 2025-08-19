<?php include 'header.php'; ?>

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
          <li class="breadcrumb-item active" aria-current="page">Cart</li>
        </ol>
      </nav>
    </div>
  </div>
  <!--end breadcrumb-->


  <!--start product details-->
  <section class="section-padding">
    <div class="container">
      <div class="d-flex align-items-center px-3 py-2 border mb-4">
        <div class="text-start">
          <h4 class="mb-0 h4 fw-bold">My Bag (8 items)</h4>
        </div>
        <div class="ms-auto">
          <button type="button" class="btn btn-light btn-ecomm">Continue Shopping</button>
        </div>
      </div>
      <div class="row g-4">
        <div class="col-12 col-xl-8">

          <?php
          // Load cart items from database for the current user
          include_once 'config/app.php';
          include_once 'config/database.php';
          if (session_status() === PHP_SESSION_NONE) {
              session_start();
          }
          $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

          // Handle remove action
          if (isset($_GET['remove_cart_id'])) {
              $remove_cart_id = intval($_GET['remove_cart_id']);
              $stmt = $pdo->prepare("DELETE FROM carts WHERE id = ? AND user_id = ?");
              $stmt->execute([$remove_cart_id, $user_id]);
              // Redirect to avoid resubmission
              echo "<script>window.location='cart.php';</script>";
              exit;
          }

          $stmt = $pdo->prepare("SELECT c.id as cart_id, c.product_id, c.size, c.quantity, c.image, p.name, p.price FROM carts c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
          $stmt->execute([$user_id]);
          $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
      if (empty($cart_items)) {
        echo '<div class="alert alert-info">Your cart is currently empty. Start shopping and add your favorite items!';
        if (!isset($_SESSION['user_id']) || !$_SESSION['user_id']) {
          echo '<br><span class="text-danger">You must be logged in to add items to your cart.</span>';
        }
        echo '</div>';
      } else {
        foreach ($cart_items as $item) {
          ?>
          <div class="card rounded-0 mb-3">
            <div class="card-body">
              <div class="d-flex flex-column flex-lg-row gap-3">
                <div class="product-img">
                  <img src="<?php echo htmlspecialchars($item['image']); ?>" width="150" alt="">
                </div>
                <div class="product-info flex-grow-1">
                  <h5 class="fw-bold mb-0"><?php echo htmlspecialchars($item['name']); ?></h5>
                  <div class="product-price d-flex align-items-center gap-2 mt-3">
                    <div class="h6 fw-bold">$<?php echo number_format($item['price']); ?></div>
                    <!-- No old_price column, so nothing to show here -->
                  </div>
                  <div class="mt-3 hstack gap-2">
                    <button type="button" class="btn btn-sm btn-light border rounded-0" data-bs-toggle="modal" data-bs-target="#SizeModal" data-size="<?php echo htmlspecialchars($item['size']); ?>">Size : <?php echo htmlspecialchars($item['size']); ?></button>
                    <button type="button" class="btn btn-sm btn-light border rounded-0" data-bs-toggle="modal" data-bs-target="#QtyModal" data-qty="<?php echo htmlspecialchars($item['quantity']); ?>">Qty : <?php echo htmlspecialchars($item['quantity']); ?></button>
                  </div>
                </div>
                <div class="d-none d-lg-block vr"></div>
                <div class="d-grid gap-2 align-self-start align-self-lg-center">
                  <a href="cart.php?remove_cart_id=<?php echo $item['cart_id']; ?>" class="btn btn-ecomm btn-remove-product" onclick="return confirm('Do you really want to remove this item from your cart?')"><i class="bi bi-x-lg me-2"></i>Remove</a>
                </div>
              </div>
            </div>
          </div>
          <?php
              }
          }
          ?>

        </div>
        <div class="col-12 col-xl-4">
          <!--start size modal-->
          <div class="modal" id="SizeModal" tabindex="-1">
            <div class="modal-dialog modal-sm modal-dialog-centered">
              <div class="modal-content rounded-0">
                <div class="modal-body">
                  <div class="d-flex align-items-center justify-content-between">
                    <div class="">
                      <h5 class="fw-bold mb-4">Select Size</h5>
                    </div>
                    <div class="">
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                  </div>
                  <hr>
                  <div class="size-chart mt-4">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                      <button type="button">36</button>
                      <button type="button">37</button>
                      <button type="button">38</button>
                      <button type="button">39</button>
                      <button type="button">40</button>
                      <button type="button">41</button>
                      <button type="button">42</button>
                      <button type="button">43</button>
                      <button type="button">44</button>
                      <button type="button">45</button>
                      <button type="button">46</button>
                    </div>
                  </div>
                  <div class="d-grid mt-4">
                    <button type="button" class="btn btn-dark btn-ecomm">Done</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!--end size modal-->
          <div class="card rounded-0 mb-3">
            <div class="card-body">
              <h5 class="fw-bold mb-4">Order Summary</h5>
              <div class="hstack align-items-center justify-content-between fw-bold text-content">
                <p class="mb-0">Total Amount</p>
                <p class="mb-0">
                  <?php
                    $total = 0;
                    foreach ($cart_items as $item) {
                      $total += $item['price'] * $item['quantity'];
                    }
                    echo '$' . number_format($total, 0, '.', ',');
                  ?>
                </p>
              </div>
              <div class="d-grid mt-4">
                <a href="order.php" class="btn btn-dark btn-ecomm py-3 px-5">Place Order</a>
              </div>
            </div>
          </div>
          <!-- Apply Coupon section removed -->


        </div>
      </div><!--end row-->

    </div>
  </section>
  <!--start product details-->




</div>
<!--end page content-->

<?php include 'footer.php'; ?>
</div>
</div>
<div class="offcanvas-footer p-3 border-top">
      <div class="card-body">
        <ul class="list-group list-group-flush mb-3">
          <li class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 mb-3">
            <div>
              <strong>Total Amount</strong>
            </div>
            <span><strong>
              <?php
                $total = 0;
                foreach ($cart_items as $item) {
                  $total += $item['price'] * $item['quantity'];
                }
                echo number_format($total, 0, '.', '.') . '₫';
              ?>
            </strong></span>
          </li>
        </ul>
              <div class="h6 fw-bold">$458</div>
              <div class="h6 fw-light text-muted text-decoration-line-through">$2089</div>
              <div class="h6 fw-bold text-danger">(70% off)</div>
            </div>
          </div>
          <div class="">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
        </div>
        <hr>
        <div class="size-chart mt-4">
          <h5 class="fw-bold mb-4">Select Size</h5>
          <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="">
              <button type="button">36</button>
            </div>
            <div class="">
              <button type="button">37</button>
            </div>
            <div class="">
              <button type="button">38</button>
            </div>
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
              <button type="button">5XL</button>
            </div>
            <div class="">
              <button type="button">44</button>
            </div>
            <div class="">
              <button type="button">45</button>
            </div>
            <div class="">
              <button type="button">46</button>
            </div>
          </div>
        </div>

        <div class="d-grid mt-4">
          <button type="button" class="btn btn-dark btn-ecomm">Done</button>
        </div>

      </div>
    </div>
  </div>
</div>
<!--end size modal-->


<!--start qty modal-->
<div class="modal" id="QtyModal" tabindex="-1">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content rounded-0">
      <div class="modal-body">
        <div class="d-flex align-items-center justify-content-between">
          <div class="">
            <h5 class="fw-bold mb-0">Select Quantity</h5>
          </div>
          <div class="">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
        </div>
        <hr>
        <div class="size-chart">
          <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="">
              <button type="button">1</button>
            </div>
            <div class="">
              <button type="button">2</button>
            </div>
            <div class="">
              <button type="button">3</button>
            </div>
            <div class="">
              <button type="button">4</button>
            </div>
            <div class="">
              <button type="button">5</button>
            </div>
            <div class="">
              <button type="button">6</button>
            </div>
            <div class="">
              <button type="button">7</button>
            </div>
            <div class="">
              <button type="button">8</button>
            </div>
            <div class="">
              <button type="button">9</button>
            </div>
            <div class="">
              <button type="button">10</button>
            </div>
            <div class="">
              <button type="button">11</button>
            </div>
            <div class="">
              <button type="button">12</button>
            </div>
          </div>
        </div>

        <div class="d-grid mt-4">
          <button type="button" class="btn btn-dark btn-ecomm">Done</button>
        </div>

      </div>
    </div>
  </div>
</div>
<!--end qty modal-->



<!--Start Back To Top Button-->
<a href="javaScript:;" class="back-to-top"><i class="bi bi-arrow-up"></i></a>
<!--End Back To Top Button-->


<!-- JavaScript files -->
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/jquery.min.js"></script>
<script src="assets/plugins/slick/slick.min.js"></script>
<script src="assets/js/main.js"></script>
<script src="assets/js/loader.js"></script>

<script>
$(document).ready(function() {
  let currentSizeBtn = null;
  let currentQtyBtn = null;

  // When clicking the Size button on the product
  $('[data-bs-target="#SizeModal"]').on('click', function() {
    currentSizeBtn = $(this);
  });

  // When clicking the Qty button on the product
  $('[data-bs-target="#QtyModal"]').on('click', function() {
    currentQtyBtn = $(this);
  });

  // When selecting size in the modal
  $('#SizeModal .size-chart button').on('click', function() {
    $('#SizeModal .size-chart button').removeClass('active');
    $(this).addClass('active');
    $('#SizeModal').data('selected-size', $(this).text());
  });


  // When clicking Done in the size modal
  $('#SizeModal .btn-ecomm').on('click', function() {
    let size = $('#SizeModal').data('selected-size');
    if (size && currentSizeBtn) {
      currentSizeBtn.text('Size : ' + size);
    }
  // Close modal, return to cart interface
    $('#SizeModal').modal('hide');
    $('body').removeClass('modal-open');
    $('.modal-backdrop').remove();
  });

  // When selecting quantity in the modal
  $('#QtyModal .size-chart button').on('click', function() {
    $('#QtyModal .size-chart button').removeClass('active');
    $(this).addClass('active');
    $('#QtyModal').data('selected-qty', $(this).text());
  });

  // When clicking Done in the quantity modal
  $('#QtyModal .btn-ecomm').on('click', function() {
    let qty = $('#QtyModal').data('selected-qty');
    if (qty && currentQtyBtn) {
      currentQtyBtn.text('Qty : ' + qty);
    }
  // Close modal, return to cart interface
    $('#QtyModal').modal('hide');
    $('body').removeClass('modal-open');
    $('.modal-backdrop').remove();
  });

  // Update the number of products displayed in "My Bag"
  function updateCartCount() {
    const count = $('.col-12.col-xl-8 .card').length;
    $('.h4.fw-bold').text('My Bag (' + count + ' items)');
  }

  // Update the product count at the cart icon in the header
  function updateCartIconCount() {
    const count = $('.col-12.col-xl-8 .card').length;
  // Assume the number is displayed in an element with class .cart-count (adjust selector if different)
    $('.cart-count').text(count);
  }

  // Call when page loads
  updateCartCount();
  updateCartIconCount();

  // Call again when a product is removed
  $(document).on('click', '.btn-remove-product', function() {
    const card = $(this).closest('.card');
  if (confirm('Are you sure you want to remove this item from your cart?')) {
      card.remove();
      updateCartCount();
      updateCartIconCount();
    }
  });

  // Event to remove product from cart with confirmation
  $(document).on('click', '.btn-remove-product', function() {
    const card = $(this).closest('.card');
  if (confirm('Are you sure you want to remove this item from your cart?')) {
      card.remove();
    }
  });

  // Event for Continue Shopping button to return to Home page
  $('.btn.btn-light.btn-ecomm').on('click', function() {
    window.location.href = 'index.php';
  });
});
</script>


</body>

</html>