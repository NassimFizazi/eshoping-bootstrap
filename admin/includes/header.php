<?php
include_once '../includes/config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    Session::setFlash('danger', 'You do not have permission to access the admin area.');
    redirect('../login.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - Admin Panel' : 'Admin Panel'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="../css/styles.css" rel="stylesheet">
    
    <!-- Additional admin styles -->
    <style>
        body {
            padding-top: 56px;
        }
        .admin-container {
            display: flex;
            min-height: calc(100vh - 56px);
        }
        .admin-sidebar {
            width: 250px;
            min-height: calc(100vh - 56px);
            padding-top: 20px;
        }
        .admin-content {
            flex: 1;
            padding: 20px;
        }
        @media (max-width: 767.98px) {
            .admin-sidebar {
                width: 100%;
                min-height: auto;
            }
            .admin-container {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">Admin Panel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php" target="_blank">
                            <i class="fas fa-external-link-alt me-1"></i> View Site
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-1"></i> <?php echo Session::getUsername(); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="../profile.php">My Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="../backend/api/user.php" method="post" id="logout-form">
                                    <input type="hidden" name="action" value="logout">
                                    <button type="submit" class="dropdown-item">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="admin-container">
        <!-- Sidebar -->
        <div class="admin-sidebar bg-dark">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($active_page == 'dashboard') ? 'active' : ''; ?>" href="index.php">
                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($active_page == 'products') ? 'active' : ''; ?>" href="products.php">
                        <i class="fas fa-box me-2"></i> Products
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($active_page == 'categories') ? 'active' : ''; ?>" href="categories.php">
                        <i class="fas fa-tags me-2"></i> Categories
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($active_page == 'orders') ? 'active' : ''; ?>" href="orders.php">
                        <i class="fas fa-shopping-cart me-2"></i> Orders
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($active_page == 'users') ? 'active' : ''; ?>" href="users.php">
                        <i class="fas fa-users me-2"></i> Users
                    </a>
                </li>
            </ul>
        </div>

        <!-- Content -->
        <div class="admin-content bg-light">
            <!-- Display flash messages -->
            <?php displayFlash(); ?>
