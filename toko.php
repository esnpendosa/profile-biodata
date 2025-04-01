<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Koneksi ke database
$servername = "localhost";
$username = "root"; // Sesuaikan dengan username database Anda
$password = ""; // Sesuaikan dengan password database Anda
$dbname = "ecommerce";

// Membuat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Fungsi untuk mendapatkan produk berdasarkan kategori
function getProducts($kategori = null) {
    global $conn;
    
    if (!empty($kategori)) {
        $sql = "SELECT * FROM products WHERE kategori = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $kategori);
    } else {
        $sql = "SELECT * FROM products";
        $stmt = $conn->prepare($sql);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Ambil kategori dari URL
$selectedKategori = $_GET['kategori'] ?? "";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Online</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 5px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        .product-card {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            background: #fff;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease-in-out;
        }
        .product-card:hover {
            transform: scale(1.05);
        }
        .price {
            color: #2ecc71;
            font-weight: bold;
            font-size: 18px;
        }
        .category {
            color: #666;
            font-size: 0.9em;
        }
        .filter-section {
            margin-bottom: 20px;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 5px;
        }
        select, button {
            padding: 10px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Daftar Produk</h2>
        
        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET">
                <label>Filter Kategori:</label>
                <select name="kategori">
                    <option value="">Semua Kategori</option>
                    <option value="Elektronik" <?= $selectedKategori === 'Elektronik' ? 'selected' : '' ?>>Elektronik</option>
                    <option value="Pakaian" <?= $selectedKategori === 'Pakaian' ? 'selected' : '' ?>>Pakaian</option>
                    <option value="Makanan" <?= $selectedKategori === 'Makanan' ? 'selected' : '' ?>>Makanan</option>
                </select>
                <button type="submit">Filter</button>
            </form>
        </div>

        <!-- Product Grid -->
        <div class="product-grid">
            <?php
            $products = getProducts($selectedKategori);
            
            if (!empty($products)) {
                foreach ($products as $product) {
                    echo '
                    <div class="product-card">
                        <h3>'.$product['nama_produk'].'</h3>
                        <div class="category">Kategori: '.$product['kategori'].'</div>
                        <div class="price">Rp '.number_format($product['harga'], 0, ',', '.').'</div>
                        <p>'.$product['deskripsi'].'</p>
                        <div>Stok: '.$product['stok'].'</div>
                    </div>';
                }
            } else {
                echo '<p>Tidak ada produk ditemukan.</p>';
            }
            ?>
        </div>
    </div>
</body>
</html>

<?php
// Tutup koneksi database
$conn->close();
?>
