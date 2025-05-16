<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include utility classes
require_once 'backend/utils/session.php';
Session::start();

// Define site constants
define('SITE_NAME', 'EShop');
define('SITE_URL', 'http://' . $_SERVER['HTTP_HOST']);

// Function to check if user is logged in
function isLoggedIn() {
    return Session::isLoggedIn();
}

// Function to check if user is admin
function isAdmin() {
    return Session::isAdmin();
}

// Function to redirect to another page
function redirect($path) {
    header("Location: " . $path);
    exit;
}

// Function to sanitize input data
function sanitize($data) {
    return htmlspecialchars(trim($data));
}

// Function to display flash messages
function displayFlash() {
    $flash = Session::getFlash();
    if ($flash) {
        echo '<div class="alert alert-' . $flash['type'] . ' alert-dismissible fade show flash-message" role="alert">';
        echo $flash['message'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
    }
}
?>
