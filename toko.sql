-- Membuat database
drop database if exists ecommerce;
create database ecommerce;
use ecommerce;

-- Membuat tabel products
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_produk VARCHAR(255) NOT NULL,
    harga INT NOT NULL,
    deskripsi TEXT,
    stok INT NOT NULL
);

-- Membuat tabel users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- Membuat tabel orders
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    product_id INT,
    quantity INT NOT NULL,
    total INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Query CRUD untuk tabel products

-- CREATE: Menambahkan produk baru
INSERT INTO products (nama_produk, harga, deskripsi, stok) 
VALUES ('Produk A', 50000, 'Deskripsi produk A', 10);

-- READ: Menampilkan semua produk
SELECT * FROM products;

-- UPDATE: Mengubah data produk
UPDATE products SET harga = 55000, stok = 15 WHERE id = 1;

-- DELETE: Menghapus produk berdasarkan ID
DELETE FROM products WHERE id = 1;
