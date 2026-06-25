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
    // Jalankan query utama (multi-JOIN) terlebih dahulu sebelum query lain
    $myTasksQuery = "
        SELECT m.MaintenanceID, m.JenisLaporan, m.Deskripsi, m.TanggalLapor,
               r.NamaRuangan, r.Lantai AS LantaiRuangan,
               p.NamaPenghuni, pt.NamaPetugas AS NamaReporterPetugas, i.NamaBarang
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
    mysqli_free_result($myTasksResult);

    $res = mysqli_query($db, "SELECT COUNT(*) as total FROM maintenance WHERE StatusMaintenance = 'Diajukan'");
    $pendingTasks = mysqli_fetch_assoc($res)['total'];
    mysqli_free_result($res);

    $res = mysqli_query($db, "SELECT COUNT(*) as total FROM maintenance WHERE StatusMaintenance = 'Diproses' AND PetugasID = $userId");
    $myOngoingTasks = mysqli_fetch_assoc($res)['total'];
    mysqli_free_result($res);

    $res = mysqli_query($db, "SELECT COUNT(*) as total FROM maintenance WHERE StatusMaintenance = 'Selesai' AND PetugasID = $userId");
    $myCompletedTasks = mysqli_fetch_assoc($res)['total'];
    mysqli_free_result($res);

    $res = mysqli_query($db, "SELECT COUNT(*) as total FROM maintenance WHERE JenisLaporan = 'Kerusakan Darurat / Berat' AND StatusMaintenance != 'Selesai'");
    $activeEmergencyTasks = mysqli_fetch_assoc($res)['total'];
    mysqli_free_result($res);

    // Pie chart: distribusi status laporan
    $pieData = ['Diajukan' => 0, 'Diproses' => 0, 'Selesai' => 0];
    $res = mysqli_query($db, "SELECT StatusMaintenance, COUNT(*) as total FROM maintenance WHERE StatusMaintenance IN ('Diajukan','Diproses','Selesai') GROUP BY StatusMaintenance");
    while ($row = mysqli_fetch_assoc($res)) { $pieData[$row['StatusMaintenance']] = (int)$row['total']; }
    mysqli_free_result($res);

    // Trend 7 hari
    $trend7Raw = [];
    $res = mysqli_query($db, "SELECT DATE(TanggalLapor) AS hari, COUNT(*) AS total FROM maintenance WHERE TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY hari ORDER BY hari ASC");
    while ($row = mysqli_fetch_assoc($res)) { $trend7Raw[$row['hari']] = (int)$row['total']; }
    mysqli_free_result($res);
    $trend7Labels = []; $trend7Values = [];
    for ($i = 6; $i >= 0; $i--) {
        $key = date('Y-m-d', strtotime("-$i day"));
        $trend7Labels[] = date('d M', strtotime("-$i day"));
        $trend7Values[] = $trend7Raw[$key] ?? 0;
    }

    // Trend 30 hari
    $trend30Raw = [];
    $res = mysqli_query($db, "SELECT DATE(TanggalLapor) AS hari, COUNT(*) AS total FROM maintenance WHERE TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 29 DAY) GROUP BY hari ORDER BY hari ASC");
    while ($row = mysqli_fetch_assoc($res)) { $trend30Raw[$row['hari']] = (int)$row['total']; }
    mysqli_free_result($res);
    $trend30Labels = []; $trend30Values = [];
    for ($i = 29; $i >= 0; $i--) {
        $key = date('Y-m-d', strtotime("-$i day"));
        $trend30Labels[] = date('d M', strtotime("-$i day"));
        $trend30Values[] = $trend30Raw[$key] ?? 0;
    }

    // Trend 6 bulan
    $trend6mRaw = [];
    $res = mysqli_query($db, "SELECT DATE_FORMAT(TanggalLapor, '%Y-%m') AS bulan, COUNT(*) AS total FROM maintenance WHERE TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH) GROUP BY bulan ORDER BY bulan ASC");
    while ($row = mysqli_fetch_assoc($res)) { $trend6mRaw[$row['bulan']] = (int)$row['total']; }
    mysqli_free_result($res);
    for ($i = 5; $i >= 0; $i--) {
        $key = date('Y-m', strtotime("-$i month"));
        $trend6mLabels[] = date('M Y', strtotime("-$i month"));
        $trend6mValues[] = $trend6mRaw[$key] ?? 0;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<?php require '../head.php'; ?>

<body class="dashboard-body tw:p-0 tw:m-0 tw:relative tw:flex tw:bg-[#f8fafc] tw:min-h-screen tw:overflow-x-hidden">
    <div
        class="tw:absolute tw:top-[-10%] tw:right-[-5%] tw:w-[500px] tw:h-[500px] tw:bg-primary/5 tw:rounded-full tw:blur-[120px] tw:pointer-events-none">
    </div>
    <div
        class="tw:absolute tw:bottom-[10%] tw:left-[20%] tw:w-[400px] tw:h-[400px] tw:bg-secondary/5 tw:rounded-full tw:blur-[100px] tw:pointer-events-none">
    </div>

    <?php require 'components/sidebar.php'; ?>
    <main class="dashboard-main tw:md:ml-[20.5rem] tw:grow tw:relative tw:z-10">
        <div class="dashboard-page tw:pt-24 tw:md:pt-9 tw:px-4 tw:md:px-8 tw:pb-8 tw:w-full">
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
                <div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-2 tw:xl:grid-cols-4 tw:gap-8 tw:mb-10">
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
                        <div class="dashboard-info-list" style="gap: 1.25rem;">
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
                        <div class="dashboard-info-list" style="gap: 1.25rem;">
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

                <!-- Baris 1: Pekerjaan Aktif + Protokol Keselamatan -->
                <div class="tw:grid tw:grid-cols-1 tw:lg:grid-cols-2 tw:gap-8 tw:mb-8">

                    <!-- Panel Kiri: Tugas Aktif (beautified) -->
                    <div class="dashboard-side-panel">
                        <h5 class="dashboard-side-panel__title tw:flex tw:items-center tw:gap-2">
                            <i class="fa-solid fa-list-check"></i> Pekerjaan Aktif Saya
                        </h5>
                        <p class="dashboard-side-panel__copy">Laporan yang saat ini sedang Anda tangani. Selesaikan sebelum mengambil tugas baru.</p>

                        <div class="tw:flex tw:flex-col tw:gap-3 tw:mt-4">
                            <?php if (count($myTasks) > 0): ?>
                                <?php foreach ($myTasks as $task): ?>
                                    <?php
                                        $jenisColor = match($task['JenisLaporan']) {
                                            'Kerusakan' => '#ef4444',
                                            'Kebersihan' => '#10b981',
                                            default      => '#f59e0b',
                                        };
                                        $jenisIcon = match($task['JenisLaporan']) {
                                            'Kerusakan' => 'fa-solid fa-triangle-exclamation',
                                            'Kebersihan' => 'fa-solid fa-broom',
                                            default      => 'fa-solid fa-circle-question',
                                        };
                                        $lokasi = !empty($task['NamaRuangan'])
                                            ? 'Ruangan ' . htmlspecialchars($task['NamaRuangan']) . ' · Lantai ' . htmlspecialchars($task['LantaiRuangan'])
                                            : (!empty($task['NamaBarang']) ? 'Inventaris: ' . htmlspecialchars($task['NamaBarang']) : '—');
                                    ?>
                                    <div style="border:1px solid #e2e8f0; border-left:4px solid <?= $jenisColor ?>; border-radius:12px; padding:14px 16px; background:#fff; display:flex; gap:12px; align-items:flex-start;">
                                        <div style="width:36px; height:36px; border-radius:8px; background:<?= $jenisColor ?>18; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                            <i class="<?= $jenisIcon ?>" style="color:<?= $jenisColor ?>; font-size:18px;"></i>
                                        </div>
                                        <div style="flex:1; min-width:0;">
                                            <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap;">
                                                <span style="font-size:13px; font-weight:600; color:#1e293b;"><?= $lokasi ?></span>
                                                <span style="font-size:11px; font-weight:500; color:<?= $jenisColor ?>; background:<?= $jenisColor ?>18; padding:2px 8px; border-radius:20px; white-space:nowrap;"><?= htmlspecialchars($task['JenisLaporan']) ?></span>
                                            </div>
                                            <p style="margin:4px 0 0; font-size:12px; color:#64748b; line-height:1.5; overflow:hidden; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;">
                                                <?= htmlspecialchars($task['Deskripsi']) ?>
                                            </p>
                                            <div style="margin-top:8px; display:flex; align-items:center; gap:6px;">
                                                <i class="fa-solid fa-user" style="font-size:11px; color:#94a3b8;"></i>
                                                <span style="font-size:11px; color:#94a3b8;"><?= htmlspecialchars($task['NamaPenghuni'] ?? $task['NamaReporterPetugas'] ?? 'Staff') ?></span>
                                                <span style="color:#cbd5e1; margin:0 2px;">·</span>
                                                <i class="fa-solid fa-calendar-days" style="font-size:11px; color:#94a3b8;"></i>
                                                <span style="font-size:11px; color:#94a3b8;"><?= date('d M Y', strtotime($task['TanggalLapor'])) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div style="text-align:center; padding:32px 16px; color:#94a3b8;">
                                    <i class="fa-regular fa-face-smile-beam fa-2x tw:block tw:mb-3" style="color:#19a7ce;"></i>
                                    <p style="font-size:14px; font-weight:500; color:#475569; margin:0 0 4px;">Tidak ada tugas aktif</p>
                                    <p style="font-size:12px; color:#94a3b8; margin:0;">Anda bebas dari pekerjaan yang sedang diproses saat ini.</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="page-summary-actions">
                            <a href="/doremi-app/dashboard/maintenance/" class="page-secondary-btn">Buka Seluruh Laporan</a>
                        </div>
                    </div>

                    <!-- Panel Kanan: Protokol Keselamatan Kerja -->
                    <div class="dashboard-side-panel">
                        <h5 class="dashboard-side-panel__title tw:flex tw:items-center tw:gap-2">
                            <i class="fa-solid fa-shield-halved"></i> Protokol Keselamatan &amp; Kerja
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

                <!-- Baris 2: Charts (Pie + Trend) -->
                <div class="tw:grid tw:grid-cols-1 tw:lg:grid-cols-2 tw:gap-8">

                    <!-- Pie Chart: Distribusi Status Laporan -->
                    <div class="dashboard-side-panel">
                        <h5 class="dashboard-side-panel__title tw:flex tw:items-center tw:gap-2">
                            <i class="fa-solid fa-chart-pie"></i> Distribusi Status Laporan
                        </h5>
                        <p class="dashboard-side-panel__copy">Proporsi laporan berdasarkan status: menunggu, sedang dikerjakan, dan selesai.</p>
                        <div class="tw:h-[260px] tw:flex tw:justify-center tw:mt-2">
                            <canvas id="maintenancePieChart"></canvas>
                        </div>
                    </div>

                    <!-- Trend Chart dengan date range filter -->
                    <div class="dashboard-side-panel">
                        <div class="tw:flex tw:items-center tw:justify-between tw:flex-wrap tw:gap-3 tw:mb-1">
                            <h5 class="dashboard-side-panel__title tw:flex tw:items-center tw:gap-2 tw:mb-0">
                                <i class="fa-solid fa-arrow-trend-up"></i> Trend Laporan Masuk
                            </h5>
                            <div style="display:flex; gap:6px;">
                                <button id="btn7d" onclick="switchTrend('7d')" style="font-size:11px; padding:4px 10px; border-radius:20px; border:1.5px solid #146c94; background:#146c94; color:#fff; cursor:pointer; font-weight:600;">7 Hari</button>
                                <button id="btn30d" onclick="switchTrend('30d')" style="font-size:11px; padding:4px 10px; border-radius:20px; border:1.5px solid #e2e8f0; background:#fff; color:#64748b; cursor:pointer; font-weight:500;">30 Hari</button>
                                <button id="btn6m" onclick="switchTrend('6m')" style="font-size:11px; padding:4px 10px; border-radius:20px; border:1.5px solid #e2e8f0; background:#fff; color:#64748b; cursor:pointer; font-weight:500;">6 Bulan</button>
                            </div>
                        </div>
                        <p class="dashboard-side-panel__copy" id="trendDescription">Jumlah laporan maintenance yang masuk dalam 7 hari terakhir.</p>
                        <div class="tw:h-[220px] tw:mt-2">
                            <canvas id="maintenanceTrendChart"></canvas>
                        </div>
                    </div>

                </div>

                <script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.min.js"></script>
                <script>
                    (function() {
                        // Pie Chart
                        const pieCtx = document.getElementById('maintenancePieChart').getContext('2d');
                        new Chart(pieCtx, {
                            type: 'doughnut',
                            data: {
                                labels: ['Diajukan', 'Diproses', 'Selesai'],
                                datasets: [{
                                    data: [<?= $pieData['Diajukan'] ?>, <?= $pieData['Diproses'] ?>, <?= $pieData['Selesai'] ?>],
                                    backgroundColor: ['#f59e0b', '#2F7FF0', '#10b981'],
                                    borderWidth: 0,
                                    hoverOffset: 8
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                cutout: '68%',
                                plugins: {
                                    legend: { position: 'bottom', labels: { boxWidth: 12, padding: 16, font: { size: 12 } } }
                                }
                            }
                        });

                        // Trend Chart datasets
                        const trendDatasets = {
                            '7d':  { labels: <?= json_encode($trend7Labels) ?>,  values: <?= json_encode($trend7Values) ?>,  desc: 'Jumlah laporan maintenance yang masuk dalam 7 hari terakhir.' },
                            '30d': { labels: <?= json_encode($trend30Labels) ?>, values: <?= json_encode($trend30Values) ?>, desc: 'Jumlah laporan maintenance yang masuk dalam 30 hari terakhir.' },
                            '6m':  { labels: <?= json_encode($trend6mLabels) ?>, values: <?= json_encode($trend6mValues) ?>, desc: 'Jumlah laporan maintenance yang masuk dalam 6 bulan terakhir.' },
                        };

                        const trendCtx = document.getElementById('maintenanceTrendChart').getContext('2d');
                        const trendChart = new Chart(trendCtx, {
                            type: 'line',
                            data: {
                                labels: trendDatasets['7d'].labels,
                                datasets: [{
                                    label: 'Laporan Masuk',
                                    data: trendDatasets['7d'].values,
                                    borderColor: '#146c94',
                                    backgroundColor: 'rgba(20,108,148,0.08)',
                                    borderWidth: 2.5,
                                    pointBackgroundColor: '#146c94',
                                    pointRadius: 4,
                                    fill: true,
                                    tension: 0.4
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { display: false } },
                                scales: {
                                    y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } }, grid: { color: 'rgba(0,0,0,0.05)' } },
                                    x: { ticks: { font: { size: 11 }, maxRotation: 45, maxTicksLimit: 8 }, grid: { display: false } }
                                }
                            }
                        });

                        const btnStyle = { active: { background: '#146c94', color: '#fff', borderColor: '#146c94', fontWeight: '600' }, inactive: { background: '#fff', color: '#64748b', borderColor: '#e2e8f0', fontWeight: '500' } };

                        window.switchTrend = function(range) {
                            const d = trendDatasets[range];
                            trendChart.data.labels = d.labels;
                            trendChart.data.datasets[0].data = d.values;
                            trendChart.update();
                            document.getElementById('trendDescription').textContent = d.desc;
                            ['7d','30d','6m'].forEach(r => {
                                const btn = document.getElementById('btn' + r);
                                const s = r === range ? btnStyle.active : btnStyle.inactive;
                                Object.assign(btn.style, { background: s.background, color: s.color, borderColor: s.borderColor, fontWeight: s.fontWeight });
                            });
                        };
                    })();
                </script>

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
