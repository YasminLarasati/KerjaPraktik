<?php
session_start();
require_once "koneksi.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pengguna') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = (int) $_GET['id'];

// Join orders + books + customers
$stmt = $conn->prepare("
    SELECT orders.id AS order_id, books.id AS book_id, orders.*, books.*, customers.*
    FROM orders
    JOIN books ON orders.book_id = books.id
    JOIN customers ON orders.customer_id = customers.id
    WHERE orders.id = ? AND customers.user_id = ?
");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Pesanan tidak ditemukan.");
}

$data = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sudah_bayar'])) {
    $update = $conn->prepare("
        UPDATE orders 
        JOIN customers ON orders.customer_id = customers.id 
        SET orders.payment_method = 'Sudah Dibayar'
        WHERE orders.id = ? AND customers.user_id = ?
    ");
    $update->bind_param("ii", $order_id, $user_id);
    if ($update->execute()) {
        header("Location: detail_pesanan_pelanggan.php?id=$order_id");
        exit;
    } else {
        echo "Gagal update: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --secondary: #10b981;
            --secondary-hover: #0d9f6e;
            --danger: #ef4444;
            --danger-hover: #dc2626;
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

        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: white;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 20px 0;
        }

        .brand {
            padding: 0 20px 30px;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 20px;
        }

        .brand h2 {
            margin: 0;
            color: var(--primary);
            font-size: 1.5rem;
        }

        .nav-item {
            padding: 12px 20px;
            margin: 5px 0;
            display: flex;
            align-items: center;
            color: var(--text-light);
            text-decoration: none;
            transition: all 0.2s;
            border-left: 4px solid transparent;
        }

        .nav-item:hover {
            background-color: var(--secondary);
            color: var(--text);
            border-left-color: var(--primary);
        }

        .nav-item.active {
            background-color: var(--secondary);
            color: var(--text);
            border-left-color: var(--primary);
        }

        .nav-item i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
        }

        .main-content {
            flex: 1;
            padding: 30px;
        }

        h2 {
            margin-bottom: 20px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }

        th, td {
            padding: 10px;
            border: 1px solid #999;
            text-align: left;
        }

        th {
            background: #eee;
        }

        .payment-info {
            margin-top: 20px;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }

        .action-buttons {
            margin-top: 30px;
            display: flex;
            gap: 15px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
        }

        .btn-secondary {
            background-color: var(--secondary);
            color: white;
        }

        .btn-secondary:hover {
            background-color: var(--secondary-hover);
        }

        .btn i {
            margin-right: 8px;
        }

        .logout-btn {
            background-color: var(--danger);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.2s;
            display: flex;
            align-items: center;
            margin-top: auto;
            margin-left: 20px;
            margin-bottom: 20px;
        }

        .logout-btn:hover {
            background-color: var(--danger-hover);
        }

        .logout-btn i {
            margin-right: 8px;
        }

        .brand {
        padding: 0 20px 30px;
        border-bottom: 1px solid #e5e7eb;
        margin-bottom: 20px;
        text-align: center;
        }

        .logo {
        width: 100px;      /* Lebih besar dari sebelumnya */
        height: auto;
        object-fit: contain;
        margin-bottom: 10px;
        display: block;
        margin-left: auto;
        margin-right: auto;
        }

        .btn-warning {
            background-color: #f59e0b; /* oranye */
            color: white;
        }

        .btn-warning:hover {
            background-color: #d97706;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="sidebar">
            <div class="brand">
                <img src="images/logo.png" alt="Logo Perusahaan" class="logo">
                <h2>Global Aksara Pers</h2>
            </div>
            
        </div>

        <div class="main-content">
            <h2>Detail Penerbitan</h2>
            <table>
                <tr><td><strong>Nama Pemesan</strong></td><td><?= htmlspecialchars($data['customer_name']) ?></td></tr>
                <tr><td><strong>Nama Penulis</strong></td><td><?= htmlspecialchars($data['author_name']) ?></td></tr>
                <tr><td><strong>Email</strong></td><td><?= htmlspecialchars($data['email']) ?></td></tr>
                <tr><td><strong>Telepon</strong></td><td><?= htmlspecialchars($data['phone']) ?></td></tr>
                <tr><td><strong>Alamat</strong></td><td><?= htmlspecialchars($data['address']) ?></td></tr>
                <tr><td><strong>Judul Buku</strong></td><td><?= htmlspecialchars($data['title']) ?></td></tr>
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
                <tr><td><strong>Total Harga</strong></td><td>Rp <?= number_format($data['total_pemesanan'], 0, ',', '.') ?></td></tr>
                <tr><td><strong>Status Pembayaran</strong></td><td><?= htmlspecialchars($data['payment_method']) ?: '-' ?></td></tr>
                <tr><td><strong>Status Penerbitan</strong></td>
                    <td>
                        <?php
                        switch ($data['status']) {
                            case 'menunggu': echo 'Menunggu'; break;
                            case 'direview': echo 'Sedang Direview'; break;
                            case 'proses terbit': echo 'Proses Penerbitan'; break;
                            case 'selesai': echo 'Sudah Terbit'; break;
                            default: echo 'Status tidak diketahui'; break;
                        }
                        ?>
                    </td>
                </tr>
            </table>

            <div class="payment-info">
                <h3>Pembayaran</h3>
                <p>Silakan transfer ke rekening berikut:</p>
                <ul>
                    <li>Bank BNI - 1234567890 - CV. Global Aksara Pers</li>
                </ul>

                <?php if (!$data['payment_method']) { ?>
                <form method="POST">
                    <label><input type="checkbox" name="sudah_bayar" value="1" required> Saya sudah melakukan pembayaran</label><br><br>
                    <button type="submit" class="btn btn-primary">Konfirmasi Pembayaran</button>
                </form>
                <?php } else { ?>
                    <p><strong>Status:</strong> Sudah dikonfirmasi</p>
                <?php } ?>
            </div>

            <div class="action-buttons">
                <a href="nota.php?id=<?= $data['order_id'] ?>" target="_blank" class="btn btn-secondary">
                    <i class="fas fa-print"></i> Cetak Nota
                </a>

  <br><a href="daftar_pesanan_pelanggan.php" class="btn btn-warning">Kembali</a>
</body>
</html>
