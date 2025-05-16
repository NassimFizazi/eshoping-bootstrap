<?php
// Set page title and active page
$page_title = isset($_GET['id']) ? "Order Details" : "Manage Orders";
$active_page = "orders";

// Include admin header
include_once 'includes/header.php';

// Include database and classes
include_once '../backend/config/database.php';
include_once '../backend/class/Order.php';
include_once '../backend/class/OrderItem.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize order object
$order = new Order($db);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    // Set order properties
    $order->id = $_POST['id'];
    $order->status = $_POST['status'];
    
    // Update order status
    if ($order->updateStatus()) {
        Session::setFlash('success', 'Order status updated successfully.');
        redirect('orders.php?id=' . $order->id);
    } else {
        Session::setFlash('danger', 'Unable to update order status.');
    }
}

// Check if viewing a specific order
if (isset($_GET['id'])) {
    // Get order details
    $order->id = $_GET['id'];
    $order_details = $order->readAll();
    
    if ($order_details->rowCount() > 0) {
        $order_data = $order_details->fetch(PDO::FETCH_ASSOC);
        
        // Get order items
        $order_item = new OrderItem($db);
        $order_item->order_id = $order->id;
        $items = $order_item->readOrderItems();
        
        // Calculate subtotal and tax
        $subtotal = $order_data['total_amount'] / 1.1; // Assuming 10% tax
        $tax = $order_data['total_amount'] - $subtotal;
    ?>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Order Details</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="orders.php">Orders</a></li>
                <li class="breadcrumb-item active">Order #<?php echo str_pad($order_data['id'], 6, '0', STR_PAD_LEFT); ?></li>
            </ol>
            
            <div class="row">
                <div class="col-lg-8">
                    <!-- Order Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-info-circle me-1"></i>
                            Order Information
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <h5>Order Details</h5>
                                    <p>
                                        <strong>Order Number:</strong> #<?php echo str_pad($order_data['id'], 6, '0', STR_PAD_LEFT); ?><br>
                                        <strong>Date:</strong> <?php echo date('F j, Y g:i A', strtotime($order_data['created_at'])); ?><br>
                                        <strong>Payment Method:</strong> 
                                        <?php 
                                            switch($order_data['payment_method']) {
                                                case 'cash_on_delivery': echo 'Cash on Delivery'; break;
                                                case 'credit_card': echo 'Credit/Debit Card'; break;
                                                case 'paypal': echo 'PayPal'; break;
                                                default: echo $order_data['payment_method'];
                                            }
                                        ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <h5>Customer Information</h5>
                                    <p>
                                        <strong>Name:</strong> <?php echo $order_data['username']; ?><br>
                                        <strong>Email:</strong> <?php echo $order_data['email']; ?><br>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Shipping Address</h5>
                                    <p>
                                        <?php echo $order_data['shipping_address']; ?><br>
                                        <?php echo $order_data['shipping_city']; ?>, <?php echo $order_data['shipping_zip_code']; ?><br>
                                        <strong>Phone:</strong> <?php echo $order_data['shipping_phone']; ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <h5>Order Status</h5>
                                    <form action="orders.php" method="post">
                                        <input type="hidden" name="id" value="<?php echo $order_data['id']; ?>">
                                        <div class="input-group">
                                            <select class="form-select" name="status">
                                                <option value="pending" <?php echo ($order_data['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                                <option value="processing" <?php echo ($order_data['status'] === 'processing') ? 'selected' : ''; ?>>Processing</option>
                                                <option value="shipped" <?php echo ($order_data['status'] === 'shipped') ? 'selected' : ''; ?>>Shipped</option>
                                                <option value="delivered" <?php echo ($order_data['status'] === 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                                                <option value="cancelled" <?php echo ($order_data['status'] === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                            <button class="btn btn-primary" type="submit" name="update_status">Update</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Items -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-box me-1"></i>
                            Order Items
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($item = $items->fetch(PDO::FETCH_ASSOC)): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php if ($item['product_image']): ?>
                                                            <img src="<?php echo $item['product_image']; ?>" alt="<?php echo $item['product_name']; ?>" class="img-thumbnail me-2" style="width: 50px; height: 50px; object-fit: cover;">
                                                        <?php endif; ?>
                                                        <div>
                                                            <?php echo $item['product_name']; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>$<?php echo number_format($item['price'], 2); ?></td>
                                                <td><?php echo $item['quantity']; ?></td>
                                                <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <!-- Order Summary -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-file-invoice-dollar me-1"></i>
                            Order Summary
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span>$<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping:</span>
                                <span>Free</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tax (10%):</span>
                                <span>$<?php echo number_format($tax, 2); ?></span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="fw-bold">Total:</span>
                                <span class="fw-bold">$<?php echo number_format($order_data['total_amount'], 2); ?></span>
                            </div>
                            
                            <!-- Order Actions -->
                            <div class="d-grid gap-2">
                                <a href="#" class="btn btn-outline-primary">
                                    <i class="fas fa-print me-1"></i> Print Invoice
                                </a>
                                <a href="orders.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Back to Orders
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Status Timeline -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-history me-1"></i>
                            Order Timeline
                        </div>
                        <div class="card-body">
                            <ul class="timeline">
                                <li class="timeline-item">
                                    <div class="timeline-marker <?php echo in_array($order_data['status'], ['pending', 'processing', 'shipped', 'delivered']) ? 'bg-success' : 'bg-secondary'; ?>"></div>
                                    <div class="timeline-content">
                                        <h5 class="timeline-title">Order Placed</h5>
                                        <p class="timeline-text">
                                            <?php echo date('F j, Y g:i A', strtotime($order_data['created_at'])); ?>
                                        </p>
                                    </div>
                                </li>
                                <li class="timeline-item">
                                    <div class="timeline-marker <?php echo in_array($order_data['status'], ['processing', 'shipped', 'delivered']) ? 'bg-success' : 'bg-secondary'; ?>"></div>
                                    <div class="timeline-content">
                                        <h5 class="timeline-title">Processing</h5>
                                        <p class="timeline-text">
                                            <?php echo in_array($order_data['status'], ['processing', 'shipped', 'delivered']) ? 'Order is being processed' : 'Pending'; ?>
                                        </p>
                                    </div>
                                </li>
                                <li class="timeline-item">
                                    <div class="timeline-marker <?php echo in_array($order_data['status'], ['shipped', 'delivered']) ? 'bg-success' : 'bg-secondary'; ?>"></div>
                                    <div class="timeline-content">
                                        <h5 class="timeline-title">Shipped</h5>
                                        <p class="timeline-text">
                                            <?php echo in_array($order_data['status'], ['shipped', 'delivered']) ? 'Order has been shipped' : 'Pending'; ?>
                                        </p>
                                    </div>
                                </li>
                                <li class="timeline-item">
                                    <div class="timeline-marker <?php echo in_array($order_data['status'], ['delivered']) ? 'bg-success' : 'bg-secondary'; ?>"></div>
                                    <div class="timeline-content">
                                        <h5 class="timeline-title">Delivered</h5>
                                        <p class="timeline-text">
                                            <?php echo in_array($order_data['status'], ['delivered']) ? 'Order has been delivered' : 'Pending'; ?>
                                        </p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <style>
                        .timeline {
                            list-style: none;
                            padding: 0;
                            position: relative;
                        }
                        
                        .timeline:before {
                            top: 0;
                            bottom: 0;
                            position: absolute;
                            content: " ";
                            width: 2px;
                            background-color: #dee2e6;
                            left: 1.25rem;
                            margin-left: -1px;
                        }
                        
                        .timeline-item {
                            margin-bottom: 1.5rem;
                            position: relative;
                        }
                        
                        .timeline-marker {
                            position: absolute;
                            width: 14px;
                            height: 14px;
                            border-radius: 50%;
                            left: 1.25rem;
                            top: 0.25rem;
                            margin-left: -7px;
                        }
                        
                        .timeline-content {
                            margin-left: 2.5rem;
                            padding-bottom: 0.5rem;
                        }
                        
                        .timeline-title {
                            margin-top: 0;
                            font-size: 1rem;
                        }
                        
                        .timeline-text {
                            margin-bottom: 0;
                            font-size: 0.875rem;
                        }
                    </style>
                </div>
            </div>
        </div>
    <?php
    } else {
        Session::setFlash('danger', 'Order not found.');
        redirect('orders.php');
    }
} else {
    // List all orders
    // Set pagination variables
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $per_page = 10;
    
    // Get status filter if any
    $status_filter = isset($_GET['status']) ? $_GET['status'] : '';
    
    // Get all orders
    $stmt = $order->readAll();
    ?>
    <div class="container-fluid px-4">
        <h1 class="mt-4">Manage Orders</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
            <li class="breadcrumb-item active">Orders</li>
        </ol>
        
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-table me-1"></i>
                Orders
            </div>
            <div class="card-body">
                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <form action="orders.php" method="get" class="d-flex">
                            <select name="status" class="form-select me-2">
                                <option value="">All Statuses</option>
                                <option value="pending" <?php echo ($status_filter === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo ($status_filter === 'processing') ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped" <?php echo ($status_filter === 'shipped') ? 'selected' : ''; ?>>Shipped</option>
                                <option value="delivered" <?php echo ($status_filter === 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo ($status_filter === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                            <button type="submit" class="btn btn-outline-primary">Filter</button>
                        </form>
                    </div>
                </div>
                
                <!-- Orders Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if ($stmt->rowCount() > 0):
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                                    // Skip if status filter is applied and doesn't match
                                    if (!empty($status_filter) && $row['status'] !== $status_filter) continue;
                            ?>
                                <tr>
                                    <td><?php echo str_pad($row['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <?php echo $row['username']; ?><br>
                                        <small class="text-muted"><?php echo $row['email']; ?></small>
                                    </td>
                                    <td>$<?php echo number_format($row['total_amount'], 2); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            switch($row['status']) {
                                                case 'pending': echo 'warning'; break;
                                                case 'processing': echo 'info'; break;
                                                case 'shipped': echo 'primary'; break;
                                                case 'delivered': echo 'success'; break;
                                                case 'cancelled': echo 'danger'; break;
                                                default: echo 'secondary';
                                            }
                                        ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="orders.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                                <tr>
                                    <td colspan="6" class="text-center">No orders found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php
}

// Include admin footer
include_once 'includes/footer.php';
?>
