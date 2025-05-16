<?php
// Set page title and active page
$page_title = "Manage Products";
$active_page = "products";

// Include admin header
include_once 'includes/header.php';

// Include database and product/category classes
include_once '../backend/config/database.php';
include_once '../backend/class/Product.php';
include_once '../backend/class/Category.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize product and category objects
$product = new Product($db);
$category = new Category($db);

// Get categories for dropdown
$categories = $category->readAll();
$category_options = [];
while ($row = $categories->fetch(PDO::FETCH_ASSOC)) {
    $category_options[$row['id']] = $row['name'];
}

// Check the action parameter
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create']) || isset($_POST['update'])) {
        // Set product properties
        $product->name = $_POST['name'];
        $product->description = $_POST['description'];
        $product->price = $_POST['price'];
        $product->category_id = $_POST['category_id'];
        $product->stock_quantity = $_POST['stock_quantity'];
        $product->image_url = $_POST['image_url'];
        
        if (isset($_POST['create'])) {
            // Create product
            if ($product->create()) {
                Session::setFlash('success', 'Product created successfully.');
                redirect('products.php');
            } else {
                Session::setFlash('danger', 'Unable to create product.');
            }
        } else if (isset($_POST['update'])) {
            // Set product ID
            $product->id = $_POST['id'];
            
            // Update product
            if ($product->update()) {
                Session::setFlash('success', 'Product updated successfully.');
                redirect('products.php');
            } else {
                Session::setFlash('danger', 'Unable to update product.');
            }
        }
    } else if (isset($_POST['delete'])) {
        // Set product ID
        $product->id = $_POST['id'];
        
        // Delete product
        if ($product->delete()) {
            Session::setFlash('success', 'Product deleted successfully.');
            redirect('products.php');
        } else {
            Session::setFlash('danger', 'Unable to delete product.');
        }
    }
}

// Handle create/edit/delete actions
if ($action === 'create') {
    // Display create form
    ?>
    <div class="container-fluid px-4">
        <h1 class="mt-4">Create Product</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="products.php">Products</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol>
        
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-plus me-1"></i>
                Add New Product
            </div>
            <div class="card-body">
                <form action="products.php" method="post">
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="price" class="form-label">Price ($) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label for="stock_quantity" class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" min="0" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($category_options as $id => $name): ?>
                                <option value="<?php echo $id; ?>"><?php echo $name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="image_url" class="form-label">Image URL</label>
                        <input type="text" class="form-control" id="image_url" name="image_url">
                        <div class="form-text">Enter a URL for the product image. Recommended size: 500x500 pixels.</div>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <a href="products.php" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" name="create" class="btn btn-primary">Create Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
} else if ($action === 'edit' && isset($_GET['id'])) {
    // Get product data
    $product->id = $_GET['id'];
    if ($product->readOne()) {
        // Display edit form
        ?>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Edit Product</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="products.php">Products</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
            
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-edit me-1"></i>
                    Edit Product: <?php echo $product->name; ?>
                </div>
                <div class="card-body">
                    <form action="products.php" method="post">
                        <input type="hidden" name="id" value="<?php echo $product->id; ?>">
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo $product->name; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="description" name="description" rows="3" required><?php echo $product->description; ?></textarea>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="price" class="form-label">Price ($) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" value="<?php echo $product->price; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="stock_quantity" class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" min="0" value="<?php echo $product->stock_quantity; ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($category_options as $id => $name): ?>
                                    <option value="<?php echo $id; ?>" <?php echo ($product->category_id == $id) ? 'selected' : ''; ?>><?php echo $name; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="image_url" class="form-label">Image URL</label>
                            <input type="text" class="form-control" id="image_url" name="image_url" value="<?php echo $product->image_url; ?>">
                            <div class="form-text">Enter a URL for the product image. Recommended size: 500x500 pixels.</div>
                        </div>
                        
                        <?php if ($product->image_url): ?>
                            <div class="mb-3">
                                <label class="form-label">Current Image</label>
                                <div>
                                    <img src="<?php echo $product->image_url; ?>" alt="<?php echo $product->name; ?>" class="img-thumbnail" style="max-height: 200px;">
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="d-flex justify-content-end">
                            <a href="products.php" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" name="update" class="btn btn-primary">Update Product</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
    } else {
        Session::setFlash('danger', 'Product not found.');
        redirect('products.php');
    }
} else {
    // List all products
    // Set pagination variables
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $per_page = 10;
    $category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;
    
    // Get products
    $stmt = $product->readAll($page, $per_page, $category_id);
    $total_rows = $product->getTotal($category_id);
    $total_pages = ceil($total_rows / $per_page);
    ?>
    <div class="container-fluid px-4">
        <h1 class="mt-4">Manage Products</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
            <li class="breadcrumb-item active">Products</li>
        </ol>
        
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-table me-1"></i>
                    Products
                </div>
                <a href="products.php?action=create" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> Add New Product
                </a>
            </div>
            <div class="card-body">
                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <form action="products.php" method="get" class="d-flex">
                            <select name="category_id" class="form-select me-2">
                                <option value="">All Categories</option>
                                <?php 
                                // Reset categories result pointer
                                $categories = $category->readAll();
                                while ($row = $categories->fetch(PDO::FETCH_ASSOC)): 
                                ?>
                                    <option value="<?php echo $row['id']; ?>" <?php echo (isset($_GET['category_id']) && $_GET['category_id'] == $row['id']) ? 'selected' : ''; ?>>
                                        <?php echo $row['name']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <button type="submit" class="btn btn-outline-primary">Filter</button>
                        </form>
                    </div>
                </div>
                
                <!-- Products Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($stmt->rowCount() > 0): ?>
                                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td>
                                            <?php if ($row['image_url']): ?>
                                                <img src="<?php echo $row['image_url']; ?>" alt="<?php echo $row['name']; ?>" class="img-thumbnail" style="max-height: 50px;">
                                            <?php else: ?>
                                                <span class="text-muted">No image</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $row['name']; ?></td>
                                        <td><?php echo $row['category_name']; ?></td>
                                        <td>$<?php echo number_format($row['price'], 2); ?></td>
                                        <td>
                                            <?php if ($row['stock_quantity'] > 10): ?>
                                                <span class="badge bg-success"><?php echo $row['stock_quantity']; ?></span>
                                            <?php elseif ($row['stock_quantity'] > 0): ?>
                                                <span class="badge bg-warning"><?php echo $row['stock_quantity']; ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Out of stock</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="products.php?action=edit&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary mb-1">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form action="products.php" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                <button type="submit" name="delete" class="btn btn-sm btn-danger mb-1 delete-btn">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">No products found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav>
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $category_id ? '&category_id=' . $category_id : ''; ?>">Previous</a>
                            </li>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo $category_id ? '&category_id=' . $category_id : ''; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $category_id ? '&category_id=' . $category_id : ''; ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}

// Include admin footer
include_once 'includes/footer.php';
?>
