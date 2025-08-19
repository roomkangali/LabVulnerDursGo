<?php
// File: /autentikasi2/fileupload/upload.php (Perbaikan Final)
require_once '../_auth_check.php';
require_once '../_header.php';
?>
<div class="box">
    <h2>📤 Upload File</h2>
    <!-- Action form diubah untuk menyertakan token saat di-submit dari browser -->
    <form action="upload.php?token=<?= htmlspecialchars($static_token, ENT_QUOTES, 'UTF-8') ?>" method="POST" enctype="multipart/form-data">
        <input type="file" name="file"><br><br>
        <button type="submit">Upload</button>
    </form>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    
    // --- PERBAIKAN KUNCI DI SINI ---
    // Daripada menyimpan ke direktori lokal (__DIR__), kita secara tidak aman 
    // menyimpan ke direktori upload publik di root web.
    // Ganti '/var/www/html/' dengan path root web server Anda jika berbeda.
    $publicUploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/';

    // Pastikan direktori ada
    if (!is_dir($publicUploadDir)) {
        mkdir($publicUploadDir, 0755, true);
    }
    
    $uploadFile = $publicUploadDir . basename($_FILES['file']['name']);

    if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadFile)) {
        // Tautan sekarang mengarah ke direktori upload publik
        $fileUrl = "/uploads/" . htmlspecialchars(basename($_FILES['file']['name']));
        echo "<p style='color:green;'>✔️ File uploaded successfully: <a href='" . $fileUrl . "' target='_blank'>View file</a></p>";
    } else {
        echo "<p style='color:red;'>❌ Failed to upload file. Check permissions for " . htmlspecialchars($publicUploadDir) . "</p>";
    }
}
?>
</div>
<?php
require_once '../_footer.php';
?>
