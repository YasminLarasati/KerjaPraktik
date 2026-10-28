<?php
session_start();
require_once "koneksi.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Ambil semua pesanan
$result = $conn->query("SELECT 
    orders.*,
    customers.customer_name AS customer_name,
    books.title AS book_title,
    books.author_name
  FROM orders
  JOIN customers ON orders.customer_id = customers.id
  JOIN books ON orders.book_id = books.id
  ORDER BY orders.created_at DESC");

// Hitung total pendapatan
$total_pendapatan = 0;
$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
    $total_pendapatan += $row['total_pemesanan'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Laporan Pesanan - Admin</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 40px;
      color: #111827;
      background: #fff;
    }

    h1 {
      text-align: center;
      margin-bottom: 30px;
      color: #4f46e5;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      font-size: 14px;
    }

    th, td {
      border: 1px solid #ccc;
      padding: 8px;
      text-align: left;
    }

    th {
      background: #eee;
    }

    .total {
      font-weight: bold;
    }

    .text-right {
      text-align: right;
    }

    .print-btn {
    margin: 40px 0;
    text-align: center;
    }

    .print-btn .btn {
    display: inline-block;
    padding: 12px 30px;
    font-size: 15px;
    border: none;
    border-radius: 6px;
    color: #fff;
    cursor: pointer;
    transition: background-color 0.3s, transform 0.2s;
    margin: 0 10px;
    text-decoration: none;
    }

    .print-btn .btn-print {
    background: #10b981; /* hijau */
    }

    .print-btn .btn-print:hover {
    background: #059669;
    transform: translateY(-2px);
    }

    .print-btn .btn-back {
    background: #f59e0b; /* oranye */
    }

    .print-btn .btn-back:hover {
    background: #d97706;
    transform: translateY(-2px);
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
        
  </style>
</head>
<body>
<div class="logo-wrapper">
        <img src="images/logo.png" alt="Logo Perusahaan" class="logo-img">
    </div>
  <h1>Laporan Penerbitan</h1>

  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>Nama Customer</th>
        <th>Judul Buku</th>
        <th>Paket</th>
        <th>Jumlah Cetak</th>
        <th>Status</th>
        <th>Total Harga</th>
        
      </tr>
    </thead>
    <tbody>
      <?php if (count($orders) > 0): ?>
        <?php foreach ($orders as $index => $order): ?>
          <tr>
            <td><?= $index + 1 ?></td>
            <td><?= htmlspecialchars($order['customer_name']) ?></td>
            <td><?= htmlspecialchars($order['book_title']) ?></td>
            <td><?= htmlspecialchars($order['package_type']) ?></td>
            <td><?= $order['jumlah_cetak'] ?> Eks</td>
            <td><?= htmlspecialchars($order['status']) ?></td>
            <td class="text-right">Rp <?= number_format($order['total_pemesanan'], 0, ',', '.') ?></td>
            
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="7" style="text-align: center;">Belum ada pesanan.</td>
        </tr>
      <?php endif; ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="6" class="total">Total Pendapatan</td>
        <td class="text-right total">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></td>
        
      </tr>
    </tfoot>
  </table>

  <div class="print-btn">
  <button class="btn btn-print" onclick="window.print()">
    Cetak Laporan PDF
  </button>
  <button class="btn btn-back" onclick="window.location.href='admin.php'">
    Kembali ke Admin
  </button>
</div>

</body>
</html>
