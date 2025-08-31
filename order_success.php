<?php
session_start();
require "db.php";

// Check if order ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: home.php");
    exit();
}

$order_id = intval($_GET['id']);

// Verify this order belongs to the current user
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT * FROM orders WHERE id = $order_id AND user_id = $user_id";
    $result = $conn->query($sql);
    
    if ($result->num_rows === 0) {
        // Order doesn't belong to user or doesn't exist
        header("Location: home.php");
        exit();
    }
    
    $order = $result->fetch_assoc();
} else {
    header("Location: login.php");
    exit();
}

// Get order items
$items_sql = "SELECT oi.*, p.title, p.img 
              FROM order_items oi 
              JOIN products p ON oi.product_id = p.id 
              WHERE oi.order_id = $order_id";
$items_result = $conn->query($items_sql);
$order_items = [];
if ($items_result->num_rows > 0) {
    while ($row = $items_result->fetch_assoc()) {
        $order_items[] = $row;
    }
}

// Check if we have shipping info, if not use session data
if (!isset($order['shipping_name']) && isset($_SESSION['shipping_info'])) {
    $shipping_info = $_SESSION['shipping_info'];
} else {
    $shipping_info = [
        'name' => $order['shipping_name'] ?? 'Not provided',
        'address' => $order['shipping_address'] ?? 'Not provided',
        'city' => $order['shipping_city'] ?? 'Not provided',
        'phone' => $order['shipping_phone'] ?? 'Not provided'
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Order Confirmation - AMF Collection</title>
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
  <!-- Animate.css -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">

  <style>
    :root{
      /* Luxury Indigo x Soft Gold */
      --bg-grad-1:#0f0c29;
      --bg-grad-2:#302b63;
      --bg-grad-3:#24243e;
      --surface:#111322; /* deep surface */
      --card:#171a2e; /* card base */
      --muted:#9aa0b4; /* muted text */
      --text:#f5f7ff; /* main text */
      --gold:#d4af37; /* metallic gold */
      --gold-soft:#ffd369; /* soft gold for UI */
      --accent:#7aa2ff; /* cool accent */
      --shadow: rgba(0,0,0,.45);
      --glass: rgba(255,255,255,.06);
      --glass-stroke: rgba(255,255,255,.12);
      --radius: 18px;
    }

    html,body{
      background: linear-gradient(120deg,var(--bg-grad-1) 0%, var(--bg-grad-2) 55%, var(--bg-grad-3) 100%) fixed;
      color: var(--text);
      font-family: 'Poppins', system-ui, -apple-system, Segoe UI, Roboto, Arial;
    }

    .navbar{
      backdrop-filter: saturate(140%) blur(10px);
      background: linear-gradient(180deg, rgba(12,12,22,.9), rgba(12,12,22,.65));
      border-bottom: 1px solid var(--glass-stroke);
    }
    .navbar-brand{
      font-family: 'Playfair Display', serif;
      font-weight: 700;
      letter-spacing:.5px;
      color: var(--gold) !important;
    }

    .success-container {
      backdrop-filter: saturate(140%) blur(10px);
      background: linear-gradient(180deg, rgba(12,12,22,.9), rgba(12,12,22,.65));
      border: 1px solid var(--glass-stroke);
      border-radius: var(--radius);
      box-shadow: 0 10px 30px var(--shadow);
      padding: 2rem;
      margin: 2rem auto;
      max-width: 800px;
    }

    .order-card {
      background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.03));
      border: 1px solid var(--glass-stroke);
      border-radius: var(--radius);
      padding: 1.5rem;
      margin-bottom: 1.5rem;
    }

    .btn-gold{
      background: linear-gradient(140deg, var(--gold) 0%, var(--gold-soft) 100%);
      color:#14151f; border:none; font-weight:600; border-radius: 999px; padding:.6rem 1rem;
      box-shadow: 0 6px 20px rgba(212,175,55,.25);
      transition: transform .25s ease, box-shadow .25s ease, filter .25s ease;
    }
    .btn-gold:hover{ transform: translateY(-2px); filter: brightness(1.05); box-shadow:0 10px 26px rgba(212,175,55,.35); }

    .checkmark {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      display: block;
      stroke-width: 5;
      stroke: #fff;
      stroke-miterlimit: 10;
      box-shadow: 0 0 30px var(--gold-soft);
      animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both;
      background: var(--gold);
      margin: 0 auto;
    }

    .checkmark-circle {
      stroke-dasharray: 166;
      stroke-dashoffset: 166;
      stroke-width: 5;
      stroke-miterlimit: 10;
      stroke: #fff;
      fill: none;
      animation: stroke .6s cubic-bezier(0.650, 0.000, 0.450, 1.000) forwards;
    }

    .checkmark-check {
      transform-origin: 50% 50%;
      stroke-dasharray: 48;
      stroke-dashoffset: 48;
      animation: stroke .3s cubic-bezier(0.650, 0.000, 0.450, 1.000) .8s forwards;
    }

    @keyframes stroke {
      100% { stroke-dashoffset: 0; }
    }

    @keyframes scale {
      0%, 100% { transform: none; }
      50% { transform: scale3d(1.1, 1.1, 1); }
    }

    @keyframes fill {
      100% { box-shadow: 0 0 40px var(--gold-soft); }
    }
  </style>
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg sticky-top py-3">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center gap-2" href="home.php">
        <i class="bi bi-gem"></i>
        <span>AMF Collection</span>
      </a>
      
      <div class="d-flex align-items-center gap-3 ms-auto">
        <?php if(isset($_SESSION['user_id'])): ?>
          <a href="#" class="text-white text-decoration-none"><?php echo $_SESSION['user_name']; ?></a>
          <a href="logout.php" class="text-white text-decoration-none">Logout</a>
        <?php else: ?>
          <a href="login.php" class="text-white text-decoration-none">Login</a>
          <a href="signup.php" class="text-white text-decoration-none">Sign Up</a>
        <?php endif; ?>
        
        <a class="btn btn-outline-light position-relative" href="checkout.php">
          <i class="bi bi-bag"></i>
          <span id="cartCount" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
            <?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>
          </span>
        </a>
      </div>
    </div>
  </nav>

  <div class="container">
    <div class="success-container animate__animated animate__fadeIn">
      <div class="text-center mb-4">
        <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
          <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
          <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
        </svg>
        <h2 class="mt-4">Order Confirmed!</h2>
        <p class="text-muted">Thank you for your purchase. Your order details are below.</p>
      </div>

      <div class="order-card">
        <div class="row">
          <div class="col-md-6">
            <h5>Order Information</h5>
            <p><strong>Order ID:</strong> #<?php echo $order['id']; ?></p>
            <p><strong>Order Date:</strong> <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
            <p><strong>Total Amount:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
            <p><strong>Status:</strong> <span class="badge bg-success">Confirmed</span></p>
          </div>
          <div class="col-md-6">
            <h5>Shipping Information</h5>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($shipping_info['name']); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($shipping_info['address']); ?></p>
            <p><strong>City:</strong> <?php echo htmlspecialchars($shipping_info['city']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($shipping_info['phone']); ?></p>
          </div>
        </div>
      </div>

      <h5 class="mb-3">Order Items</h5>
      <?php if (!empty($order_items)): ?>
        <?php foreach ($order_items as $item): ?>
          <div class="order-card">
            <div class="row align-items-center">
              <div class="col-2">
                <img src="<?php echo $item['img']; ?>" alt="<?php echo $item['title']; ?>" class="img-fluid rounded" style="max-height: 60px;">
              </div>
              <div class="col-5">
                <h6 class="mb-0"><?php echo $item['title']; ?></h6>
              </div>
              <div class="col-2">
                <span class="text-muted">Qty: <?php echo $item['quantity']; ?></span>
              </div>
              <div class="col-3 text-end">
                <span class="price">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="alert alert-warning">No order items found.</div>
      <?php endif; ?>

      <div class="text-center mt-4">
        <a href="home.php" class="btn btn-gold me-2">Continue Shopping</a>
        <a href="#" class="btn btn-outline-light">Track Order</a>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>