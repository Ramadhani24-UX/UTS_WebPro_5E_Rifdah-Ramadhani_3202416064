<?php
// Panggil file koneksi database
require_once 'connection.php';

$pesan = "";
$nim_lama = "";
$namalengkap = "";
$nilaiakhir = "";

// 1. Jika form disubmit (proses update data ke database)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nim_lama = trim($_POST['nim_lama'] ?? '');
    $nim_baru = trim($_POST['nim_baru'] ?? '');
    $namalengkap = trim($_POST['namalengkap'] ?? '');
    $nilaiakhir = $_POST['nilaiakhir'] ?? '';

    // Validasi input tidak boleh kosong
    if (!empty($nim_lama) && !empty($nim_baru) && !empty($namalengkap) && $nilaiakhir !== '') {
        $nilaiakhir_float = floatval($nilaiakhir);

        // Logika otomatis penentuan Nilai Huruf dan Predikat baru
        if ($nilaiakhir_float >= 85) {
            $nilaihuruf = 'A';
            $predikat = 'Sangat Memuaskan';
        } elseif ($nilaiakhir_float >= 70) {
            $nilaihuruf = 'B';
            $predikat = 'Memuaskan';
        } elseif ($nilaiakhir_float >= 55) {
            $nilaihuruf = 'C';
            $predikat = 'Kurang Memuaskan';
        } else {
            $nilaihuruf = 'E';
            $predikat = 'Tidak Lulus';
        }

        // Gunakan Prepared Statement untuk keamanan terhadap SQL Injection
        $stmt = $conn->prepare("UPDATE nilaimhsw SET nim = ?, namalengkap = ?, nilaiakhir = ?, nilaihuruf = ?, predikat = ? WHERE nim = ?");
        $stmt->bind_param("ssdsss", $nim_baru, $namalengkap, $nilaiakhir_float, $nilaihuruf, $predikat, $nim_lama);

        if ($stmt->execute()) {
            $pesan = "Data nilai mahasiswa berhasil diperbarui!<br><a href='readnilaimhsw.php'>Kembali ke Rekap Nilai</a>";
        } else {
            $pesan = "Error saat memperbarui data: " . $stmt->error;
        }
        $stmt->close();
        
        // Perbarui nim_lama agar form tetap memegang NIM aktif
        $nim_lama = $nim_baru;
    } else {
        $pesan = "Error: Semua kolom wajib diisi!";
    }
} 
// 2. Jika diakses pertama kali melalui URL dengan parameter GET (e.g. editnilaimhsw.php?nim=3202416064)
else {
    $nim = $_GET['nim'] ?? '';
    if (!empty($nim)) {
        $stmt = $conn->prepare("SELECT nim, namalengkap, nilaiakhir FROM nilaimhsw WHERE nim = ?");
        $stmt->bind_param("s", $nim);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $nim_lama = $row['nim'];
            $namalengkap = $row['namalengkap'];
            $nilaiakhir = $row['nilaiakhir'];
        } else {
            echo "Data mahasiswa tidak ditemukan!";
            exit;
        }
        $stmt->close();
    } else {
        echo "NIM tidak valid!";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Edit Nilai Mahasiswa</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            background-color: #f9f9f9;
            color: #333;
        }
        .container {
            max-width: 400px;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h2 {
            color: #2c3e50;
            margin-top: 0;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input[type="submit"] {
            background-color: #2c3e50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #34495e;
        }
        .msg {
            margin-bottom: 15px;
            padding: 10px;
            background: #e2f0d9;
            border: 1px solid #c8e6c9;
            border-radius: 4px;
            color: #2e7d32;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Form Edit Nilai Mahasiswa</h2>
    
    <?php if (!empty($pesan)): ?>
        <div class="msg"><?php echo $pesan; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <!-- Menyimpan NIM lama sebagai acuan WHERE di query database -->
        <input type="hidden" name="nim_lama" value="<?php echo htmlspecialchars($nim_lama); ?>">
        
        <label for="nim_baru">NIM:</label>
        <input type="text" id="nim_baru" name="nim_baru" maxlength="10" value="<?php echo htmlspecialchars($nim_lama); ?>" required>
        
        <label for="namalengkap">Nama Lengkap:</label>
        <input type="text" id="namalengkap" name="namalengkap" maxlength="50" value="<?php echo htmlspecialchars($namalengkap); ?>" required>
        
        <label for="nilaiakhir">Nilai Akhir (1 - 100):</label>
        <input type="number" id="nilaiakhir" name="nilaiakhir" step="0.01" min="1" max="100" value="<?php echo htmlspecialchars($nilaiakhir); ?>" required>
        
        <input type="submit" value="Perbarui Data">
    </form>
    
    <br>
    <a href="readnilaimhsw.php">&laquo; Kembali ke Rekap Nilai</a>
</div>

</body>
</html>

<?php
// Tutup koneksi database
$conn->close();
?>