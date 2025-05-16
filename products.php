<?php
// Set page title and active page
$page_title = "Products";
$active_page = "products";

// Include header
include_once 'includes/header.php';

// Include database and product classes
include_once 'backend/config/database.php';
include_once 'backend/class/Product.php';
include_once 'backend/class/Category.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize product and category objects
$product = new Product($db);
$category = new Category($db);

// Set pagination variables
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 8;
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;
$search = isset($_GET['search']) ? $_GET['search'] : "";

// Get products based on search or category
if (!empty($search)) {
    $stmt = $product->search($search, $page, $per_page);
    $total_rows = $product->getSearchTotal($search);
} else {
    $stmt = $product->readAll($page, $per_page, $category_id);
    $total_rows = $product->getTotal($category_id);
}

// Get all categories
$categories = $category->readAll();

// Calculate total pages
$total_pages = ceil($total_rows / $per_page);
?>

<div class="container px-4 px-lg-5 mt-5">
    <div class="row">
        <!-- Categories Sidebar -->
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Categories</h5>
                </div>
                <div class="card-body">
                    <a href="products.php" class="category-pill <?php echo !isset($_GET['category_id']) ? 'active' : ''; ?>">
                        All Products
                    </a>
                    <?php while ($row = $categories->fetch(PDO::FETCH_ASSOC)) : ?>
                        <a href="products.php?category_id=<?php echo $row['id']; ?>" 
                           class="category-pill <?php echo (isset($_GET['category_id']) && $_GET['category_id'] == $row['id']) ? 'active' : ''; ?>">
                            <?php echo $row['name']; ?>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
        
        <!-- Product Listing -->
        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold">
                    <?php 
                    if (!empty($search)) {
                        echo 'Search Results for "' . htmlspecialchars($search) . '"';
                    } elseif ($category_id) {
                        $cat = new Category($db);
                        $cat->id = $category_id;
                        $cat->readOne();
                        echo $cat->name;
                    } else {
                        echo 'All Products';
                    }
                    ?>
                </h2>
                <span class="text-muted"><?php echo $total_rows; ?> products found</span>
            </div>
            
            <!-- Products Grid -->
            <div class="row gx-4 gx-lg-5 row-cols-1 row-cols-md-2 row-cols-lg-3 justify-content-center">
                <?php 
                if ($stmt->rowCount() > 0) {
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) : 
                ?>
                    <div class="col mb-5">
                        <div class="card product-card h-100">
                            <!-- Product image-->
                            <img class="card-img-top" src="<?php echo $row['image_url']; ?>" alt="<?php echo $row['name']; ?>" />
                            <!-- Product details-->
                            <div class="card-body p-4">
                                <div class="text-center">
                                    <!-- Product name-->
                                    <h5 class="fw-bolder"><?php echo $row['name']; ?></h5>
                                    <!-- Product category -->
                                    <div class="mb-2 text-muted small"><?php echo $row['category_name']; ?></div>
                                    <!-- Product price-->
                                    $<?php echo number_format($row['price'], 2); ?>
                                </div>
                            </div>
                            <!-- Product actions-->
                            <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                                <div class="text-center">
                                    <a class="btn btn-outline-primary mt-auto" href="product-detail.php?id=<?php echo $row['id']; ?>">
                                        View Details
                                    </a>
                                    <?php if (isLoggedIn()): ?>
                                    <button class="btn btn-primary mt-2" onclick="addToCart(<?php echo $row['id']; ?>)">
                                        <i class="fas fa-shopping-cart"></i> Add to Cart
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php 
                    endwhile;
                } else {
                ?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-search fa-3x mb-3 text-muted"></i>
                        <h3>No Products Found</h3>
                        <p class="text-muted">We couldn't find any products matching your criteria.</p>
                        <a href="products.php" class="btn btn-primary mt-3">View All Products</a>
                    </div>
                <?php
                }
                ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Product Pagination">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $category_id ? '&category_id='.$category_id : ''; ?><?php echo !empty($search) ? '&search='.$search : ''; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $category_id ? '&category_id='.$category_id : ''; ?><?php echo !empty($search) ? '&search='.$search : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $category_id ? '&category_id='.$category_id : ''; ?><?php echo !empty($search) ? '&search='.$search : ''; ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Include footer
include_once 'includes/footer.php';
?>
