<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if order ID is provided
if (!isset($_GET['id'])) {
    header('Location: orders.php');
    exit();
}

$order_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Get order details
$sql = "SELECT o.*, u.username 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE o.id = ? AND o.user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

// If order not found or doesn't belong to user
if (!$order) {
    header('Location: orders.php');
    exit();
}

// Get order items
$sql = "SELECT oi.*, p.name, p.image_url 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$order_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details #<?php echo $order_id; ?></title>
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
                        <a href="orders.php" class="active">My Orders</a>
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
        <a href="orders.php" class="back-button">← Back to Orders</a>
        <h1>Order Details #<?php echo $order_id; ?></h1>

        <main>
            <div class="order-info">
                <div class="order-summary">
                    <h2>Order Summary</h2>
                    <p>Order Date: <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                    <p>Status: <span class="status-<?php echo strtolower($order['status']); ?>"><?php echo ucfirst($order['status']); ?></span></p>
                    <p>Total Amount: $<?php echo number_format($order['total_amount'], 2); ?></p>
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
        </main>
    </div>
</body>
</html> 