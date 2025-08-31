<?php
session_start();
require "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get order details
$order_sql = "SELECT o.*, u.name as user_name 
              FROM orders o 
              JOIN users u ON o.user_id = u.id 
              WHERE o.id = $order_id AND o.user_id = $user_id";
$order_result = $conn->query($order_sql);

if ($order_result->num_rows === 0) {
    header("Location: orders.php");
    exit;
}

$order = $order_result->fetch_assoc();

// Get order items
$items_sql = "SELECT oi.*, p.title, p.img, p.description 
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - AMF Collection</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
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
        }

        body {
            background: linear-gradient(120deg, var(--bg-grad-1) 0%, var(--bg-grad-2) 55%, var(--bg-grad-3) 100%) fixed;
            color: var(--text);
            font-family: 'Poppins', sans-serif;
        }

        .navbar {
            backdrop-filter: saturate(140%) blur(10px);
            background: linear-gradient(180deg, rgba(12,12,22,.9), rgba(12,12,22,.65));
            border-bottom: 1px solid rgba(255,255,255,.12);
        }

        .card {
            background: var(--card);
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 12px;
            color: var(--text);
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
        .status-cancellation-pending { background-color: #fd7e14; color: white; }

        .btn-gold {
            background: linear-gradient(140deg, var(--gold) 0%, var(--gold-soft) 100%);
            color: #101018;
            border: none;
            font-weight: 600;
            border-radius: 999px;
        }

        .btn-gold:hover {
            background: linear-gradient(140deg, var(--gold-soft) 0%, var(--gold) 100%);
            color: #101018;
        }

        .tracking-progress {
            height: 8px;
            border-radius: 4px;
            background: rgba(255,255,255,0.1);
        }

        .progress-step {
            width: 25%;
            height: 100%;
            border-radius: 4px;
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
                <a href="home.php" class="text-white text-decoration-none">Home</a>
                <a href="cart.php" class="text-white text-decoration-none">Cart</a>
                <a href="order_history.php" class="text-white text-decoration-none fw-bold">My Orders</a>
                <a href="logout.php" class="text-white text-decoration-none">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-receipt me-2"></i>Order Details #<?php echo $order['id']; ?></h2>
            <a href="order_history.php" class="btn btn-outline-light">Back to Orders</a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <!-- Order Status Tracking -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Order Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="tracking-progress mb-4">
                            <div class="progress-step bg-<?php 
                                if ($order['status'] == 'completed') echo 'success';
                                elseif ($order['status'] == 'shipped') echo 'warning';
                                elseif ($order['status'] == 'confirmed') echo 'info';
                                else echo 'secondary';
                            ?>" style="width: <?php 
                                if ($order['status'] == 'pending') echo '25%';
                                elseif ($order['status'] == 'confirmed') echo '50%';
                                elseif ($order['status'] == 'shipped') echo '75%';
                                else echo '100%';
                            ?>"></div>
                        </div>
                        
                        <div class="row text-center">
                            <div class="col-3">
                                <div class="<?php echo $order['status'] == 'pending' ? 'text-warning' : 'text-muted'; ?>">
                                    <i class="bi bi-clock-history fs-4"></i>
                                    <p class="mb-0 mt-1">Pending</p>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="<?php echo in_array($order['status'], ['confirmed', 'shipped', 'completed']) ? 'text-info' : 'text-muted'; ?>">
                                    <i class="bi bi-check-circle fs-4"></i>
                                    <p class="mb-0 mt-1">Confirmed</p>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="<?php echo in_array($order['status'], ['shipped', 'completed']) ? 'text-warning' : 'text-muted'; ?>">
                                    <i class="bi bi-truck fs-4"></i>
                                    <p class="mb-0 mt-1">Shipped</p>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="<?php echo $order['status'] == 'completed' ? 'text-success' : 'text-muted'; ?>">
                                    <i class="bi bi-box-seam fs-4"></i>
                                    <p class="mb-0 mt-1">Delivered</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Order Items</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($order_items)): ?>
                            <?php foreach ($order_items as $item): ?>
                                <div class="d-flex mb-3 pb-3 border-bottom">
                                    <img src="<?php echo $item['img']; ?>" alt="<?php echo $item['title']; ?>" 
                                         class="img-thumbnail me-3" style="width: 80px; height: 80px; object-fit: cover;">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?php echo $item['title']; ?></h6>
                                        <p class="text-muted small mb-1"><?php echo substr($item['description'], 0, 100); ?>...</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-muted">Qty: <?php echo $item['quantity']; ?></span>
                                            <span class="fw-bold">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-center text-muted">No items found in this order.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Order Summary -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>$<?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping:</span>
                            <span>Free</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax:</span>
                            <span>$0.00</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total:</strong>
                            <strong>$<?php echo number_format($order['total_amount'], 2); ?></strong>
                        </div>
                        
                        <?php 
                        $status_class = 'status-' . $order['status'];
                        $display_status = ucfirst($order['status']);
                        
                        if ($order['status'] == 'cancelled' && isset($order['cancellation_status']) && $order['cancellation_status'] == 'pending') {
                            $status_class = 'status-cancellation-pending';
                            $display_status = 'Cancellation Pending';
                        }
                        ?>
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span>Status:</span>
                            <span class="status-badge <?php echo $status_class; ?>"><?php echo $display_status; ?></span>
                        </div>
                        
                        <?php if ($order['status'] == 'cancelled' && !empty($order['cancellation_reason'])): ?>
                            <div class="alert alert-danger">
                                <strong>Cancellation Reason:</strong><br>
                                <?php echo htmlspecialchars($order['cancellation_reason']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Shipping Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Shipping Information</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><strong>Recipient:</strong> <?php echo $order['shipping_name']; ?></p>
                        <p class="mb-1"><strong>Phone:</strong> <?php echo $order['shipping_phone']; ?></p>
                        <p class="mb-0"><strong>Address:</strong> <?php echo $order['shipping_address']; ?></p>
                    </div>
                </div>

                <!-- Order Actions -->
                <?php if (in_array($order['status'], ['pending', 'confirmed'])): ?>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Order Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="order_cancel.php?id=<?php echo $order['id']; ?>" class="btn btn-danger">Cancel Order</a>
                                <a href="contact.php" class="btn btn-outline-light">Contact Support</a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>