<?php
session_start();
require_once 'config.php';

// Fetch all products
$stmt = $pdo->query("SELECT * FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle add to cart
if (isset($_POST['add_to_cart']) && isset($_SESSION['user_id'])) {
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
    
    if ($product_id && $quantity) {
        // Check if product already in cart
        $stmt = $pdo->prepare("SELECT quantity FROM shopping_cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$_SESSION['user_id'], $product_id]);
        $existing_item = $stmt->fetch();
        
        if ($existing_item) {
            // Update quantity
            $stmt = $pdo->prepare("UPDATE shopping_cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$quantity, $_SESSION['user_id'], $product_id]);
        } else {
            // Add new item
            $stmt = $pdo->prepare("INSERT INTO shopping_cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $product_id, $quantity]);
        }
        $success_message = "Product added to cart!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Market</title>
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
        <?php if (isset($success_message)): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <h1>Our Products</h1>
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                    <p class="price">$<?php echo number_format($product['price'], 2); ?></p>
                    <p class="stock">In Stock: <?php echo $product['stock']; ?></p>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <form method="POST" class="add-to-cart-form">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <div class="form-group">
                                <label for="quantity_<?php echo $product['id']; ?>">Quantity:</label>
                                <input type="number" id="quantity_<?php echo $product['id']; ?>" name="quantity" min="1" max="<?php echo $product['stock']; ?>" value="1" required>
                            </div>
                            <button type="submit" name="add_to_cart" class="add-to-cart">Add to Cart</button>
                        </form>
                    <?php else: ?>
                        <a href="login.php" class="login-to-buy">Login to Purchase</a>
                    <?php endif; ?>
                    <a href="product.php?id=<?php echo $product['id']; ?>" class="view-details">View Details</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html> 