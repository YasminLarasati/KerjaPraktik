<?php
session_start();
require_once "koneksi.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pengguna') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil customer_id
$customerQuery = $conn->prepare("SELECT id FROM customers WHERE user_id = ?");
$customerQuery->bind_param("i", $user_id);
$customerQuery->execute();
$customerResult = $customerQuery->get_result();
$customer = $customerResult->fetch_assoc();

$customer_id = $customer['id'] ?? 0;

// Query orders JOIN books
$stmt = $conn->prepare("
    SELECT orders.id AS order_id, books.title AS book_title, books.author_name, orders.status
    FROM orders
    JOIN books ON orders.book_id = books.id
    WHERE orders.customer_id = ?
    ORDER BY orders.id DESC
");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Anda</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --secondary: #f3f4f6;
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

        .status {
            font-weight: bold;
        }

        .status.menunggu {
            color: grey;
        }


        .status.diproses {
            color: blue;
        }

        .status.selesai {
            color: green;
        }

        .action-buttons {
            margin-top: 20px;
        }

        .btn {
            padding: 5px 10px; /* Mengurangi padding untuk ukuran lebih kecil */
            border-radius: 4px; /* Mengubah radius untuk tampilan lebih kecil */
            text-decoration: none;
            font-weight: 500;
            background-color: var(--primary);
            color: white;
            transition: all 0.2s;
        }

        .btn:hover {
            background-color: var(--primary-hover);
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
            <a href="dashboard_pelanggan.php" class="nav-item">
                <i class="fas fa-home"></i> Dashboard
            </a>
           
            <a href="pesanan_pengguna.php" class="nav-item active">
                <i class="fas fa-list"></i> Daftar Penerbitan
            </a>
            <button class="logout-btn" onclick="window.location.href='logout.php'">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </div>

        <div class="main-content">
            <h2>Daftar Penerbitan Anda</h2>

            <table>
                <tr>
                    <th>Judul Buku</th>
                    <th>Nama Penulis</th>
                    <th>Status</th>
                    <th>Detail</th>
                </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['book_title']) ?></td>
        <td><?= htmlspecialchars($row['author_name']) ?></td>
        <td class="status <?= htmlspecialchars($row['status']) ?>">
            <?php
                switch ($row['status']) {
                    case 'menunggu': echo 'Menunggu'; break;
                    case 'direview': echo 'Sedang Direview'; break;
                    case 'proses terbit': echo 'Proses Penerbitan'; break;
                    case 'selesai': echo 'Sudah Terbit'; break;
                    default: echo 'Tidak diketahui';
                }
            ?>
        </td>
        <td><a href="detail_pesanan_pelanggan.php?id=<?= $row['order_id'] ?>" class="btn">Lihat Detail</a></td>
      </tr>
    <?php endwhile; ?>
  </table>
  <div class="action-buttons">
                <a href="dashboard_pelanggan.php" class="btn btn-warning">
                    <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                </a>
            </div>
</body>
</html>
