<?php
require_once "koneksi.php";
session_start();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi dasar
    if (empty($username)) {
        $errors[] = "Username wajib diisi.";
    }

    if (strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Konfirmasi password tidak cocok.";
    }

    // Cek apakah username sudah digunakan
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = "Username sudah terdaftar.";
    }

    // Simpan ke database jika tidak ada error
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = 'pengguna';

        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $hashed_password, $role);

        if ($stmt->execute()) {
            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['role'] = $role;
            header("Location: login.php");
            exit;
        } else {
            $errors[] = "Terjadi kesalahan saat mendaftar.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Registrasi Pengguna</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .form-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        h2 {
            margin-bottom: 20px;
            color: rgb(80, 121, 188);
        }

        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
            text-align: left;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            margin-top: 20px;
            width: 100%;
            background-color: rgb(80, 121, 188);
            border: none;
            padding: 10px;
            color: white;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: rgb(136, 145, 178);
        }

        .errors {
            background-color: #fee2e2;
            color: #b91c1c;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: left;
        }

        p {
            margin-top: 15px;
            font-size: 14px;
        }

        a {
            color: #4f46e5;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .form-container img {
            max-width: 150px;
            margin: 0 auto 20px;
            display: block;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <img src="images/logo.png" alt="Logo">
        <h2>Daftar Akun</h2>
        
        <?php if (!empty($errors)) : ?>
            <div class="errors">
                <ul>
                    <?php foreach ($errors as $err) echo "<li>$err</li>"; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST">
            <label>Username</label>
            <input type="text" name="username" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <label>Konfirmasi Password</label>
            <input type="password" name="confirm_password" required>

            <button type="submit">Daftar</button>
        </form>

        <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
    </div>
</body>
</html>
