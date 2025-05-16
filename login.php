<?php
// Set page title and active page
$page_title = "Login";
$active_page = "";

// Include header
include_once 'includes/header.php';

// Check if already logged in
if (isLoggedIn()) {
    Session::setFlash('info', 'You are already logged in.');
    redirect('index.php');
}

// Get redirect URL if any
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';

// Process form submission via AJAX, provide form for non-JS browsers
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Include database and user classes
    include_once 'backend/config/database.php';
    include_once 'backend/class/User.php';
    include_once 'backend/utils/validator.php';
    
    // Get database connection
    $database = new Database();
    $db = $database->getConnection();
    
    // Validate input
    $validator = new Validator();
    $validator->required('username', $_POST['username'] ?? '');
    $validator->required('password', $_POST['password'] ?? '');
    
    if ($validator->hasErrors()) {
        Session::setFlash('danger', 'Please fill in all required fields.');
    } else {
        // Initialize user
        $user = new User($db);
        $user->username = $_POST['username'];
        $user->password = $_POST['password'];
        
        // Attempt login
        if ($user->login()) {
            // Set session
            Session::setUser($user->id, $user->username, $user->email, $user->is_admin);
            
            // Set success message and redirect
            Session::setFlash('success', 'Login successful. Welcome, ' . $user->username . '!');
            redirect($redirect);
        } else {
            Session::setFlash('danger', 'Invalid username or password.');
        }
    }
}
?>

<div class="container px-4 px-lg-5 mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card auth-form">
                <div class="card-header bg-dark text-white text-center py-3">
                    <h4 class="mb-0">Login to Your Account</h4>
                </div>
                <div class="card-body p-4">
                    <form action="login.php<?php echo !empty($redirect) ? '?redirect=' . urlencode($redirect) : ''; ?>" method="post">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username or Email <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember-me">
                            <label class="form-check-label" for="remember-me">Remember me</label>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i> Login
                            </button>
                        </div>
                    </form>
                    
                    <div class="mt-4 text-center">
                        <p class="mb-0">Don't have an account? <a href="register.php<?php echo !empty($redirect) ? '?redirect=' . urlencode($redirect) : ''; ?>" class="text-primary">Register now</a></p>
                    </div>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title">Test Credentials</h5>
                    <p class="card-text">To access the admin area, use the following credentials:</p>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">Username: <strong>admin</strong></li>
                        <li class="list-group-item">Password: <strong>admin123</strong></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include_once 'includes/footer.php';
?>
