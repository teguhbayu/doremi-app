<?php
session_start();
if (!isset($_SESSION['userId'])) {
    header("Location: /doremi-app/login.php");
    exit;
}
require '../db.php';

$role = $_SESSION['userRole'];
$userName = $_SESSION['userName'];

// Data for PENGURUS
$stats = [];
if ($role === 'PENGURUS') {
    // Total Active Penghuni
    $activePenghuni = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as total FROM penghuni WHERE IsDeleted = 0 AND IsActive = 1"))['total'];

    // InOut Today
    $today = date('Y-m-d');
    $inOutToday = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as total FROM inoutpenghuni WHERE DATE(WaktuKeluar) = '$today' OR DATE(WaktuMasuk) = '$today'"))['total'];

    // Available Kamar
    $totalKamar = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as total FROM kamar WHERE IsDeleted = 0"))['total'];

    // Maintenance Pending
    $pendingMaintenance = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(*) as total FROM maintenance WHERE StatusMaintenance = 'Diajukan'"))['total'];

    // Chart Data: Gender Distribution
    $genderStats = mysqli_query($db, "SELECT JenisKelamin, COUNT(*) as count FROM penghuni WHERE IsDeleted = 0 GROUP BY JenisKelamin");
    $chartData = ['L' => 0, 'P' => 0];
    while ($row = mysqli_fetch_assoc($genderStats)) {
        $chartData[$row['JenisKelamin']] = $row['count'];
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<?php require '../head.php'; ?>

<body class="tw:p-0 tw:m-0 relative tw:flex tw:bg-[#f8fafc] tw:min-h-screen tw:overflow-x-hidden">
    <div
        class="tw:absolute tw:top-[-10%] tw:right-[-5%] tw:w-[500px] tw:h-[500px] tw:bg-primary/5 tw:rounded-full tw:blur-[120px] tw:pointer-events-none">
    </div>
    <div
        class="tw:absolute tw:bottom-[10%] tw:left-[20%] tw:w-[400px] tw:h-[400px] tw:bg-secondary/5 tw:rounded-full tw:blur-[100px] tw:pointer-events-none">
    </div>

    <?php require 'components/sidebar.php'; ?>
    <main class="tw:md:ml-75 tw:grow tw:relative tw:z-10">
        <div class="tw:pt-20 tw:md:pt-8 tw:px-8 tw:mb-8 tw:flex-1 tw:w-dvw tw:md:w-full">
            <div class="tw:mb-10">
                <h1 class="tw:font-bold tw:text-4xl tw:text-slate-900 tw:tracking-tight">
                    Hello, <?= htmlspecialchars($userName) ?> 👋
                </h1>
                <p class="tw:text-slate-500 tw:mt-2 tw:text-lg">Selamat datang kembali di dashboard DOREMI.</p>
            </div>

            <?php if ($role === 'PENGURUS'): ?>
                <div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-2 tw:lg:grid-cols-4 tw:gap-6 tw:mb-10">
                    <!-- Stat Card 1 -->
                    <div
                        class="tw:group tw:relative tw:bg-white/60 tw:backdrop-blur-xl tw:p-6 tw:rounded-[24px] tw:border tw:border-white tw:shadow-[0_8px_30px_rgb(0,0,0,0.04)] tw:transition-all tw:duration-500 tw:hover:shadow-[0_20px_50px_rgba(59,130,246,0.1)] tw:hover:-translate-y-1">
                        <div class="tw:flex tw:items-center tw:gap-5">
                            <div
                                class="tw:p-4 tw:bg-blue-500/10 tw:text-blue-600 tw:rounded-[18px] tw:transition-colors tw:duration-500 tw:group-hover:bg-blue-500 tw:group-hover:text-white">
                                <i class="iconsax tw:text-3xl" icon-name="group"></i>
                            </div>
                            <div>
                                <p
                                    class="tw:text-sm tw:font-medium tw:text-slate-500 tw:uppercase tw:tracking-wider tw:m-0">
                                    Penghuni</p>
                                <h3 class="tw:text-3xl tw:font-bold tw:text-slate-900 tw:m-0"><?= $activePenghuni ?></h3>
                            </div>
                        </div>
                    </div>
                    <!-- Stat Card 2 -->
                    <div
                        class="tw:group tw:relative tw:bg-white/60 tw:backdrop-blur-xl tw:p-6 tw:rounded-[24px] tw:border tw:border-white tw:shadow-[0_8px_30px_rgb(0,0,0,0.04)] tw:transition-all tw:duration-500 tw:hover:shadow-[0_20px_50px_rgba(34,197,94,0.1)] tw:hover:-translate-y-1">
                        <div class="tw:flex tw:items-center tw:gap-5">
                            <div
                                class="tw:p-4 tw:bg-green-500/10 tw:text-green-600 tw:rounded-[18px] tw:transition-colors tw:duration-500 tw:group-hover:bg-green-500 tw:group-hover:text-white">
                                <i class="iconsax tw:text-3xl" icon-name="arrow-up-down"></i>
                            </div>
                            <div>
                                <p
                                    class="tw:text-sm tw:font-medium tw:text-slate-500 tw:uppercase tw:tracking-wider tw:m-0">
                                    Total In/Out</p>
                                <h3 class="tw:text-3xl tw:font-bold tw:text-slate-900 tw:m-0"><?= $inOutToday ?></h3>
                            </div>
                        </div>
                    </div>
                    <!-- Stat Card 3 -->
                    <div
                        class="tw:group tw:relative tw:bg-white/60 tw:backdrop-blur-xl tw:p-6 tw:rounded-[24px] tw:border tw:border-white tw:shadow-[0_8px_30px_rgb(0,0,0,0.04)] tw:transition-all tw:duration-500 tw:hover:shadow-[0_20px_50px_rgba(168,85,247,0.1)] tw:hover:-translate-y-1">
                        <div class="tw:flex tw:items-center tw:gap-5">
                            <div
                                class="tw:p-4 tw:bg-purple-500/10 tw:text-purple-600 tw:rounded-[18px] tw:transition-colors tw:duration-500 tw:group-hover:bg-purple-500 tw:group-hover:text-white">
                                <i class="iconsax tw:text-3xl" icon-name="house-1"></i>
                            </div>
                            <div>
                                <p
                                    class="tw:text-sm tw:font-medium tw:text-slate-500 tw:uppercase tw:tracking-wider tw:m-0">
                                    Total Kamar</p>
                                <h3 class="tw:text-3xl tw:font-bold tw:text-slate-900 tw:m-0"><?= $totalKamar ?></h3>
                            </div>
                        </div>
                    </div>
                    <!-- Stat Card 4 -->
                    <div
                        class="tw:group tw:relative tw:bg-white/60 tw:backdrop-blur-xl tw:p-6 tw:rounded-[24px] tw:border tw:border-white tw:shadow-[0_8px_30px_rgb(0,0,0,0.04)] tw:transition-all tw:duration-500 tw:hover:shadow-[0_20px_50px_rgba(249,115,22,0.1)] tw:hover:-translate-y-1">
                        <div class="tw:flex tw:items-center tw:gap-5">
                            <div
                                class="tw:p-4 tw:bg-orange-500/10 tw:text-orange-600 tw:rounded-[18px] tw:transition-colors tw:duration-500 tw:group-hover:bg-orange-500 tw:group-hover:text-white">
                                <i class="iconsax tw:text-3xl" icon-name="setting-2"></i>
                            </div>
                            <div>
                                <p
                                    class="tw:text-sm tw:font-medium tw:text-slate-500 tw:uppercase tw:tracking-wider tw:m-0">
                                    Pending</p>
                                <h3 class="tw:text-3xl tw:font-bold tw:text-slate-900 tw:m-0"><?= $pendingMaintenance ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tw:grid tw:grid-cols-1 tw:lg:grid-cols-2 tw:gap-8">
                    <div
                        class="tw:bg-white/60 tw:backdrop-blur-xl tw:p-8 tw:rounded-[32px] tw:border tw:border-white tw:shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
                        <h5 class="tw:text-xl tw:font-bold tw:text-slate-900 tw:mb-6">Distribusi Gender Penghuni</h5>
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
                                backgroundColor: ['#3B82F6', '#EC4899'],
                                borderWeight: 0
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
            <?php else: ?>
                <!-- Content for other roles (Petugas, etc) -->
                <div class="tw:bg-white tw:p-8 tw:rounded-2xl tw:shadow-sm tw:border tw:border-gray-100">
                    <h5 class="tw:font-semibold">Quick Actions</h5>
                    <p class="tw:text-gray-500">Silahkan gunakan menu di samping untuk mengelola data dormitory.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <?php require '../bootstrap.php'; ?>
</body>

</html>