<?php
session_start();
if (!isset($_SESSION['userId'])) {
    header("Location: /doremi-app/login.php");
    exit;
}
require '../db.php';

$role = $_SESSION['userRole'];
$userName = $_SESSION['userName'];
$userId = (int) ($_SESSION['userId'] ?? 0);
$latestPickupDashboardSubquery = "
    LEFT JOIN (
        SELECT pp1.*
        FROM pengambilanpaket pp1
        INNER JOIN (
            SELECT PaketID, MAX(PengambilanPaketID) AS LatestPengambilanPaketID
            FROM pengambilanpaket
            GROUP BY PaketID
        ) latest ON latest.LatestPengambilanPaketID = pp1.PengambilanPaketID
    ) pp ON pp.PaketID = pk.PaketID
";

$stats = [];
if ($role === 'PENGURUS') {
    $activePenghuni = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as total FROM penghuni WHERE IsDeleted = 0 AND IsActive = 1"))['total'];
    $pendingInOut = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as total FROM inoutpenghuni WHERE Status = 'Pending'"))['total'];
    $pendingMaintenance = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as total FROM maintenance WHERE StatusMaintenance = 'Diajukan'"))['total'];
    $pendingPackagePickup = mysqli_fetch_assoc(mysqli_query(
        $db,
        "SELECT COUNT(*) AS total
         FROM paket pk
         $latestPickupDashboardSubquery
         WHERE pp.PengambilanPaketID IS NULL OR pp.Status = 'Belum Diambil'"
    ))['total'];

    $genderStats = mysqli_query($db, "SELECT JenisKelamin, COUNT(*) as count FROM penghuni WHERE IsDeleted = 0 GROUP BY JenisKelamin");
    $chartData = ['L' => 0, 'P' => 0];
    while ($row = mysqli_fetch_assoc($genderStats)) {
        $chartData[$row['JenisKelamin']] = $row['count'];
    }
}

if ($role === 'PENGHUNI') {
    $izinAktif = mysqli_fetch_assoc(mysqli_query(
        $db,
        "SELECT COUNT(*) AS total
         FROM inoutpenghuni
         WHERE PenghuniID = $userId AND Status IN ('Pending', 'Keluar')"
    ))['total'];

    $paketSummaryResult = mysqli_query(
        $db,
        "SELECT COALESCE(pp.Status, 'Belum Diambil') AS Status
         FROM paket pk
         $latestPickupDashboardSubquery
         WHERE pk.PenghuniID = $userId"
    );
    $paketSummaryRows = mysqli_fetch_all($paketSummaryResult, MYSQLI_ASSOC);
    $totalPaketMasuk = count($paketSummaryRows);
    $paketBelumDiambil = count(array_filter($paketSummaryRows, static fn(array $row): bool => ($row['Status'] ?? 'Belum Diambil') === 'Belum Diambil'));
    $paketSudahDiambil = count(array_filter($paketSummaryRows, static fn(array $row): bool => ($row['Status'] ?? '') === 'Sudah Diambil'));
    $paketTertukar = count(array_filter($paketSummaryRows, static fn(array $row): bool => ($row['Status'] ?? '') === 'TERTUKAR'));

    $maintenanceSummaryResult = mysqli_query(
        $db,
        "SELECT StatusMaintenance
         FROM maintenance
         WHERE PenghuniID = $userId"
    );
    $maintenanceSummary = ['Diajukan' => 0, 'Diproses' => 0, 'Selesai' => 0];
    while ($row = mysqli_fetch_assoc($maintenanceSummaryResult)) {
        $status = $row['StatusMaintenance'] ?? '';
        if (array_key_exists($status, $maintenanceSummary)) {
            $maintenanceSummary[$status]++;
        }
    }

    $diprosesAtauSelesai = $maintenanceSummary['Diproses'] + $maintenanceSummary['Selesai'];
}

