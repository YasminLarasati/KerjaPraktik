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
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>Nota Penerbitan</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 40px; }
    h1 { text-align: center; }
    .info { margin-top: 20px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #ddd; padding: 8px; }
    th { background: #eee; }
    .total { font-weight: bold; }
    .text-right { text-align: right; }
    .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #666; }
    .print-btn { margin-top: 20px; display: block; text-align: center; }
    .print-btn button {
      background: #4f46e5;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    .print-btn button:hover {
      background: #4338ca;
    }

    .logo {
            text-align: center;
            margin-bottom: 20px;
    }
    
    .logo img {
            max-width: 150px; /* Sesuaikan ukuran logo */
    }

  </style>
</head>
<body>

    <div class="logo">
        <img src="images/logo.png" alt="Logo CV. Global Aksara Pers"> <!-- Ganti dengan path logo Anda -->
    </div>

  <h1>Nota Penerbitan</h1>
  <div class="info">
    <p><strong>No. Penerbitan:</strong> <?= $data['order_id'] ?></p>
    <p><strong>Nama Pemesan:</strong> <?= htmlspecialchars($data['customer_name']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($data['email']) ?></p>
    <p><strong>Telepon:</strong> <?= htmlspecialchars($data['phone']) ?></p>
    <p><strong>Alamat:</strong> <?= htmlspecialchars($data['address']) ?></p>   
    <p><strong>Tanggal:</strong> <?= date('d-m-Y') ?></p>
  </div>

    <table>
    <tr>
        <th>Nama Penulis</th>
        <th>Judul Buku</th>
        <th>Paket</th>
        <th>Jumlah Cetak</th>
        <th>Harga</th>
    </tr>
    <tr>
        <td><?= htmlspecialchars($data['author_name']) ?></td>
        <td><?= htmlspecialchars($data['title']) ?></td>
        <td><?= htmlspecialchars($data['package_type']) ?></td>
        <td class="text-right"><?= $data['jumlah_cetak'] ?> Eks</td>
        <td class="text-right">Rp <?= number_format($data['total_pemesanan'], 0, ',', '.') ?></td>
    </tr>
    <tr>
        <td colspan="4" class="total">Total</td>
        <td class="text-right total">Rp <?= number_format($data['total_pemesanan'], 0, ',', '.') ?></td>
    </tr>
    </table>

  <p><strong>Status Pembayaran:</strong> <?= $data['payment_method'] ?: '-' ?></p>
  <p><strong>Status Penerbitan:</strong> <?= htmlspecialchars($data['status']) ?></p>

  <div class="footer">
    <p>Terima kasih telah melakukan penerbitan di CV. Global Aksara Pers.</p>
    <p>Nota ini dicetak secara otomatis.</p>
  </div>

  <div class="print-btn">
    <button onclick="window.print()">Cetak Nota</button>
  </div>

</body>
</html>
