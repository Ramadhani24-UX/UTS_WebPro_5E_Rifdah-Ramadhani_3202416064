<?php
// Hubungkan ke server database melalui file connection.php
require_once 'connection.php';

// Query SQL untuk membuat tabel nilaimhsw dengan kolom-kolom yang sesuai
$sql = "CREATE TABLE IF NOT EXISTS nilaimhsw (
    nim CHAR(10) PRIMARY KEY,
    namalengkap VARCHAR(50) NOT NULL,
    nilaiakhir DECIMAL(5,2) NOT NULL,
    nilaihuruf CHAR(1),
    predikat VARCHAR(20),
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Tabel 'nilaimhsw' berhasil dibuat!";
} else {
    echo "Error saat membuat tabel: " . $conn->error;
}

// Tutup koneksi database
$conn->close();
?>