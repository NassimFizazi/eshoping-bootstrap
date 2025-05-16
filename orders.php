<?php
// Set page title and active page
$page_title = isset($_GET['id']) ? "Order Details" : "My Orders";
$active_page = "";

// Include header
include_once 'includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    Session::setFlash('danger', 'Please login to view your orders.');
    redirect('login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

// Include database and order classes
include_once 'backend/config/database.php';
include_once 'backend/class/Order.php';
include_once 'backend/class/OrderItem.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Check for success message
if (isset($_GET['success']) && $_GET['success'] == 1) {
    Session::setFlash('success', 'Your order has been placed successfully!');
}

// Initialize order
$order = new Order($db);
$order->user_id = Session::getUserId();

// Check if viewing a specific order
if (isset($_GET['id'])) {
    $order->id = $_GET['id'];
    
    // Read the order details
    if (!$order->readOne()) {
        Session::setFlash('danger', 'Order not found.');
        redirect('orders.php');
    }
    
    // Get order items
    $order_item = new OrderItem($db);
    $order_item->order_id = $order->id;
    $stmt_items = $order_item->readOrderItems();
?>

<div class="container px-4 px-lg-5 mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold">Order Details</h1>
        <a href="orders.php" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-1"></i> Back to Orders
        </a>
    </div>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Order #<?php echo str_pad($order->id, 6, '0', STR_PAD_LEFT); ?></h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <div>
                            <strong>Order Date:</strong>
                            <div><?php echo date('F j, Y g:i A', strtotime($order->created_at)); ?></div>
                        </div>
                        <div>
                            <strong>Status:</strong>
                            <div>
                                <span class="order-status status-<?php echo $order->status; ?>">
                                    <?php echo ucfirst($order->status); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Shipping Address:</strong>
                            <address>
                                <?php echo $order->shipping_address; ?><br>
                                <?php echo $order->shipping_city; ?>, <?php echo $order->shipping_zip_code; ?><br>
                                Phone: <?php echo $order->shipping_phone; ?>
                            </address>
                        </div>
                        <div class="col-md-6">
                            <strong>Payment Method:</strong>
                            <div>
                                <?php 
                                    switch($order->payment_method) {
                                        case 'cash_on_delivery': echo 'Cash on Delivery'; break;
                                        case 'credit_card': echo 'Credit/Debit Card'; break;
                                        case 'paypal': echo 'PayPal'; break;
                                        default: echo $order->payment_method;
                                    }
                                ?>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h5 class="mb-3">Order Items</h5>
                    <?php while ($item = $stmt_items->fetch(PDO::FETCH_ASSOC)) : ?>
                        <div class="row align-items-center mb-3">
                            <div class="col-md-2 col-3">
                                <img src="<?php echo $item['product_image']; ?>" alt="<?php echo $item['product_name']; ?>" class="img-fluid rounded">
                            </div>
                            <div class="col-md-6 col-9">
                                <h6 class="mb-0"><?php echo $item['product_name']; ?></h6>
                                <div class="text-muted small">Quantity: <?php echo $item['quantity']; ?></div>
                            </div>
                            <div class="col-md-4 col-12 text-md-end mt-2 mt-md-0">
                                <div>Price: $<?php echo number_format($item['price'], 2); ?></div>
                                <div class="fw-bold">Subtotal: $<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card mb-4 cart-summary">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span>$<?php echo number_format($order->total_amount / 1.1, 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping:</span>
                        <span>Free</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tax (10%):</span>
                        <span>$<?php echo number_format($order->total_amount - ($order->total_amount / 1.1), 2); ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fw-bold">Total:</span>
                        <span class="fw-bold">$<?php echo number_format($order->total_amount, 2); ?></span>
                    </div>
                    
                    <?php if ($order->status == 'pending' || $order->status == 'processing'): ?>
                        <a href="#" class="btn btn-outline-primary w-100 mb-2">
                            <i class="fas fa-truck me-1"></i> Track Order
                        </a>
                    <?php endif; ?>
                    
                    <a href="#" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-print me-1"></i> Print Receipt
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
} else {
    // Get all orders
    $stmt = $order->readUserOrders();
    $order_count = $stmt->rowCount();
?>

<div class="container px-4 px-lg-5 mt-5">
    <h1 class="fw-bold mb-4">My Orders</h1>
    
    <?php if ($order_count > 0) : ?>
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">Order History</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Date</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) : ?>
                                        <tr>
                                            <td><?php echo str_pad($row['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                            <td><?php echo date('M j, Y', strtotime($row['created_at'])); ?></td>
                                            <td>$<?php echo number_format($row['total_amount'], 2); ?></td>
                                            <td>
                                                <span class="order-status status-<?php echo $row['status']; ?>">
                                                    <?php echo ucfirst($row['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="orders.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else : ?>
        <div class="text-center py-5">
            <i class="fas fa-shopping-bag fa-4x mb-3 text-muted"></i>
            <h2>No Orders Yet</h2>
            <p class="text-muted">You haven't placed any orders yet.</p>
            <a href="products.php" class="btn btn-primary btn-lg mt-3">Start Shopping</a>
        </div>
    <?php endif; ?>
</div>

<?php
}
// Include footer
include_once 'includes/footer.php';
?>
