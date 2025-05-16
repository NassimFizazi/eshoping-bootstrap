<?php
// Set page title and active page
$page_title = "Shopping Cart";
$active_page = "";

// Add cart.js to page scripts
$page_scripts = ['js/cart.js'];

// Include header
include_once 'includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    Session::setFlash('danger', 'Please login to view your cart.');
    redirect('login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

// Include database and cart classes
include_once 'backend/config/database.php';
include_once 'backend/class/CartItem.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize cart item object
$cart_item = new CartItem($db);
$cart_item->user_id = Session::getUserId();

// Get cart items
$stmt = $cart_item->getUserCart();
$cart_count = $stmt->rowCount();

// Get cart total
$cart_total = $cart_item->getCartTotal();
?>

<div class="container px-4 px-lg-5 mt-5">
    <h1 class="fw-bold mb-4">Shopping Cart</h1>
    
    <?php if ($cart_count > 0) : ?>
        <div class="row">
            <div class="col-lg-8">
                <!-- Cart Items -->
                <div class="card mb-4">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">Cart Items (<?php echo $cart_count; ?>)</h5>
                    </div>
                    <div class="card-body">
                        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) : ?>
                            <div class="cart-item row align-items-center">
                                <div class="col-md-2 col-3 mb-2 mb-md-0">
                                    <img src="<?php echo $row['product_image']; ?>" alt="<?php echo $row['product_name']; ?>" class="img-fluid rounded cart-item-image">
                                </div>
                                <div class="col-md-4 col-9 mb-2 mb-md-0">
                                    <h5 class="mb-1"><?php echo $row['product_name']; ?></h5>
                                    <div class="text-muted small">SKU: <?php echo 'PROD-' . str_pad($row['product_id'], 4, '0', STR_PAD_LEFT); ?></div>
                                    <div class="mt-2">
                                        <a href="#" onclick="removeFromCart(<?php echo $row['id']; ?>); return false;" class="text-danger small">
                                            <i class="fas fa-trash-alt"></i> Remove
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="input-group quantity-control cart-quantity">
                                        <button class="btn btn-outline-secondary decrement-btn" type="button">-</button>
                                        <input type="number" class="form-control text-center quantity-input" data-cart-item-id="<?php echo $row['id']; ?>" value="<?php echo $row['quantity']; ?>" min="1">
                                        <button class="btn btn-outline-secondary increment-btn" type="button">+</button>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6 text-end">
                                    <div class="fw-bold">$<?php echo number_format($row['product_price'], 2); ?></div>
                                    <div class="text-muted small">Subtotal: $<?php echo number_format($row['product_price'] * $row['quantity'], 2); ?></div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                        
                        <div class="mt-3 text-end">
                            <a href="products.php" class="btn btn-outline-primary">
                                <i class="fas fa-arrow-left me-1"></i> Continue Shopping
                            </a>
                            <button class="btn btn-outline-danger ms-2" onclick="clearCart()">
                                <i class="fas fa-trash-alt me-1"></i> Clear Cart
                            </button>
                            
                            <script>
                                function clearCart() {
                                    if (confirm('Are you sure you want to clear your cart?')) {
                                        fetch('backend/api/cart.php?action=clear', {
                                            method: 'DELETE'
                                        })
                                        .then(response => response.json())
                                        .then(data => {
                                            window.location.reload();
                                        });
                                    }
                                }
                            </script>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Cart Summary -->
                <div class="card mb-4 cart-summary">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>$<?php echo number_format($cart_total, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping:</span>
                            <span>Free</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax (10%):</span>
                            <span>$<?php echo number_format($cart_total * 0.1, 2); ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-bold">Total:</span>
                            <span class="fw-bold">$<?php echo number_format($cart_total * 1.1, 2); ?></span>
                        </div>
                        <a href="checkout.php" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-shopping-cart me-1"></i> Proceed to Checkout
                        </a>
                    </div>
                </div>
                
                <!-- Coupon Code -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Coupon Code</h5>
                    </div>
                    <div class="card-body">
                        <form>
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Enter coupon code">
                                <button class="btn btn-outline-primary" type="button">Apply</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php else : ?>
        <div class="text-center py-5">
            <i class="fas fa-shopping-cart fa-4x mb-3 text-muted"></i>
            <h2>Your Cart is Empty</h2>
            <p class="text-muted">You haven't added any products to your cart yet.</p>
            <a href="products.php" class="btn btn-primary btn-lg mt-3">Start Shopping</a>
        </div>
    <?php endif; ?>
</div>

<?php
// Include footer
include_once 'includes/footer.php';
?>
