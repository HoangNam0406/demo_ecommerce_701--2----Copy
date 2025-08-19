
<?php
include 'header.php';
require_once 'config/database.php';

$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validate
  if ($name === '' || $email === '' || $phone === '' || $password === '') {
    $error = 'Please enter all required information.';
    } else {
        // Check if email or phone already exists
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? OR phone = ?');
        $stmt->execute([$email, $phone]);
        if ($stmt->fetch()) {
            $error = 'Email or phone number has already been registered.';
        } else {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)');
            if ($stmt->execute([$name, $email, $phone, $hashedPassword])) {
                $success = 'Registration successful! You can now log in.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
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
          <li class="breadcrumb-item"><a href="javascript:;">Authentication</a></li>
          <li class="breadcrumb-item active" aria-current="page">Register</li>
        </ol>
      </nav>
    </div>
  </div>
  <!--end breadcrumb-->


  <!--start product details-->
  <section class="section-padding">
    <div class="container">

      <div class="row">
        <div class="col-12 col-lg-6 col-xl-5 col-xxl-5 mx-auto">
          <div class="card rounded-0">
            <div class="card-body p-4">
              <h4 class="mb-0 fw-bold text-center">Registration</h4>
              <hr>

              <?php if ($success): ?>
                <div class="alert alert-success"> <?= $success ?> </div>
              <?php elseif ($error): ?>
                <div class="alert alert-danger"> <?= $error ?> </div>
              <?php endif; ?>
              <form method="post" autocomplete="off">
                <div class="row g-4">
                  <div class="col-12">
                    <label for="exampleName" class="form-label">Name</label>
                    <input type="text" name="name" class="form-control rounded-0" id="exampleName" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                  </div>
                  <div class="col-12">
                    <label for="exampleMobile" class="form-label">Mobile</label>
                    <input type="text" name="phone" class="form-control rounded-0" id="exampleMobile" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                  </div>
                  <div class="col-12">
                    <label for="exampleEmail" class="form-label">Email ID</label>
                    <input type="email" name="email" class="form-control rounded-0" id="exampleEmail" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                  </div>
                  <div class="col-12">
                    <label for="examplePassword" class="form-label">Password</label>
                    <input type="password" name="password" class="form-control rounded-0" id="examplePassword">
                  </div>
                  <div class="col-12">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" value="1" id="flexCheckDefault" required>
                      <label class="form-check-label" for="flexCheckDefault">
                        I agree to Terms and Conditions
                      </label>
                    </div>
                  </div>
                  <div class="col-12">
                    <hr class="my-0">
                  </div>
                  <div class="col-12">
                    <button type="submit" class="btn btn-dark rounded-0 btn-ecomm w-100">Sign Up</button>
                  </div>
                  <div class="col-12 text-center">
                    <p class="mb-0 rounded-0 w-100">Already have an account? <a href="authentication-login.php" class="text-danger">Sign In</a></p>
                  </div>
                </div><!---end row-->
              </form>
            </div>
          </div>
        </div>
      </div><!--end row-->

    </div>
  </section>
  <!--start product details-->


</div>
<!--end page content-->

<?php include 'footer.php'; ?>