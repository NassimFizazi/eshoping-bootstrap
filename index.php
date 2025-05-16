<?php
// Set page title and active page
$page_title = "Home";
$active_page = "home";

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

// Get featured products (first 8 products)
$featured_products = $product->readAll(1, 8);

// Get all categories
$categories = $category->readAll();
?>

<!-- Banner -->
<div class="hero-banner" style="background-image: url('https://pixabay.com/get/g64e74877d907a8b3a302719d603ca13758a8b487e587a1408092afe1eb3de0d30ce67ce1fee445db10475b8ab0ba6c31448f9866c3475b802145f1b8411f94e2_1280.jpg');">
    <div class="container">
        <div class="hero-content text-center">
            <h1 class="display-4 fw-bold mb-3">Welcome to <?php echo SITE_NAME; ?></h1>
            <p class="lead mb-4">Discover amazing products at unbeatable prices</p>
            <a href="products.php" class="btn btn-primary btn-lg">Shop Now</a>
        </div>
    </div>
</div>

<!-- Categories Section -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-4">Shop by Category</h2>
        <div class="row justify-content-center">
            <?php while ($row = $categories->fetch(PDO::FETCH_ASSOC)) : ?>
                <div class="col-md-3 col-6 mb-4">
                    <a href="products.php?category_id=<?php echo $row['id']; ?>" class="text-decoration-none">
                        <div class="card h-100 text-center">
                            <div class="card-body">
                                <i class="fas 
                                    <?php 
                                    switch($row['id']) {
                                        case 1: echo 'fa-mobile-alt'; break;
                                        case 2: echo 'fa-tshirt'; break;
                                        case 3: echo 'fa-home'; break;
                                        case 4: echo 'fa-book'; break;
                                        default: echo 'fa-tags';
                                    }
                                    ?> 
                                    fa-3x mb-3 text-primary"></i>
                                <h5 class="card-title"><?php echo $row['name']; ?></h5>
                                <p class="card-text text-muted small"><?php echo $row['description']; ?></p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="py-5 bg-light">
    <div class="container px-4 px-lg-5">
        <h2 class="fw-bold mb-4">Featured Products</h2>
        <div class="row gx-4 gx-lg-5 row-cols-1 row-cols-md-2 row-cols-lg-4 justify-content-center">
            <?php while ($row = $featured_products->fetch(PDO::FETCH_ASSOC)) : ?>
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
            <?php endwhile; ?>
        </div>
        <div class="text-center mt-4">
            <a href="products.php" class="btn btn-lg btn-primary">View All Products</a>
        </div>
    </div>
</section>

<!-- Promotional Banner -->
<section class="py-5" style="background-image: url('https://pixabay.com/get/g386fbaa21e26a21c3d90c437ad9e0338a064c9ba31b5cdb72b1cdf257d4ce7754b4ef13598aa7a40c25c6a0c9c1b3764d413de5a43ada5234d6e529e9b76e5a4_1280.jpg'); background-size: cover; background-position: center;">
    <div class="container px-4 px-lg-5 my-5">
        <div class="text-center text-white">
            <h2 class="display-5 fw-bolder">Special Offers</h2>
            <p class="lead fw-normal text-white-50 mb-0">Get up to 50% off on selected items</p>
            <div class="mt-4">
                <a href="products.php" class="btn btn-light btn-lg">Shop the Sale</a>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
include_once 'includes/footer.php';
?>
