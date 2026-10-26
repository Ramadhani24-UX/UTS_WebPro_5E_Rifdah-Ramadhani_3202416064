<?php
// Panggil file koneksi database
require_once 'connection.php';

$pesan_sukses = "";

// --- PROSES PHP (TAMBAH, EDIT, HAPUS) DI SATU FILE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    // 1. TAMBAH DATA
    if ($aksi == 'tambah') {
        $nim = trim($_POST['nim'] ?? '');
        $namalengkap = trim($_POST['namalengkap'] ?? '');
        $nilaiakhir = $_POST['nilaiakhir'] ?? '';

        if (!empty($nim) && !empty($namalengkap) && $nilaiakhir !== '') {
            $nilaiakhir_float = floatval($nilaiakhir);

            // Hitung otomatis nilai huruf & predikat baru
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

            // Proses Upload Foto
            $photo_name = null;
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $file_tmp = $_FILES['photo']['tmp_name'];
                $file_ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
                
                if (in_array($file_ext, $allowed_ext)) {
                    $photo_name = 'foto_' . time() . '_' . rand(100, 999) . '.' . $file_ext;
                    if (!is_dir('uploads')) {
                        mkdir('uploads', 0777, true);
                    }
                    move_uploaded_file($file_tmp, 'uploads/' . $photo_name);
                }
            }

            $stmt = $conn->prepare("INSERT INTO nilaimhsw (nim, namalengkap, nilaiakhir, nilaihuruf, predikat, photo) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdsss", $nim, $namalengkap, $nilaiakhir_float, $nilaihuruf, $predikat, $photo_name);
            if ($stmt->execute()) {
                $pesan_sukses = "Data nilai mahasiswa <strong>{$namalengkap}</strong> (NIM: {$nim}) berhasil ditambahkan!";
            }
            $stmt->close();
        }
    }

    // 2. EDIT / UPDATE DATA
    elseif ($aksi == 'edit') {
        $nim_lama = trim($_POST['nim_lama'] ?? ''); // NIM sebelum diubah
        $nim_baru = trim($_POST['nim_baru'] ?? ''); // NIM baru
        $namalengkap = trim($_POST['namalengkap'] ?? '');
        $nilaiakhir = $_POST['nilaiakhir'] ?? '';

        if (!empty($nim_lama) && !empty($nim_baru) && !empty($namalengkap) && $nilaiakhir !== '') {
            $nilaiakhir_float = floatval($nilaiakhir);

            // Hitung ulang nilai huruf & predikat baru
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

            // Cek apakah ada upload foto baru
            $photo_query_part = "";
            $has_new_photo = false;
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $file_tmp = $_FILES['photo']['tmp_name'];
                $file_ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
                
                if (in_array($file_ext, $allowed_ext)) {
                    $photo_name = 'foto_' . time() . '_' . rand(100, 999) . '.' . $file_ext;
                    if (!is_dir('uploads')) {
                        mkdir('uploads', 0777, true);
                    }
                    if (move_uploaded_file($file_tmp, 'uploads/' . $photo_name)) {
                        $has_new_photo = true;
                        // Ambil foto lama untuk dihapus dari server
                        $q_old = $conn->prepare("SELECT photo FROM nilaimhsw WHERE nim = ?");
                        $q_old->bind_param("s", $nim_lama);
                        $q_old->execute();
                        $res_old = $q_old->get_result()->fetch_assoc();
                        if (!empty($res_old['photo']) && file_exists('uploads/' . $res_old['photo'])) {
                            unlink('uploads/' . $res_old['photo']);
                        }
                        $q_old->close();
                    }
                }
            }

            if ($has_new_photo) {
                $stmt = $conn->prepare("UPDATE nilaimhsw SET nim = ?, namalengkap = ?, nilaiakhir = ?, nilaihuruf = ?, predikat = ?, photo = ? WHERE nim = ?");
                $stmt->bind_param("ssdssss", $nim_baru, $namalengkap, $nilaiakhir_float, $nilaihuruf, $predikat, $photo_name, $nim_lama);
            } else {
                $stmt = $conn->prepare("UPDATE nilaimhsw SET nim = ?, namalengkap = ?, nilaiakhir = ?, nilaihuruf = ?, predikat = ? WHERE nim = ?");
                $stmt->bind_param("ssdsss", $nim_baru, $namalengkap, $nilaiakhir_float, $nilaihuruf, $predikat, $nim_lama);
            }

            if ($stmt->execute()) {
                $pesan_sukses = "Data nilai mahasiswa <strong>{$namalengkap}</strong> (NIM: {$nim_baru}) berhasil diperbarui!";
            }
            $stmt->close();
        }
    }

    // 3. HAPUS DATA
    elseif ($aksi == 'hapus') {
        $nim = trim($_POST['nim'] ?? '');
        if (!empty($nim)) {
            // Ambil nama dan foto dulu untuk dihapus file fisiknya
            $stmt_sel = $conn->prepare("SELECT namalengkap, photo FROM nilaimhsw WHERE nim = ?");
            $stmt_sel->bind_param("s", $nim);
            $stmt_sel->execute();
            $row_del = $stmt_sel->get_result()->fetch_assoc();
            $nama_del = $row_del ? $row_del['namalengkap'] : '';
            if (!empty($row_del['photo']) && file_exists('uploads/' . $row_del['photo'])) {
                unlink('uploads/' . $row_del['photo']);
            }
            $stmt_sel->close();

            $stmt = $conn->prepare("DELETE FROM nilaimhsw WHERE nim = ?");
            $stmt->bind_param("s", $nim);
            if ($stmt->execute()) {
                $pesan_sukses = "Data nilai mahasiswa <strong>{$nama_del}</strong> (NIM: {$nim}) berhasil dihapus!";
            }
            $stmt->close();
        }
    }
}

