<?php
session_start();
require 'helpers.php';
maintenance_require_roles(['MAINTENANCE', 'PENGURUS']);
require '../../db.php';

$role = $_SESSION['userRole'];
$userId = (int) $_SESSION['userId'];
$userName = $_SESSION['userName'];
session_write_close();

$allowedRanges = ['7d', '30d', '6m', 'all'];
$range = in_array($_GET['range'] ?? '', $allowedRanges) ? $_GET['range'] : '7d';

$whereDate = match ($range) {
    '7d' => "m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)",
    '30d' => "m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)",
    '6m' => "m.TanggalLapor >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)",
    'all' => "1=1",
};

$rangeLabel = match ($range) {
    '7d' => '7 Hari Terakhir',
    '30d' => '30 Hari Terakhir',
    '6m' => '6 Bulan Terakhir',
    'all' => 'Semua Waktu',
};

if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    $filename = 'laporan-maintenance-' . $range . '-' . date('Ymd') . '.csv';
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo "\xEF\xBB\xBF";

    $out = fopen('php://output', 'w');
    fputcsv($out, ['No', 'Pelapor', 'Lokasi / Target', 'Jenis', 'Status', 'Tanggal Lapor', 'Tanggal Selesai', 'Durasi (Hari)', 'Petugas'], ';');

    $exportQuery = "
        SELECT m.JenisLaporan, m.Deskripsi, m.StatusMaintenance, m.TanggalLapor, m.TanggalSelesai,
               COALESCE(p.NamaPenghuni, pt.NamaPetugas, 'Staff') AS Pelapor,
               COALESCE(r.NamaRuangan, i.NamaBarang, '-') AS Lokasi,
               tech.NamaPetugas AS Petugas,
               CASE WHEN m.StatusMaintenance = 'Selesai' AND m.TanggalSelesai IS NOT NULL
                    THEN DATEDIFF(m.TanggalSelesai, m.TanggalLapor) ELSE NULL END AS Durasi
        FROM maintenance m
        LEFT JOIN penghuni p   ON m.PenghuniID  = p.PenghuniID
        LEFT JOIN petugas pt   ON m.PetugasID   = pt.PetugasID
        LEFT JOIN petugas tech ON m.PetugasID   = tech.PetugasID
        LEFT JOIN ruangan r    ON m.RuanganID   = r.RuanganID
        LEFT JOIN inventaris i ON m.InventarisID= i.InventarisID
        WHERE $whereDate AND m.IsDeleted = 0
        ORDER BY m.TanggalLapor DESC
    ";
    $exportRes = mysqli_query($db, $exportQuery);
    $no = 1;
    while ($row = mysqli_fetch_assoc($exportRes)) {
        fputcsv($out, [
            $no++,
            $row['Pelapor'],
            $row['Lokasi'],
            $row['JenisLaporan'],
            $row['StatusMaintenance'],
            $row['TanggalLapor'] ? date('d/m/Y', strtotime($row['TanggalLapor'])) : '-',
            $row['TanggalSelesai'] ? date('d/m/Y', strtotime($row['TanggalSelesai'])) : '-',
            $row['Durasi'] ?? '-',
            $row['Petugas'] ?? '-',
        ], ';');
    }
    mysqli_free_result($exportRes);
    fclose($out);
    exit;
}

$res = mysqli_query($db, "
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN m.StatusMaintenance = 'Selesai'  THEN 1 ELSE 0 END) AS selesai,
        SUM(CASE WHEN m.StatusMaintenance = 'Diproses' THEN 1 ELSE 0 END) AS diproses,
        SUM(CASE WHEN m.StatusMaintenance = 'Diajukan' THEN 1 ELSE 0 END) AS diajukan,
        ROUND(AVG(CASE WHEN m.StatusMaintenance = 'Selesai' AND m.TanggalSelesai IS NOT NULL
            THEN DATEDIFF(m.TanggalSelesai, m.TanggalLapor) ELSE NULL END), 1) AS avg_hari
    FROM maintenance m WHERE $whereDate AND m.IsDeleted = 0
");
$stats = mysqli_fetch_assoc($res);
mysqli_free_result($res);

