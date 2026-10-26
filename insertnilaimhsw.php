<?php
// Hubungkan ke server database
require_once 'connection.php';

// Ambil data dari metode POST dengan aman
$nim = $_POST['nim'] ?? '';
$namalengkap = $_POST['namalengkap'] ?? '';
$nilaiakhir = $_POST['nilaiakhir'] ?? '';

// Validasi sisi server: pastikan field utama tidak kosong
if (empty(trim($nim)) || empty(trim($namalengkap)) || $nilaiakhir === '') {
    echo "Error: Semua kolom wajib diisi!";
    exit;
}

// Logika otomatis penentuan Nilai Huruf dan Predikat baru
$nilaiakhir_float = floatval($nilaiakhir);
$nilaihuruf = '';
$predikat = '';

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
$sql = "INSERT INTO nilaimhsw (nim, namalengkap, nilaiakhir, nilaihuruf, predikat) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("ssdss", $nim, $namalengkap, $nilaiakhir_float, $nilaihuruf, $predikat);
    
    if ($stmt->execute()) {
        echo "Data nilai mahasiswa berhasil ditambahkan!<br>";
        echo "<a href='formnilaimhsw.php'>Kembali ke Form</a>";
    } else {
        echo "Error saat menyimpan data: " . $stmt->error;
    }
    
    $stmt->close();
} else {
    echo "Error pada query: " . $conn->error;
}

// Tutup koneksi database
$conn->close();
?>