// --- AMBIL DATA UNTUK DITAMPILKAN, PENCARIAN, FILTER, SORTING & PAGINATION ---
$search = $_GET['search'] ?? '';
$predikat_filter = $_GET['predikat'] ?? '';
$order_nilai = $_GET['order_nilai'] ?? 'ASC'; // Default sorting Nilai Akhir
$order_nilai = ($order_nilai === 'DESC') ? 'DESC' : 'ASC';

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

$sql_count = "SELECT COUNT(*) as total FROM nilaimhsw WHERE 1=1";
if (!empty($search)) {
    $search_esc = $conn->real_escape_string($search);
    $sql_count .= " AND namalengkap LIKE '%$search_esc%'";
}
if (!empty($predikat_filter)) {
    $predikat_esc = $conn->real_escape_string($predikat_filter);
    $sql_count .= " AND predikat = '$predikat_esc'";
}
$total_data = $conn->query($sql_count)->fetch_assoc()['total'];
$total_pages = ceil($total_data / $limit);

$sql = "SELECT nim, namalengkap, nilaiakhir, nilaihuruf, predikat, photo, created_date, modified_date FROM nilaimhsw WHERE 1=1";
if (!empty($search)) {
    $sql .= " AND namalengkap LIKE '%$search_esc%'";
}
if (!empty($predikat_filter)) {
    $sql .= " AND predikat = '$predikat_esc'";
}
// Terapkan Order By khusus Nilai Akhir secara dinamis (ASC / DESC)
$sql .= " ORDER BY nilaiakhir $order_nilai LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

$showing_from = $total_data > 0 ? $offset + 1 : 0;
$showing_to = min($offset + $limit, $total_data);

