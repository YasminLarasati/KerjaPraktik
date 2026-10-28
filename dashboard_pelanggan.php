<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pengguna') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pelanggan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --secondary: #f3f4f6;
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
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .user-profile {
            display: flex;
            align-items: center;
        }
        
        .user-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background-color: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
            text-transform: uppercase;
        }
        
        .user-info h3 {
            margin: 0;
            font-size: 1.2rem;
        }
        
        .user-info p {
            margin: 4px 0 0;
            color: var(--text-light);
            font-size: 0.9rem;
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
        
        .welcome-section {
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
        }
        
        .card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .dashboard {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
            }
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
            <a href="#" class="nav-item active">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="form_pemesanan.php" class="nav-item">
                <i class="fas fa-plus-circle"></i> Terbitkan Buku
            </a>
            <a href="daftar_pesanan_pelanggan.php" class="nav-item">
                <i class="fas fa-list"></i> Daftar Penerbitan
            </a>
            <a href="detail_customer.php" class="nav-item">
                <i class="fas fa-cog"></i> Pengaturan Profil
            </a>
        </div>
        
        <div class="main-content">
            <div class="header">
                <div class="user-profile">
                    <div class="user-avatar">
                        <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                    </div>
                    <div class="user-info">
                        <h3><?= htmlspecialchars($_SESSION['username']) ?></h3>
                        <p>Penulis</p>
                    </div>
                </div>
                
                <button class="logout-btn" onclick="window.location.href='logout.php'">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </div>
            
            <div class="welcome-section">
                <h1>Selamat Datang !</h1>
                <p>Silahkan pilih menu di sidebar untuk mulai menggunakan sistem. Anda dapat membuat pesanan untuk menerbitkan buku atau melihat daftar pesanan yang telah Anda buat.</p>
            </div>
            
            <div class="card">
                <h3>Menu Cepat</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px; margin-top: 20px;">
                    <a href="form_pemesanan.php" style="text-decoration: none; color: inherit;">
                        <div style="background-color: var(--secondary); padding: 15px; border-radius: 8px; text-align: center; transition: all 0.2s;">
                            <i class="fas fa-plus-circle" style="font-size: 1.5rem; margin-bottom: 10px; color: var(--primary);"></i>
                            <div>Terbitkan Buku</div>
                        </div>
                    </a>
                    <a href="daftar_pesanan_pelanggan.php" style="text-decoration: none; color: inherit;">
                        <div style="background-color: var(--secondary); padding: 15px; border-radius: 8px; text-align: center; transition: all 0.2s;">
                            <i class="fas fa-list" style="font-size: 1.5rem; margin-bottom: 10px; color: var(--primary);"></i>
                            <div>Daftar Penerbitan</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
