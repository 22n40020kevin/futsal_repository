<?php
/**
 * ============================================================
 * REHAM FUTSAL - HALAMAN UTAMA (USER)
 * ============================================================
 * Disinkronkan dengan admin/manage_lapangan.php
 * ============================================================
 * - Mengambil data lapangan dari kolom `foto`
 * - Menampilkan gambar dengan path yang sama seperti admin
 * - Menampilkan placeholder jika foto kosong / file hilang
 * - Tampilan biru ke abu-abuan (konsisten dengan tema elegan)
 */

session_start();
require_once '../config/init.php';
require_once '../config/database.php';

// Zona waktu
date_default_timezone_set('Asia/Jakarta');

// Redirect jika user sudah login
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'user') {
    header("Location: dashboard.php");
    exit();
}

// ============================================================
// AMBIL DATA LAPANGAN (Sinkron dengan Admin)
// ============================================================
try {
    $stmt = $conn->prepare("SELECT * FROM lapangan WHERE status = 'Aktif' ORDER BY created_at DESC LIMIT 6");
    $stmt->execute();
    $lapangan_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $lapangan_list = [];
    error_log("[FETCH LAPANGAN ERROR] " . $e->getMessage());
}

// ============================================================
// LOG AKTIVITAS PENGUNJUNG
// ============================================================
try {
    $log = $conn->prepare("INSERT INTO log_aktivitas (user_type, aktivitas, deskripsi, ip_address, user_agent, created_at)
                           VALUES ('user', ?, ?, ?, ?, NOW())");
    $log->execute([
        'Mengakses halaman utama',
        'Pengunjung membuka halaman user/index.php',
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ]);
} catch (PDOException $e) {
    error_log("[LOG ERROR] " . $e->getMessage());
}

// ============================================================
// STYLING & PAGE TITLE
// ============================================================
$page_title = "Reham Futsal - Booking Lapangan Futsal Online";
$styles = '
<style>
body { background-color: #f8f9fb; color: #333; }
.hero-bg { background: #1e3a8a; border-left: 8px solid #d1d5db; border-right: 8px solid #d1d5db; }
.lapangan-card { transition: transform 0.3s ease, box-shadow 0.3s ease; }
.lapangan-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
.btn-primary { background: linear-gradient(135deg, #5a84c7 0%, #7e93a8 100%); transition: all 0.3s ease; }
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(90,132,199,0.4); }
.text-muted { color: #666; }
.section-fade { opacity: 0; transform: translateY(20px); transition: opacity 0.8s ease, transform 0.8s ease; }
.section-fade.visible { opacity: 1; transform: translateY(0); }
</style>
';
?>

<?php include '../templates/header.php'; ?>

<!-- ================= HERO SECTION (BLUE-GRAY ENHANCED) ================= -->
<section id="home" class="relative bg-[#1e3a8a] text-white py-24 text-center border-l-8 border-r-8 border-gray-300 shadow-lg">
    <div class="container mx-auto px-6 md:px-8">
        <h1 class="text-5xl font-extrabold mb-6 text-gray-100 drop-shadow-lg tracking-wide">
            Main Futsal Jadi Semudah Klik!
        </h1>
        <p class="text-lg md:text-xl mb-10 max-w-2xl mx-auto text-gray-200 leading-relaxed">
            Booking lapangan futsal favoritmu secara cepat dan praktis tanpa antre.
        </p>

        <div class="flex flex-col sm:flex-row justify-center gap-4 mt-6">
            <a href="daftar.php"
               class="bg-white text-blue-900 px-10 py-4 rounded-lg font-bold text-lg border border-gray-200 hover:bg-gray-100 hover:shadow-md transition duration-300">
               <i class="fas fa-user-plus mr-2"></i>Daftar Sekarang
            </a>
            <a href="#lapangan"
               class="px-10 py-4 rounded-lg font-bold text-lg border-2 border-gray-200 text-white hover:bg-gray-100 hover:text-blue-900 transition duration-300">
               <i class="fas fa-futbol mr-2"></i>Lihat Lapangan
            </a>
        </div>

        <!-- Decorative bottom divider -->
        <div class="absolute bottom-0 left-0 w-full h-2 bg-gradient-to-r from-gray-300 via-blue-800 to-gray-300"></div>
    </div>
</section>

<!-- ================= LAPANGAN SECTION ================= -->
<section id="lapangan" class="py-16 bg-gray-100 section-fade">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-800 mb-3">Pilihan Lapangan Kami</h2>
            <p class="text-muted">Lapangan futsal berkualitas dengan fasilitas terbaik dan harga bersahabat.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($lapangan_list as $lapangan): ?>
                <?php
                    // ========================================================
                    // Gunakan kolom `foto` (sinkron admin/manage_lapangan.php)
                    // ========================================================
                    $foto_file = $lapangan['foto'] ?? '';
                    $foto_path = __DIR__ . '/../uploads/lapangan/' . $foto_file;
                    $foto_url  = '../uploads/lapangan/' . $foto_file;

                    if (empty($foto_file) || !file_exists($foto_path)) {
                        $foto_url = 'https://via.placeholder.com/400x250?text=Lapangan+Futsal';
                    }
                ?>
                <div class="lapangan-card bg-white rounded-xl shadow-md overflow-hidden border border-gray-200">
                    <div class="h-48">
                        <img src="<?= htmlspecialchars($foto_url) ?>" 
                             alt="<?= htmlspecialchars($lapangan['nama_lapangan']) ?>"
                             class="w-full h-48 object-cover rounded-t-lg">
                    </div>
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-3">
                            <h3 class="text-xl font-semibold text-gray-800"><?= htmlspecialchars($lapangan['nama_lapangan']); ?></h3>
                            <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-medium">
                                <?= htmlspecialchars($lapangan['jenis']); ?>
                            </span>
                        </div>
                        <p class="text-gray-600 mb-4 line-clamp-2">
                            <?= htmlspecialchars($lapangan['deskripsi'] ?? 'Lapangan futsal standar profesional dengan kenyamanan optimal.') ?>
                        </p>
                        <div class="flex justify-between items-center">
                            <span class="text-xl font-bold text-green-600">
                                Rp <?= number_format($lapangan['harga'], 0, ',', '.') ?>/jam
                            </span>
                            <a href="login.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                                Booking Now
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-12">
            <a href="daftar.php" class="btn-primary text-white px-8 py-3 rounded-lg font-bold inline-flex items-center">
                <i class="fas fa-eye mr-2"></i>Lihat Semua Lapangan
            </a>
        </div>
    </div>
</section>

<!-- ================= CTA SECTION ================= -->
<section class="bg-gradient-to-r from-gray-600 via-blue-700 to-gray-700 text-white py-16 section-fade">
    <div class="container mx-auto text-center px-4">
        <h2 class="text-3xl font-bold mb-4">Siap Bermain Sekarang?</h2>
        <p class="text-lg mb-8 max-w-2xl mx-auto text-gray-200">
            Daftar sekarang dan rasakan kemudahan memesan lapangan futsal secara online!
        </p>
        <div class="flex flex-col sm:flex-row justify-center gap-6">
            <a href="daftar.php" class="bg-white text-blue-700 px-8 py-4 rounded-lg font-bold hover:bg-gray-100 transition">
                <i class="fas fa-user-plus mr-2"></i>Daftar Sekarang
            </a>
            <a href="login.php" class="border border-white text-white px-8 py-4 rounded-lg font-bold hover:bg-white hover:text-blue-700 transition">
                <i class="fas fa-sign-in-alt mr-2"></i>Masuk Akun
            </a>
        </div>
    </div>
</section>

<?php
$scripts = '
<script>
document.querySelectorAll(\'a[href^="#"]\').forEach(anchor => {
    anchor.addEventListener(\'click\', e => {
        e.preventDefault();
        const target = document.querySelector(anchor.getAttribute(\'href\'));
        if (target) target.scrollIntoView({ behavior: "smooth" });
    });
});
const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
        if (entry.isIntersecting) entry.target.classList.add("visible");
    });
}, { threshold: 0.2 });
document.querySelectorAll(".section-fade").forEach(el => observer.observe(el));
</script>
';
include '../templates/footer.php';
?>