// Toggle URL Sorting untuk Nilai Akhir
$next_order = ($order_nilai === 'ASC') ? 'DESC' : 'ASC';
$sort_icon = ($order_nilai === 'ASC') ? '<i class="fa-solid fa-arrow-up-short-wide"></i>' : '<i class="fa-solid fa-arrow-down-wide-short"></i>';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Nilai Mahasiswa</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- SweetAlert2 & FontAwesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #1a1d20;
            color: #e9ecef;
            padding: 20px;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #2c3237;
            padding-bottom: 15px;
        }

        h2 {
            margin: 0;
            color: #f8f9fa;
            font-size: 24px;
        }

        .breadcrumb {
            color: #adb5bd;
            font-size: 14px;
            margin: 0;
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .search-input,
        .filter-select {
            background-color: #2b3035;
            border: 1px solid #495057;
            color: #fff;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
        }

        .search-input::placeholder {
            color: #adb5bd;
        }

        .card-table {
            background-color: #212529;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            border: 1px solid #2c3237;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            color: #e9ecef;
        }

        th,
        td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #2c3237;
            font-size: 14px;
            vertical-align: middle;
        }

        th {
            background-color: #2b3035;
            color: #ced4da;
            text-transform: uppercase;
            font-size: 12px;
        }

        tr:hover {
            background-color: #2c3237;
        }

        .card-footer {
            padding: 15px 20px;
            background-color: #212529;
            border-top: 1px solid #2c3237;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .app-footer {
            margin-top: 30px;
            padding: 15px 0;
            border-top: 1px solid #2c3237;
            display: flex;
            justify-content: space-between;
            color: #adb5bd;
            font-size: 13px;
        }

        .app-footer a {
            color: #3b82f6;
            text-decoration: none;
        }

        /* Modal Dark Theme Adjustments */
        .modal-content {
            background-color: #212529;
            color: #e9ecef;
            border: 1px solid #495057;
        }

        .modal-header,
        .modal-footer {
            border-color: #2c3237;
        }

        .form-control,
        .form-select {
            background-color: #2b3035;
            border-color: #495057;
            color: #fff;
        }

        .form-control::placeholder {
            color: #6c757d;
            opacity: 1;
        }

        .form-control:focus,
        .form-select:focus {
            background-color: #2b3035;
            color: #fff;
            border-color: #3b82f6;
            box-shadow: none;
        }
    </style>
</head>

