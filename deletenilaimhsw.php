<?php
// Hubungkan ke database
require_once 'connection.php';

$nim = $_GET['nim'] ?? '';

if (!empty($nim)) {
    // 1. Ambil data nama mahasiswa terlebih dahulu sebelum dihapus
    $sql_select = "SELECT namalengkap FROM nilaimhsw WHERE nim = ?";
    $stmt_select = $conn->prepare($sql_select);
    $stmt_select->bind_param("s", $nim);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    $row = $result->fetch_assoc();
    $nama = $row ? $row['namalengkap'] : 'Mahasiswa';
    $stmt_select->close();

    // 2. Jalankan proses hapus data
    $sql_delete = "DELETE FROM nilaimhsw WHERE nim = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    
    if ($stmt_delete) {
        $stmt_delete->bind_param("s", $nim);
        if ($stmt_delete->execute()) {
            $stmt_delete->close();
            $conn->close();
            // Redirect dengan membawa parameter status, nim, dan nama
            header("Location: readnilaimhsw.php?status=sukses_hapus&nim=" . urlencode($nim) . "&nama=" . urlencode($nama));
            exit;
        }
        $stmt_delete->close();
    }
}

$conn->close();
header("Location: readnilaimhsw.php");
exit;
?>