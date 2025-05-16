<?php
// Set page title and active page
$page_title = "Checkout";
$active_page = "";

// Add cart.js to page scripts
$page_scripts = ['js/cart.js'];

// Include header
include_once 'includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    Session::setFlash('danger', 'Please login to proceed to checkout.');
    redirect('login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

// Include database and cart/user classes
include_once 'backend/config/database.php';
include_once 'backend/class/CartItem.php';
include_once 'backend/class/User.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize cart item object
$cart_item = new CartItem($db);
$cart_item->user_id = Session::getUserId();

// Check if cart has items
$stmt = $cart_item->getUserCart();
$cart_count = $stmt->rowCount();

if ($cart_count <= 0) {
    Session::setFlash('warning', 'Your cart is empty. Add products to your cart before proceeding to checkout.');
    redirect('cart.php');
}

// Get cart total
$cart_total = $cart_item->getCartTotal();

// Get user details
$user = new User($db);
$user->id = Session::getUserId();
$user->readOne();
?>

<div class="container px-4 px-lg-5 mt-5">
    <h1 class="fw-bold mb-4">Checkout</h1>
    
    <div id="checkout-errors" class="d-none"></div>
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Checkout Form -->
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Shipping Information</h5>
                </div>
                <div class="card-body">
                    <form id="checkout-form">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="shipping-address" class="form-label">Address <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="shipping-address" value="<?php echo $user->address; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="shipping-city" class="form-label">City <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="shipping-city" value="<?php echo $user->city; ?>" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="shipping-zip-code" class="form-label">ZIP Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="shipping-zip-code" value="<?php echo $user->zip_code; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="shipping-phone" class="form-label">Phone <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="shipping-phone" value="<?php echo $user->phone; ?>" required>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h5 class="mb-3">Payment Method</h5>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="payment_method" id="payment-cash" value="cash_on_delivery" checked>
                            <label class="form-check-label" for="payment-cash">
                                Cash on Delivery
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="payment_method" id="payment-card" value="credit_card">
                            <label class="form-check-label" for="payment-card">
                                Credit/Debit Card
                            </label>
                        </div>
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="radio" name="payment_method" id="payment-paypal" value="paypal">
                            <label class="form-check-label" for="payment-paypal">
                                PayPal
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-shopping-cart me-1"></i> Place Order
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Order Summary -->
            <div class="card mb-4 cart-summary sticky-top" style="top: 80px;">
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
                    
                    <!-- Order Items -->
                    <div class="mt-3">
                        <h6 class="mb-3">Order Items:</h6>
                        <?php 
                        $stmt->execute(); // Reset statement to get cart items again
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) : 
                        ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span><?php echo $row['product_name']; ?> (×<?php echo $row['quantity']; ?>)</span>
                                <span>$<?php echo number_format($row['product_price'] * $row['quantity'], 2); ?></span>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include_once 'includes/footer.php';
?>
