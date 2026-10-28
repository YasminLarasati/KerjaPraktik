<?php
require_once "koneksi.php";
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// JOIN orders, books, customers
$result = $conn->query("
    SELECT 
        orders.id AS order_id,
        customers.customer_name AS customer_name,
        books.author_name,
        books.title AS book_title
    FROM orders
    JOIN books ON orders.book_id = books.id
    JOIN customers ON orders.customer_id = customers.id
    ORDER BY orders.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pesanan - Admin</title>
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

        .logout-btn {
            background-color: rgb(180, 77, 77);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.2s;
            display: block;
            margin: 20px auto;
            text-align: center;
        }

        .logout-btn:hover {
            background-color:rgb(159, 13, 13);
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

        .btn {
            padding: 5px 10px; /* Mengurangi padding untuk ukuran lebih kecil */
            border-radius: 4px; /* Mengubah radius untuk tampilan lebih kecil */
            text-decoration: none;
            font-weight: 500;
            background-color: var(--primary);
            color: white;
            transition: all 0.2s;
        }

        .laporan-btn {
        background-color: #3b82f6; /* biru */
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        cursor: pointer;
        transition: background-color 0.2s;
        display: block;
        margin: 10px auto; /* Tengah + jarak */
        text-align: center;
        }

        .laporan-btn:hover {
        background-color: #2563eb;
        }
    </style>
</head>
<body>
    <div class="container">

    <div class="logo-wrapper">
        <img src="images/logo.png" alt="Logo Perusahaan" class="logo-img">
    </div>

        <h2>Daftar Penerbitan Penulis</h2>
        <table>
            <tr>
                <th>Nama Customer</th>
                <th>Nama Penulis</th>
                <th>Judul Buku</th>
                <th>Detail</th>
            </tr>
            <?php while($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= htmlspecialchars($row['customer_name']) ?></td>
                <td><?= htmlspecialchars($row['author_name']) ?></td>
                <td><?= htmlspecialchars($row['book_title']) ?></td>
                <td><a href="detail_pesanan_admin.php?id=<?= $row['order_id'] ?>" class="btn">Lihat Detail</a></td>
            </tr>
            <?php } ?>
        </table>
        <br>
        <button class="laporan-btn" onclick="window.location.href='laporan_admin.php'">
            <i class="fas fa-file-alt"></i> Lihat Laporan
        </button>
        <button class="logout-btn" onclick="window.location.href='logout.php'">
            <i class="fas fa-sign-out-alt"></i> Logout
        </button>
        
    </div>
</body>
</html>
