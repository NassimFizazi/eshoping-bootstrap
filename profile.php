<?php
// Set page title and active page
$page_title = "My Profile";
$active_page = "";

// Include header
include_once 'includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    Session::setFlash('danger', 'Please login to view your profile.');
    redirect('login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

// Include database and user classes
include_once 'backend/config/database.php';
include_once 'backend/class/User.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize user object
$user = new User($db);
$user->id = Session::getUserId();
$user->readOne();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include_once 'backend/utils/validator.php';
    
    // Validate input
    $validator = new Validator();
    $validator->required('username', $_POST['username'] ?? '');
    $validator->required('email', $_POST['email'] ?? '');
    $validator->email('email', $_POST['email'] ?? '');
    
    // Check password match if provided
    if (!empty($_POST['password'])) {
        $validator->minLength('password', $_POST['password'] ?? '', 6);
        $validator->required('confirm_password', $_POST['confirm_password'] ?? '');
        $validator->match('confirm_password', $_POST['confirm_password'] ?? '', 'password', $_POST['password'] ?? '', 'Passwords do not match');
    }
    
    if ($validator->hasErrors()) {
        $errors = $validator->getErrors();
        $error_message = implode('<br>', $errors);
        Session::setFlash('danger', $error_message);
    } else {
        // Set user properties
        $user->username = $_POST['username'];
        $user->email = $_POST['email'];
        if (!empty($_POST['password'])) {
            $user->password = $_POST['password'];
        }
        $user->first_name = $_POST['first_name'] ?? '';
        $user->last_name = $_POST['last_name'] ?? '';
        $user->address = $_POST['address'] ?? '';
        $user->city = $_POST['city'] ?? '';
        $user->zip_code = $_POST['zip_code'] ?? '';
        $user->phone = $_POST['phone'] ?? '';
        
        // Update user
        if ($user->update()) {
            // Update session info
            Session::setUser($user->id, $user->username, $user->email, Session::isAdmin());
            
            Session::setFlash('success', 'Profile updated successfully.');
            redirect('profile.php');
        } else {
            Session::setFlash('danger', 'Unable to update profile. Please try again.');
        }
    }
}
?>

<div class="container px-4 px-lg-5 mt-5">
    <h1 class="fw-bold mb-4">My Profile</h1>
    
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card profile-card">
                <div class="text-center py-4">
                    <i class="fas fa-user-circle fa-5x text-primary"></i>
                    <h3 class="mt-3"><?php echo $user->username; ?></h3>
                    <p class="text-muted"><?php echo $user->email; ?></p>
                    <?php if (isAdmin()): ?>
                        <span class="badge bg-danger">Administrator</span>
                    <?php endif; ?>
                </div>
                <div class="list-group list-group-flush">
                    <a href="#profile-info" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                        <i class="fas fa-user me-2"></i> Personal Information
                    </a>
                    <a href="#account-settings" class="list-group-item list-group-item-action" data-bs-toggle="list">
                        <i class="fas fa-cog me-2"></i> Account Settings
                    </a>
                    <a href="orders.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-shopping-bag me-2"></i> Order History
                    </a>
                    <?php if (isAdmin()): ?>
                        <a href="admin/index.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-tachometer-alt me-2"></i> Admin Dashboard
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="tab-content">
                <!-- Personal Information -->
                <div class="tab-pane fade show active" id="profile-info">
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Personal Information</h5>
                        </div>
                        <div class="card-body">
                            <form action="profile.php" method="post">
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
                                    <div class="col-md-6">
                                        <label for="city" class="form-label">City</label>
                                        <input type="text" class="form-control" id="city" name="city" value="<?php echo $user->city; ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="zip_code" class="form-label">ZIP Code</label>
                                        <input type="text" class="form-control" id="zip_code" name="zip_code" value="<?php echo $user->zip_code; ?>">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo $user->phone; ?>">
                                </div>
                                
                                <!-- These fields need to be included in both tabs -->
                                <input type="hidden" name="username" value="<?php echo $user->username; ?>">
                                <input type="hidden" name="email" value="<?php echo $user->email; ?>">
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i> Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Account Settings -->
                <div class="tab-pane fade" id="account-settings">
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Account Settings</h5>
                        </div>
                        <div class="card-body">
                            <form action="profile.php" method="post">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="username" name="username" value="<?php echo $user->username; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo $user->email; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="password" name="password">
                                    <div class="form-text">Leave blank to keep your current password.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                </div>
                                
                                <!-- Include these personal info fields as hidden inputs -->
                                <input type="hidden" name="first_name" value="<?php echo $user->first_name; ?>">
                                <input type="hidden" name="last_name" value="<?php echo $user->last_name; ?>">
                                <input type="hidden" name="address" value="<?php echo $user->address; ?>">
                                <input type="hidden" name="city" value="<?php echo $user->city; ?>">
                                <input type="hidden" name="zip_code" value="<?php echo $user->zip_code; ?>">
                                <input type="hidden" name="phone" value="<?php echo $user->phone; ?>">
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i> Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
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
