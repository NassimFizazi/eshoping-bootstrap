-- Create tables for PostgreSQL

-- Create tables
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
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
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS products (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(255),
    category_id INTEGER,
    stock_quantity INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Create order status type
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'order_status') THEN
        CREATE TYPE order_status AS ENUM ('pending', 'processing', 'shipped', 'delivered', 'cancelled');
    END IF;
END$$;

CREATE TABLE IF NOT EXISTS orders (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status order_status DEFAULT 'pending',
    shipping_address TEXT,
    shipping_city VARCHAR(50),
    shipping_zip_code VARCHAR(20),
    shipping_phone VARCHAR(20),
    payment_method VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS order_items (
    id SERIAL PRIMARY KEY,
    order_id INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    quantity INTEGER NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS cart_items (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    quantity INTEGER NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE (user_id, product_id)
);

-- Insert sample categories
INSERT INTO categories (name, description) VALUES 
('Electronics', 'Electronic devices and accessories'),
('Clothing', 'Apparel and fashion items'),
('Home & Kitchen', 'Home decor and kitchen appliances'),
('Books', 'Books and reading materials');

-- Insert sample products (after categories are created)
INSERT INTO products (name, description, price, image_url, category_id, stock_quantity) VALUES 
('Smartphone', 'High-end smartphone with the latest features', 699.99, 'https://cdn.pixabay.com/photo/2016/11/29/12/30/android-1869510_640.jpg', 1, 50),
('Laptop', 'Powerful laptop for work and gaming', 1299.99, 'https://cdn.pixabay.com/photo/2016/03/27/07/12/apple-1283162_640.jpg', 1, 30),
('Smart Watch', 'Track your fitness and stay connected', 249.99, 'https://cdn.pixabay.com/photo/2015/06/25/17/22/smart-watch-821559_640.jpg', 1, 45),
('Designer T-shirt', 'Premium cotton t-shirt with modern design', 29.99, 'https://cdn.pixabay.com/photo/2017/01/13/04/56/t-shirt-1976334_640.png', 2, 100),
('Coffee Maker', 'Automatic coffee maker for perfect brew every time', 79.99, 'https://cdn.pixabay.com/photo/2016/11/29/12/54/cafe-1869598_640.jpg', 3, 25),
('Bestselling Novel', 'Latest bestseller from renowned author', 19.99, 'https://cdn.pixabay.com/photo/2015/11/19/21/14/glasses-1052023_640.jpg', 4, 75),
('Wireless Headphones', 'High-quality wireless headphones with noise cancellation', 159.99, 'https://cdn.pixabay.com/photo/2019/11/29/20/00/headphones-4662456_640.jpg', 1, 40),
('Designer Jeans', 'Premium quality jeans with perfect fit', 89.99, 'https://cdn.pixabay.com/photo/2014/08/26/21/48/jeans-428614_640.jpg', 2, 60);

-- Insert admin user (password: admin123)
INSERT INTO users (username, email, password, first_name, last_name, is_admin) VALUES 
('admin', 'admin@example.com', '$2y$10$8SOl8Iq.yV6w0Z.NZM8zVeLJldJBEXGKUGCvskABJPQ8kouaXnKxy', 'Admin', 'User', TRUE);
