<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Koneksi ke database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ecommerce";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Fungsi untuk membersihkan input
function cleanInput($data) {
    return htmlspecialchars(trim($data));
}

// Fungsi untuk mendapatkan produk
function getProductById($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// PROSES CRUD
// Tambah/Edit Produk
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_produk = cleanInput($_POST["nama_produk"]);
    $kategori = cleanInput($_POST["kategori"]);
    $harga = (float)$_POST["harga"];
    $deskripsi = cleanInput($_POST["deskripsi"]);
    $stok = (int)$_POST["stok"];
    $id = $_POST["id"] ?? null;

    // Handle upload gambar
    $target_file = null;
    if (!empty($_FILES["gambar"]["name"])) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        
        // Validasi file
        $imageFileType = strtolower(pathinfo($_FILES["gambar"]["name"], PATHINFO_EXTENSION));
        $allowed_types = ["jpg", "jpeg", "png", "gif"];
        
        if (in_array($imageFileType, $allowed_types) && $_FILES["gambar"]["size"] < 2097152) {
            $gambar_name = uniqid() . '_' . basename($_FILES["gambar"]["name"]);
            $target_file = $target_dir . $gambar_name;
            move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file);
        }
    }

    if ($id) {
        // Update produk
        $current_data = getProductById($id);
        $gambar = $target_file ?: $current_data['gambar'];
        $stmt = $conn->prepare("UPDATE products SET nama_produk=?, kategori=?, harga=?, deskripsi=?, stok=?, gambar=? WHERE id=?");
        $stmt->bind_param("ssdsisi", $nama_produk, $kategori, $harga, $deskripsi, $stok, $gambar, $id);
    } else {
        // Tambah produk baru
        $stmt = $conn->prepare("INSERT INTO products (nama_produk, kategori, harga, deskripsi, stok, gambar) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdiss", $nama_produk, $kategori, $harga, $deskripsi, $stok, $target_file);
    }
    
    if ($stmt->execute()) {
        $_SESSION['message'] = $id ? "Produk berhasil diupdate!" : "Produk berhasil ditambahkan!";
    } else {
        $_SESSION['error'] = "Error: " . $stmt->error;
    }
    
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Hapus Produk
if (isset($_GET["hapus"])) {
    $id = (int)$_GET["hapus"];
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $_SESSION['message'] = "Produk berhasil dihapus!";
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Ambil data produk
$selectedKategori = $_GET['kategori'] ?? '';
$sql = "SELECT * FROM products" . ($selectedKategori ? " WHERE kategori = ?" : "");
$stmt = $conn->prepare($sql);
if ($selectedKategori) $stmt->bind_param("s", $selectedKategori);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Edit Produk
$edit_product = isset($_GET["edit"]) ? getProductById((int)$_GET["edit"]) : null;

// KERANJANG BELANJA
if (!isset($_SESSION["cart"])) $_SESSION["cart"] = [];

if (isset($_GET["tambah_keranjang"])) {
    $id = (int)$_GET["tambah_keranjang"];
    $_SESSION["cart"][$id] = ($_SESSION["cart"][$id] ?? 0) + 1;
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

if (isset($_GET["hapus_keranjang"])) {
    unset($_SESSION["cart"][(int)$_GET["hapus_keranjang"]]);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Commerce</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-6xl mx-auto">
        <!-- Notifikasi -->
        <?php if(isset($_SESSION['message'])): ?>
            <div class="bg-green-100 p-4 mb-4 rounded"><?= $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="bg-red-100 p-4 mb-4 rounded"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Form Produk -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            <h2 class="text-xl font-bold mb-4"><?= $edit_product ? 'Edit' : 'Tambah' ?> Produk</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $edit_product['id'] ?? '' ?>">
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <input type="text" name="nama_produk" placeholder="Nama Produk" 
                               class="w-full p-2 border rounded" 
                               value="<?= $edit_product['nama_produk'] ?? '' ?>" required>
                        
                        <select name="kategori" class="w-full p-2 border rounded" required>
                            <option value="">Pilih Kategori</option>
                            <option value="Elektronik" <?= ($edit_product['kategori'] ?? '') == 'Elektronik' ? 'selected' : '' ?>>Elektronik</option>
                            <option value="Pakaian" <?= ($edit_product['kategori'] ?? '') == 'Pakaian' ? 'selected' : '' ?>>Pakaian</option>
                            <option value="Makanan" <?= ($edit_product['kategori'] ?? '') == 'Makanan' ? 'selected' : '' ?>>Makanan</option>
                        </select>
                        
                        <input type="number" name="harga" placeholder="Harga" step="100" 
                               class="w-full p-2 border rounded" 
                               value="<?= $edit_product['harga'] ?? '' ?>" required>
                        
                        <input type="number" name="stok" placeholder="Stok" 
                               class="w-full p-2 border rounded" 
                               value="<?= $edit_product['stok'] ?? '' ?>" required>
                    </div>
                    
                    <div class="space-y-2">
                        <textarea name="deskripsi" placeholder="Deskripsi" 
                                  class="w-full p-2 border rounded h-32" 
                                  required><?= $edit_product['deskripsi'] ?? '' ?></textarea>
                        
                        <input type="file" name="gambar" class="w-full p-2 border rounded" <?= !$edit_product ? 'required' : '' ?>>
                        
                        <?php if ($edit_product && !empty($edit_product['gambar'])): ?>
                            <img src="<?= $edit_product['gambar'] ?>" class="w-32 h-32 object-cover">
                        <?php endif; ?>
                    </div>
                </div>
                
                <button type="submit" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    <?= $edit_product ? 'Update' : 'Simpan' ?> Produk
                </button>
                
                <?php if ($edit_product): ?>
                    <a href="?" class="ml-2 bg-gray-500 text-white px-4 py-2 rounded">Batal</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Filter -->
        <div class="bg-white p-4 rounded-lg shadow-md mb-6">
            <form method="GET" class="flex gap-4">
                <select name="kategori" class="p-2 border rounded flex-1">
                    <option value="">Semua Kategori</option>
                    <option value="Elektronik" <?= $selectedKategori == 'Elektronik' ? 'selected' : '' ?>>Elektronik</option>
                    <option value="Pakaian" <?= $selectedKategori == 'Pakaian' ? 'selected' : '' ?>>Pakaian</option>
                    <option value="Makanan" <?= $selectedKategori == 'Makanan' ? 'selected' : '' ?>>Makanan</option>
                </select>
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Filter</button>
            </form>
        </div>

        <!-- Daftar Produk -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php foreach ($products as $product): ?>
                <div class="bg-white p-4 rounded-lg shadow-md">
                    <img src="<?= $product['gambar'] ?>" alt="<?= $product['nama_produk'] ?>" class="w-full h-48 object-cover mb-4">
                    <h3 class="font-bold text-lg"><?= $product['nama_produk'] ?></h3>
                    <div class="text-gray-600 mb-2"><?= $product['kategori'] ?></div>
                    <div class="text-green-600 font-bold mb-2">Rp <?= number_format($product['harga'], 0, ',', '.') ?></div>
                    <p class="text-gray-700 mb-4"><?= nl2br($product['deskripsi']) ?></p>
                    <div class="flex justify-between items-center">
                        <div class="text-gray-500">Stok: <?= $product['stok'] ?></div>
                        <div class="space-x-2">
                            <a href="?tambah_keranjang=<?= $product['id'] ?>" class="bg-blue-600 text-white px-3 py-1 rounded">+ Keranjang</a>
                            <a href="?edit=<?= $product['id'] ?>" class="bg-yellow-600 text-white px-3 py-1 rounded">Edit</a>
                            <a href="?hapus=<?= $product['id'] ?>" class="bg-red-600 text-white px-3 py-1 rounded" 
                               onclick="return confirm('Yakin hapus produk?')">Hapus</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Keranjang Belanja -->
        <div class="bg-white p-6 rounded-lg shadow-md mt-6">
            <h2 class="text-xl font-bold mb-4">Keranjang Belanja</h2>
            <?php if (!empty($_SESSION["cart"])): ?>
                <?php foreach ($_SESSION["cart"] as $id => $qty): ?>
                    <?php $product = getProductById($id); ?>
                    <div class="flex items-center justify-between border-b py-2">
                        <div>
                            <span class="font-semibold"><?= $product['nama_produk'] ?></span>
                            <span class="text-gray-600">(x<?= $qty ?>)</span>
                        </div>
                        <div>
                            <span class="mr-4">Rp <?= number_format($product['harga'] * $qty, 0, ',', '.') ?></span>
                            <a href="?hapus_keranjang=<?= $id ?>" class="text-red-600">Hapus</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-gray-600">Keranjang belanja kosong</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>