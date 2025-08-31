<?php
session_start();
require "db.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// DEBUG: Check what user ID we have
error_log("User ID: " . $user_id);

// SECURE QUERY: Get only orders for the logged-in user
$sql = "SELECT * FROM orders WHERE user_id = '$user_id' ORDER BY created_at DESC";
error_log("SQL Query: " . $sql);

$result = $conn->query($sql);

// DEBUG: Check if query was successful
if (!$result) {
    error_log("Query failed: " . $conn->error);
}

$orders = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
        error_log("Order found: ID " . $row['id'] . " for user " . $row['user_id']);
    }
} else {
    error_log("No orders found for user " . $user_id);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - AMF Collection</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --bg-grad-1: #0f0c29;
            --bg-grad-2: #302b63;
            --bg-grad-3: #24243e;
            --surface: #111322;
            --card: #171a2e;
            --muted: #9aa0b4;
            --text: #f5f7ff;
            --gold: #d4af37;
            --gold-soft: #ffd369;
            --glass: rgba(255,255,255,.06);
            --glass-stroke: rgba(255,255,255,.12);
            --radius: 18px;
            --shadow: rgba(0,0,0,.45);
        }

        body {
            background: linear-gradient(120deg, var(--bg-grad-1) 0%, var(--bg-grad-2) 55%, var(--bg-grad-3) 100%) fixed;
            color: var(--text);
            font-family: 'Poppins', sans-serif;
        }

        .navbar {
            backdrop-filter: saturate(140%) blur(10px);
            background: linear-gradient(180deg, rgba(12,12,22,.9), rgba(12,12,22,.65));
            border-bottom: 1px solid var(--glass-stroke);
        }

        .navbar-brand {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            letter-spacing: .5px;
            color: var(--gold) !important;
        }

        .nav-link, .text-white {
            color: #e9ecff !important;
            opacity: .85;
            transition: .25s ease;
        }

        .nav-link:hover, .text-white:hover {
            color: var(--gold-soft) !important;
            opacity: 1;
        }

        .btn-gold {
            background: linear-gradient(140deg, var(--gold) 0%, var(--gold-soft) 100%);
            color: #14151f;
            border: none;
            font-weight: 600;
            border-radius: 999px;
            padding: .6rem 1rem;
            box-shadow: 0 6px 20px rgba(212,175,55,.25);
            transition: transform .25s ease, box-shadow .25s ease, filter .25s ease;
        }

        .btn-gold:hover {
            transform: translateY(-2px);
            filter: brightness(1.05);
            box-shadow: 0 10px 26px rgba(212,175,55,.35);
        }

        .btn-outline-light {
            border-radius: 999px;
            border: 1px solid var(--glass-stroke);
            backdrop-filter: blur(6px);
        }

        .card {
            background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.03));
            border: 1px solid var(--glass-stroke);
            border-radius: var(--radius);
            box-shadow: 0 10px 30px var(--shadow);
        }

        .status-badge {
            padding: 0.35em 0.65em;
            border-radius: 999px;
            font-size: 0.75em;
            font-weight: 700;
        }

        .status-pending { background-color: #6c757d; color: white; }
        .status-confirmed { background-color: #17a2b8; color: white; }
        .status-shipped { background-color: #ffc107; color: black; }
        .status-completed { background-color: #28a745; color: white; }
        .status-cancelled { background-color: #dc3545; color: white; }

        .order-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }

        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
            border-color: rgba(212,175,55,.45);
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            color: #fff;
            letter-spacing: .2px;
        }

        .section-sub {
            color: var(--muted);
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--muted);
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg sticky-top py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="home.php">
                <i class="bi bi-gem"></i>
                <span>AMF Collection</span>
            </a>
            
            <div class="d-flex align-items-center gap-3 ms-auto">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="account.php" class="text-white text-decoration-none">My Account</a>
                    <a href="order_history.php" class="text-white text-decoration-none fw-bold">My Orders</a>
                    <a href="logout.php" class="text-white text-decoration-none">Logout</a>
                    <a class="btn btn-outline-light position-relative" href="checkout.php">
                        <i class="bi bi-bag"></i>
                        <span id="cartCount" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?php
                            $cart_count = 0;
                            if (isset($_SESSION['user_id'])) {
                                $user_id = $_SESSION['user_id'];
                                $result = $conn->query("SELECT SUM(quantity) as total FROM cart WHERE user_id = '$user_id'");
                                if ($result) {
                                    $row = $result->fetch_assoc();
                                    $cart_count = $row['total'] ? $row['total'] : 0;
                                }
                            } elseif (isset($_SESSION['cart'])) {
                                foreach ($_SESSION['cart'] as $item) {
                                    $cart_count += $item['quantity'];
                                }
                            }
                            echo $cart_count;
                            ?>
                        </span>
                    </a>
                <?php else: ?>
                    <a href="login.php" class="text-white text-decoration-none">Login</a>
                    <a href="signup.php" class="text-white text-decoration-none">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between mb-4">
            <div>
                <h2 class="section-title mb-1">My Orders</h2>
                <p class="section-sub">View your order history and track your purchases</p>
            </div>
            <a href="home.php" class="btn btn-gold mt-3 mt-md-0">
                <i class="bi bi-arrow-left me-1"></i>Continue Shopping
            </a>
        </div>

        <?php if (empty($orders)): ?>
            <div class="card empty-state">
                <i class="bi bi-bag-x"></i>
                <h3 class="section-title">No orders yet</h3>
                <p class="section-sub">You haven't placed any orders with us yet.</p>
                <a href="home.php" class="btn btn-gold mt-3">Start Shopping</a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($orders as $order): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card order-card h-100 p-3" onclick="window.location='order_details.php?id=<?php echo $order['id']; ?>'">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="mb-1">Order #<?php echo $order['id']; ?></h5>
                                    <small class="text-muted">Placed on <?php echo date('M j, Y', strtotime($order['created_at'])); ?></small>
                                </div>
                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </div>
                            
                            <div class="mb-3">
                                <p class="mb-1"><strong>Total:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
                                <p class="mb-0 text-truncate"><strong>Shipped to:</strong> <?php echo htmlspecialchars($order['shipping_name']); ?></p>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <a href="order_details.php?id=<?php echo $order['id']; ?>" class="text-gold" style="color: var(--gold-soft); text-decoration: none;">
                                    View Details <i class="bi bi-arrow-right"></i>
                                </a>
                                
                                <?php if (in_array($order['status'], ['pending', 'confirmed'])): ?>
                                    <a href="order_cancel.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-danger">Cancel</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>