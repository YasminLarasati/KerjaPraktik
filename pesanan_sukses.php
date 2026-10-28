<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Berhasil</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --secondary: #f3f4f6;
            --success: #10b981;
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
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .success-card {
            background-color: white;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
            text-align: center;
            max-width: 600px;
            width: 100%;
        }

        .success-icon {
            font-size: 5rem;
            color: var(--success);
            margin-bottom: 20px;
        }

        .success-title {
            font-size: 2rem;
            margin-bottom: 15px;
            color: var(--primary);
        }

        .success-message {
            font-size: 1.1rem;
            margin-bottom: 30px;
            color: var(--text-light);
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 24px;
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
            color: var(--text);
        }

        .btn-secondary:hover {
            background-color: #e5e7eb;
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
            <div class="success-card">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1 class="success-title">Pesanan Anda Berhasil Dikirim!</h1>
                <p class="success-message">Terima kasih telah menerbitkan buku di CV. Global Aksara Pers. Kami akan segera memproses penerbitan Anda dan menghubungi Anda melalui email atau telepon.</p>
                
                <div class="action-buttons">
                    <a href="form_pemesanan.php" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Terbitkan Buku
                    </a>
                    <a href="daftar_pesanan_pelanggan.php" class="btn btn-secondary">
                        <i class="fas fa-list"></i> Lihat Daftar Pesanan
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