// AMBIL DATA DASHBOARD KHUSUS TIM MAINTENANCE
if ($role === 'MAINTENANCE') {
    $pendingTasks = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as total FROM maintenance WHERE StatusMaintenance = 'Diajukan'"))['total'];
    $myOngoingTasks = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as total FROM maintenance WHERE StatusMaintenance = 'Diproses' AND PetugasID = $userId"))['total'];
    $myCompletedTasks = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as total FROM maintenance WHERE StatusMaintenance = 'Selesai' AND PetugasID = $userId"))['total'];
    $activeEmergencyTasks = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as total FROM maintenance WHERE JenisLaporan = 'Kerusakan Darurat / Berat' AND StatusMaintenance != 'Selesai'"))['total'];

    // Ambil 5 Laporan Kerusakan Terakhir yang sedang diproses oleh Teknisi yang login
    // KOREKSI JALUR: Menambahkan relasi JOIN ke tabel inventaris (i)
    $myTasksQuery = "
        SELECT m.*, r.NamaRuangan, r.Lantai AS LantaiRuangan, p.NamaPenghuni, pt.NamaPetugas AS NamaReporterPetugas, i.NamaBarang
        FROM maintenance m
        LEFT JOIN ruangan r ON m.RuanganID = r.RuanganID
        LEFT JOIN penghuni p ON m.PenghuniID = p.PenghuniID
        LEFT JOIN petugas pt ON m.PetugasID = pt.PetugasID
        LEFT JOIN inventaris i ON m.InventarisID = i.InventarisID
        WHERE m.StatusMaintenance = 'Diproses' AND m.PetugasID = $userId
        ORDER BY m.MaintenanceID DESC
        LIMIT 5
    ";
    $myTasksResult = mysqli_query($db, $myTasksQuery);
    $myTasks = mysqli_fetch_all($myTasksResult, MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<?php require '../head.php'; ?>

<body class="dashboard-body tw:p-0 tw:m-0 relative tw:flex tw:bg-[#f8fafc] tw:min-h-screen tw:overflow-x-hidden">
    <div
        class="tw:absolute tw:top-[-10%] tw:right-[-5%] tw:w-[500px] tw:h-[500px] tw:bg-primary/5 tw:rounded-full tw:blur-[120px] tw:pointer-events-none">
    </div>
    <div
        class="tw:absolute tw:bottom-[10%] tw:left-[20%] tw:w-[400px] tw:h-[400px] tw:bg-secondary/5 tw:rounded-full tw:blur-[100px] tw:pointer-events-none">
    </div>

    <?php require 'components/sidebar.php'; ?>
    <main class="dashboard-main tw:md:ml-75 tw:grow tw:relative tw:z-10">
        <div class="dashboard-page tw:pt-20 tw:md:pt-8 tw:px-8 tw:mb-8 tw:flex-1 tw:w-dvw tw:md:w-full">
            <h1 class="page-title" data-kicker="Dashboard Utama"
                data-subtitle="Selamat datang kembali di dashboard DOREMI. Semua aktivitas operasional asrama ada di satu tempat dan mengikuti pola kerja yang sama.">
                Halo, <?= htmlspecialchars($userName) ?>
            </h1>

            <!-- 1. BLOK STATS UNTUK PENGURUS -->
            <?php if ($role === 'PENGURUS'): ?>
                <div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-2 tw:lg:grid-cols-4 tw:gap-6 tw:mb-10">
                    <div class="dashboard-stat-card">
                        <div class="dashboard-stat-card__row">
                            <div class="dashboard-stat-card__icon dashboard-stat-card__icon--primary">
                                <i class="iconsax tw:text-3xl" icon-name="group"></i>
                            </div>
                            <div>
                                <span class="dashboard-stat-card__eyebrow">Penghuni</span>
                                <strong class="dashboard-stat-card__value"><?= $activePenghuni ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="dashboard-stat-card">
                        <div class="dashboard-stat-card__row">
                            <div class="dashboard-stat-card__icon dashboard-stat-card__icon--warning">
                                <i class="iconsax tw:text-3xl" icon-name="clock-1"></i>
                            </div>
                            <div>
                                <span class="dashboard-stat-card__eyebrow">Antrean Izin</span>
                                <strong class="dashboard-stat-card__value"><?= $pendingInOut ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="dashboard-stat-card">
                        <div class="dashboard-stat-card__row">
                            <div class="dashboard-stat-card__icon dashboard-stat-card__icon--success">
                                <i class="iconsax tw:text-3xl" icon-name="box-time"></i>
                            </div>
                            <div>
                                <span class="dashboard-stat-card__eyebrow">Paket Tertunda</span>
                                <strong class="dashboard-stat-card__value"><?= $pendingPackagePickup ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="dashboard-stat-card">
                        <div class="dashboard-stat-card__row">
                            <div class="dashboard-stat-card__icon dashboard-stat-card__icon--warning">
                                <i class="iconsax tw:text-3xl" icon-name="setting-2"></i>
                            </div>
                            <div>
                                <span class="dashboard-stat-card__eyebrow">Antrean Perbaikan</span>
                                <strong class="dashboard-stat-card__value"><?= $pendingMaintenance ?></strong>
                            </div>
                        </div>
                    </div>
                </div>

            <!-- 2. BLOK STATS UNTUK PENGHUNI -->
            <?php elseif ($role === 'PENGHUNI'): ?>
                <div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-2 tw:xl:grid-cols-4 tw:gap-6 tw:mb-10">
                    <div class="dashboard-stat-card">
                        <div class="dashboard-stat-card__row">
                            <div class="dashboard-stat-card__icon dashboard-stat-card__icon--primary">
                                <i class="iconsax tw:text-3xl" icon-name="box-1"></i>
                            </div>
                            <div>
                                <span class="dashboard-stat-card__eyebrow">Paket Masuk</span>
                                <strong class="dashboard-stat-card__value"><?= $totalPaketMasuk ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="dashboard-stat-card">
                        <div class="dashboard-stat-card__row">
                            <div class="dashboard-stat-card__icon dashboard-stat-card__icon--warning">
                                <i class="iconsax tw:text-3xl" icon-name="box-time"></i>
                            </div>
                            <div>
                                <span class="dashboard-stat-card__eyebrow">Belum Diambil</span>
                                <strong class="dashboard-stat-card__value"><?= $paketBelumDiambil ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="dashboard-stat-card">
                        <div class="dashboard-stat-card__row">
                            <div class="dashboard-stat-card__icon dashboard-stat-card__icon--danger">
                                <i class="iconsax tw:text-3xl" icon-name="danger"></i>
                            </div>
                            <div>
                                <span class="dashboard-stat-card__eyebrow">Izin Aktif</span>
                                <strong class="dashboard-stat-card__value"><?= $izinAktif ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="dashboard-stat-card">
                        <div class="dashboard-stat-card__row">
                            <div class="dashboard-stat-card__icon dashboard-stat-card__icon--success">
                                <i class="iconsax tw:text-3xl" icon-name="setting-2"></i>
                            </div>
                            <div>
                                <span class="dashboard-stat-card__eyebrow">Laporan Disetujui</span>
                                <strong class="dashboard-stat-card__value"><?= $diprosesAtauSelesai ?></strong>
                            </div>
                        </div>
                    </div>
                </div>

            <!-- 3. BLOK STATS UNTUK MAINTENANCE TEAM -->
            <?php elseif ($role === 'MAINTENANCE'): ?>
                <div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-2 tw:lg:grid-cols-4 tw:gap-6 tw:mb-10">
                    <div class="dashboard-stat-card">
                        <div class="dashboard-stat-card__row">
                            <div class="dashboard-stat-card__icon dashboard-stat-card__icon--warning">
                                <i class="iconsax tw:text-3xl" icon-name="clock-1"></i>
                            </div>
                            <div>
                                <span class="dashboard-stat-card__eyebrow">Antrean Tugas</span>
                                <strong class="dashboard-stat-card__value"><?= $pendingTasks ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="dashboard-stat-card">
                        <div class="dashboard-stat-card__row">
                            <div class="dashboard-stat-card__icon dashboard-stat-card__icon--primary">
                                <i class="iconsax tw:text-3xl" icon-name="setting-2"></i>
                            </div>
                            <div>
                                <span class="dashboard-stat-card__eyebrow">Sedang Diproses</span>
                                <strong class="dashboard-stat-card__value"><?= $myOngoingTasks ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="dashboard-stat-card">
                        <div class="dashboard-stat-card__row">
                            <div class="dashboard-stat-card__icon dashboard-stat-card__icon--success">
                                <i class="iconsax tw:text-3xl" icon-name="tick-circle"></i>
                            </div>
                            <div>
                                <span class="dashboard-stat-card__eyebrow">Selesai Diperbaiki</span>
                                <strong class="dashboard-stat-card__value"><?= $myCompletedTasks ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="dashboard-stat-card">
                        <div class="dashboard-stat-card__row">
                            <!-- Berwarna merah jika ada tugas skala darurat aktif yang harus diselesaikan segera -->
                            <div class="dashboard-stat-card__icon <?= $activeEmergencyTasks > 0 ? 'dashboard-stat-card__icon--danger' : 'tw:bg-slate-100 tw:text-slate-500' ?>">
                                <i class="iconsax tw:text-3xl" icon-name="danger"></i>
                            </div>
                            <div>
                                <span class="dashboard-stat-card__eyebrow">Darurat Aktif</span>
                                <strong class="dashboard-stat-card__value <?= $activeEmergencyTasks > 0 ? 'tw:text-red-600' : '' ?>"><?= $activeEmergencyTasks ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- BAGIAN PANEL INFORMASI MENYESUAIKAN ROLE -->
            <?php if ($role === 'PENGURUS'): ?>
                <div class="tw:grid tw:grid-cols-1 tw:lg:grid-cols-2 tw:gap-8">
                    <div class="dashboard-side-panel">
                        <h5 class="dashboard-side-panel__title">Ringkasan Pending</h5>
                        <p class="dashboard-side-panel__copy">Fokus utama pengurus hari ini ada di permintaan izin keluar, distribusi paket, dan laporan maintenance yang masih menunggu tindakan.</p>
                        <div class="dashboard-info-list">
                            <div class="dashboard-info-item">
                                <span class="dashboard-info-item__label">Permintaan izin keluar</span>
                                <strong><?= $pendingInOut ?> permintaan</strong>
                                <p>Perlu konfirmasi dari petugas SIGAP agar tidak menumpuk.</p>
                            </div>
                            <div class="dashboard-info-item">
                                <span class="dashboard-info-item__label">Paket belum diambil</span>
                                <strong><?= $pendingPackagePickup ?> paket</strong>
                                <p>Pantau paket yang masih menunggu diambil penghuni.</p>
                            </div>
                            <div class="dashboard-info-item">
                                <span class="dashboard-info-item__label">Laporan maintenance baru</span>
                                <strong><?= $pendingMaintenance ?> laporan</strong>
                                <p>Prioritaskan laporan yang masih berstatus diajukan.</p>
                            </div>
                        </div>
                    </div>

                    <div class="dashboard-side-panel">
                        <h5 class="dashboard-side-panel__title">Distribusi Gender Penghuni</h5>
                        <p class="dashboard-side-panel__copy">Ringkasan komposisi penghuni aktif untuk pemantauan cepat di level pengurus.</p>
                        <div class="tw:h-[320px] tw:flex tw:justify-center">
                            <canvas id="genderChart"></canvas>
                        </div>
                    </div>
                </div>
                <script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.min.js"></script>
                <script>
                    const ctx = document.getElementById('genderChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Laki-laki', 'Perempuan'],
                            datasets: [{
                                data: [<?= $chartData['L'] ?>, <?= $chartData['P'] ?>],
                                backgroundColor: ['#2F7FF0', '#FF7BB4'], // Blue for Male, Pink for Female
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                }
                            },
                            cutout: '70%'
                        }
                    });
                </script>

            <?php elseif ($role === 'PENGHUNI'): ?>
                <div class="tw:grid tw:grid-cols-1 tw:lg:grid-cols-2 tw:gap-8">
                    <div class="dashboard-side-panel">
                        <h5 class="dashboard-side-panel__title">Ringkasan Paket</h5>
                        <p class="dashboard-side-panel__copy">Pantau paket yang baru datang, yang belum diambil, dan kasus tertukar tanpa harus masuk ke menu paket dulu.</p>
                        <div class="dashboard-info-list">
                            <div class="dashboard-info-item">
                                <span class="dashboard-info-item__label">Belum diambil</span>
                                <strong><?= $paketBelumDiambil ?> paket</strong>
                                <p>Segera cek menu paket bila ada kiriman baru yang belum Anda ambil.</p>
                            </div>
                            <div class="dashboard-info-item">
                                <span class="dashboard-info-item__label">Sudah diambil</span>
                                <strong><?= $paketSudahDiambil ?> paket</strong>
                                <p>Riwayat pengambilan paket Anda sudah tercatat.</p>
                            </div>
                            <div class="dashboard-info-item">
                                <span class="dashboard-info-item__label">Tertukar</span>
                                <strong><?= $paketTertukar ?> paket</strong>
                                <p>Hubungi petugas SIGAP jika ada paket yang ditandai tertukar.</p>
                            </div>
                        </div>
                        <div class="page-summary-actions">
                            <a href="/doremi-app/dashboard/paket/" class="page-secondary-btn">Buka Menu Paket</a>
                        </div>
                    </div>

                    <div class="dashboard-side-panel">
                        <h5 class="dashboard-side-panel__title">Status Laporan Kerusakan</h5>
                        <p class="dashboard-side-panel__copy">Lihat progres laporan yang Anda kirim, mulai dari diajukan sampai selesai dikerjakan.</p>
                        <div class="dashboard-info-list">
                            <div class="dashboard-info-item">
                                <span class="dashboard-info-item__label">Diajukan</span>
                                <strong><?= $maintenanceSummary['Diajukan'] ?> laporan</strong>
                                <p>Masih menunggu diproses oleh unit maintenance.</p>
                            </div>
                            <div class="dashboard-info-item">
                                <span class="dashboard-info-item__label">Diproses</span>
                                <strong><?= $maintenanceSummary['Diproses'] ?> laporan</strong>
                                <p>Sudah diterima dan sedang dikerjakan.</p>
                            </div>
                            <div class="dashboard-info-item">
                                <span class="dashboard-info-item__label">Selesai</span>
                                <strong><?= $maintenanceSummary['Selesai'] ?> laporan</strong>
                                <p>Perbaikan telah diselesaikan oleh teknisi.</p>
                            </div>
                        </div>
                        <div class="page-summary-actions">
                            <a href="/doremi-app/dashboard/maintenance/" class="page-secondary-btn">Kelola Semua Pekerjaan</a>
                        </div>
                    </div>
                </div>

            <!-- 4. BERANDA DINAMIS KHUSUS UNTUK MAINTENANCE TEAM -->
            <?php elseif ($role === 'MAINTENANCE'): ?>
                <div class="tw:grid tw:grid-cols-1 tw:lg:grid-cols-2 tw:gap-8">
                    
                    <!-- Panel Kiri: Tugas Aktif yang Sedang Diproses -->
                    <div class="dashboard-side-panel">
                        <h5 class="dashboard-side-panel__title tw:flex tw:items-center tw:gap-2">
                            <i class="iconsax" icon-name="task-square"></i> Pekerjaan Aktif Saya
                        </h5>
                        <p class="dashboard-side-panel__copy">Daftar laporan kerusakan fasilitas asrama yang saat ini sedang Anda tangani/proses.</p>
                        
                        <div class="dashboard-guide-list">
                            <?php if (count($myTasks) > 0): ?>
                                <?php foreach ($myTasks as $task): ?>
                                    <div class="dashboard-guide-item" style="border-left-color: #2f7ff0;">
                                        <strong>
                                            <?php if (!empty($task['NamaRuangan'])): ?>
                                                Ruangan: <?= htmlspecialchars($task['NamaRuangan']) ?> (Lantai <?= htmlspecialchars($task['LantaiRuangan']) ?>)
                                            <?php elseif (!empty($task['NamaBarang'])): ?>
                                                Inventaris: <?= htmlspecialchars($task['NamaBarang']) ?>
                                            <?php endif; ?>
                                        </strong>
                                        <p class="tw:text-xs tw:mt-1 tw:text-slate-600">
                                            <strong>Pelapor:</strong> <?= htmlspecialchars($task['NamaPenghuni'] ?? $task['NamaReporterPetugas'] ?? 'Staff') ?><br>
                                            <strong>Masalah:</strong> <span class="tw:italic">"<?= htmlspecialchars($task['Deskripsi']) ?>"</span>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="dashboard-empty-state text-center tw:py-6">
                                    <i class="iconsax tw:text-2xl tw:mb-1 tw:block" icon-name="emoji-happy"></i>
                                    Anda sedang bebas dari tugas aktif yang diproses saat ini.
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="page-summary-actions">
                            <a href="/doremi-app/dashboard/maintenance/" class="page-secondary-btn">Buka Seluruh Laporan</a>
                        </div>
                    </div>

                    <!-- Panel Kanan: Protokol Keselamatan Kerja OSHA -->
                    <div class="dashboard-side-panel">
                        <h5 class="dashboard-side-panel__title tw:flex tw:items-center tw:gap-2">
                            <i class="iconsax" icon-name="shield"></i> Protokol Keselamatan & Kerja
                        </h5>
                        <p class="dashboard-side-panel__copy">Demi menjaga keselamatan kerja Anda dan penghuni asrama, mohon selalu patuhi instruksi OSHA berikut:</p>
                        
                        <div class="dashboard-guide-list">
                            <div class="dashboard-guide-item" style="border-left-color: #bc4f45;">
                                <strong>1. Selesaikan Darurat Terlebih Dahulu</strong>
                                <p class="tw:text-xs">Prioritaskan kerusakan yang berpotensi mencederai penghuni asrama atau merusak bangunan secara parah (seperti kebocoran gas atau korsleting listrik).</p>
                            </div>
                            <div class="dashboard-guide-item" style="border-left-color: #d48d2f;">
                                <strong>2. Gunakan APD Lengkap</strong>
                                <p class="tw:text-xs">Pastikan Anda menggunakan alat pelindung diri (sarung tangan tebal, kacamata pelindung, masker) saat bersentuhan dengan zat kimia atau kelistrikan.</p>
                            </div>
                            <div class="dashboard-guide-item" style="border-left-color: #2f7ff0;">
                                <strong>3. Foto Hasil Perbaikan</strong>
                                <p class="tw:text-xs">Kredibilitas tim pemeliharaan dihargai tinggi. Jangan lupa memotret hasil akhir pengerjaan sebelum mengubah status menjadi 'Selesai'.</p>
                            </div>
                        </div>
                    </div>

                </div>

            <?php else: ?>
                <div class="dashboard-side-panel">
                    <h5 class="dashboard-side-panel__title">Navigasi Cepat</h5>
                    <p class="dashboard-side-panel__copy">Gunakan menu di samping untuk membuka modul yang sesuai dengan peran Anda.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <?php require '../bootstrap.php'; ?>
</body>

</html>