<body>

    <div class="header-container">
        <h2>Rekap Nilai Mahasiswa</h2>
        <div class="breadcrumb">Beranda / Nilai</div>
    </div>

    <!-- Notifikasi Sukses Berdurasi 5 Detik -->
    <?php if (!empty($pesan_sukses)): ?>
        <div class="alert alert-success" id="alert-box"
            style="background-color: #064e3b; color: #a7f3d0; border: 1px solid #047857;">
            <?php echo $pesan_sukses; ?>
        </div>
    <?php endif; ?>

    <!-- Toolbar -->
    <div class="toolbar">
        <div style="font-size: 16px; color: #ced4da; font-weight: 500;">Direktori Nilai</div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <form method="GET" action="" style="display: flex; gap: 10px; margin: 0; align-items: center;">
                <input type="text" name="search" class="search-input" placeholder="🔍 Cari Nama"
                    value="<?php echo htmlspecialchars($search); ?>">

                <!-- Tombol Reset akan muncul jika sedang melakukan pencarian atau filter -->
                <?php if (!empty($search) || !empty($predikat_filter)): ?>
                    <a href="readnilaimhsw.php" class="btn btn-outline-secondary btn-sm" title="Reset Pencarian"
                        style="padding: 8px 12px; color: #ced4da; border-color: #495057;">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                <?php endif; ?>

                <select name="predikat" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Predikat</option>
                    <option value="Sangat Memuaskan" <?php if ($predikat_filter == 'Sangat Memuaskan') echo 'selected'; ?>>Sangat Memuaskan</option>
                    <option value="Memuaskan" <?php if ($predikat_filter == 'Memuaskan') echo 'selected'; ?>>Memuaskan</option>
                    <option value="Kurang Memuaskan" <?php if ($predikat_filter == 'Kurang Memuaskan') echo 'selected'; ?>>Kurang Memuaskan</option>
                    <option value="Tidak Lulus" <?php if ($predikat_filter == 'Tidak Lulus') echo 'selected'; ?>>Tidak Lulus</option>
                </select>

                <!-- Pertahankan nilai order saat form search/filter dikirim -->
                <input type="hidden" name="order_nilai" value="<?php echo htmlspecialchars($order_nilai); ?>">
            </form>

            <!-- Tombol Pemicu Modal Tambah -->
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah"
                style="background-color: #2563eb; border: none;">
                <i class="fa-solid fa-plus"></i> Tambah Data Nilai
            </button>
        </div>
    </div>

    <!-- Tabel Data -->
    <div class="card-table">
        <table>
            <thead>
                <tr>
                    <th>NIM</th>
                    <th>Nama Lengkap (Pengguna & Foto)</th>
                    <!-- Kolom Nilai Akhir dilengkapi Sorting (Ascending/Descending) -->
                    <th>
                        <a href="?search=<?php echo urlencode($search); ?>&predikat=<?php echo urlencode($predikat_filter); ?>&order_nilai=<?php echo $next_order; ?>" style="color: inherit; text-decoration: none; display: flex; align-items: center; gap: 5px;">
                            Nilai Akhir <?php echo $sort_icon; ?>
                        </a>
                    </th>
                    <th>Nilai Huruf</th>
                    <th>Predikat</th>
                    <th>Tgl Dibuat</th>
                    <th>Tgl Diubah</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row["nim"]) . "</td>";
                        
                        // Kolom Foto & Nama Berdampingan secara Dinamis di sebelah kiri nama
                        echo "<td>";
                        echo "<div style='display: flex; align-items: center; gap: 12px;'>";
                        $foto_src = (!empty($row["photo"]) && file_exists('uploads/' . $row["photo"])) ? 'uploads/' . $row["photo"] : 'https://via.placeholder.com/40';
                        echo "<img src='" . htmlspecialchars($foto_src) . "' alt='Foto' style='width: 40px; height: 40px; object-fit: cover; border-radius: 50%; border: 1px solid #495057;'>";
                        echo "<span>" . htmlspecialchars($row["namalengkap"]) . "</span>";
                        echo "</div>";
                        echo "</td>";

                        echo "<td>" . htmlspecialchars($row["nilaiakhir"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["nilaihuruf"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["predikat"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["created_date"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["modified_date"]) . "</td>";
                        echo "<td class=\"text-center\">";
                        echo "<button type='button' class='btn btn-secondary btn-sm' onclick=\"bukaModalEdit('" . htmlspecialchars($row["nim"]) . "', '" . htmlspecialchars($row["namalengkap"]) . "', '" . htmlspecialchars($row["nilaiakhir"]) . "')\" title='Edit'><i class='fa-solid fa-pen-to-square'></i></button> ";
                        echo "<button type='button' class='btn btn-danger btn-sm' onclick=\"konfirmasiHapus('" . htmlspecialchars($row["nim"]) . "', '" . htmlspecialchars($row["namalengkap"]) . "')\" title='Hapus'><i class='fa-solid fa-trash'></i></button>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='8' class='text-center' style='padding: 30px; color: #adb5bd;'>Tidak ada data nilai mahasiswa.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Footer Pagination -->
        <div class="card-footer">
            <div style="color: #adb5bd; font-size: 14px;">Showing <?php echo $showing_from; ?> to
                <?php echo $showing_to; ?> of <?php echo $total_data; ?> mahasiswa</div>
            <?php if ($total_pages > 1): ?>
                <ul class="pagination pagination-sm m-0">
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link bg-dark text-light border-secondary"
                            href="?page=<?php echo max(1, $page - 1); ?>&search=<?php echo urlencode($search); ?>&predikat=<?php echo urlencode($predikat_filter); ?>&order_nilai=<?php echo $order_nilai; ?>">&laquo;</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                            <a class="page-link bg-dark text-light border-secondary"
                                href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&predikat=<?php echo urlencode($predikat_filter); ?>&order_nilai=<?php echo $order_nilai; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link bg-dark text-light border-secondary"
                            href="?page=<?php echo min($total_pages, $page + 1); ?>&search=<?php echo urlencode($search); ?>&predikat=<?php echo urlencode($predikat_filter); ?>&order_nilai=<?php echo $order_nilai; ?>">&raquo;</a>
                    </li>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer Bawah -->
    <footer class="app-footer">
        <div><strong>Copyright &copy; 2014-2026 <a href="https://adminlte.io">AdminLTE.io</a>.</strong> All rights reserved.</div>
        <div>Anything you want</div>
    </footer>


    <!-- ================= MODAL TAMBAH DATA (DENGAN INPUT FOTO) ================= -->
    <div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="aksi" value="tambah">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTambahLabel"><i class="fa-solid fa-plus me-2"></i>Tambah Data Nilai</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nim_tambah" class="form-label">NIM <span style="color: red;">*</span></label>
                            <input type="text" class="form-control" id="nim_tambah" name="nim" maxlength="10"
                                placeholder="Contoh: 3202416064"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                        </div>
                        <div class="mb-3">
                            <label for="nama_tambah" class="form-label">Nama Lengkap <span style="color: red;">*</span></label>
                            <input type="text" class="form-control" id="nama_tambah" name="namalengkap" maxlength="50"
                                placeholder="Contoh: Rifdah Ramadhani" required>
                        </div>
                        <div class="mb-3">
                            <label for="nilai_tambah" class="form-label">Nilai Akhir (1 - 100) <span style="color: red;">*</span></label>
                            <input type="number" step="0.01" min="1" max="100" class="form-control" id="nilai_tambah"
                                name="nilaiakhir" placeholder="Contoh: 85.5" required>
                        </div>
                        <div class="mb-3">
                            <label for="photo_tambah" class="form-label">Foto Profil (JPG/PNG)</label>
                            <input type="file" class="form-control" id="photo_tambah" name="photo" accept=".jpg, .jpeg, .png, .webp">
                        </div>

                        <!-- Keterangan wajib diisi -->
                        <div class="text-danger small mt-3">
                            *wajib diisi
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- ================= MODAL EDIT DATA (DENGAN INPUT FOTO) ================= -->
    <div class="modal fade" id="modalEdit" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="aksi" value="edit">
                    <!-- Menyimpan NIM lama untuk acuan database -->
                    <input type="hidden" name="nim_lama" id="nim_lama">

                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditLabel"><i class="fa-solid fa-pen-to-square me-2"></i>Edit Data Nilai</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nim_edit" class="form-label">NIM <span style="color: red;">*</span></label>
                            <input type="text" class="form-control" id="nim_edit" name="nim_baru" maxlength="10"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                        </div>
                        <div class="mb-3">
                            <label for="nama_edit" class="form-label">Nama Lengkap <span style="color: red;">*</span></label>
                            <input type="text" class="form-control" id="nama_edit" name="namalengkap" maxlength="50"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="nilai_edit" class="form-label">Nilai Akhir (1 - 100) <span style="color: red;">*</span></label>
                            <input type="number" step="0.01" min="1" max="100" class="form-control" id="nilai_edit"
                                name="nilaiakhir" required>
                        </div>
                        <div class="mb-3">
                            <label for="photo_edit" class="form-label">Ganti Foto Profil (Opsional)</label>
                            <input type="file" class="form-control" id="photo_edit" name="photo" accept=".jpg, .jpeg, .png, .webp">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Perbarui Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- ================= FORM TERSEMBUNYI UNTUK HAPUS ================= -->
    <form id="formHapus" method="POST" action="" style="display: none;">
        <input type="hidden" name="aksi" value="hapus">
        <input type="hidden" name="nim" id="nim_hapus">
    </form>


    <!-- JS Bootstrap & SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function bukaModalEdit(nim, nama, nilai) {
            document.getElementById('nim_lama').value = nim;
            document.getElementById('nim_edit').value = nim;
            document.getElementById('nama_edit').value = nama;
            document.getElementById('nilai_edit').value = nilai;

            var myModal = new bootstrap.Modal(document.getElementById('modalEdit'));
            myModal.show();
        }

        function konfirmasiHapus(nim, nama) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data mahasiswa " + nama + " (NIM: " + nim + ") akan dihapus permanen!",
                icon: 'warning',
                background: '#212529',
                color: '#fff',
                showCancelButton: true,
                confirmButtonColor: '#b91c1c',
                cancelButtonColor: '#495057',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('nim_hapus').value = nim;
                    document.getElementById('formHapus').submit();
                }
            })
        }

        window.addEventListener('DOMContentLoaded', (event) => {
            const alertBox = document.getElementById('alert-box');
            if (alertBox) {
                setTimeout(() => {
                    alertBox.style.opacity = '0';
                    setTimeout(() => {
                        alertBox.style.display = 'none';
                    }, 500);
                }, 5000);
            }
        });
    </script>
</body>

</html>

<?php
$conn->close();
?>