$priorityOrder = ['Kerusakan Darurat / Berat', 'Kerusakan Sedang', 'Kerusakan Ringan'];
$priorityColors = [
    'Kerusakan Darurat / Berat' => '#ef4444',
    'Kerusakan Sedang' => '#f59e0b',
    'Kerusakan Ringan' => '#10b981'
];
$pieData = [
    'Kerusakan Darurat / Berat' => 0,
    'Kerusakan Sedang' => 0,
    'Kerusakan Ringan' => 0
];
$res = mysqli_query($db, "SELECT m.JenisLaporan, COUNT(*) AS n FROM maintenance m WHERE $whereDate AND m.IsDeleted = 0 GROUP BY m.JenisLaporan");
while ($row = mysqli_fetch_assoc($res)) {
    if (array_key_exists($row['JenisLaporan'], $pieData)) {
        $pieData[$row['JenisLaporan']] = (int) $row['n'];
    }
}
mysqli_free_result($res);
$priorityValues = array_map(fn($k) => $pieData[$k], $priorityOrder);
$priorityBgColors = array_map(fn($k) => $priorityColors[$k], $priorityOrder);

if (in_array($range, ['7d', '30d'])) {
    $days = $range === '7d' ? 7 : 30;
    $res = mysqli_query($db, "SELECT DATE(m.TanggalLapor) AS d, COUNT(*) AS n FROM maintenance m WHERE $whereDate AND m.IsDeleted = 0 GROUP BY d ORDER BY d ASC");
    $trendRaw = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $trendRaw[$row['d']] = (int) $row['n'];
    }
    mysqli_free_result($res);
    $trendLabels = [];
    $trendValues = [];
    for ($i = $days - 1; $i >= 0; $i--) {
        $key = date('Y-m-d', strtotime("-$i day"));
        $trendLabels[] = date('d M', strtotime("-$i day"));
        $trendValues[] = $trendRaw[$key] ?? 0;
    }
} else {
    $res = mysqli_query($db, "SELECT DATE_FORMAT(m.TanggalLapor, '%Y-%m') AS m, COUNT(*) AS n FROM maintenance m WHERE $whereDate AND m.IsDeleted = 0 GROUP BY m ORDER BY m ASC");
    $trendRaw = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $trendRaw[$row['m']] = (int) $row['n'];
    }
    mysqli_free_result($res);
    if ($range === '6m') {
        $trendLabels = [];
        $trendValues = [];
        for ($i = 5; $i >= 0; $i--) {
            $key = date('Y-m', strtotime("-$i month"));
            $trendLabels[] = date('M Y', strtotime("-$i month"));
            $trendValues[] = $trendRaw[$key] ?? 0;
        }
    } else {
        // all time
        $trendLabels = array_map(fn($k) => date('M Y', strtotime($k . '-01')), array_keys($trendRaw));
        $trendValues = array_values($trendRaw);
        if (empty($trendLabels)) {
            $trendLabels = [date('M Y')];
            $trendValues = [0];
        }
    }
}

