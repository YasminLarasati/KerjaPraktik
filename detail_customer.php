<?php
session_start();
require_once "koneksi.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pengguna') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil data customer
$stmt = $conn->prepare("SELECT * FROM customers WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Data customer tidak ditemukan.";
    exit();
}

$customer = $result->fetch_assoc();

// Proses update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = $_POST['customer_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    $update = $conn->prepare("UPDATE customers SET customer_name = ?, email = ?, phone = ?, address = ? WHERE user_id = ?");
    $update->bind_param("ssssi", $customer_name, $email, $phone, $address, $user_id);
    $update->execute();

    header("Location: detail_customer.php?success=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Pengaturan Profil</title>
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
            text-align: center;
        }

        .logo {
            width: 100px;
            height: auto;
            object-fit: contain;
            margin-bottom: 10px;
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

        .card {
        background-color: white;
        border-radius: 10px;
        padding: 30px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
        max-width: 1100px; /* Lebih lebar */
        width: 100%;
        }

        h2 {
            margin-bottom: 20px;
            color: var(--primary);
        }

        label {
            display: block;
            margin-top: 20px;
            font-weight: 600;
            color: var(--text);
        }

        input, textarea {
            width: 100%;
            padding: 12px;
            margin-top: 5px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 1rem;
            background: #fff;
        }

        textarea {
            resize: vertical;
        }

        button {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 6px;
            margin-top: 30px;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.2s;
        }

        button:hover {
            background: var(--primary-hover);
        }

        .save-btn {
        background-color: #10b981; /* Tailwind Emerald 500 */
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 6px;
        cursor: pointer;
        transition: background-color 0.2s;
        font-size: 1rem;
        }

        .save-btn:hover {
        background-color: #059669; /* Tailwind Emerald 600 */
        }

        .notif-success {
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        background-color: #10b981;
        color: white;
        padding: 15px 25px;
        border-radius: 6px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        z-index: 9999;
        animation: fadeOut 4s forwards;
        }

        @keyframes fadeOut {
        0% { opacity: 1; }
        80% { opacity: 1; }
        100% { opacity: 0; display: none; }
        }
    </style>
</head>

<?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
  <div class="notif-success">Profil berhasil disimpan!</div>
<?php endif; ?>

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
            <a href="form_pemesanan.php" class="nav-item">
                <i class="fas fa-plus-circle"></i> Terbitkan Buku
            </a>
            <a href="detail_customer.php" class="nav-item active">
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
                        <p>Pelanggan</p>
                    </div>
                </div>
                
            </div>

            <h2>Pengaturan Profil</h2>
            <div class="card">
                <form method="POST">
                    <label>Nama Lengkap</label>
                    <input type="text" name="customer_name" value="<?= htmlspecialchars($customer['customer_name']) ?>" required>

                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($customer['email']) ?>" required>

                    <label>Telepon</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($customer['phone']) ?>" required>

                    <label>Alamat</label>
                    <textarea name="address" rows="4" required><?= htmlspecialchars($customer['address']) ?></textarea>

                    <button type="submit" class="save-btn">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
