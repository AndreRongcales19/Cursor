-- Create the database
CREATE DATABASE IF NOT EXISTS product_reviews;
USE product_reviews;

-- Create products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create reviews table
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Insert some sample products
INSERT INTO products (name, description, price, image_url) VALUES
('Smartphone X', 'Latest smartphone with advanced features', 999.99, 'https://via.placeholder.com/300x300?text=Smartphone'),
('Laptop Pro', 'High-performance laptop for professionals', 1499.99, 'https://via.placeholder.com/300x300?text=Laptop'),
('Wireless Headphones', 'Premium wireless headphones with noise cancellation', 199.99, 'https://via.placeholder.com/300x300?text=Headphones'); 