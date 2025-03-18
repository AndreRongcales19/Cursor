<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: orders.php');
    exit();
}

$order_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Get order details
$stmt = $pdo->prepare("
    SELECT o.*, u.username 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: orders.php');
    exit();
}

// Get order items
$stmt = $pdo->prepare("
    SELECT oi.*, p.name, p.image_url 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Online Market</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="main-nav">
        <div class="container">
            <div class="nav-content">
                <a href="index.php" class="logo">Online Market</a>
                <div class="nav-links">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="cart.php">Cart</a>
                        <a href="orders.php">My Orders</a>
                        <a href="logout.php">Logout</a>
                    <?php else: ?>
                        <a href="login.php">Login</a>
                        <a href="register.php">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="confirmation-container">
            <div class="confirmation-header">
                <img src="check-circle.svg" alt="Success" class="success-icon">
                <h1>Order Confirmed!</h1>
                <p>Thank you for your order. Your order number is #<?php echo $order_id; ?></p>
            </div>

            <div class="order-info">
                <div class="order-summary">
                    <h2>Order Summary</h2>
                    <p>Order Date: <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                    <p>Status: <span class="status-<?php echo strtolower($order['status']); ?>"><?php echo ucfirst($order['status']); ?></span></p>
                    <p>Total Amount: $<?php echo number_format($order['total_amount'], 2); ?></p>
                </div>

                <div class="shipping-info">
                    <h2>Shipping Information</h2>
                    <p><?php echo htmlspecialchars($order['shipping_address']); ?></p>
                    <p><?php echo htmlspecialchars($order['city']); ?>, <?php echo htmlspecialchars($order['postal_code']); ?></p>
                    <p>Phone: <?php echo htmlspecialchars($order['phone']); ?></p>
                </div>

                <div class="order-items">
                    <h2>Order Items</h2>
                    <?php foreach ($items as $item): ?>
                        <div class="order-item">
                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <div class="item-details">
                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                <p>Quantity: <?php echo $item['quantity']; ?></p>
                                <p>Price: $<?php echo number_format($item['price'], 2); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="confirmation-actions">
                <a href="orders.php" class="btn">View All Orders</a>
                <a href="index.php" class="btn btn-secondary">Continue Shopping</a>
            </div>
        </div>
    </div>
</body>
</html> 