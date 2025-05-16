<?php
// Set page title and active page
$page_title = "Manage Categories";
$active_page = "categories";

// Include admin header
include_once 'includes/header.php';

// Include database and category class
include_once '../backend/config/database.php';
include_once '../backend/class/Category.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize category object
$category = new Category($db);

// Check the action parameter
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create']) || isset($_POST['update'])) {
        // Set category properties
        $category->name = $_POST['name'];
        $category->description = $_POST['description'];
        
        if (isset($_POST['create'])) {
            // Create category
            if ($category->create()) {
                Session::setFlash('success', 'Category created successfully.');
                redirect('categories.php');
            } else {
                Session::setFlash('danger', 'Unable to create category.');
            }
        } else if (isset($_POST['update'])) {
            // Set category ID
            $category->id = $_POST['id'];
            
            // Update category
            if ($category->update()) {
                Session::setFlash('success', 'Category updated successfully.');
                redirect('categories.php');
            } else {
                Session::setFlash('danger', 'Unable to update category.');
            }
        }
    } else if (isset($_POST['delete'])) {
        // Set category ID
        $category->id = $_POST['id'];
        
        // Delete category
        if ($category->delete()) {
            Session::setFlash('success', 'Category deleted successfully.');
            redirect('categories.php');
        } else {
            Session::setFlash('danger', 'Unable to delete category. Make sure there are no products assigned to this category.');
        }
    }
}

// Handle create/edit/delete actions
if ($action === 'create') {
    // Display create form
    ?>
    <div class="container-fluid px-4">
        <h1 class="mt-4">Create Category</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="categories.php">Categories</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol>
        
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-plus me-1"></i>
                Add New Category
            </div>
            <div class="card-body">
                <form action="categories.php" method="post">
                    <div class="mb-3">
                        <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <a href="categories.php" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" name="create" class="btn btn-primary">Create Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
} else if ($action === 'edit' && isset($_GET['id'])) {
    // Get category data
    $category->id = $_GET['id'];
    if ($category->readOne()) {
        // Display edit form
        ?>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Edit Category</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="categories.php">Categories</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
            
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-edit me-1"></i>
                    Edit Category: <?php echo $category->name; ?>
                </div>
                <div class="card-body">
                    <form action="categories.php" method="post">
                        <input type="hidden" name="id" value="<?php echo $category->id; ?>">
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo $category->name; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo $category->description; ?></textarea>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <a href="categories.php" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" name="update" class="btn btn-primary">Update Category</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
    } else {
        Session::setFlash('danger', 'Category not found.');
        redirect('categories.php');
    }
} else {
    // List all categories
    $stmt = $category->readAll();
    ?>
    <div class="container-fluid px-4">
        <h1 class="mt-4">Manage Categories</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
            <li class="breadcrumb-item active">Categories</li>
        </ol>
        
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-table me-1"></i>
                    Categories
                </div>
                <a href="categories.php?action=create" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> Add New Category
                </a>
            </div>
            <div class="card-body">
                <!-- Categories Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Date Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($stmt->rowCount() > 0): ?>
                                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo $row['name']; ?></td>
                                        <td><?php echo $row['description']; ?></td>
                                        <td><?php echo date('M j, Y', strtotime($row['created_at'])); ?></td>
                                        <td>
                                            <a href="categories.php?action=edit&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary mb-1">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form action="categories.php" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this category?');">
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
                                    <td colspan="5" class="text-center">No categories found.</td>
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
