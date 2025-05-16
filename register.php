<?php
// Set page title and active page
$page_title = "Register";
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

// Process form submission
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
    $validator->required('email', $_POST['email'] ?? '');
    $validator->required('password', $_POST['password'] ?? '');
    $validator->required('confirm_password', $_POST['confirm_password'] ?? '');
    $validator->email('email', $_POST['email'] ?? '');
    $validator->minLength('password', $_POST['password'] ?? '', 6);
    $validator->match('confirm_password', $_POST['confirm_password'] ?? '', 'password', $_POST['password'] ?? '', 'Passwords do not match');
    
    if ($validator->hasErrors()) {
        $errors = $validator->getErrors();
        $error_message = implode('<br>', $errors);
        Session::setFlash('danger', $error_message);
    } else {
        // Initialize user
        $user = new User($db);
        $user->username = $_POST['username'];
        $user->email = $_POST['email'];
        $user->password = $_POST['password'];
        $user->first_name = $_POST['first_name'] ?? '';
        $user->last_name = $_POST['last_name'] ?? '';
        
        // Check if username exists
        if ($user->usernameExists()) {
            Session::setFlash('danger', 'Username already exists. Please choose a different username.');
        } 
        // Check if email exists
        elseif ($user->emailExists()) {
            Session::setFlash('danger', 'Email already exists. Please use a different email or login to your account.');
        } 
        // Attempt to create user
        elseif ($user->create()) {
            // Set session
            Session::setUser($user->id, $user->username, $user->email);
            
            // Set success message and redirect
            Session::setFlash('success', 'Registration successful. Welcome, ' . $user->username . '!');
            redirect($redirect);
        } else {
            Session::setFlash('danger', 'Unable to create account. Please try again later.');
        }
    }
}
?>

<div class="container px-4 px-lg-5 mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card auth-form">
                <div class="card-header bg-dark text-white text-center py-3">
                    <h4 class="mb-0">Create an Account</h4>
                </div>
                <div class="card-body p-4">
                    <form action="register.php<?php echo !empty($redirect) ? '?redirect=' . urlencode($redirect) : ''; ?>" method="post">
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name">
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="form-text">Choose a unique username that will be used to login.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="form-text">Password must be at least 6 characters long.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" required>
                            <label class="form-check-label" for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus me-2"></i> Register
                            </button>
                        </div>
                    </form>
                    
                    <div class="mt-4 text-center">
                        <p class="mb-0">Already have an account? <a href="login.php<?php echo !empty($redirect) ? '?redirect=' . urlencode($redirect) : ''; ?>" class="text-primary">Login here</a></p>
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
