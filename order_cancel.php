<?php
session_start();
require "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get order details - ONLY FOR LOGGED-IN USER
$order_sql = "SELECT o.*, u.name as user_name 
              FROM orders o 
              JOIN users u ON o.user_id = u.id 
              WHERE o.id = $order_id AND o.user_id = $user_id";
$order_result = $conn->query($order_sql);

if ($order_result->num_rows === 0) {
    // Order doesn't exist or doesn't belong to this user
    header("Location: orders.php");
    exit;
}

$order = $order_result->fetch_assoc();

// Check if order can be cancelled (only pending or confirmed orders)
if (!in_array($order['status'], ['pending', 'confirmed'])) {
    $_SESSION['error'] = "This order cannot be cancelled because it has already been " . $order['status'];
    header("Location: order_details.php?id=$order_id");
    exit;
}

// Handle cancellation request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);
    $details = isset($_POST['details']) ? mysqli_real_escape_string($conn, $_POST['details']) : '';
    
    // Update order status
    $update_sql = "UPDATE orders SET 
                  status = 'cancelled',
                  cancellation_reason = '$reason',
                  cancellation_details = '$details',
                  cancelled_at = NOW(),
                  cancellation_status = 'pending'
                  WHERE id = $order_id AND user_id = $user_id";
    
    if ($conn->query($update_sql)) {
        // Restore product stock
        $items_sql = "SELECT product_id, quantity FROM order_items WHERE order_id = $order_id";
        $items_result = $conn->query($items_sql);
        
        while ($item = $items_result->fetch_assoc()) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];
            $conn->query("UPDATE products SET stock = stock + $quantity WHERE id = $product_id");
        }
        
        $_SESSION['success'] = "Cancellation request submitted successfully. It will be reviewed by our team.";
        header("Location: order_details.php?id=$order_id");
        exit;
    } else {
        $_SESSION['error'] = "Error processing cancellation: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancel Order - AMF Collection</title>
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

        .form-control {
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.15);
            color: #fff;
            border-radius: var(--radius);
        }

        .form-control:focus {
            background: rgba(255,255,255,.12);
            border-color: var(--gold-soft);
            box-shadow: 0 0 0 0.25rem rgba(212, 175, 55, 0.25);
            color: #fff;
        }

        .alert {
            border-radius: var(--radius);
            border: 1px solid var(--glass-stroke);
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
                    <a href="order_history.php" class="text-white text-decoration-none">My Orders</a>
                    <a href="logout.php" class="text-white text-decoration-none">Logout</a>
                    <a class="btn btn-outline-light position-relative" href="checkout.php">
                        <i class="bi bi-bag"></i>
                        <span id="cartCount" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?php
                            $cart_count = 0;
                            if (isset($_SESSION['user_id'])) {
                                $user_id = $_SESSION['user_id'];
                                $result = $conn->query("SELECT SUM(quantity) as total FROM cart WHERE user_id = $user_id");
                                $row = $result->fetch_assoc();
                                $cart_count = $row['total'] ? $row['total'] : 0;
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
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card p-4">
                    <div class="text-center mb-4">
                        <i class="bi bi-x-circle-fill text-danger" style="font-size: 3rem;"></i>
                        <h2 class="section-title mt-3">Cancel Order #<?php echo $order['id']; ?></h2>
                        <p class="section-sub">Please tell us why you want to cancel this order</p>
                    </div>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                    <?php endif; ?>

                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Please note:</strong> Cancellation requests are subject to approval. If your order has already been shipped, 
                        you may need to refuse delivery or return the items instead.
                    </div>

                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Order Details</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Order ID:</strong> #<?php echo $order['id']; ?></p>
                                    <p><strong>Order Date:</strong> <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                                    <p><strong>Total Amount:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Status:</strong> <?php echo ucfirst($order['status']); ?></p>
                                    <p><strong>Shipping to:</strong> <?php echo htmlspecialchars($order['shipping_name']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form method="POST">
                        <div class="mb-4">
                            <label for="reason" class="form-label">Reason for Cancellation *</label>
                            <select class="form-select" id="reason" name="reason" required>
                                <option value="">Select a reason</option>
                                <option value="Changed mind">Changed my mind</option>
                                <option value="Found better price">Found a better price elsewhere</option>
                                <option value="Ordered by mistake">Ordered by mistake</option>
                                <option value="Delivery time too long">Delivery time is too long</option>
                                <option value="Product not needed anymore">Product no longer needed</option>
                                <option value="Other">Other reason</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="details" class="form-label">Additional Details (Optional)</label>
                            <textarea class="form-control" id="details" name="details" rows="4" placeholder="Please provide any additional information about your cancellation"></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-danger btn-lg">Submit Cancellation Request</button>
                            <a href="order_details.php?id=<?php echo $order_id; ?>" class="btn btn-outline-light">Go Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simple form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const reason = document.getElementById('reason').value;
            if (!reason) {
                e.preventDefault();
                alert('Please select a reason for cancellation');
                document.getElementById('reason').focus();
            }
        });
    </script>
</body>
</html>