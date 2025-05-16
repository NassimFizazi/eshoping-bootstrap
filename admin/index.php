<?php
// Set page title and active page
$page_title = "Dashboard";
$active_page = "dashboard";

// Include admin header
include_once 'includes/header.php';

// Include database and classes
include_once '../backend/config/database.php';
include_once '../backend/class/Product.php';
include_once '../backend/class/Order.php';
include_once '../backend/class/User.php';
include_once '../backend/class/Category.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize classes
$product = new Product($db);
$order = new Order($db);
$user = new User($db);
$category = new Category($db);

// Get product count
$product_stmt = $product->readAll();
$product_count = $product->getTotal();

// Get order count
$order_stmt = $order->readAll();
$order_count = $order_stmt->rowCount();

// Get order statistics (count by status)
$order_stats = [
    'pending' => 0,
    'processing' => 0,
    'shipped' => 0,
    'delivered' => 0,
    'cancelled' => 0
];

while ($row = $order_stmt->fetch(PDO::FETCH_ASSOC)) {
    if (isset($order_stats[$row['status']])) {
        $order_stats[$row['status']]++;
    }
}

// Get user count
$user_stmt = $user->readAll();
$user_count = $user_stmt->rowCount();

// Get category count
$category_stmt = $category->readAll();
$category_count = $category_stmt->rowCount();

// Get recent orders (limit 5)
$recent_orders_stmt = $order->readAll();
?>

<div class="row mb-4">
    <div class="col-md-3 mb-4">
        <div class="card bg-primary text-white h-100">
            <div class="card-body py-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase">Total Products</h6>
                        <h2 class="mb-0"><?php echo $product_count; ?></h2>
                    </div>
                    <i class="fas fa-box fa-3x opacity-50"></i>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a href="products.php" class="text-white text-decoration-none small">View Details</a>
                <i class="fas fa-angle-right text-white"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card bg-success text-white h-100">
            <div class="card-body py-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase">Total Orders</h6>
                        <h2 class="mb-0"><?php echo $order_count; ?></h2>
                    </div>
                    <i class="fas fa-shopping-cart fa-3x opacity-50"></i>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a href="orders.php" class="text-white text-decoration-none small">View Details</a>
                <i class="fas fa-angle-right text-white"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card bg-info text-white h-100">
            <div class="card-body py-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase">Total Users</h6>
                        <h2 class="mb-0"><?php echo $user_count; ?></h2>
                    </div>
                    <i class="fas fa-users fa-3x opacity-50"></i>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a href="users.php" class="text-white text-decoration-none small">View Details</a>
                <i class="fas fa-angle-right text-white"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card bg-warning text-white h-100">
            <div class="card-body py-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase">Categories</h6>
                        <h2 class="mb-0"><?php echo $category_count; ?></h2>
                    </div>
                    <i class="fas fa-tags fa-3x opacity-50"></i>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a href="categories.php" class="text-white text-decoration-none small">View Details</a>
                <i class="fas fa-angle-right text-white"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Recent Orders</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
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
                            $counter = 0;
                            while ($row = $recent_orders_stmt->fetch(PDO::FETCH_ASSOC)) :
                                if ($counter++ >= 5) break; // Limit to 5 recent orders
                            ?>
                                <tr>
                                    <td><?php echo str_pad($row['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($row['created_at'])); ?></td>
                                    <td><?php echo $row['username']; ?></td>
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
                                        <a href="orders.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-end mt-3">
                    <a href="orders.php" class="btn btn-outline-primary">View All Orders</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Order Status</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Pending</span>
                        <span class="text-warning"><?php echo $order_stats['pending']; ?></span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo $order_count > 0 ? ($order_stats['pending'] / $order_count) * 100 : 0; ?>%" aria-valuenow="<?php echo $order_stats['pending']; ?>" aria-valuemin="0" aria-valuemax="<?php echo $order_count; ?>"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Processing</span>
                        <span class="text-info"><?php echo $order_stats['processing']; ?></span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo $order_count > 0 ? ($order_stats['processing'] / $order_count) * 100 : 0; ?>%" aria-valuenow="<?php echo $order_stats['processing']; ?>" aria-valuemin="0" aria-valuemax="<?php echo $order_count; ?>"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Shipped</span>
                        <span class="text-primary"><?php echo $order_stats['shipped']; ?></span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $order_count > 0 ? ($order_stats['shipped'] / $order_count) * 100 : 0; ?>%" aria-valuenow="<?php echo $order_stats['shipped']; ?>" aria-valuemin="0" aria-valuemax="<?php echo $order_count; ?>"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Delivered</span>
                        <span class="text-success"><?php echo $order_stats['delivered']; ?></span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $order_count > 0 ? ($order_stats['delivered'] / $order_count) * 100 : 0; ?>%" aria-valuenow="<?php echo $order_stats['delivered']; ?>" aria-valuemin="0" aria-valuemax="<?php echo $order_count; ?>"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Cancelled</span>
                        <span class="text-danger"><?php echo $order_stats['cancelled']; ?></span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo $order_count > 0 ? ($order_stats['cancelled'] / $order_count) * 100 : 0; ?>%" aria-valuenow="<?php echo $order_stats['cancelled']; ?>" aria-valuemin="0" aria-valuemax="<?php echo $order_count; ?>"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <a href="products.php?action=create" class="list-group-item list-group-item-action">
                        <i class="fas fa-plus me-2"></i> Add New Product
                    </a>
                    <a href="categories.php?action=create" class="list-group-item list-group-item-action">
                        <i class="fas fa-plus me-2"></i> Add New Category
                    </a>
                    <a href="orders.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-truck me-2"></i> Manage Orders
                    </a>
                    <a href="../index.php" target="_blank" class="list-group-item list-group-item-action">
                        <i class="fas fa-external-link-alt me-2"></i> View Store Front
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include admin footer
include_once 'includes/footer.php';
?>
