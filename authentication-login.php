
<?php
session_start();
include 'header.php';
require_once 'config/database.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

  if ($email === '' || $password === '') {
    $error = 'Please enter all required information.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['login_success'] = 'Login successful!';
            if ($user['role'] === 'admin') {
                header('Location: admin');
            } else {
                header('Location: index.php');
            }
            exit;
        } else {
            $error = 'Incorrect email or password.';
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
          <li class="breadcrumb-item active" aria-current="page">Login</li>
        </ol>
      </nav>
    </div>
  </div>
  <!--end breadcrumb-->


  <!--start product details-->
  <section class="section-padding">
    <div class="container">

      <div class="row">
        <div class="col-12 col-lg-6 col-xl-5 col-xxl-4 mx-auto">
          <div class="card rounded-0">
            <div class="card-body p-4">
              <h4 class="mb-0 fw-bold text-center">User Login</h4>
              <hr>
              <?php if ($error): ?>
                <div class="alert alert-danger"> <?= $error ?> </div>
              <?php endif; ?>
              <form method="post" autocomplete="off">
                <div class="row g-4">
                  <div class="col-12">
                    <label for="exampleEmail" class="form-label">Email</label>
                    <input type="email" name="email" class="form-control rounded-0" id="exampleEmail" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                  </div>
                  <div class="col-12">
                    <label for="examplePassword" class="form-label">Password</label>
                    <input type="password" name="password" class="form-control rounded-0" id="examplePassword">
                  </div>
                  <div class="col-12">
                    <a href="javascript:;" class="text-content btn bg-light rounded-0 w-100"><i
                        class="bi bi-lock me-2"></i>Forgot Password</a>
                  </div>
                  <div class="col-12">
                    <hr class="my-0">
                  </div>
                  <div class="col-12">
                    <button type="submit" class="btn btn-dark rounded-0 btn-ecomm w-100">Login</button>
                  </div>
                  <div class="col-12 text-center">
                    <p class="mb-0 rounded-0 w-100">Don't have an account? <a href="authentication-register.php"
                        class="text-danger">Sign Up</a></p>
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