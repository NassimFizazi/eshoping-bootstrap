-- Create database
CREATE DATABASE IF NOT EXISTS ecommerce;
USE ecommerce;

-- Create tables
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    address TEXT,
    city VARCHAR(50),
    zip_code VARCHAR(20),
    phone VARCHAR(20),
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(255),
    category_id INT,
    stock_quantity INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT,
    shipping_city VARCHAR(50),
    shipping_zip_code VARCHAR(20),
    shipping_phone VARCHAR(20),
    payment_method VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY (user_id, product_id)
);

-- Insert sample categories
INSERT INTO categories (name, description) VALUES 
('Electronics', 'Electronic devices and accessories'),
('Clothing', 'Apparel and fashion items'),
('Home & Kitchen', 'Home decor and kitchen appliances'),
('Books', 'Books and reading materials');

-- Insert sample products
INSERT INTO products (name, description, price, image_url, category_id, stock_quantity) VALUES 
('Smartphone', 'High-end smartphone with the latest features', 699.99, 'https://pixabay.com/get/g0a0674daee3ee8a68557397e3637fb186be8e1ea293ae94a70052a9147c7ac65fd73f2b97a5677ecb6da2efbe4a2de806879daaae24aeefa56329d38db790353_1280.jpg', 1, 50),
('Laptop', 'Powerful laptop for work and gaming', 1299.99, 'https://pixabay.com/get/g34e6b1fef33c310b07f9736e0b77fd0d1c14a250b65d29f6800efae8b44d7680a038f4c4f86af7a5b7b24822a0563aae24e708575937818a3af6df7797760c3a_1280.jpg', 1, 30),
('Smart Watch', 'Track your fitness and stay connected', 249.99, 'https://pixabay.com/get/g631d9ce61ac8d605f8a7faa0a62aa321fdaf0feffa028a6a9533956d4fc8deb92e1fde4b1a274e012b8aa15776594e2d_1280.jpg', 1, 45),
('Designer T-shirt', 'Premium cotton t-shirt with modern design', 29.99, 'https://pixabay.com/get/ga0ccaed9a768ed145926bbd6be8326e53d19efb5c9145c81e05894f252f23e11921a2c1b2e05216466da49dccf8efd50c4f506cdfc7f317832aa79bcedff2e12_1280.jpg', 2, 100),
('Coffee Maker', 'Automatic coffee maker for perfect brew every time', 79.99, 'https://pixabay.com/get/gc3c5457aadcb24d3c455b4d9fb292627e7067830262f6d6940c88a7201516a7ab7adfac41f8b5fd96ca8855b9acb48f8cb460f58cdd27b3c2f8e29b0934f94fa_1280.jpg', 3, 25),
('Bestselling Novel', 'Latest bestseller from renowned author', 19.99, 'https://pixabay.com/get/g1ec34e881d1b42b9c0418df31b57f5fdb86438f3101450ead0b8fd75fef0de80c0dd1c66ba2075b895f3b53d8d4fb1918169b83791b7630070cc5f9f2998b6ed_1280.jpg', 4, 75),
('Wireless Headphones', 'High-quality wireless headphones with noise cancellation', 159.99, 'https://pixabay.com/get/g7354d31de357557295c833ba3de87e8476c89dd25e26576dd5b3632903f9f4d79a9886e591c88735dcbb7372511755d99596cb531f669bc06c59af43c7c36c98_1280.jpg', 1, 40),
('Designer Jeans', 'Premium quality jeans with perfect fit', 89.99, 'https://pixabay.com/get/g4cc530e1381b88cea7d567d414d1e7266118073018f384603ee4db4b79f6141ee468b01fd6112134fb9b6970bcc85c7294ad27b587f21c2836d3824fb58a55cb_1280.jpg', 2, 60);

-- Insert admin user (password: admin123)
INSERT INTO users (username, email, password, first_name, last_name, is_admin) VALUES 
('admin', 'admin@example.com', '$2y$10$8SOl8Iq.yV6w0Z.NZM8zVeLJldJBEXGKUGCvskABJPQ8kouaXnKxy', 'Admin', 'User', TRUE);
