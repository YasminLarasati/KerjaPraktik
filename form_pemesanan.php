<?php
session_start();
require_once "koneksi.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pengguna') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];


// Buat folder upload kalau belum ada
$uploadDir = __DIR__ . '/uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if (!isset($_SESSION['step'])) {
    $_SESSION['step'] = 1;
}

// Jika step == 1 dan user sudah pernah pesan, skip step 1
if ($_SESSION['step'] === 1) {
    $query = $conn->prepare("SELECT * FROM customers WHERE user_id = ?");
    $query->bind_param("i", $user_id);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $customer = $result->fetch_assoc();
        $_SESSION['customer_id'] = $customer['id'];
        $_SESSION['customer_name'] = $customer['customer_name'];
        $_SESSION['email'] = $customer['email'];
        $_SESSION['phone'] = $customer['phone'];
        $_SESSION['address'] = $customer['address'];
        $_SESSION['step'] = 2;
    }
}

// Hanya pertama kali buka halaman (GET request tanpa session step)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_SESSION['step'])) {
    // Cek apakah user sudah punya data customer
    $query = $conn->prepare("SELECT * FROM customers WHERE user_id = ?");
    $query->bind_param("i", $user_id);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $customer = $result->fetch_assoc();
        $_SESSION['customer_id'] = $customer['id'];
        $_SESSION['step'] = 2;
    } else {
        $_SESSION['step'] = 1;
    }
}

