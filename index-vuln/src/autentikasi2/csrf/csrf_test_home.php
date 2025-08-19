<?php
// File: /autentikasi2/csrf/csrf_test_home.php
// Versi ini sudah benar, terproteksi, dan berisi semua elemen form yang dibutuhkan.

// Langkah 1: Pastikan pengguna terautentikasi dengan token.
require_once '../_auth_check.php';

// Langkah 2: Mulai sesi untuk mengelola token CSRF.
session_start();

// Buat token CSRF yang sebenarnya jika belum ada.
if (empty($_SESSION['csrf_real_token'])) {
    $_SESSION['csrf_real_token'] = bin2hex(random_bytes(32));
}
$real_csrf_token = $_SESSION['csrf_real_token'];
$decoy_csrf_token = "decoy-static-token-123"; // Token umpan untuk form yang rentan.

// Ambil pesan status dari redirect (jika ada).
$message = '';
if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
}

// Langkah 3: Sertakan header tampilan yang konsisten.
require_once '../_header.php';
?>

<!-- Konten halaman dimulai di sini, di dalam .main-content dari header -->
<div class="box">
    <h1>CSRF Scanner Test Page</h1>

    <!-- Tampilkan pesan status dari aksi sebelumnya -->
    <?php if ($message): ?>
    <p class="message <?= strpos(strtolower($message), 'error') !== false || strpos(strtolower($message), 'invalid') !== false ? 'error' : 'success' ?>">
        <?= $message ?>
    </p>
    <?php endif; ?>

    <!-- 
      FORMULIR 1: AMAN 
      Menggunakan token CSRF yang valid dan unik per sesi.
    -->
    <h2>Form 1: Update Email (Protected by VALID CSRF Token)</h2>
    <form action="csrf_action_safe.php" method="POST">
        <label for="email_safe">New Email:</label><br>
        <input type="email" id="email_safe" name="email" value="safe_user@example.com" required /><br>
        <input type="hidden" name="csrf_token" value="<?= $real_csrf_token ?>" />
        <button type="submit" name="update_email_safe">Update Email (Safe)</button>
    </form>

    <hr style="margin: 30px 0;">

    <!-- 
      FORMULIR 2: RENTAN 
      Memiliki field token, tetapi backend akan mengabaikannya.
    -->
    <h2>Form 2: Update Email (Vulnerable - Decoy/Ignored CSRF Token)</h2>
    <p>Form ini memiliki field token, tetapi backend akan mengabaikannya atau tidak memvalidasinya dengan benar.</p>
    <form action="csrf_action_vulnerable.php" method="POST">
        <label for="email_vuln">New Email:</label><br>
        <input type="email" id="email_vuln" name="email" value="vulnerable_user@example.com" required /><br>
        <input type="hidden" name="csrf_token" value="<?= $decoy_csrf_token ?>" /> 
        <button type="submit" name="update_email_vulnerable" class="vulnerable">Update Email (Vulnerable)</button>
    </form>

    <hr style="margin: 30px 0;">

    <!-- 
      FORMULIR 3: RENTAN 
      Tidak memiliki field token CSRF sama sekali.
    -->
    <h2>Form 3: Update Email (Vulnerable - No CSRF Token Field)</h2>
    <p>Form ini tidak mengirimkan field token CSRF sama sekali.</p>
    <form action="csrf_action_vulnerable.php" method="POST">
        <label for="email_no_token">New Email:</label><br>
        <input type="email" id="email_no_token" name="email" value="no_token_user@example.com" required /><br>
        <button type="submit" name="update_email_no_token" class="vulnerable">Update Email (No Token Field)</button>
    </form>

</div>

<?php
// Langkah 4: Sertakan footer tampilan.
require_once '../_footer.php';
?>
