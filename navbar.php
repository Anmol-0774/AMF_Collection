<!-- navbar.php -->
 <?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<?php session_start(); ?>
<nav class="navbar navbar-expand-lg sticky-top py-3">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2" href="home.php">
      <i class="bi bi-gem"></i>
      <span>AMF Collection</span>
    </a>
    <button class="navbar-toggler text-white" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div id="nav" class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
        <li class="nav-item"><a class="nav-link" href="home.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="shop.php">Shop</a></li>
        <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
        <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
        <li class="nav-item"><a class="btn btn-gold" href="#cart"><i class="bi bi-bag me-1"></i> Cart</a></li>

        <!-- 👇 Login/Register OR Welcome/Logout in same row -->
        <?php if(isset($_SESSION['user_id'])): ?>
          <li class="nav-item"><a class="nav-link">Welcome, <?= $_SESSION['name'] ?></a></li>
          <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
          <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