// TANGANI NAVIGASI BACK
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['back'])) {
    if ($_SESSION['step'] === 3) {
        $_SESSION['step'] = 2;
    } elseif ($_SESSION['step'] === 2) {
        if (isset($_SESSION['customer_id'])) {
            // Sudah punya data customer → kembali ke dashboard
            header("Location: dashboard_pelanggan.php");
            exit();
        } else {
            // Belum punya → kembali ke step 1
            $_SESSION['step'] = 1;
        }
    } else {
        header("Location: dashboard_pelanggan.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // STEP 1 : SIMPAN CUSTOMER
    if (isset($_POST['next_customer'])) {

        $_SESSION['customer_name'] = $_POST['customer_name'];
        $_SESSION['email'] = $_POST['email'];
        $_SESSION['phone'] = $_POST['phone'];
        $_SESSION['address'] = $_POST['address'];

        // Kalau user belum punya data customer → insert

        $stmtCustomer = $conn->prepare("INSERT INTO customers (user_id, customer_name, email, phone, address) VALUES (?, ?, ?, ?, ?)");
        $stmtCustomer->bind_param(
            "issss",
            $user_id,
            $_POST['customer_name'],
            $_POST['email'],
            $_POST['phone'],
            $_POST['address']
        );
        $stmtCustomer->execute();
        $_SESSION['customer_id'] = $stmtCustomer->insert_id;

        $_SESSION['step'] = 2;
    }

    elseif (isset($_POST['next_book'])) {

        $_SESSION['book_title'] =
        ($_POST['book_title'] === '__NEW__') ? $_POST['book_title_input'] : $_POST['book_title'];
        $_SESSION['author_name'] = $_POST['author_name'];
        $_SESSION['jumlah_halaman'] = (int)$_POST['jumlah_halaman'];
        $_SESSION['notes'] = $_POST['notes'] ?? '';

        if (isset($_FILES['book_file']) && $_FILES['book_file']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['book_file']['tmp_name'];
            $fileName = basename($_FILES['book_file']['name']);
            $fileName = preg_replace('/[^a-zA-Z0-9-_\.]/', '_', $fileName);
            $destination = $uploadDir . time() . '_' . $fileName;

              if (move_uploaded_file($fileTmpPath, $destination)) {
                $_SESSION['file_path'] = 'uploads/' . basename($destination);
              } else {
                die("Upload file gagal.");
              }
            } else {
              $_SESSION['file_path'] = '';
            }

  $jumlah_halaman = $_SESSION['jumlah_halaman'];
  if ($jumlah_halaman < 60) {
    $_SESSION['error'] = "Jumlah halaman minimal 60. Silakan perbaiki dan kirim ulang.";
    $_SESSION['step'] = 2;
    header("Location: form_pemesanan.php");
    exit();
  }

  // HANYA set step lanjut → TIDAK insert DB di sini!
      $_SESSION['step'] = 3;
    header("Location: form_pemesanan.php");
    exit();
}

elseif (isset($_POST['submit_order'])) {

  // Debugging: Tampilkan isi sesi
    var_dump($_SESSION);

  if (!isset($_SESSION['book_title'], $_SESSION['author_name'], $_SESSION['jumlah_halaman'])) {
        die("Data buku tidak ditemukan. Silakan ulangi langkah sebelumnya.");
    }
    
  // Tangkap POST
  $package_type = $_POST['package_type'];
  $jumlah_cetak = (int)$_POST['jumlah_cetak'];
  $hki = isset($_POST['hki']) ? 1 : 0;
  $originality_verified = isset($_POST['originality_verified']) ? 1 : 0;
  $order_note = $_POST['notes'] ?? '';

  // Hitung total harga dsb → PAKAI SESSION DATA
  $jumlah_halaman = $_SESSION['jumlah_halaman'];

  $harga_paket = [ "Paket A5 HVS Ekonomis" => 375000,
        "Paket A5 Bookpaper Ekonomis" => 400000,
        "Paket B5 HVS Ekonomis" => 450000,
        "Paket B5 Bookpaper Ekonomis" => 450000,
        "Paket A5 Bookpaper Eksklusif" => 850000,
        "Paket B5 Bookpaper Eksklusif" => 1000000,
        "Paket B5 HVS Eksklusif" => 950000,
        "Paket A5 HVS Premium" => 1250000,
        "Paket A5 Bookpaper Premium" => 1300000,
        "Paket B5 HVS Premium" => 1500000,
        "Paket B5 Bookpaper Premium" => 1500000,
        "Paket A5 Full Colour HVS Premium" => 2000000 ];

  $harga_dasar = $harga_paket[$package_type] ?? 0;

  $harga_tambahan_halaman = 0;
  if ($jumlah_halaman > 150) {
    $kelebihan = $jumlah_halaman - 150;
    $blok = ceil($kelebihan / 10);
    $harga_tambahan_halaman = $blok * 100000;
  }

  $biaya_hki = $hki ? 500000 : 0;
  $total_pemesanan = (($harga_dasar + $harga_tambahan_halaman) * $jumlah_cetak) + $biaya_hki;

  // INSERT books
  $stmtBook = $conn->prepare("INSERT INTO books (customer_id, title, author_name, jumlah_halaman, file_path, notes) VALUES (?, ?, ?, ?, ?, ?)");
  $stmtBook->bind_param(
    "ississ",
    $_SESSION['customer_id'],
    $_SESSION['book_title'],
    $_SESSION['author_name'],
    $_SESSION['jumlah_halaman'],
    $_SESSION['file_path'],
    $_SESSION['notes']
  );
  $stmtBook->execute();
  $book_id = $stmtBook->insert_id;

  // INSERT orders
  $stmtOrder = $conn->prepare("INSERT INTO orders (customer_id, book_id, jumlah_cetak, package_type, hki, originality_verified, order_note, total_pemesanan) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
  $stmtOrder->bind_param(
    "iiisiisi",
    $_SESSION['customer_id'],
    $book_id,
    $jumlah_cetak,
    $package_type,
    $hki,
    $originality_verified,
    $order_note,
    $total_pemesanan
  );
  $stmtOrder->execute();

  unset($_SESSION['step'], $_SESSION['customer_name'], $_SESSION['email'], $_SESSION['phone'], $_SESSION['address'], $_SESSION['book_title'], $_SESSION['author_name'], $_SESSION['jumlah_halaman'], $_SESSION['notes'], $_SESSION['file_path']);
  header("Location: pesanan_sukses.php");
  exit();
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>Form Pemesanan</title>
  <style>
    
    label { display: block; margin-top: 10px; }
    input, textarea, select { width: 100%; padding: 8px; margin-top: 5px; }
    button { margin-top: 20px; background: #4f46e5; color: #fff; border: none; padding: 10px 20px; border-radius: 4px; }
    button:hover { background: #4338ca; }
    
    #packagePreview {
      display: none;
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      max-width: 340px;
      background: #fff;
      border: 2px solid #333;
      border-radius: 8px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.2);
      padding: 20px;
      z-index: 1000;
    }

    #packagePreview img {
      display: block;
      width: 100%;
      height: auto;
      border-radius: 4px;
    }

    .checkbox-group {
      margin-top: 15px;
    }

    .custom-checkbox {
      position: relative;
      padding-left: 35px;
      margin-bottom: 12px;
      cursor: pointer;
      display: block;
      font-size: 16px;
      user-select: none;
    }

    .custom-checkbox input {
      position: absolute;
      opacity: 0;
      cursor: pointer;
    }

    .custom-checkbox .checkmark {
      position: absolute;
      top: 2px;
      left: 0;
      height: 20px;
      width: 20px;
      background-color: #eee;
      border: 2px solid #999;
      border-radius: 4px;
    }

    .custom-checkbox:hover input ~ .checkmark {
      background-color: #ccc;
    }

    .custom-checkbox input:checked ~ .checkmark {
      background-color: #4f46e5;
      border-color: #4f46e5;
    }

    .custom-checkbox .checkmark:after {
      content: "";
      position: absolute;
      display: none;
    }

    .custom-checkbox input:checked ~ .checkmark:after {
      display: block;
    }

    .custom-checkbox .checkmark:after {
      left: 6px;
      top: 2px;
      width: 5px;
      height: 10px;
      border: solid #fff;
      border-width: 0 2px 2px 0;
      transform: rotate(45deg);
    }

          /* Tombol umum */
      button {
        border: none;
        padding: 10px 20px;
        border-radius: 4px;
        cursor: pointer;
        margin-right: 10px;
      }

      /* Tombol Selanjutnya & Kirim Pesanan: hijau */
      .btn-next,
      .btn-submit {
        background: #16a34a; /* hijau */
        color: #fff;
      }

      .btn-next:hover,
      .btn-submit:hover {
        background: #15803d; /* hijau lebih gelap */
      }

      /* Tombol Kembali: kuning */
      .btn-back {
        background: #facc15; /* kuning */
        color: #000;
      }

      .btn-back:hover {
        background: #eab308; /* kuning lebih gelap */
      }

        .floating-alert {
        position: fixed;
        top: 40%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: #f87171; /* Merah */
        color: #fff;
        padding: 20px 30px;
        border-radius: 8px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
        z-index: 9999;
        opacity: 0;
        animation: fadein 0.5s ease forwards, fadeout 0.5s ease forwards 5s;
      }

      @keyframes fadein {
        from { opacity: 0; transform: translate(-50%, -60%); }
        to { opacity: 1; transform: translate(-50%, -50%); }
      }

      @keyframes fadeout {
        from { opacity: 1; transform: translate(-50%, -50%); }
        to { opacity: 0; transform: translate(-50%, -60%); }
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

        .main-content {
            flex: 1;
            padding: 30px;
        }

        h2 {
            margin-bottom: 20px;
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

        .btn-close {
          background: #dc2626; /* Merah */
          color: #fff;
        }

        .btn-close:hover {
          background: #b91c1c; /* Merah lebih gelap */
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

        <h2>Silahkan Isi Detail Penerbitan </h2>

<?php if ($_SESSION['step'] === 1): ?>

  <h2>Step 1: Data Penulis</h2>
  <form method="POST">
    <label>Nama Lengkap :</label>
    <input type="text" name="customer_name" required value="<?= isset($_SESSION['customer_name']) ? htmlspecialchars($_SESSION['customer_name']) : '' ?>">
    <label>Email :</label>
    <input type="email" name="email" required value="<?= isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : '' ?>">
    <label>Telepon :</label>
    <input type="text" name="phone" required value="<?= isset($_SESSION['phone']) ? htmlspecialchars($_SESSION['phone']) : '' ?>">
    <label>Alamat :</label>
    <textarea name="address" required><?= isset($_SESSION['address']) ? htmlspecialchars($_SESSION['address']) : '' ?></textarea>
    
    <button type="button" class="btn-back" onclick="kembali()">Kembali</button>
    <button type="submit" name="next_customer" class="btn-next">Selanjutnya</button>
  </form>

  

<?php elseif ($_SESSION['step'] === 2): ?>

    <?php if (isset($_SESSION['error'])): ?>
    <div class="floating-alert">
      <?= htmlspecialchars($_SESSION['error']); ?>
    </div>
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>

  <h2>Step 2: Data Buku</h2>
    <?php
    // Ambil buku milik user ini saja
    $books = [];
    $stmt = $conn->prepare("SELECT * FROM books WHERE customer_id = ?");
    $stmt->bind_param("i", $_SESSION['customer_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }
    ?>
  <form method="POST" enctype="multipart/form-data">
    <!-- SELECT: default terlihat -->
    <label>Judul Buku :</label>
    <select name="book_title" id="bookDropdown" onchange="handleBookSelection()" required>
      <option value="">-- Pilih Judul Buku --</option>
      <?php foreach ($books as $book): ?>
        <option value="<?= htmlspecialchars($book['title']) ?>"
                data-pages="<?= (int)$book['jumlah_halaman'] ?>">
          <?= htmlspecialchars($book['title']) ?>
        </option>
      <?php endforeach; ?>
      <option value="__NEW__">+ Judul Buku Baru</option>
    </select>

    <input type="text" name="book_title_input"
          id="bookTitleInput"
          placeholder="Tulis judul buku baru..."
          style="display:none; margin-top:10px;">

    <label>Nama Penulis :</label>
    <input type="text" name="author_name" required value="<?= isset($_SESSION['author_name']) ? htmlspecialchars($_SESSION['author_name']) : '' ?>">
    <label>Jumlah Halaman (minimal 60 halaman):</label>
    <input type="number" name="jumlah_halaman"
          id="pageCount"
          min="1" required
          value="<?= isset($_SESSION['jumlah_halaman']) ? (int)$_SESSION['jumlah_halaman'] : '' ?>">

    <label>Catatan :</label>
    <textarea name="notes"><?= isset($_SESSION['notes']) ? htmlspecialchars($_SESSION['notes']) : '' ?></textarea>
    <label>Upload File Buku (PDF):</label>
    <input type="file" name="book_file" accept=".pdf" required>
    
    <button type="button" class="btn-back" onclick="kembali()">Kembali</button>
    <button type="submit" name="next_book" class="btn-next">Selanjutnya</button>
  </form>

    <script>
    function handleBookSelection() {
      const dropdown = document.getElementById('bookDropdown');
      const selected = dropdown.options[dropdown.selectedIndex];
      const bookTitleInput = document.getElementById('bookTitleInput');
      const pageCountInput = document.getElementById('pageCount');

      if (selected.value === '__NEW__') {
        // Pilih Buku Baru → tampilkan input manual
        bookTitleInput.style.display = 'block';
        pageCountInput.value = '';
      } else {
        // Pilih buku lama → sembunyikan input manual
        bookTitleInput.style.display = 'none';
        const pages = selected.getAttribute('data-pages');
        pageCountInput.value = pages ? pages : '';
      }
    }
    </script>

<?php elseif ($_SESSION['step'] === 3): ?>
  <h2>Step 3: Detail Order</h2>
  <form method="POST">
    <label>Pilih Paket :</label>
                <select name="package_type" id="package" onchange="showPreview()" required>
                    <option value="">-- Pilih Paket --</option>
                    <?php
                    $packages = [
                        "Paket A5 HVS Ekonomis", "Paket A5 Bookpaper Ekonomis", "Paket B5 HVS Ekonomis",
                        "Paket B5 Bookpaper Ekonomis", "Paket A5 Bookpaper Eksklusif", "Paket B5 Bookpaper Eksklusif",
                        "Paket B5 HVS Eksklusif", "Paket A5 HVS Premium", "Paket A5 Bookpaper Premium",
                        "Paket B5 HVS Premium", "Paket B5 Bookpaper Premium", "Paket A5 Full Colour HVS Premium"
                    ];
                    foreach ($packages as $pack) {
                        echo "<option value=\"$pack\">$pack</option>";
                    }
                    ?>
                </select>
    <label>Jumlah Cetak :</label>
    <input type="number" name="jumlah_cetak" min="1" required>
    <br>
    <div class="checkbox-group">
      <label class="custom-checkbox">
        <input type="checkbox" name="hki">
        <span class="checkmark"></span>
        HKI <small>(Biaya Tambahan Rp. 500.000)</small>
      </label>

      <label class="custom-checkbox">
        <input type="checkbox" name="originality_verified">
        <span class="checkmark"></span>
        Keaslian Karya <small>(Sudah Tercantum di Halaman Terakhir File Buku)</small>
      </label>
    </div>
    <br>
    <label>Catatan :</label>
    <textarea name="notes"></textarea>
    
    <button type="button" class="btn-back" onclick="kembali()">Kembali</button>
    <button type="submit" name="submit_order" class="btn-next">Kirim Pesanan</button>
  </form>

  <!-- Preview Container -->
<div id="packagePreview" style="display:none; border: 1px solid #ccc; padding: 20px; margin-top: 20px;">
  <h3 id="previewTitle"></h3>
  <img id="previewImage" src="" alt="Preview Paket" style="max-width: 100%; height: auto;">
  <br>
  <button type="button" onclick="confirmPackage()">Konfirmasi Paket</button>
  <button type="button" class="btn-close" onclick="closePreview()">Tutup</button>
</div>

<!-- Script Preview -->
<script>
  const paketDetails = {
    "Paket A5 HVS Ekonomis": "Paket-Eko-A5-HVS.jpeg",
    "Paket A5 Bookpaper Ekonomis": "Paket-Eko-A5-Bookpaper.jpeg",
    "Paket B5 HVS Ekonomis": "Paket-Eko-B5-HVS.jpeg",
    "Paket B5 Bookpaper Ekonomis": "Paket-Eko-B5-Bookpaper.jpeg",
    "Paket A5 Bookpaper Eksklusif": "Paket-Eksklusif-A5-bookpaper.jpeg",
    "Paket B5 Bookpaper Eksklusif": "Paket-Eksklusif-B5-bookpaper.jpeg",
    "Paket B5 HVS Eksklusif": "Paket-Eksklusif-B5-HVS.jpeg",
    "Paket A5 HVS Premium": "Paket-Premium-A5-HVS.jpeg",
    "Paket A5 Bookpaper Premium": "Paket-Premium-A5-Bookpaper.jpeg",
    "Paket B5 HVS Premium": "Paket-Premium-B5-HVS.jpeg",
    "Paket B5 Bookpaper Premium": "Paket-Premium-B5-bookpaper.jpeg",
    "Paket A5 Full Colour HVS Premium": "Paket-Premium-A5-Fullcolor-HVS.jpeg"
  };

  function showPreview() {
    const selected = document.getElementById("package").value;
    const preview = document.getElementById("packagePreview");
    const img = document.getElementById("previewImage");
    const title = document.getElementById("previewTitle");
    if (paketDetails[selected]) {
      img.src = "images/" + paketDetails[selected];
      title.textContent = selected;
      preview.style.display = "block";
    }
  }

  function confirmPackage() {
    document.getElementById("packagePreview").style.display = "none";
    alert("Paket berhasil dikonfirmasi!");
  }

  function closePreview() {
    document.getElementById("packagePreview").style.display = "none";
  }
</script>



<?php endif; ?>

<script>
  function kembali() {
    const form = document.createElement('form');
    form.method = 'POST';

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'back';
    input.value = '1';

    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
  }
</script>
</div>
</div>
</body>
</html>