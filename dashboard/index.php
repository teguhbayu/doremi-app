<?php
session_start();
if (!isset($_SESSION['userId'])) {
    header("Location: /doremi-app/login.php");
    exit;
}
require '../db.php';
require '../database/dashboard.php';

$role = $_SESSION['userRole'];
$userName = $_SESSION['userName'];
$userId = (int) ($_SESSION['userId'] ?? 0);

$stats = [];
if ($role === 'PENGURUS') {
    $pengurusStats = fetchDashboardPengurusStats($db);
    $activePenghuni = $pengurusStats['activePenghuni'];
    $pendingInOut = $pengurusStats['pendingInOut'];
    $pendingMaintenance = $pengurusStats['pendingMaintenance'];
    $pendingPackagePickup = $pengurusStats['pendingPackagePickup'];

    $genderStats = fetchDashboardGenderStats($db);
    $chartData = ['L' => 0, 'P' => 0];
    foreach ($genderStats as $row) {
        $chartData[$row['JenisKelamin']] = $row['count'];
    }
}

if ($role === 'PENGHUNI') {
    $izinAktif = fetchDashboardPenghuniIzinAktif($db, $userId);

    $paketSummaryRows = fetchDashboardPenghuniPaketSummary($db, $userId);
    $totalPaketMasuk = count($paketSummaryRows);
    $paketBelumDiambil = count(array_filter($paketSummaryRows, static fn(array $row): bool => ($row['Status'] ?? 'Belum Diambil') === 'Belum Diambil'));
    $paketSudahDiambil = count(array_filter($paketSummaryRows, static fn(array $row): bool => ($row['Status'] ?? '') === 'Sudah Diambil'));
    $paketTertukar = count(array_filter($paketSummaryRows, static fn(array $row): bool => ($row['Status'] ?? '') === 'TERTUKAR'));

    $maintenanceSummaryRows = fetchDashboardPenghuniMaintenanceSummary($db, $userId);
    $maintenanceSummary = ['Diajukan' => 0, 'Diproses' => 0, 'Selesai' => 0];
    foreach ($maintenanceSummaryRows as $row) {
        $status = $row['StatusMaintenance'] ?? '';
        if (array_key_exists($status, $maintenanceSummary)) {
            $maintenanceSummary[$status]++;
        }
    }

    $diprosesAtauSelesai = $maintenanceSummary['Diproses'] + $maintenanceSummary['Selesai'];
}

if ($role === 'SIGAP') {
    $pendingConfirmation = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as total FROM inoutpenghuni WHERE Status = 'Pending'"))['total'];
    $currentlyOutside = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as total FROM inoutpenghuni WHERE Status = 'Keluar'"))['total'];
    $pendingPackagePickupSigap = mysqli_fetch_assoc(mysqli_query(
        $db,
        "SELECT COUNT(*) AS total
         FROM paket pk
         $latestPickupDashboardSubquery
         WHERE pp.PengambilanPaketID IS NULL OR pp.Status = 'Belum Diambil'"
    ))['total'];
    $packagesToday = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as total FROM paket WHERE DATE(WaktuSampai) = CURDATE()"))['total'];
}

