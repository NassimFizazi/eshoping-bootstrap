<?php
// Check if product ID is set
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: products.php');
    exit;
}

// Set page title and active page
$page_title = "Product Detail";
$active_page = "products";

// Add cart.js to page scripts
$page_scripts = ['js/cart.js'];

// Include header
include_once 'includes/header.php';

// Include database and product classes
include_once 'backend/config/database.php';
include_once 'backend/class/Product.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize product object
$product = new Product($db);
$product->id = $_GET['id'];

// Read the product details
if (!$product->readOne()) {
    Session::setFlash('danger', 'Product not found.');
    header('Location: products.php');
    exit;
}
?>

<div class="container px-4 px-lg-5 mt-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="products.php">Products</a></li>
            <li class="breadcrumb-item"><a href="products.php?category_id=<?php echo $product->category_id; ?>"><?php echo $product->category_name; ?></a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo $product->name; ?></li>
        </ol>
    </nav>
    
    <div class="row gx-4 gx-lg-5 align-items-center">
        <div class="col-md-6">
            <img class="card-img-top product-image mb-5 mb-md-0" src="<?php echo $product->image_url; ?>" alt="<?php echo $product->name; ?>" />
        </div>
        <div class="col-md-6 product-details">
            <div class="small mb-1">SKU: <?php echo 'PROD-' . str_pad($product->id, 4, '0', STR_PAD_LEFT); ?></div>
            <h1 class="display-5 fw-bolder"><?php echo $product->name; ?></h1>
            <div class="product-price mb-3">$<?php echo number_format($product->price, 2); ?></div>
            <p class="lead"><?php echo $product->description; ?></p>
            
            <div class="mb-3">
                <span class="badge bg-<?php echo $product->stock_quantity > 0 ? 'success' : 'danger'; ?>">
                    <?php echo $product->stock_quantity > 0 ? 'In Stock (' . $product->stock_quantity . ')' : 'Out of Stock'; ?>
                </span>
                <span class="badge bg-secondary"><?php echo $product->category_name; ?></span>
            </div>
            
            <?php if (isLoggedIn() && $product->stock_quantity > 0): ?>
                <form id="add-to-cart-form" data-product-id="<?php echo $product->id; ?>">
                    <div class="d-flex">
                        <div class="input-group quantity-control me-3">
                            <button class="btn btn-outline-secondary decrement-btn" type="button">-</button>
                            <input type="number" class="form-control text-center quantity-input" id="product-quantity" value="1" min="1" max="<?php echo $product->stock_quantity; ?>">
                            <button class="btn btn-outline-secondary increment-btn" type="button">+</button>
                        </div>
                        <button class="btn btn-primary flex-shrink-0" type="submit">
                            <i class="fas fa-shopping-cart me-1"></i>
                            Add to Cart
                        </button>
                    </div>
                </form>
            <?php elseif (!isLoggedIn()): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-1"></i> Please <a href="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">login</a> to add items to your cart.
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Product details tabs -->
    <div class="row mt-5">
        <div class="col-12">
            <ul class="nav nav-tabs" id="productTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab" aria-controls="description" aria-selected="true">Description</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="specifications-tab" data-bs-toggle="tab" data-bs-target="#specifications" type="button" role="tab" aria-controls="specifications" aria-selected="false">Specifications</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="shipping-tab" data-bs-toggle="tab" data-bs-target="#shipping" type="button" role="tab" aria-controls="shipping" aria-selected="false">Shipping & Returns</button>
                </li>
            </ul>
            <div class="tab-content p-4 border border-top-0 rounded-bottom" id="productTabContent">
                <div class="tab-pane fade show active" id="description" role="tabpanel" aria-labelledby="description-tab">
                    <p><?php echo $product->description; ?></p>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed tempus nibh sed elit mattis adipiscing. Fusce in hendrerit purus. Suspendisse potenti. Proin quis eros odio, dapibus dictum mauris. Donec nisi libero, adipiscing id pretium eget, consectetur sit amet leo.</p>
                </div>
                <div class="tab-pane fade" id="specifications" role="tabpanel" aria-labelledby="specifications-tab">
                    <h5>Product Specifications</h5>
                    <table class="table table-striped">
                        <tbody>
                            <tr>
                                <th scope="row">Category</th>
                                <td><?php echo $product->category_name; ?></td>
                            </tr>
                            <tr>
                                <th scope="row">Weight</th>
                                <td>1.5 kg</td>
                            </tr>
                            <tr>
                                <th scope="row">Dimensions</th>
                                <td>30 × 30 × 10 cm</td>
                            </tr>
                            <tr>
                                <th scope="row">Color</th>
                                <td>Black, White, Blue</td>
                            </tr>
                            <tr>
                                <th scope="row">Material</th>
                                <td>Premium Quality</td>
                            </tr>
                            <tr>
                                <th scope="row">Warranty</th>
                                <td>1 Year</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="tab-pane fade" id="shipping" role="tabpanel" aria-labelledby="shipping-tab">
                    <h5>Shipping Information</h5>
                    <p>We ship to all domestic locations within 2-5 business days via our trusted shipping partners.</p>
                    
                    <h5 class="mt-4">Return Policy</h5>
                    <p>If you're not satisfied with your purchase, you can return it within 30 days for a full refund or exchange. The product must be in its original condition and packaging.</p>
                    
                    <p>To initiate a return:</p>
                    <ol>
                        <li>Contact our customer service team</li>
                        <li>Pack the item securely in its original packaging</li>
                        <li>Ship it back to our return address</li>
                        <li>Once received, we'll process your refund within 5-7 business days</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Related products will go here -->
    <h2 class="fw-bold mt-5 mb-4">You May Also Like</h2>
    <div class="row gx-4 gx-lg-5 row-cols-1 row-cols-md-3 row-cols-lg-4 justify-content-center">
        <?php
        // Get products from the same category (limit 4)
        $related_products = new Product($db);
        $stmt = $related_products->readAll(1, 4, $product->category_id);
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) :
            // Skip the current product
            if ($row['id'] == $product->id) continue;
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
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<?php
// Include footer
include_once 'includes/footer.php';
?>
