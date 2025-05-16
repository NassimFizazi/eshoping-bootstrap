    </main>
    
    <!-- Footer -->
    <footer class="py-5 bg-dark">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5 class="text-white mb-3"><?php echo SITE_NAME; ?></h5>
                    <p class="text-white-50">Your one-stop shop for quality products at the best prices. We're committed to providing excellent service and a seamless shopping experience.</p>
                </div>
                <div class="col-md-2 mb-4 mb-md-0">
                    <h5 class="text-white mb-3">Shop</h5>
                    <ul class="list-unstyled mb-0">
                        <li><a href="products.php" class="text-white-50">All Products</a></li>
                        <li><a href="products.php?category_id=1" class="text-white-50">Electronics</a></li>
                        <li><a href="products.php?category_id=2" class="text-white-50">Clothing</a></li>
                        <li><a href="products.php?category_id=3" class="text-white-50">Home & Kitchen</a></li>
                    </ul>
                </div>
                <div class="col-md-2 mb-4 mb-md-0">
                    <h5 class="text-white mb-3">Account</h5>
                    <ul class="list-unstyled mb-0">
                        <?php if (isLoggedIn()): ?>
                            <li><a href="profile.php" class="text-white-50">My Profile</a></li>
                            <li><a href="orders.php" class="text-white-50">My Orders</a></li>
                            <li><a href="cart.php" class="text-white-50">My Cart</a></li>
                            <li><a href="#" onclick="document.getElementById('logout-form').submit(); return false;" class="text-white-50">Logout</a></li>
                        <?php else: ?>
                            <li><a href="login.php" class="text-white-50">Login</a></li>
                            <li><a href="register.php" class="text-white-50">Register</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5 class="text-white mb-3">Stay Connected</h5>
                    <p class="text-white-50">Subscribe to our newsletter for updates and promotions.</p>
                    <div class="input-group mb-3">
                        <input type="email" class="form-control" placeholder="Email Address" aria-label="Email Address" aria-describedby="subscribe-btn">
                        <button class="btn btn-primary" type="button" id="subscribe-btn">Subscribe</button>
                    </div>
                    <div class="d-flex gap-3 mt-3">
                        <a href="#" class="text-white fs-5"><i class="fab fa-facebook-square"></i></a>
                        <a href="#" class="text-white fs-5"><i class="fab fa-twitter-square"></i></a>
                        <a href="#" class="text-white fs-5"><i class="fab fa-instagram-square"></i></a>
                        <a href="#" class="text-white fs-5"><i class="fab fa-pinterest-square"></i></a>
                    </div>
                </div>
            </div>
            <p class="m-0 text-center text-white mt-4">Copyright &copy; <?php echo SITE_NAME . ' ' . date('Y'); ?></p>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Main JavaScript -->
    <script src="js/main.js"></script>
    
    <?php if (isset($page_scripts)): ?>
        <?php foreach ($page_scripts as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
