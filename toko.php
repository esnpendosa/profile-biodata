<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$error = "";
$nama_produk = $harga = $deskripsi = $stok = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validasi input
    if (empty($_POST["nama_produk"]) || empty($_POST["harga"]) || empty($_POST["deskripsi"]) || empty($_POST["stok"])) {
        $error = "Semua bidang harus diisi!";
    } else {
        // Sanitasi input
        $nama_produk = htmlspecialchars($_POST["nama_produk"]);
        $harga = (float)$_POST["harga"];
        $deskripsi = htmlspecialchars($_POST["deskripsi"]);
        $stok = (int)$_POST["stok"];

        // Koneksi database
        $conn = new mysqli("localhost", "root", "", "ecommerce");
        
        if ($conn->connect_error) {
            die("Koneksi database gagal: " . $conn->connect_error);
        }

        // Prepared statement
        $stmt = $conn->prepare("INSERT INTO products (nama_produk, harga, deskripsi, stok) VALUES (?, ?, ?, ?)");
        
        if (!$stmt) {
            die("Error preparing statement: " . $conn->error);
        }

        // Bind parameter (sesuaikan dengan tipe data di database)
        $stmt->bind_param("sdis", $nama_produk, $harga, $deskripsi, $stok);

        if ($stmt->execute()) {
            echo "<div style='color:green; padding:10px; border:1px solid green; margin:10px;'>
                    Produk berhasil ditambahkan!
                  </div>";
        } else {
            $error = "Error: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Produk</title>
    <style>
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            font-family: Arial, sans-serif;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .error {
            color: red;
            padding: 10px;
            border: 1px solid red;
            margin-bottom: 15px;
        }
        button {
            background: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Tambah Produk</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="form-group">
                <label>Nama Produk:</label>
                <input type="text" name="nama_produk" value="<?php echo htmlspecialchars($nama_produk); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Harga:</label>
                <input type="number" name="harga" step="100" value="<?php echo htmlspecialchars($harga); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Deskripsi:</label>
                <textarea name="deskripsi" required><?php echo htmlspecialchars($deskripsi); ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Stok:</label>
                <input type="number" name="stok" value="<?php echo htmlspecialchars($stok); ?>" required>
            </div>
            
            <button type="submit">Tambah Produk</button>
        </form>
    </div>
</body>
</html>