// AMBIL DATA DASHBOARD KHUSUS TIM MAINTENANCE
if ($role === 'MAINTENANCE') {
    $myTasks = fetchDashboardMyTasks($db, $userId);

    $maintenanceCounts = fetchDashboardMaintenanceCounts($db, $userId);
    $pendingTasks = $maintenanceCounts['pendingTasks'];
    $myOngoingTasks = $maintenanceCounts['myOngoingTasks'];
    $myCompletedTasks = $maintenanceCounts['myCompletedTasks'];
    $activeEmergencyTasks = $maintenanceCounts['activeEmergencyTasks'];

    // Ambil detail laporan darurat yang masih aktif
    $emergencyList = fetchDashboardEmergencyList($db);

    // Pie chart: distribusi status laporan
    $pieData = ['Diajukan' => 0, 'Diproses' => 0, 'Selesai' => 0];
    foreach (fetchMaintenanceStatusPie($db) as $row) {
        $pieData[$row['StatusMaintenance']] = (int) $row['total'];
    }

    // Trend 7 hari
    $trend7Raw = [];
    foreach (fetchMaintenanceTrendDaily($db, 6) as $row) {
        $trend7Raw[$row['hari']] = (int) $row['total'];
    }
    $trend7Labels = [];
    $trend7Values = [];
    for ($i = 6; $i >= 0; $i--) {
        $key = date('Y-m-d', strtotime("-$i day"));
        $trend7Labels[] = date('d M', strtotime("-$i day"));
        $trend7Values[] = $trend7Raw[$key] ?? 0;
    }

    // Trend 30 hari
    $trend30Raw = [];
    foreach (fetchMaintenanceTrendDaily($db, 29) as $row) {
        $trend30Raw[$row['hari']] = (int) $row['total'];
    }
    $trend30Labels = [];
    $trend30Values = [];
    for ($i = 29; $i >= 0; $i--) {
        $key = date('Y-m-d', strtotime("-$i day"));
        $trend30Labels[] = date('d M', strtotime("-$i day"));
        $trend30Values[] = $trend30Raw[$key] ?? 0;
    }

    // Trend 6 bulan
    $trend6mRaw = [];
    foreach (fetchMaintenanceTrendMonthly($db) as $row) {
        $trend6mRaw[$row['bulan']] = (int) $row['total'];
    }
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

<body class="dashboard-body tw:p-0 tw:m-0 tw:relative tw:flex tw:min-h-screen tw:overflow-x-hidden">
    <?php require 'components/sidebar.php'; ?>
    <main class="tw:md:ml-75 tw:grow tw:relative tw:z-10">
        <div class="tw:pt-20 tw:md:pt-8 tw:px-8 tw:mb-8 tw:flex-1 tw:w-dvw tw:md:w-full">
            <h1 class="page-title" data-kicker="Dashboard Utama"
                data-subtitle="Selamat datang kembali di dashboard DOREMI. Semua aktivitas operasional Dormitory ada di satu tempat dan mengikuti pola kerja yang sama.">
                Halo, <?= htmlspecialchars($userName) ?>
            </h1>

            <!-- 1. BLOK STATS UNTUK PENGURUS -->
            <?php if ($role === 'PENGURUS'): ?>
                <div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-2 tw:lg:grid-cols-4 tw:gap-6 tw:mb-10">
                    <div data-gsap="stat-card"
                        class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.85)] tw:shadow-sm">
                        <div class="tw:flex tw:items-center tw:gap-4">
                            <div
                                class="tw:w-[3.4rem] tw:h-[3.4rem] tw:inline-flex tw:items-center tw:justify-center tw:rounded-[20px] tw:flex-shrink-0 tw:text-primary tw:bg-accent/80">
                                <i class="iconsax tw:text-3xl" icon-name="group"></i>
                            </div>
                            <div>
                                <span
                                    class="tw:block tw:text-slate-500 tw:text-[0.72rem] tw:font-extrabold tw:tracking-[0.08em] tw:uppercase">Penghuni</span>
                                <strong
                                    class="tw:block tw:mt-[0.3rem] tw:text-[1.9rem] tw:leading-none tw:text-slate-900 tw:font-bold"
                                    data-count="<?= $activePenghuni ?>">0</strong>
                            </div>
                        </div>
                    </div>
                    <div data-gsap="stat-card"
                        class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.85)] tw:shadow-sm">
                        <div class="tw:flex tw:items-center tw:gap-4">
                            <div
                                class="tw:w-[3.4rem] tw:h-[3.4rem] tw:inline-flex tw:items-center tw:justify-center tw:rounded-[20px] tw:flex-shrink-0 tw:text-amber-700 tw:bg-[rgba(250,236,207,0.82)]">
                                <i class="iconsax tw:text-3xl" icon-name="clock"></i>
                            </div>
                            <div>
                                <span
                                    class="tw:block tw:text-slate-500 tw:text-[0.72rem] tw:font-extrabold tw:tracking-[0.08em] tw:uppercase">Antrean
                                    Izin</span>
                                <strong
                                    class="tw:block tw:mt-[0.3rem] tw:text-[1.9rem] tw:leading-none tw:text-slate-900 tw:font-bold"
                                    data-count="<?= $pendingInOut ?>">0</strong>
                            </div>
                        </div>
                    </div>
                    <div data-gsap="stat-card"
                        class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.85)] tw:shadow-sm">
                        <div class="tw:flex tw:items-center tw:gap-4">
                            <div
                                class="tw:w-[3.4rem] tw:h-[3.4rem] tw:inline-flex tw:items-center tw:justify-center tw:rounded-[20px] tw:flex-shrink-0 tw:text-emerald-800 tw:bg-[rgba(220,244,239,0.82)]">
                                <i class="iconsax tw:text-3xl" icon-name="box-time"></i>
                            </div>
                            <div>
                                <span
                                    class="tw:block tw:text-slate-500 tw:text-[0.72rem] tw:font-extrabold tw:tracking-[0.08em] tw:uppercase">Paket
                                    Tertunda</span>
                                <strong
                                    class="tw:block tw:mt-[0.3rem] tw:text-[1.9rem] tw:leading-none tw:text-slate-900 tw:font-bold"
                                    data-count="<?= $pendingPackagePickup ?>">0</strong>
                            </div>
                        </div>
                    </div>
                    <div data-gsap="stat-card"
                        class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.85)] tw:shadow-sm">
                        <div class="tw:flex tw:items-center tw:gap-4">
                            <div
                                class="tw:w-[3.4rem] tw:h-[3.4rem] tw:inline-flex tw:items-center tw:justify-center tw:rounded-[20px] tw:flex-shrink-0 tw:text-amber-700 tw:bg-[rgba(250,236,207,0.82)]">
                                <i class="iconsax tw:text-3xl" icon-name="setting-2"></i>
                            </div>
                            <div>
                                <span
                                    class="tw:block tw:text-slate-500 tw:text-[0.72rem] tw:font-extrabold tw:tracking-[0.08em] tw:uppercase">Antrean
                                    Perbaikan</span>
                                <strong
                                    class="tw:block tw:mt-[0.3rem] tw:text-[1.9rem] tw:leading-none tw:text-slate-900 tw:font-bold"
                                    data-count="<?= $pendingMaintenance ?>">0</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. BLOK STATS UNTUK PENGHUNI -->
            <?php elseif ($role === 'PENGHUNI'): ?>
                <div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-2 tw:xl:grid-cols-4 tw:gap-6 tw:mb-10">
                    <div data-gsap="stat-card"
                        class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.85)] tw:shadow-sm">
                        <div class="tw:flex tw:items-center tw:gap-4">
                            <div
                                class="tw:w-[3.4rem] tw:h-[3.4rem] tw:inline-flex tw:items-center tw:justify-center tw:rounded-[20px] tw:flex-shrink-0 tw:text-primary tw:bg-accent/80">
                                <i class="iconsax tw:text-3xl" icon-name="box-1"></i>
                            </div>
                            <div>
                                <span
                                    class="tw:block tw:text-slate-500 tw:text-[0.72rem] tw:font-extrabold tw:tracking-[0.08em] tw:uppercase">Paket
                                    Masuk</span>
                                <strong
                                    class="tw:block tw:mt-[0.3rem] tw:text-[1.9rem] tw:leading-none tw:text-slate-900 tw:font-bold"
                                    data-count="<?= $totalPaketMasuk ?>">0</strong>
                            </div>
                        </div>
                    </div>
                    <div data-gsap="stat-card"
                        class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.85)] tw:shadow-sm">
                        <div class="tw:flex tw:items-center tw:gap-4">
                            <div
                                class="tw:w-[3.4rem] tw:h-[3.4rem] tw:inline-flex tw:items-center tw:justify-center tw:rounded-[20px] tw:flex-shrink-0 tw:text-amber-700 tw:bg-[rgba(250,236,207,0.82)]">
                                <i class="iconsax tw:text-3xl" icon-name="box-time"></i>
                            </div>
                            <div>
                                <span
                                    class="tw:block tw:text-slate-500 tw:text-[0.72rem] tw:font-extrabold tw:tracking-[0.08em] tw:uppercase">Belum
                                    Diambil</span>
                                <strong
                                    class="tw:block tw:mt-[0.3rem] tw:text-[1.9rem] tw:leading-none tw:text-slate-900 tw:font-bold"
                                    data-count="<?= $paketBelumDiambil ?>">0</strong>
                            </div>
                        </div>
                    </div>
                    <div data-gsap="stat-card"
                        class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.85)] tw:shadow-sm">
                        <div class="tw:flex tw:items-center tw:gap-4">
                            <div
                                class="tw:w-[3.4rem] tw:h-[3.4rem] tw:inline-flex tw:items-center tw:justify-center tw:rounded-[20px] tw:flex-shrink-0 tw:text-red-700 tw:bg-[rgba(245,221,218,0.82)]">
                                <i class="iconsax tw:text-3xl" icon-name="danger"></i>
                            </div>
                            <div>
                                <span
                                    class="tw:block tw:text-slate-500 tw:text-[0.72rem] tw:font-extrabold tw:tracking-[0.08em] tw:uppercase">Izin
                                    Aktif</span>
                                <strong
                                    class="tw:block tw:mt-[0.3rem] tw:text-[1.9rem] tw:leading-none tw:text-slate-900 tw:font-bold"
                                    data-count="<?= $izinAktif ?>">0</strong>
                            </div>
                        </div>
                    </div>
                    <div data-gsap="stat-card"
                        class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.85)] tw:shadow-sm">
                        <div class="tw:flex tw:items-center tw:gap-4">
                            <div
                                class="tw:w-[3.4rem] tw:h-[3.4rem] tw:inline-flex tw:items-center tw:justify-center tw:rounded-[20px] tw:flex-shrink-0 tw:text-emerald-800 tw:bg-[rgba(220,244,239,0.82)]">
                                <i class="iconsax tw:text-3xl" icon-name="setting-2"></i>
                            </div>
                            <div>
                                <span
                                    class="tw:block tw:text-slate-500 tw:text-[0.72rem] tw:font-extrabold tw:tracking-[0.08em] tw:uppercase">Laporan
                                    Disetujui</span>
                                <strong
                                    class="tw:block tw:mt-[0.3rem] tw:text-[1.9rem] tw:leading-none tw:text-slate-900 tw:font-bold"
                                    data-count="<?= $diprosesAtauSelesai ?>">0</strong>
                            </div>
                        </div>
                    </div>
                </div>

            <!-- 3. BLOK STATS UNTUK SIGAP -->
            <?php elseif ($role === 'SIGAP'): ?>
                <div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-2 tw:lg:grid-cols-4 tw:gap-6 tw:mb-10">
                    <div data-gsap="stat-card" class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.85)] tw:shadow-sm">
                        <div class="tw:flex tw:items-center tw:gap-4">
                            <div class="tw:w-[3.4rem] tw:h-[3.4rem] tw:inline-flex tw:items-center tw:justify-center tw:rounded-[20px] tw:flex-shrink-0 tw:text-amber-700 tw:bg-[rgba(250,236,207,0.82)]">
                                <i class="iconsax tw:text-3xl" icon-name="clock"></i>
                            </div>
                            <div>
                                <span class="tw:block tw:text-slate-500 tw:text-[0.72rem] tw:font-extrabold tw:tracking-[0.08em] tw:uppercase">Menunggu Konfirmasi</span>
                                <strong class="tw:block tw:mt-[0.3rem] tw:text-[1.9rem] tw:leading-none tw:text-slate-900 tw:font-bold" data-count="<?= $pendingConfirmation ?>">0</strong>
                            </div>
                        </div>
                    </div>
                    <div data-gsap="stat-card" class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.85)] tw:shadow-sm">
                        <div class="tw:flex tw:items-center tw:gap-4">
                            <div class="tw:w-[3.4rem] tw:h-[3.4rem] tw:inline-flex tw:items-center tw:justify-center tw:rounded-[20px] tw:flex-shrink-0 tw:text-red-700 tw:bg-[rgba(245,221,218,0.82)]">
                                <i class="iconsax tw:text-3xl" icon-name="logout-1"></i>
                            </div>
                            <div>
                                <span class="tw:block tw:text-slate-500 tw:text-[0.72rem] tw:font-extrabold tw:tracking-[0.08em] tw:uppercase">Sedang di Luar</span>
                                <strong class="tw:block tw:mt-[0.3rem] tw:text-[1.9rem] tw:leading-none tw:text-slate-900 tw:font-bold" data-count="<?= $currentlyOutside ?>">0</strong>
                            </div>
                        </div>
                    </div>
                    <div data-gsap="stat-card" class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.85)] tw:shadow-sm">
                        <div class="tw:flex tw:items-center tw:gap-4">
                            <div class="tw:w-[3.4rem] tw:h-[3.4rem] tw:inline-flex tw:items-center tw:justify-center tw:rounded-[20px] tw:flex-shrink-0 tw:text-emerald-800 tw:bg-[rgba(220,244,239,0.82)]">
                                <i class="iconsax tw:text-3xl" icon-name="box-time"></i>
                            </div>
                            <div>
                                <span class="tw:block tw:text-slate-500 tw:text-[0.72rem] tw:font-extrabold tw:tracking-[0.08em] tw:uppercase">Paket Belum Diambil</span>
                                <strong class="tw:block tw:mt-[0.3rem] tw:text-[1.9rem] tw:leading-none tw:text-slate-900 tw:font-bold" data-count="<?= $pendingPackagePickupSigap ?>">0</strong>
                            </div>
                        </div>
                    </div>
                    <div data-gsap="stat-card" class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.85)] tw:shadow-sm">
                        <div class="tw:flex tw:items-center tw:gap-4">
                            <div class="tw:w-[3.4rem] tw:h-[3.4rem] tw:inline-flex tw:items-center tw:justify-center tw:rounded-[20px] tw:flex-shrink-0 tw:text-primary tw:bg-accent/80">
                                <i class="iconsax tw:text-3xl" icon-name="box-1"></i>
                            </div>
                            <div>
                                <span class="tw:block tw:text-slate-500 tw:text-[0.72rem] tw:font-extrabold tw:tracking-[0.08em] tw:uppercase">Paket Tercatat Hari Ini</span>
                                <strong class="tw:block tw:mt-[0.3rem] tw:text-[1.9rem] tw:leading-none tw:text-slate-900 tw:font-bold" data-count="<?= $packagesToday ?>">0</strong>
                            </div>
                        </div>
                    </div>
                </div>

            <!-- 4. BLOK STATS UNTUK MAINTENANCE TEAM -->
            <?php elseif ($role === 'MAINTENANCE'): ?>
                <div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-2 tw:lg:grid-cols-4 tw:gap-6 tw:mb-10">
                    <div data-gsap="stat-card"
                        class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.85)] tw:shadow-sm">
                        <div class="tw:flex tw:items-center tw:gap-4">
                            <div
                                class="tw:w-[3.4rem] tw:h-[3.4rem] tw:inline-flex tw:items-center tw:justify-center tw:rounded-[20px] tw:flex-shrink-0 tw:text-amber-700 tw:bg-[rgba(250,236,207,0.82)]">
                                <i class="iconsax tw:text-3xl" icon-name="clock"></i>
                            </div>
                            <div>
                                <span
                                    class="tw:block tw:text-slate-500 tw:text-[0.72rem] tw:font-extrabold tw:tracking-[0.08em] tw:uppercase">Antrean
                                    Tugas</span>
                                <strong
                                    class="tw:block tw:mt-[0.3rem] tw:text-[1.9rem] tw:leading-none tw:text-slate-900 tw:font-bold"
                                    data-count="<?= $pendingTasks ?>">0</strong>
                            </div>
                        </div>
                    </div>
                    <div data-gsap="stat-card"
                        class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.85)] tw:shadow-sm">
                        <div class="tw:flex tw:items-center tw:gap-4">
                            <div
                                class="tw:w-[3.4rem] tw:h-[3.4rem] tw:inline-flex tw:items-center tw:justify-center tw:rounded-[20px] tw:flex-shrink-0 tw:text-primary tw:bg-accent/80">
                                <i class="iconsax tw:text-3xl" icon-name="setting-2"></i>
                            </div>
                            <div>
                                <span
                                    class="tw:block tw:text-slate-500 tw:text-[0.72rem] tw:font-extrabold tw:tracking-[0.08em] tw:uppercase">Sedang
                                    Diproses</span>
                                <strong
                                    class="tw:block tw:mt-[0.3rem] tw:text-[1.9rem] tw:leading-none tw:text-slate-900 tw:font-bold"
                                    data-count="<?= $myOngoingTasks ?>">0</strong>
                            </div>
                        </div>
                    </div>
                    <div data-gsap="stat-card"
                        class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.85)] tw:shadow-sm">
                        <div class="tw:flex tw:items-center tw:gap-4">
                            <div
                                class="tw:w-[3.4rem] tw:h-[3.4rem] tw:inline-flex tw:items-center tw:justify-center tw:rounded-[20px] tw:flex-shrink-0 tw:text-emerald-800 tw:bg-[rgba(220,244,239,0.82)]">
                                <i class="iconsax tw:text-3xl" icon-name="tick-circle"></i>
                            </div>
                            <div>
                                <span
                                    class="tw:block tw:text-slate-500 tw:text-[0.72rem] tw:font-extrabold tw:tracking-[0.08em] tw:uppercase">Selesai
                                    Diperbaiki</span>
                                <strong
                                    class="tw:block tw:mt-[0.3rem] tw:text-[1.9rem] tw:leading-none tw:text-slate-900 tw:font-bold"
                                    data-count="<?= $myCompletedTasks ?>">0</strong>
                            </div>
                        </div>
                    </div>
                    <div data-gsap="stat-card"
                        class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.85)] tw:shadow-sm">
                        <div class="tw:flex tw:items-center tw:gap-4">
                            <!-- Berwarna merah jika ada tugas skala darurat aktif yang harus diselesaikan segera -->
                            <div
                                class="tw:w-[3.4rem] tw:h-[3.4rem] tw:inline-flex tw:items-center tw:justify-center tw:rounded-[20px] tw:flex-shrink-0 <?= $activeEmergencyTasks > 0 ? 'tw:text-red-700 tw:bg-[rgba(245,221,218,0.82)]' : 'tw:bg-slate-100 tw:text-slate-500' ?>">
                                <i class="iconsax tw:text-3xl" icon-name="danger"></i>
                            </div>
                            <div>
                                <span
                                    class="tw:block tw:text-slate-500 tw:text-[0.72rem] tw:font-extrabold tw:tracking-[0.08em] tw:uppercase">Darurat
                                    Aktif</span>
                                <strong
                                    class="tw:block tw:mt-[0.3rem] tw:text-[1.9rem] tw:leading-none tw:text-slate-900 tw:font-bold <?= $activeEmergencyTasks > 0 ? 'tw:text-red-600' : '' ?>"
                                    data-count="<?= $activeEmergencyTasks ?>">0</strong>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- BAGIAN PANEL INFORMASI MENYESUAIKAN ROLE -->
            <?php if ($role === 'PENGURUS'): ?>
                <div class="tw:grid tw:grid-cols-1 tw:lg:grid-cols-2 tw:gap-8">
                    <div data-gsap="panel"
                        class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm">
                        <h5 class="tw:m-0 tw:text-[1.2rem] tw:text-slate-900">Ringkasan Pending</h5>
                        <p class="tw:m-0 tw:text-slate-500 tw:leading-[1.75] tw:text-sm">Fokus utama pengurus hari ini ada
                            di permintaan izin keluar, distribusi paket, dan laporan maintenance yang masih menunggu
                            tindakan.</p>
                        <div class="tw:grid tw:gap-[0.85rem]">
                            <div
                                class="tw:p-4 tw:rounded-[18px] tw:bg-[rgba(255,255,255,0.80)] tw:border tw:border-[rgba(22,60,122,0.08)]">
                                <span class="tw:block tw:mb-[0.3rem] tw:text-slate-500 tw:text-xs tw:font-bold">Permintaan
                                    izin keluar</span>
                                <strong><?= $pendingInOut ?> permintaan</strong>
                                <p>Perlu konfirmasi dari petugas SIGAP agar tidak menumpuk.</p>
                            </div>
                            <div
                                class="tw:p-4 tw:rounded-[18px] tw:bg-[rgba(255,255,255,0.80)] tw:border tw:border-[rgba(22,60,122,0.08)]">
                                <span class="tw:block tw:mb-[0.3rem] tw:text-slate-500 tw:text-xs tw:font-bold">Paket belum
                                    diambil</span>
                                <strong><?= $pendingPackagePickup ?> paket</strong>
                                <p>Pantau paket yang masih menunggu diambil penghuni.</p>
                            </div>
                            <div
                                class="tw:p-4 tw:rounded-[18px] tw:bg-[rgba(255,255,255,0.80)] tw:border tw:border-[rgba(22,60,122,0.08)]">
                                <span class="tw:block tw:mb-[0.3rem] tw:text-slate-500 tw:text-xs tw:font-bold">Laporan
                                    maintenance baru</span>
                                <strong><?= $pendingMaintenance ?> laporan</strong>
                                <p>Prioritaskan laporan yang masih berstatus diajukan.</p>
                            </div>
                        </div>
                    </div>

                    <div data-gsap="panel"
                        class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm">
                        <h5 class="tw:m-0 tw:text-[1.2rem] tw:text-slate-900">Distribusi Gender Penghuni</h5>
                        <p class="tw:m-0 tw:text-slate-500 tw:leading-[1.75] tw:text-sm">Ringkasan komposisi penghuni aktif
                            untuk pemantauan cepat di level pengurus.</p>
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
                    <div data-gsap="panel"
                        class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm">
                        <h5 class="tw:m-0 tw:text-[1.2rem] tw:text-slate-900">Ringkasan Paket</h5>
                        <p class="tw:m-0 tw:text-slate-500 tw:leading-[1.75] tw:text-sm">Pantau paket yang baru datang, yang
                            belum diambil, dan kasus tertukar tanpa harus masuk ke menu paket dulu.</p>
                        <div class="tw:grid tw:gap-[0.85rem]">
                            <div
                                class="tw:p-4 tw:rounded-[18px] tw:bg-[rgba(255,255,255,0.80)] tw:border tw:border-[rgba(22,60,122,0.08)]">
                                <span class="tw:block tw:mb-[0.3rem] tw:text-slate-500 tw:text-xs tw:font-bold">Belum
                                    diambil</span>
                                <strong><?= $paketBelumDiambil ?> paket</strong>
                                <p>Segera cek menu paket bila ada kiriman baru yang belum Anda ambil.</p>
                            </div>
                            <div
                                class="tw:p-4 tw:rounded-[18px] tw:bg-[rgba(255,255,255,0.80)] tw:border tw:border-[rgba(22,60,122,0.08)]">
                                <span class="tw:block tw:mb-[0.3rem] tw:text-slate-500 tw:text-xs tw:font-bold">Sudah
                                    diambil</span>
                                <strong><?= $paketSudahDiambil ?> paket</strong>
                                <p>Riwayat pengambilan paket Anda sudah tercatat.</p>
                            </div>
                            <div
                                class="tw:p-4 tw:rounded-[18px] tw:bg-[rgba(255,255,255,0.80)] tw:border tw:border-[rgba(22,60,122,0.08)]">
                                <span
                                    class="tw:block tw:mb-[0.3rem] tw:text-slate-500 tw:text-xs tw:font-bold">Tertukar</span>
                                <strong><?= $paketTertukar ?> paket</strong>
                                <p>Hubungi petugas SIGAP jika ada paket yang ditandai tertukar.</p>
                            </div>
                        </div>
                        <div class="tw:inline-flex tw:items-center tw:gap-3 tw:flex-wrap tw:mt-4">
                            <a href="/doremi-app/dashboard/paket/"
                                class="tw:inline-flex tw:items-center tw:justify-center tw:gap-2 tw:min-h-12 tw:px-4 tw:py-[0.85rem] tw:rounded-2xl tw:border tw:border-[rgba(22,60,122,0.12)] tw:font-extrabold tw:no-underline tw:text-slate-900 tw:bg-[rgba(255,255,255,0.82)] tw:hover:bg-gray-50 tw:transition-all tw:text-sm">Buka
                                Menu Paket</a>
                        </div>
                    </div>

                    <div data-gsap="panel"
                        class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm">
                        <h5 class="tw:m-0 tw:text-[1.2rem] tw:text-slate-900">Status Laporan Kerusakan</h5>
                        <p class="tw:m-0 tw:text-slate-500 tw:leading-[1.75] tw:text-sm">Lihat progres laporan yang Anda
                            kirim, mulai dari diajukan sampai selesai dikerjakan.</p>
                        <div class="tw:grid tw:gap-[0.85rem]">
                            <div
                                class="tw:p-4 tw:rounded-[18px] tw:bg-[rgba(255,255,255,0.80)] tw:border tw:border-[rgba(22,60,122,0.08)]">
                                <span
                                    class="tw:block tw:mb-[0.3rem] tw:text-slate-500 tw:text-xs tw:font-bold">Diajukan</span>
                                <strong><?= $maintenanceSummary['Diajukan'] ?> laporan</strong>
                                <p>Masih menunggu diproses oleh unit maintenance.</p>
                            </div>
                            <div
                                class="tw:p-4 tw:rounded-[18px] tw:bg-[rgba(255,255,255,0.80)] tw:border tw:border-[rgba(22,60,122,0.08)]">
                                <span
                                    class="tw:block tw:mb-[0.3rem] tw:text-slate-500 tw:text-xs tw:font-bold">Diproses</span>
                                <strong><?= $maintenanceSummary['Diproses'] ?> laporan</strong>
                                <p>Sudah diterima dan sedang dikerjakan.</p>
                            </div>
                            <div
                                class="tw:p-4 tw:rounded-[18px] tw:bg-[rgba(255,255,255,0.80)] tw:border tw:border-[rgba(22,60,122,0.08)]">
                                <span
                                    class="tw:block tw:mb-[0.3rem] tw:text-slate-500 tw:text-xs tw:font-bold">Selesai</span>
                                <strong><?= $maintenanceSummary['Selesai'] ?> laporan</strong>
                                <p>Perbaikan telah diselesaikan oleh teknisi.</p>
                            </div>
                        </div>
                        <div class="tw:inline-flex tw:items-center tw:gap-3 tw:flex-wrap tw:mt-4">
                            <a href="/doremi-app/dashboard/maintenance/"
                                class="tw:inline-flex tw:items-center tw:justify-center tw:gap-2 tw:min-h-12 tw:px-4 tw:py-[0.85rem] tw:rounded-2xl tw:border tw:border-[rgba(22,60,122,0.12)] tw:font-extrabold tw:no-underline tw:text-slate-900 tw:bg-[rgba(255,255,255,0.82)] tw:hover:bg-gray-50 tw:transition-all tw:text-sm">Kelola
                                Semua Pekerjaan</a>
                        </div>
                    </div>
                </div>

            <!-- BERANDA RINGKASAN UNTUK SIGAP -->
            <?php elseif ($role === 'SIGAP'): ?>
                <div class="tw:grid tw:grid-cols-1 tw:lg:grid-cols-2 tw:gap-8">
                    <div data-gsap="panel" class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm">
                        <h5 class="tw:m-0 tw:text-[1.2rem] tw:text-slate-900">Ringkasan Izin Keluar</h5>
                        <p class="tw:m-0 tw:text-slate-500 tw:leading-[1.75] tw:text-sm">Pantau permintaan izin keluar penghuni yang menunggu konfirmasi dan yang masih berada di luar asrama.</p>
                        <div class="tw:grid tw:gap-[0.85rem]">
                            <div class="tw:p-4 tw:rounded-[18px] tw:bg-[rgba(255,255,255,0.80)] tw:border tw:border-[rgba(22,60,122,0.08)]">
                                <span class="tw:block tw:mb-[0.3rem] tw:text-slate-500 tw:text-xs tw:font-bold">Menunggu konfirmasi</span>
                                <strong><?= $pendingConfirmation ?> permintaan</strong>
                                <p>Konfirmasi keberangkatan penghuni yang mengajukan izin keluar.</p>
                            </div>
                            <div class="tw:p-4 tw:rounded-[18px] tw:bg-[rgba(255,255,255,0.80)] tw:border tw:border-[rgba(22,60,122,0.08)]">
                                <span class="tw:block tw:mb-[0.3rem] tw:text-slate-500 tw:text-xs tw:font-bold">Sedang di luar</span>
                                <strong><?= $currentlyOutside ?> penghuni</strong>
                                <p>Belum konfirmasi masuk kembali ke asrama.</p>
                            </div>
                        </div>
                        <div class="tw:inline-flex tw:items-center tw:gap-3 tw:flex-wrap tw:mt-4">
                            <a href="/doremi-app/dashboard/inout/" class="tw:inline-flex tw:items-center tw:justify-center tw:gap-2 tw:min-h-12 tw:px-4 tw:py-[0.85rem] tw:rounded-2xl tw:border tw:border-[rgba(22,60,122,0.12)] tw:font-extrabold tw:no-underline tw:text-slate-900 tw:bg-[rgba(255,255,255,0.82)] tw:hover:bg-gray-50 tw:transition-all tw:text-sm">Buka Konfirmasi In/Out</a>
                        </div>
                    </div>

                    <div data-gsap="panel" class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm">
                        <h5 class="tw:m-0 tw:text-[1.2rem] tw:text-slate-900">Ringkasan Paket</h5>
                        <p class="tw:m-0 tw:text-slate-500 tw:leading-[1.75] tw:text-sm">Lihat paket yang masih menunggu diambil penghuni dan aktivitas pencatatan paket hari ini.</p>
                        <div class="tw:grid tw:gap-[0.85rem]">
                            <div class="tw:p-4 tw:rounded-[18px] tw:bg-[rgba(255,255,255,0.80)] tw:border tw:border-[rgba(22,60,122,0.08)]">
                                <span class="tw:block tw:mb-[0.3rem] tw:text-slate-500 tw:text-xs tw:font-bold">Belum diambil</span>
                                <strong><?= $pendingPackagePickupSigap ?> paket</strong>
                                <p>Ingatkan penghuni untuk segera mengambil paketnya.</p>
                            </div>
                            <div class="tw:p-4 tw:rounded-[18px] tw:bg-[rgba(255,255,255,0.80)] tw:border tw:border-[rgba(22,60,122,0.08)]">
                                <span class="tw:block tw:mb-[0.3rem] tw:text-slate-500 tw:text-xs tw:font-bold">Tercatat hari ini</span>
                                <strong><?= $packagesToday ?> paket</strong>
                                <p>Jumlah paket yang Anda catat masuk hari ini.</p>
                            </div>
                        </div>
                        <div class="tw:inline-flex tw:items-center tw:gap-3 tw:flex-wrap tw:mt-4">
                            <a href="/doremi-app/dashboard/paket/" class="tw:inline-flex tw:items-center tw:justify-center tw:gap-2 tw:min-h-12 tw:px-4 tw:py-[0.85rem] tw:rounded-2xl tw:border tw:border-[rgba(22,60,122,0.12)] tw:font-extrabold tw:no-underline tw:text-slate-900 tw:bg-[rgba(255,255,255,0.82)] tw:hover:bg-gray-50 tw:transition-all tw:text-sm">Buka Menu Paket</a>
                        </div>
                    </div>
                </div>

            <!-- 4. BERANDA DINAMIS KHUSUS UNTUK MAINTENANCE TEAM -->
            <?php elseif ($role === 'MAINTENANCE'): ?>

                <!-- Baris 1: Laporan Darurat Aktif -->
                <?php if ($activeEmergencyTasks > 0): ?>
                    <div class="tw:mb-8">
                        <div
                            class="tw:rounded-t-2xl tw:p-[16px_24px] tw:flex tw:items-center tw:gap-[14px] tw:bg-[linear-gradient(135deg,#7f1d1d,#bc4f45)]">
                            <div
                                class="tw:w-10 tw:h-10 tw:rounded-full tw:bg-[rgba(255,255,255,0.15)] tw:flex tw:items-center tw:justify-center tw:flex-shrink-0 tw:animate-pulse">
                                <i class="fa-solid fa-triangle-exclamation tw:text-white tw:text-[18px]"></i>
                            </div>
                            <div class="tw:flex-1">
                                <div class="tw:flex tw:items-center tw:gap-[10px] tw:flex-wrap">
                                    <span class="tw:text-white tw:text-[15px] tw:font-extrabold tw:tracking-[-0.01em]">Laporan
                                        Darurat Aktif</span>
                                    <span
                                        class="tw:bg-[rgba(255,255,255,0.22)] tw:text-white tw:text-[11px] tw:font-bold tw:px-[10px] tw:py-[2px] tw:rounded-full"><?= $activeEmergencyTasks ?>
                                        laporan</span>
                                </div>
                                <p class="tw:text-[rgba(255,255,255,0.78)] tw:text-xs tw:mt-[3px] tw:m-0">Laporan bertingkat
                                    kerusakan darurat / berat yang belum selesai. Tangani segera sesuai protokol prioritas OSHA.
                                </p>
                            </div>
                            <a href="/doremi-app/dashboard/maintenance/"
                                class="tw:bg-[rgba(255,255,255,0.18)] tw:text-white tw:border tw:border-[rgba(255,255,255,0.3)] tw:text-xs tw:font-bold tw:px-4 tw:py-2 tw:rounded-[10px] tw:no-underline tw:whitespace-nowrap tw:flex-shrink-0">Lihat
                                Semua &rarr;</a>
                        </div>

                        <div class="tw:border tw:border-red-300 tw:border-t-0 tw:rounded-b-2xl tw:bg-white tw:overflow-hidden">
                            <?php foreach ($emergencyList as $idx => $em): ?>
                                <?php
                                $emLokasi = !empty($em['NamaRuangan'])
                                    ? 'Ruangan ' . htmlspecialchars($em['NamaRuangan']) . ' Lantai ' . htmlspecialchars($em['LantaiRuangan'])
                                    : (!empty($em['NamaBarang']) ? 'Inventaris: ' . htmlspecialchars($em['NamaBarang']) : 'Lokasi tidak diketahui');
                                $emPelapor = htmlspecialchars($em['NamaPenghuni'] ?? $em['NamaReporterPetugas'] ?? 'Staff');
                                $emStatus = $em['StatusMaintenance'];
                                $emStatusColor = $emStatus === 'Diproses' ? '#2F7FF0' : '#f59e0b';
                                $emStatusBg = $emStatus === 'Diproses' ? '#eff6ff' : '#fffbeb';
                                ?>
                                <div
                                    class="tw:flex tw:items-start tw:gap-[14px] tw:p-[16px_24px] <?= $idx < count($emergencyList) - 1 ? 'tw:border-b tw:border-red-100' : '' ?>">
                                    <div
                                        class="tw:w-7 tw:h-7 tw:rounded-full tw:bg-red-100 tw:flex tw:items-center tw:justify-center tw:flex-shrink-0 tw:mt-[2px]">
                                        <span class="tw:text-[11px] tw:font-extrabold tw:text-red-600"><?= $idx + 1 ?></span>
                                    </div>
                                    <div class="tw:flex-1 tw:min-w-0">
                                        <div class="tw:flex tw:items-center tw:gap-2 tw:flex-wrap tw:mb-1">
                                            <span class="tw:text-[13px] tw:font-bold tw:text-slate-900"><?= $emLokasi ?></span>
                                            <span
                                                style="font-size:11px; font-weight:600; color:<?= $emStatusColor ?>; background:<?= $emStatusBg ?>; padding:2px 9px; border-radius:20px; border:1px solid <?= $emStatusColor ?>33;"><?= htmlspecialchars($emStatus) ?></span>
                                        </div>
                                        <p class="tw:text-xs tw:text-slate-600 tw:m-0 tw:mb-[6px] tw:leading-[1.5] tw:line-clamp-2">
                                            <?= htmlspecialchars($em['Deskripsi']) ?></p>
                                        <div class="tw:flex tw:items-center tw:gap-3 tw:flex-wrap">
                                            <span class="tw:text-[11px] tw:text-slate-400"><i
                                                    class="fa-solid fa-user tw:mr-1"></i><?= $emPelapor ?></span>
                                            <span class="tw:text-[11px] tw:text-slate-400"><i
                                                    class="fa-solid fa-calendar-days tw:mr-1"></i><?= date('d M Y', strtotime($em['TanggalLapor'])) ?></span>
                                        </div>
                                    </div>
                                    <a href="/doremi-app/dashboard/maintenance/"
                                        class="tw:text-[11px] tw:font-bold tw:text-red-600 tw:bg-red-100 tw:px-3 tw:py-[6px] tw:rounded-lg tw:no-underline tw:whitespace-nowrap tw:flex-shrink-0 tw:mt-[2px]">Proses
                                        &rarr;</a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="tw:mb-8">
                        <div
                            class="tw:border tw:border-emerald-200 tw:rounded-2xl tw:bg-green-50 tw:p-[20px_24px] tw:flex tw:items-center tw:gap-[14px]">
                            <div
                                class="tw:w-10 tw:h-10 tw:rounded-full tw:bg-emerald-200 tw:flex tw:items-center tw:justify-center tw:flex-shrink-0">
                                <i class="fa-solid fa-shield-halved tw:text-green-700 tw:text-[18px]"></i>
                            </div>
                            <div>
                                <span class="tw:text-sm tw:font-extrabold tw:text-green-900">Tidak Ada Laporan Darurat</span>
                                <p class="tw:text-xs tw:text-green-600 tw:mt-[3px] tw:m-0">Saat ini tidak ada laporan dengan
                                    skala prioritas darurat yang aktif. Kondisi asrama aman terkendali.</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Baris 2: Pekerjaan Aktif + Protokol Keselamatan -->
                <div class="tw:grid tw:grid-cols-1 tw:lg:grid-cols-2 tw:gap-8 tw:mb-8">

                    <!-- Panel Kiri: Tugas Aktif (beautified) -->
                    <div data-gsap="panel"
                        class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm">
                        <h5 class="tw:m-0 tw:text-[1.2rem] tw:text-slate-900 tw:flex tw:items-center tw:gap-2">
                            <i class="fa-solid fa-list-check"></i> Pekerjaan Aktif Saya
                        </h5>
                        <p class="tw:m-0 tw:text-slate-500 tw:leading-[1.75] tw:text-sm">Laporan yang saat ini sedang Anda
                            tangani. Selesaikan sebelum mengambil tugas baru.</p>

                        <div class="tw:flex tw:flex-col tw:gap-3 tw:mt-4">
                            <?php if (count($myTasks) > 0): ?>
                                <?php foreach ($myTasks as $task): ?>
                                    <?php
                                    $jenisColor = match ($task['JenisLaporan']) {
                                        'Kerusakan' => '#ef4444',
                                        'Kebersihan' => '#10b981',
                                        default => '#f59e0b',
                                    };
                                    $jenisIcon = match ($task['JenisLaporan']) {
                                        'Kerusakan' => 'fa-solid fa-triangle-exclamation',
                                        'Kebersihan' => 'fa-solid fa-broom',
                                        default => 'fa-solid fa-circle-question',
                                    };
                                    $lokasi = !empty($task['NamaRuangan'])
                                        ? 'Ruangan ' . htmlspecialchars($task['NamaRuangan']) . ' Lantai ' . htmlspecialchars($task['LantaiRuangan'])
                                        : (!empty($task['NamaBarang']) ? 'Inventaris: ' . htmlspecialchars($task['NamaBarang']) : '-');
                                    ?>
                                    <div class="tw:border tw:border-slate-200 tw:border-l-4 tw:rounded-xl tw:p-[14px_16px] tw:bg-white tw:flex tw:gap-3 tw:items-start"
                                        style="border-left-color:<?= $jenisColor ?>">
                                        <div class="tw:w-9 tw:h-9 tw:rounded-lg tw:flex tw:items-center tw:justify-center tw:flex-shrink-0"
                                            style="background:<?= $jenisColor ?>18;">
                                            <i class="<?= $jenisIcon ?> tw:text-[18px]" style="color:<?= $jenisColor ?>;"></i>
                                        </div>
                                        <div class="tw:flex-1 tw:min-w-0">
                                            <div class="tw:flex tw:items-center tw:justify-between tw:gap-2 tw:flex-wrap">
                                                <span
                                                    class="tw:text-[13px] tw:font-semibold tw:text-slate-900"><?= $lokasi ?></span>
                                                <span
                                                    class="tw:text-[11px] tw:font-medium tw:px-2 tw:py-[2px] tw:rounded-full tw:whitespace-nowrap"
                                                    style="color:<?= $jenisColor ?>; background:<?= $jenisColor ?>18;"><?= htmlspecialchars($task['JenisLaporan']) ?></span>
                                            </div>
                                            <p class="tw:mt-1 tw:m-0 tw:text-xs tw:text-slate-500 tw:leading-[1.5] tw:line-clamp-2">
                                                <?= htmlspecialchars($task['Deskripsi']) ?>
                                            </p>
                                            <div class="tw:mt-2 tw:flex tw:items-center tw:gap-[6px]">
                                                <i class="fa-solid fa-user tw:text-[11px] tw:text-slate-400"></i>
                                                <span
                                                    class="tw:text-[11px] tw:text-slate-400"><?= htmlspecialchars($task['NamaPenghuni'] ?? $task['NamaReporterPetugas'] ?? 'Staff') ?></span>
                                                <span class="tw:text-slate-300 tw:mx-[2px]">·</span>
                                                <i class="fa-solid fa-calendar-days tw:text-[11px] tw:text-slate-400"></i>
                                                <span
                                                    class="tw:text-[11px] tw:text-slate-400"><?= date('d M Y', strtotime($task['TanggalLapor'])) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="tw:text-center tw:p-[32px_16px] tw:text-slate-400">
                                    <i class="fa-regular fa-face-smile-beam fa-2x tw:block tw:mb-3 tw:text-secondary"></i>
                                    <p class="tw:text-sm tw:font-medium tw:text-slate-600 tw:m-0 tw:mb-1">Tidak ada tugas aktif
                                    </p>
                                    <p class="tw:text-xs tw:text-slate-400 tw:m-0">Anda bebas dari pekerjaan yang sedang
                                        diproses saat ini.</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="tw:inline-flex tw:items-center tw:gap-3 tw:flex-wrap tw:mt-4">
                            <a href="/doremi-app/dashboard/maintenance/"
                                class="tw:inline-flex tw:items-center tw:justify-center tw:gap-2 tw:min-h-12 tw:px-4 tw:py-[0.85rem] tw:rounded-2xl tw:border tw:border-[rgba(22,60,122,0.12)] tw:font-extrabold tw:no-underline tw:text-slate-900 tw:bg-[rgba(255,255,255,0.82)] tw:hover:bg-gray-50 tw:transition-all tw:text-sm">Buka
                                Seluruh Laporan</a>
                        </div>
                    </div>

                    <!-- Panel Kanan: Protokol Keselamatan Kerja -->
                    <div data-gsap="panel"
                        class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm">
                        <h5 class="tw:m-0 tw:text-[1.2rem] tw:text-slate-900 tw:flex tw:items-center tw:gap-2">
                            <i class="fa-solid fa-shield-halved"></i> Protokol Keselamatan &amp; Kerja
                        </h5>
                        <p class="tw:m-0 tw:text-slate-500 tw:leading-[1.75] tw:text-sm">Demi menjaga keselamatan kerja Anda
                            dan penghuni asrama, mohon selalu patuhi instruksi OSHA berikut:</p>

                        <div class="tw:grid tw:gap-[0.85rem]">
                            <div
                                class="tw:p-4 tw:rounded-[18px] tw:bg-[rgba(245,221,218,0.55)] tw:border tw:border-l-4 tw:border-[rgba(188,79,69,0.20)] tw:border-l-red-500">
                                <strong>1. Selesaikan Darurat Terlebih Dahulu</strong>
                                <p class="tw:text-xs">Prioritaskan kerusakan yang berpotensi mencederai penghuni asrama atau
                                    merusak bangunan secara parah (seperti kebocoran gas atau korsleting listrik).</p>
                            </div>
                            <div
                                class="tw:p-4 tw:rounded-[18px] tw:bg-[rgba(250,236,207,0.55)] tw:border tw:border-l-4 tw:border-[rgba(212,141,47,0.20)] tw:border-l-amber-500">
                                <strong>2. Gunakan APD Lengkap</strong>
                                <p class="tw:text-xs">Pastikan Anda menggunakan alat pelindung diri (sarung tangan tebal,
                                    kacamata pelindung, masker) saat bersentuhan dengan zat kimia atau kelistrikan.</p>
                            </div>
                            <div
                                class="tw:p-4 tw:rounded-[18px] tw:bg-[rgba(219,234,254,0.55)] tw:border tw:border-l-4 tw:border-[rgba(47,127,240,0.20)] tw:border-l-blue-400">
                                <strong>3. Foto Hasil Perbaikan</strong>
                                <p class="tw:text-xs">Kredibilitas tim pemeliharaan dihargai tinggi. Jangan lupa memotret
                                    hasil akhir pengerjaan sebelum mengubah status menjadi 'Selesai'.</p>
                            </div>
                        </div>
                    </div>

                </div>

            <?php else: ?>
                <div
                    class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm">
                    <h5 class="tw:m-0 tw:text-[1.2rem] tw:text-slate-900">Navigasi Cepat</h5>
                    <p class="tw:m-0 tw:text-slate-500 tw:leading-[1.75] tw:text-sm">Gunakan menu di samping untuk membuka
                        modul yang sesuai dengan peran Anda.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <?php require '../bootstrap.php'; ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            /* number counter for stat cards */
            document.querySelectorAll('[data-count]').forEach(el => {
                const target = parseInt(el.dataset.count, 10) || 0;
                if (target === 0) { el.textContent = '0'; return; }
                gsap.to({ val: 0 }, {
                    val: target,
                    duration: 1,
                    delay: 0.4,
                    ease: 'power2.out',
                    onUpdate() { el.textContent = Math.round(this.targets()[0].val); },
                    onComplete() { el.textContent = target; },
                });
            });
        });
    </script>
</body>

</html>