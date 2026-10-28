<?php
session_start();
require_once "koneksi.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$order_id = (int) $_GET['id'];

// JOIN orders + books + customers
$stmt = $conn->prepare("
    SELECT 
        orders.*,
        books.title AS book_title,
        books.author_name,
        books.file_path,
        books.jumlah_halaman,
        customers.customer_name AS customer_name,
        customers.email,
        customers.phone,
        customers.address
    FROM orders
    JOIN books ON orders.book_id = books.id
    JOIN customers ON orders.customer_id = customers.id
    WHERE orders.id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Pesanan tidak ditemukan.";
    exit();
}

$data = $result->fetch_assoc();

// Handle perubahan status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ubah_status'])) {
    $status_baru = $_POST['status'];
    $update = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $update->bind_param("si", $status_baru, $order_id);
    $update->execute();
    header("Location: detail_pesanan_admin.php?id=$order_id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Penerbitan Penulis - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --secondary: #10b981;
            --text: #111827;
            --text-light: #6b7280;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9fafb;
            color: var(--text);
        }

        .container {
            padding: 30px;
        }

        h2 {
            text-align: center;
            color: var(--primary);
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
        }

        th {
            background: #eee;
        }

        a {
            color: var(--primary);
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .status-form {
            display: flex;
            align-items: center;
        }

        .status-form select {
            margin-right: 10px;
            padding: 5px;
        }

        .btn {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            background-color: var(--primary);
            color: white;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: var(--primary-hover);
        }

        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
        }

        .action-buttons {
        display: flex;
        justify-content: center;
        margin-top: 40px; /* Memberi jarak dari tabel */
        gap: 20px; /* Jarak antar tombol */
        }

        .logo-wrapper {
        text-align: center;
        margin-bottom: 20px;
        }

        .logo-img {
        width: 150px;
        max-width: 100%;
        height: auto;
        }

        .btn-ubah {
        background-color: #3b82f6; /* biru */
        }

        .btn-ubah:hover {
            background-color: #2563eb;
        }

        .btn-cetak {
            background-color: #10b981; /* hijau */
        }

        .btn-cetak:hover {
            background-color: #059669;
        }

        .btn-kembali {
            background-color: #f59e0b; /* oranye */
        }

        .btn-kembali:hover {
            background-color: #d97706;
        }

    </style>
</head>
<body>
    <div class="container">
        <div class="logo-wrapper">
        <img src="images/logo.png" alt="Logo Perusahaan" class="logo-img">
    </div>
        <h2>Detail Penerbitan Penulis</h2>
        <table>
            <tr><td><strong>Nama Customer</strong></td><td><?= htmlspecialchars($data['customer_name']) ?></td></tr>
            <tr><td><strong>Nama Penulis</strong></td><td><?= htmlspecialchars($data['author_name']) ?></td></tr>
            <tr><td><strong>Email</strong></td><td><?= htmlspecialchars($data['email']) ?></td></tr>
            <tr><td><strong>Telepon</strong></td><td><?= htmlspecialchars($data['phone']) ?></td></tr>
            <tr><td><strong>Alamat</strong></td><td><?= htmlspecialchars($data['address']) ?></td></tr>
            <tr><td><strong>Judul Buku</strong></td><td><?= htmlspecialchars($data['book_title']) ?></td></tr>
            <tr>
                <td><strong>File Buku</strong></td>
                <td>
                    <?php if (!empty($data['file_path'])): ?>
                        <a href="<?= htmlspecialchars($data['file_path']) ?>" target="_blank">Lihat File</a>
                    <?php else: ?>
                        Tidak ada file
                    <?php endif; ?>
                </td>
            </tr>
            <tr><td><strong>Jumlah Halaman</strong></td><td><?= $data['jumlah_halaman'] ?></td></tr>
            <tr><td><strong>Paket</strong></td><td><?= htmlspecialchars($data['package_type']) ?></td></tr>
            <tr><td><strong>Jumlah Cetak</strong></td><td><?= $data['jumlah_cetak'] ?></td></tr>
            <tr><td><strong>HKI</strong></td><td><?= $data['hki'] ? 'Ya' : 'Tidak' ?></td></tr>
            <tr><td><strong>Keaslian Karya</strong></td><td><?= $data['originality_verified'] ? 'Sudah Ada' : 'Belum Ada' ?></td></tr>
            <tr><td><strong>Catatan Penerbitan</strong></td><td><?= htmlspecialchars($data['order_note']) ?: '-' ?></td></tr>
            <tr><td><strong>Total Harga</strong></td><td>Rp <?= number_format($data['total_pemesanan'], 0, ',', '.') ?></td></tr>
            <tr><td><strong>Pembayaran</strong></td><td><?= htmlspecialchars($data['payment_method']) ?: '-' ?></td></tr>
            <tr>
                <td><strong>Status Penerbitan</strong></td>
                <td>
                    <form method="POST" class="status-form">
                        <select name="status">
                            <option value="menunggu" <?= $data['status'] === 'menunggu' ? 'selected' : '' ?>>Menunggu</option>
                            <option value="direview" <?= $data['status'] === 'direview' ? 'selected' : '' ?>>Sedang Direview</option>
                            <option value="proses terbit" <?= $data['status'] === 'proses terbit' ? 'selected' : '' ?>>Proses Penerbitan</option>
                            <option value="selesai" <?= $data['status'] === 'selesai' ? 'selected' : '' ?>>Sudah Terbit</option>
                        </select>
                        <button type="submit" name="ubah_status" class="btn btn-ubah">Ubah Status</button>
                    </form>
                </td>
            </tr>
        </table>

        <div class="action-buttons">
            
            <a href="admin.php" class="btn btn-kembali">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
</body>
</html>