$topRuanganLabels = [];
$topRuanganValues = [];
$res = mysqli_query($db, "
    SELECT r.NamaRuangan, COUNT(*) AS n
    FROM maintenance m
    JOIN ruangan r ON m.RuanganID = r.RuanganID
    WHERE $whereDate AND m.RuanganID IS NOT NULL AND m.IsDeleted = 0
    GROUP BY m.RuanganID, r.NamaRuangan
    ORDER BY n DESC LIMIT 5
");
while ($row = mysqli_fetch_assoc($res)) {
    $topRuanganLabels[] = $row['NamaRuangan'];
    $topRuanganValues[] = (int) $row['n'];
}
mysqli_free_result($res);

$stackedData = [
    'Kerusakan Darurat / Berat' => ['Diajukan' => 0, 'Diproses' => 0, 'Selesai' => 0],
    'Kerusakan Sedang' => ['Diajukan' => 0, 'Diproses' => 0, 'Selesai' => 0],
    'Kerusakan Ringan' => ['Diajukan' => 0, 'Diproses' => 0, 'Selesai' => 0],
];
$res = mysqli_query($db, "SELECT m.JenisLaporan, m.StatusMaintenance, COUNT(*) AS n FROM maintenance m WHERE $whereDate AND m.IsDeleted = 0 GROUP BY m.JenisLaporan, m.StatusMaintenance");
while ($row = mysqli_fetch_assoc($res)) {
    $j = $row['JenisLaporan'];
    $s = $row['StatusMaintenance'];
    if (isset($stackedData[$j][$s])) {
        $stackedData[$j][$s] = (int) $row['n'];
    }
}
mysqli_free_result($res);

$petugasPerforma = [];
if ($role === 'PENGURUS') {
    $res = mysqli_query($db, "
        SELECT pt.NamaPetugas,
               COUNT(m.MaintenanceID) AS total,
               SUM(CASE WHEN m.StatusMaintenance = 'Selesai' THEN 1 ELSE 0 END) AS selesai,
               SUM(CASE WHEN m.StatusMaintenance = 'Diproses' THEN 1 ELSE 0 END) AS diproses,
               ROUND(AVG(CASE WHEN m.StatusMaintenance = 'Selesai' AND m.TanggalSelesai IS NOT NULL
                   THEN DATEDIFF(m.TanggalSelesai, m.TanggalLapor) ELSE NULL END), 1) AS avg_hari
        FROM maintenance m
        JOIN petugas pt ON m.PetugasID = pt.PetugasID
        WHERE $whereDate AND m.PetugasID IS NOT NULL AND pt.Jabatan = 'MAINTENANCE' AND pt.IsDeleted = 0 AND m.IsDeleted = 0
        GROUP BY m.PetugasID, pt.NamaPetugas
        ORDER BY selesai DESC, total DESC
    ");
    while ($row = mysqli_fetch_assoc($res)) {
        $petugasPerforma[] = $row;
    }
    mysqli_free_result($res);
}

$detailRows = [];
$res = mysqli_query($db, "
    SELECT m.MaintenanceID, m.JenisLaporan, m.StatusMaintenance, m.TanggalLapor, m.TanggalSelesai, m.Deskripsi,
           COALESCE(p.NamaPenghuni, rpt.NamaPetugas, 'Staff') AS Pelapor,
           COALESCE(r.NamaRuangan, i.NamaBarang, '-') AS Lokasi,
           tech.NamaPetugas AS Petugas,
           CASE WHEN m.StatusMaintenance = 'Selesai' AND m.TanggalSelesai IS NOT NULL
                THEN DATEDIFF(m.TanggalSelesai, m.TanggalLapor) ELSE NULL END AS Durasi
    FROM maintenance m
    LEFT JOIN penghuni p    ON m.PenghuniID   = p.PenghuniID
    LEFT JOIN petugas rpt   ON m.PetugasID    = rpt.PetugasID
    LEFT JOIN petugas tech  ON m.PetugasID    = tech.PetugasID
    LEFT JOIN ruangan r     ON m.RuanganID    = r.RuanganID
    LEFT JOIN inventaris i  ON m.InventarisID = i.InventarisID
    WHERE $whereDate AND m.IsDeleted = 0
    ORDER BY m.TanggalLapor DESC
");
while ($row = mysqli_fetch_assoc($res)) {
    $detailRows[] = $row;
}
mysqli_free_result($res);
?>
<!DOCTYPE html>
<html lang="id">
<?php require '../../head.php'; ?>

<body class="dashboard-body tw:p-0 tw:m-0 tw:relative tw:flex tw:min-h-screen tw:overflow-x-hidden">

    <?php require '../components/sidebar.php'; ?>

    <main class="tw:md:ml-75 tw:grow tw:relative tw:z-10">
        <div class="tw:pt-20 tw:md:pt-8 tw:px-8 tw:mb-8 tw:flex-1 tw:w-dvw tw:md:w-full" id="report-content">

            <h1 class="page-title" data-kicker="Laporan &amp; Analitik"
                data-subtitle="Statistik dan ringkasan seluruh laporan maintenance dalam periode yang dipilih.">
                <i class="fa-solid fa-chart-bar tw:mr-[10px] tw:text-[#146c94]!"></i>Laporan Maintenance
            </h1>

            <div class="tw:flex tw:flex-wrap tw:items-center tw:justify-between tw:gap-3 tw:mb-8 no-print">
                <div class="tw:flex tw:gap-2 tw:flex-wrap tw:mt-4">
                    <?php foreach (['7d' => '7 Hari', '30d' => '30 Hari', '6m' => '6 Bulan', 'all' => 'Semua'] as $val => $label): ?>
                        <a href="?range=<?= $val ?>" style="
                    font-size:12px; font-weight:600; padding:6px 16px; border-radius:20px; text-decoration:none;
                    border: 1.5px solid <?= $range === $val ? '#146c94' : '#e2e8f0' ?>;
                    background: <?= $range === $val ? '#146c94' : '#fff' ?>;
                    color: <?= $range === $val ? '#fff' : '#64748b' ?>;
                    transition: all .15s;
                "><?= $label ?></a>
                    <?php endforeach; ?>
                </div>
                <div class="tw:flex tw:gap-2 tw:mt-4">
                    <a href="?range=<?= $range ?>&export=excel"
                        class="tw:inline-flex tw:items-center tw:gap-[6px] tw:text-xs tw:font-semibold tw:px-4 tw:py-[7px] tw:rounded-[10px] tw:bg-emerald-500 tw:text-white tw:no-underline tw:border-none">
                        <i class="fa-solid fa-file-excel"></i> Export Excel
                    </a>
                    <button onclick="window.print()"
                        class="tw:inline-flex tw:items-center tw:gap-[6px] tw:text-xs tw:font-semibold tw:px-4 tw:py-[7px] tw:rounded-[10px] tw:bg-red-500 tw:text-white tw:border-none tw:cursor-pointer">
                        <i class="fa-solid fa-file-pdf"></i> Export PDF
                    </button>
                </div>
            </div>

            <div class="print-only print-header-block">
                <div style="display:flex; align-items:center; gap:14px; margin-bottom:6px;">
                    <img src="/doremi-app/images/logo.png" alt="Logo DOREMI" style="height:48px; width:auto;">
                    <div>
                        <div style="font-size:18px; font-weight:800; color:#146c94; line-height:1.2;">DOREMI</div>
                        <div style="font-size:11px; color:#64748b;">Dormitory Control Center</div>
                    </div>
                </div>
                <h2 style="font-size:15px; font-weight:700; margin:0; color:#1e293b;">Laporan Maintenance</h2>
                <p style="font-size:11px; color:#64748b; margin:2px 0 0;">Periode: <?= $rangeLabel ?> &mdash; Dicetak:
                    <?= date('d M Y H:i') ?>
                </p>
                <hr style="border:none; border-top:2px solid #146c94; margin:8px 0 0;">
            </div>

            <div class="tw:grid tw:grid-cols-2 tw:lg:grid-cols-4 tw:gap-5 tw:mb-8">
                <div
                    class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.85)] tw:shadow-sm">
                    <div class="tw:flex tw:items-center tw:gap-4">
                        <div
                            class="tw:w-[3.4rem] tw:h-[3.4rem] tw:inline-flex tw:items-center tw:justify-center tw:rounded-[20px] tw:flex-shrink-0 tw:text-primary tw:bg-accent/80">
                            <i class="fa-solid fa-clipboard-list tw:text-[22px]"></i>
                        </div>
                        <div>
                            <span
                                class="tw:block tw:text-slate-500 tw:text-[0.72rem] tw:font-extrabold tw:tracking-[0.08em] tw:uppercase">Total
                                Laporan</span>
                            <strong
                                class="tw:block tw:mt-[0.3rem] tw:text-[1.9rem] tw:leading-none tw:text-slate-900 tw:font-bold"><?= (int) ($stats['total'] ?? 0) ?></strong>
                        </div>
                    </div>
                </div>
                <div
                    class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.85)] tw:shadow-sm">
                    <div class="tw:flex tw:items-center tw:gap-4">
                        <div
                            class="tw:w-[3.4rem] tw:h-[3.4rem] tw:inline-flex tw:items-center tw:justify-center tw:rounded-[20px] tw:flex-shrink-0 tw:text-emerald-800 tw:bg-[rgba(220,244,239,0.82)]">
                            <i class="fa-solid fa-circle-check tw:text-[22px]"></i>
                        </div>
                        <div>
                            <span
                                class="tw:block tw:text-slate-500 tw:text-[0.72rem] tw:font-extrabold tw:tracking-[0.08em] tw:uppercase">Selesai</span>
                            <strong
                                class="tw:block tw:mt-[0.3rem] tw:text-[1.9rem] tw:leading-none tw:text-slate-900 tw:font-bold"><?= (int) ($stats['selesai'] ?? 0) ?></strong>
                        </div>
                    </div>
                </div>
                <div
                    class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.85)] tw:shadow-sm">
                    <div class="tw:flex tw:items-center tw:gap-4">
                        <div
                            class="tw:w-[3.4rem] tw:h-[3.4rem] tw:inline-flex tw:items-center tw:justify-center tw:rounded-[20px] tw:flex-shrink-0 tw:text-amber-700 tw:bg-[rgba(250,236,207,0.82)]">
                            <i class="fa-solid fa-spinner tw:text-[22px]"></i>
                        </div>
                        <div>
                            <span
                                class="tw:block tw:text-slate-500 tw:text-[0.72rem] tw:font-extrabold tw:tracking-[0.08em] tw:uppercase">Dalam
                                Proses</span>
                            <strong
                                class="tw:block tw:mt-[0.3rem] tw:text-[1.9rem] tw:leading-none tw:text-slate-900 tw:font-bold"><?= (int) ($stats['diproses'] ?? 0) ?></strong>
                        </div>
                    </div>
                </div>
                <div
                    class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.85)] tw:shadow-sm">
                    <div class="tw:flex tw:items-center tw:gap-4">
                        <div
                            class="tw:w-[3.4rem] tw:h-[3.4rem] tw:inline-flex tw:items-center tw:justify-center tw:rounded-[20px] tw:flex-shrink-0 tw:text-primary tw:bg-accent/80">
                            <i class="fa-regular fa-clock tw:text-[22px]"></i>
                        </div>
                        <div>
                            <span
                                class="tw:block tw:text-slate-500 tw:text-[0.72rem] tw:font-extrabold tw:tracking-[0.08em] tw:uppercase">Rata-rata
                                Selesai</span>
                            <strong
                                class="tw:block tw:mt-[0.3rem] tw:text-[1.9rem] tw:leading-none tw:text-slate-900 tw:font-bold"><?= $stats['avg_hari'] ?? 'â€”' ?><span
                                    class="tw:text-[13px] tw:font-normal tw:text-slate-500"><?= $stats['avg_hari'] ? ' hari' : '' ?></span></strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tw:grid tw:grid-cols-1 tw:lg:grid-cols-2 tw:gap-6 tw:mb-6">

                <div
                    class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm">
                    <h5 class="tw:m-0 tw:text-[1.2rem] tw:text-slate-900 tw:flex tw:items-center tw:gap-2">
                        <i class="fa-solid fa-triangle-exclamation"></i> Distribusi Jenis Laporan (Skala Prioritas)
                    </h5>
                    <p class="tw:m-0 tw:text-slate-500 tw:leading-[1.75] tw:text-sm">Laporan diurutkan berdasarkan
                        tingkat prioritas penanganan dalam periode <?= $rangeLabel ?>.</p>
                    <div class="tw:flex tw:gap-2 tw:flex-wrap tw:mb-[10px]">
                        <span
                            class="tw:inline-flex tw:items-center tw:gap-1 tw:text-[10px] tw:font-bold tw:px-[9px] tw:py-[3px] tw:rounded-full tw:bg-red-100 tw:text-red-600">
                            <i class="fa-solid fa-circle-exclamation"></i> Tinggi
                        </span>
                        <span
                            class="tw:inline-flex tw:items-center tw:gap-1 tw:text-[10px] tw:font-bold tw:px-[9px] tw:py-[3px] tw:rounded-full tw:bg-yellow-100 tw:text-yellow-800">
                            <i class="fa-solid fa-circle-minus"></i> Sedang
                        </span>
                        <span
                            class="tw:inline-flex tw:items-center tw:gap-1 tw:text-[10px] tw:font-bold tw:px-[9px] tw:py-[3px] tw:rounded-full tw:bg-green-100 tw:text-green-600">
                            <i class="fa-solid fa-circle-check"></i> Rendah
                        </span>
                    </div>
                    <div class="tw:h-[200px] tw:mt-1">
                        <canvas id="chartPriority"></canvas>
                    </div>
                </div>

                <div
                    class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm">
                    <h5 class="tw:m-0 tw:text-[1.2rem] tw:text-slate-900 tw:flex tw:items-center tw:gap-2">
                        <i class="fa-solid fa-arrow-trend-up"></i> Trend Laporan Masuk
                    </h5>
                    <p class="tw:m-0 tw:text-slate-500 tw:leading-[1.75] tw:text-sm">Jumlah laporan yang masuk per
                        periode dalam rentang <?= $rangeLabel ?>.</p>
                    <div class="tw:h-[240px] tw:mt-2">
                        <canvas id="chartTrend"></canvas>
                    </div>
                </div>

            </div>

            <div class="tw:grid tw:grid-cols-1 tw:lg:grid-cols-2 tw:gap-6 tw:mb-6">

                <div
                    class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm">
                    <h5 class="tw:m-0 tw:text-[1.2rem] tw:text-slate-900 tw:flex tw:items-center tw:gap-2">
                        <i class="fa-solid fa-building"></i> Top 5 Ruangan Terbanyak
                    </h5>
                    <p class="tw:m-0 tw:text-slate-500 tw:leading-[1.75] tw:text-sm">Ruangan yang paling sering
                        dilaporkan dalam periode <?= $rangeLabel ?>.</p>
                    <div class="tw:h-[220px] tw:mt-2">
                        <canvas id="chartRuangan"></canvas>
                    </div>
                </div>

                <div
                    class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm">
                    <h5 class="tw:m-0 tw:text-[1.2rem] tw:text-slate-900 tw:flex tw:items-center tw:gap-2">
                        <i class="fa-solid fa-chart-bar"></i> Status per Jenis Laporan
                    </h5>
                    <p class="tw:m-0 tw:text-slate-500 tw:leading-[1.75] tw:text-sm">Perbandingan progres penyelesaian
                        untuk setiap jenis laporan.</p>
                    <div class="tw:h-[220px] tw:mt-2">
                        <canvas id="chartStacked"></canvas>
                    </div>
                </div>

            </div>

            <?php if ($role === 'PENGURUS' && count($petugasPerforma) > 0): ?>
                <!-- â”€â”€ Ranking Petugas (PENGURUS only â€” full width) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
                <div class="tw:mb-6">
                    <div
                        class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm">
                        <h5 class="tw:m-0 tw:text-[1.2rem] tw:text-slate-900 tw:flex tw:items-center tw:gap-2">
                            <i class="fa-solid fa-ranking-star"></i> Ranking Petugas Maintenance
                        </h5>
                        <p class="tw:m-0 tw:text-slate-500 tw:leading-[1.75] tw:text-sm">Peringkat performa anggota tim
                            berdasarkan jumlah tugas selesai dalam <?= $rangeLabel ?>.</p>
                        <div class="table-panel tw:mt-2">
                            <div class="doremi-table-wrapper">
                                <table class="table doremi-table tw:w-full">
                                    <thead>
                                        <tr>
                                            <th class="tw:w-[50px]">Rank</th>
                                            <th>Petugas</th>
                                            <th>Total Tugas</th>
                                            <th>Selesai</th>
                                            <th>Diproses</th>
                                            <th>Tingkat Selesai</th>
                                            <th>Avg Penyelesaian</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($petugasPerforma as $i => $p):
                                            $rankIcon = match ($i) {
                                                0 => '<i class="fa-solid fa-trophy" style="color:#f59e0b; font-size:16px;"></i>',
                                                1 => '<i class="fa-solid fa-medal" style="color:#94a3b8; font-size:16px;"></i>',
                                                2 => '<i class="fa-solid fa-medal" style="color:#b45309; font-size:16px;"></i>',
                                                default => '<span style="color:#94a3b8; font-weight:700;">#' . ($i + 1) . '</span>',
                                            };
                                            $completionRate = $p['total'] > 0 ? round(($p['selesai'] / $p['total']) * 100) : 0;
                                            $rateColor = $completionRate >= 80 ? '#16a34a' : ($completionRate >= 50 ? '#d97706' : '#dc2626');
                                            ?>
                                            <tr>
                                                <td class="text-center"><?= $rankIcon ?></td>
                                                <td><strong><?= htmlspecialchars($p['NamaPetugas']) ?></strong></td>
                                                <td class="text-center"><?= (int) $p['total'] ?></td>
                                                <td class="text-center"><span
                                                        class="tw:bg-emerald-100 tw:text-green-700 tw:px-[10px] tw:py-[2px] tw:rounded-full tw:text-xs tw:font-semibold"><?= (int) $p['selesai'] ?></span>
                                                </td>
                                                <td class="text-center"><span
                                                        class="tw:bg-blue-100 tw:text-blue-700 tw:px-[10px] tw:py-[2px] tw:rounded-full tw:text-xs tw:font-semibold"><?= (int) $p['diproses'] ?></span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="tw:flex tw:items-center tw:gap-[6px] tw:justify-center">
                                                        <div
                                                            class="tw:bg-gray-200 tw:rounded tw:h-[6px] tw:w-20 tw:overflow-hidden">
                                                            <div
                                                                style="background:<?= $rateColor ?>; height:100%; width:<?= $completionRate ?>%; border-radius:4px;">
                                                            </div>
                                                        </div>
                                                        <span
                                                            style="font-size:12px; font-weight:700; color:<?= $rateColor ?>;"><?= $completionRate ?>%</span>
                                                    </div>
                                                </td>
                                                <td class="text-center"><?= $p['avg_hari'] ? $p['avg_hari'] . ' hari' : 'â€”' ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="table-panel">
                <div class="tw:flex tw:items-center tw:justify-between tw:flex-wrap tw:gap-3 tw:mb-4">
                    <div>
                        <h5 class="tw:m-0 tw:text-[1.2rem] tw:text-slate-900 tw:flex tw:items-center tw:gap-2 tw:mb-1">
                            <i class="fa-solid fa-table-list"></i> Daftar Laporan
                        </h5>
                        <p class="tw:m-0 tw:text-slate-500 tw:leading-[1.75] tw:text-sm"><?= count($detailRows) ?>
                            laporan ditemukan dalam periode <?= $rangeLabel ?>.</p>
                    </div>
                </div>
                <div class="doremi-table-wrapper print-table-wrapper">
                    <table id="reportTable"
                        class="table doremi-table text-center align-middle tw:mb-0 tw:w-full print-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Pelapor</th>
                                <th>Lokasi / Target</th>
                                <th>Jenis</th>
                                <th>Status</th>
                                <th>Tanggal Lapor</th>
                                <th>Selesai</th>
                                <th>Durasi</th>
                                <?php if ($role === 'PENGURUS'): ?>
                                    <th>Petugas</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detailRows as $i => $r):
                                $statusStyle = match ($r['StatusMaintenance']) {
                                    'Selesai' => 'background:#dcfce7; color:#16a34a;',
                                    'Diproses' => 'background:#dbeafe; color:#1d4ed8;',
                                    default => 'background:#fef9c3; color:#854d0e;',
                                };
                                $jenisStyle = match ($r['JenisLaporan']) {
                                    'Kerusakan Darurat / Berat' => 'background:#fee2e2; color:#dc2626;',
                                    'Kerusakan Ringan' => 'background:#dcfce7; color:#16a34a;',
                                    default => 'background:#fef3c7; color:#92400e;', // Kerusakan Sedang
                                };
                                ?>
                                <tr>
                                    <td class="row-number-cell"></td>
                                    <td><?= htmlspecialchars($r['Pelapor']) ?></td>
                                    <td><?= htmlspecialchars($r['Lokasi']) ?></td>
                                    <td><span
                                            style="font-size:11px; font-weight:600; padding:2px 8px; border-radius:20px; <?= $jenisStyle ?>"><?= htmlspecialchars($r['JenisLaporan']) ?></span>
                                    </td>
                                    <td><span
                                            style="font-size:11px; font-weight:600; padding:2px 8px; border-radius:20px; <?= $statusStyle ?>"><?= htmlspecialchars($r['StatusMaintenance']) ?></span>
                                    </td>
                                    <td><?= $r['TanggalLapor'] ? date('d M Y', strtotime($r['TanggalLapor'])) : '—' ?>
                                    </td>
                                    <td><?= $r['TanggalSelesai'] ? date('d M Y', strtotime($r['TanggalSelesai'])) : '—' ?>
                                    </td>
                                    <td><?= $r['Durasi'] !== null ? $r['Durasi'] . ' jm' : '—' ?></td>
                                    <?php if ($role === 'PENGURUS'): ?>
                                        <td><?= htmlspecialchars($r['Petugas'] ?? '—') ?></td><?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div><!-- /dashboard-page -->
    </main>

    <?php require '../../bootstrap.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            const primary = '#146c94';
            const baseOpts = { responsive: true, maintainAspectRatio: false };
            const gridOpts = { color: 'rgba(0,0,0,0.05)' };

            // Bar Horizontal â€” Distribusi Jenis berdasarkan Skala Prioritas
            new Chart(document.getElementById('chartPriority'), {
                type: 'bar',
                data: {
                    labels: ['Darurat\n(Prioritas Tinggi)', 'Sedang\n(Prioritas Sedang)', 'Ringan\n(Prioritas Rendah)'],
                    datasets: [{
                        label: 'Jumlah Laporan',
                        data: <?= json_encode($priorityValues) ?>,
                        backgroundColor: <?= json_encode($priorityBgColors) ?>,
                        borderRadius: 8,
                        borderSkipped: false,
                    }]
                },
                options: {
                    ...baseOpts,
                    indexAxis: 'y',
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: ctx => ` ${ctx.parsed.x} laporan` } }
                    },
                    scales: {
                        x: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } }, grid: gridOpts },
                        y: { ticks: { font: { size: 11 } }, grid: { display: false } }
                    }
                }
            });

            // Line â€” Trend
            new Chart(document.getElementById('chartTrend'), {
                type: 'line',
                data: {
                    labels: <?= json_encode($trendLabels) ?>,
                    datasets: [{ label: 'Laporan', data: <?= json_encode($trendValues) ?>, borderColor: primary, backgroundColor: 'rgba(20,108,148,0.08)', borderWidth: 2.5, pointRadius: 4, pointBackgroundColor: primary, fill: true, tension: 0.4 }]
                },
                options: { ...baseOpts, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } }, grid: gridOpts }, x: { ticks: { font: { size: 11 }, maxRotation: 45, maxTicksLimit: 10 }, grid: { display: false } } } }
            });

            // Bar Horizontal â€” Top Ruangan
            new Chart(document.getElementById('chartRuangan'), {
                type: 'bar',
                data: {
                    labels: <?= json_encode($topRuanganLabels ?: ['Tidak ada data']) ?>,
                    datasets: [{ label: 'Laporan', data: <?= json_encode($topRuanganValues ?: [0]) ?>, backgroundColor: 'rgba(20,108,148,0.75)', borderRadius: 6, borderSkipped: false }]
                },
                options: { ...baseOpts, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } }, grid: gridOpts }, y: { ticks: { font: { size: 11 } }, grid: { display: false } } } }
            });

            // Stacked Bar â€” Status per Jenis
            new Chart(document.getElementById('chartStacked'), {
                type: 'bar',
                data: {
                    labels: ['Darurat', 'Sedang', 'Ringan'],
                    datasets: [
                        { label: 'Diajukan', data: [<?= $stackedData['Kerusakan Darurat / Berat']['Diajukan'] ?>, <?= $stackedData['Kerusakan Sedang']['Diajukan'] ?>, <?= $stackedData['Kerusakan Ringan']['Diajukan'] ?>], backgroundColor: '#f59e0b', borderRadius: 4 },
                        { label: 'Diproses', data: [<?= $stackedData['Kerusakan Darurat / Berat']['Diproses'] ?>, <?= $stackedData['Kerusakan Sedang']['Diproses'] ?>, <?= $stackedData['Kerusakan Ringan']['Diproses'] ?>], backgroundColor: '#2F7FF0', borderRadius: 4 },
                        { label: 'Selesai', data: [<?= $stackedData['Kerusakan Darurat / Berat']['Selesai'] ?>, <?= $stackedData['Kerusakan Sedang']['Selesai'] ?>, <?= $stackedData['Kerusakan Ringan']['Selesai'] ?>], backgroundColor: '#10b981', borderRadius: 4 },
                    ]
                },
                options: { ...baseOpts, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 12, font: { size: 11 } } } }, scales: { x: { stacked: true, ticks: { font: { size: 12 } }, grid: { display: false } }, y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } }, grid: gridOpts } } }
            });
        })();
    </script>

    <style>
        /* â”€â”€ Print-only elements (hidden on screen) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
        .print-only {
            display: none;
        }

        @media print {

            /* Hide navigation and controls */
            .no-print,
            .dashboard-sidebar,
            .sidebar,
            .dashboard-topbar,
            .dashboard-overlay,
            nav {
                display: none !important;
            }

            /* Remove sidebar offset */
            .dashboard-main {
                margin-left: 0 !important;
                width: 100% !important;
            }

            .dashboard-page {
                padding: 8px 12px !important;
                width: 100% !important;
                max-width: 100% !important;
            }

            body {
                background: #fff !important;
                font-size: 11px !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /* Show print-only header with logo */
            .print-only {
                display: block !important;
            }

            .print-header-block {
                margin-bottom: 14px;
            }

            /* Cards */
            .dashboard-stat-card,
            .dashboard-side-panel {
                box-shadow: none !important;
                border: 1px solid #e2e8f0 !important;
            }

            /* Limit chart height */
            canvas {
                max-height: 170px !important;
            }

            /* Keep grid layouts in print */
            .tw\:grid {
                display: grid !important;
            }

            .tw\:lg\:grid-cols-2 {
                grid-template-columns: 1fr 1fr !important;
            }

            .tw\:lg\:grid-cols-4 {
                grid-template-columns: repeat(4, 1fr) !important;
            }

            .tw\:gap-5,
            .tw\:gap-6 {
                gap: 8px !important;
            }

            .tw\:mb-8,
            .tw\:mb-6 {
                margin-bottom: 10px !important;
            }

            /* â”€â”€ Table PDF fixes â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
            .print-table-wrapper {
                overflow: visible !important;
                /* prevent horizontal cutoff */
                width: 100% !important;
            }

            .print-table {
                width: 100% !important;
                table-layout: fixed !important;
                /* prevent columns from overflowing */
                font-size: 9px !important;
                border-collapse: collapse !important;
                page-break-inside: auto !important;
            }

            .print-table thead {
                display: table-header-group !important;
                /* repeat header on every printed page */
            }

            .print-table th,
            .print-table td {
                padding: 4px 5px !important;
                word-wrap: break-word !important;
                overflow: hidden !important;
                border: 1px solid #d1d5db !important;
                white-space: normal !important;
            }

            .print-table th {
                background: #146c94 !important;
                color: #fff !important;
                font-weight: 700 !important;
            }

            .print-table tr {
                page-break-inside: avoid !important;
            }

            .print-table tbody tr:nth-child(even) {
                background: #f8fafc !important;
            }

            /* Page break before table section */
            .table-panel {
                page-break-before: auto;
            }
        }
    </style>

</body>

</html>