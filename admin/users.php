<?php
// Set page title and active page
$page_title = "Manage Users";
$active_page = "users";

// Include admin header
include_once 'includes/header.php';

// Include database and user class
include_once '../backend/config/database.php';
include_once '../backend/class/User.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize user object
$user = new User($db);

// Check if viewing a specific user
if (isset($_GET['id'])) {
    $user->id = $_GET['id'];
    if ($user->readOne()) {
        // Handle user update
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
            // Set user properties
            $user->username = $_POST['username'];
            $user->email = $_POST['email'];
            $user->first_name = $_POST['first_name'];
            $user->last_name = $_POST['last_name'];
            $user->address = $_POST['address'];
            $user->city = $_POST['city'];
            $user->zip_code = $_POST['zip_code'];
            $user->phone = $_POST['phone'];
            
            // Update password if provided
            if (!empty($_POST['password'])) {
                $user->password = $_POST['password'];
            }
            
            // Update admin status if checked
            if (isset($_POST['is_admin'])) {
                $user->is_admin = 1;
            } else {
                $user->is_admin = 0;
            }
            
            // Update user
            if ($user->update()) {
                Session::setFlash('success', 'User updated successfully.');
                redirect('users.php');
            } else {
                Session::setFlash('danger', 'Unable to update user.');
            }
        }
        
        // User details form
        ?>
        <div class="container-fluid px-4">
            <h1 class="mt-4">User Details</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="users.php">Users</a></li>
                <li class="breadcrumb-item active">User Details</li>
            </ol>
            
            <div class="row">
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-user-edit me-1"></i>
                            Edit User: <?php echo $user->username; ?>
                        </div>
                        <div class="card-body">
                            <form action="users.php?id=<?php echo $user->id; ?>" method="post">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="username" name="username" value="<?php echo $user->username; ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $user->email; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="first_name" class="form-label">First Name</label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo $user->first_name; ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="last_name" class="form-label">Last Name</label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo $user->last_name; ?>">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <input type="text" class="form-control" id="address" name="address" value="<?php echo $user->address; ?>">
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="city" class="form-label">City</label>
                                        <input type="text" class="form-control" id="city" name="city" value="<?php echo $user->city; ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="zip_code" class="form-label">ZIP Code</label>
                                        <input type="text" class="form-control" id="zip_code" name="zip_code" value="<?php echo $user->zip_code; ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="phone" class="form-label">Phone</label>
                                        <input type="text" class="form-control" id="phone" name="phone" value="<?php echo $user->phone; ?>">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password">
                                    <div class="form-text">Leave blank to keep current password.</div>
                                </div>
                                
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="is_admin" name="is_admin" value="1" <?php echo ($user->is_admin) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_admin">Administrator</label>
                                </div>
                                
                                <div class="d-flex justify-content-end">
                                    <a href="users.php" class="btn btn-secondary me-2">Cancel</a>
                                    <button type="submit" name="update" class="btn btn-primary">Update User</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-info-circle me-1"></i>
                            User Information
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <i class="fas fa-user-circle fa-5x text-primary"></i>
                                <h5 class="mt-2"><?php echo $user->username; ?></h5>
                                <p class="text-muted"><?php echo $user->email; ?></p>
                                <?php if ($user->is_admin): ?>
                                    <span class="badge bg-danger">Administrator</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Customer</span>
                                <?php endif; ?>
                            </div>
                            
                            <hr>
                            
                            <p><strong>Created:</strong> <?php echo date('F j, Y', strtotime($user->created_at)); ?></p>
                            
                            <?php if ($user->id !== Session::getUserId()): ?>
                                <div class="d-grid">
                                    <form action="users.php" method="post" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        <input type="hidden" name="id" value="<?php echo $user->id; ?>">
                                        <button type="submit" name="delete" class="btn btn-danger delete-btn">
                                            <i class="fas fa-trash me-1"></i> Delete User
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    } else {
        Session::setFlash('danger', 'User not found.');
        redirect('users.php');
    }
} else {
    // List all users
    
    // Handle user deletion
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
        // Can't delete yourself
        if ($_POST['id'] == Session::getUserId()) {
            Session::setFlash('danger', 'You cannot delete your own account.');
        } else {
            // Set user ID
            $user->id = $_POST['id'];
            
            // Delete user
            if ($user->delete()) {
                Session::setFlash('success', 'User deleted successfully.');
                redirect('users.php');
            } else {
                Session::setFlash('danger', 'Unable to delete user.');
            }
        }
    }
    
    // Get all users
    $stmt = $user->readAll();
    ?>
    <div class="container-fluid px-4">
        <h1 class="mt-4">Manage Users</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
            <li class="breadcrumb-item active">Users</li>
        </ol>
        
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-table me-1"></i>
                Users
            </div>
            <div class="card-body">
                <!-- Users Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($stmt->rowCount() > 0): ?>
                                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo $row['username']; ?></td>
                                        <td><?php echo $row['email']; ?></td>
                                        <td>
                                            <?php 
                                                $name = trim($row['first_name'] . ' ' . $row['last_name']);
                                                echo !empty($name) ? $name : '-';
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($row['is_admin']): ?>
                                                <span class="badge bg-danger">Admin</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Customer</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($row['created_at'])); ?></td>
                                        <td>
                                            <a href="users.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary mb-1">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <?php if ($row['id'] !== Session::getUserId()): ?>
                                                <form action="users.php" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                    <button type="submit" name="delete" class="btn btn-sm btn-danger mb-1 delete-btn">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">No users found.</td>
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
