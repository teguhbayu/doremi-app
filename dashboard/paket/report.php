<?php
session_start();
require 'helpers.php';
paket_require_roles(['SIGAP', 'PENGURUS']);
require '../../db.php';

$role = $_SESSION['userRole'];
session_write_close();

$allowedRanges = ['7d', '30d', '6m', 'all'];
$range = in_array($_GET['range'] ?? '', $allowedRanges) ? $_GET['range'] : '7d';

$whereDate = match ($range) {
    '7d' => "pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)",
    '30d' => "pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)",
    '6m' => "pk.WaktuSampai >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)",
    'all' => "1=1",
};

$rangeLabel = match ($range) {
    '7d' => '7 Hari Terakhir',
    '30d' => '30 Hari Terakhir',
    '6m' => '6 Bulan Terakhir',
    'all' => 'Semua Waktu',
};

/** Latest pickup record per paket (mirrors index.php). */
$latestPickupJoin = "
    LEFT JOIN (
        SELECT pp1.PaketID, pp1.Status, pp1.WaktuPengambilan, pp1.PetugasID AS PickupPetugasID
        FROM pengambilanpaket pp1
        INNER JOIN (
            SELECT PaketID, MAX(PengambilanPaketID) AS LatestID
            FROM pengambilanpaket
            GROUP BY PaketID
        ) latest ON latest.LatestID = pp1.PengambilanPaketID
    ) pp ON pp.PaketID = pk.PaketID
";

/** Format a duration given in minutes into a human-friendly string. */
function paket_format_duration(?int $minutes): string
{
    if ($minutes === null) {
        return '—';
    }
    if ($minutes < 0) {
        $minutes = 0;
    }
    if ($minutes < 60) {
        return $minutes . ' mnt';
    }
    if ($minutes < 1440) {
        $hours = intdiv($minutes, 60);
        $rest = $minutes % 60;
        return $rest ? "{$hours} j {$rest} m" : "{$hours} jam";
    }
    $days = intdiv($minutes, 1440);
    $hours = intdiv($minutes % 1440, 60);
    return $hours ? "{$days} hr {$hours} j" : "{$days} hari";
}

$statusMeta = [
    'Sudah Diambil' => ['label' => 'Sudah Diambil', 'badge' => 'background:#dcfce7; color:#16a34a;'],
    'Belum Diambil' => ['label' => 'Belum Diambil', 'badge' => 'background:#fef9c3; color:#854d0e;'],
    'TERTUKAR' => ['label' => 'Tertukar', 'badge' => 'background:#fee2e2; color:#dc2626;'],
];

if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    $filename = 'laporan-paket-' . $range . '-' . date('Ymd') . '.csv';
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo "\xEF\xBB\xBF";

    $out = fopen('php://output', 'w');
    fputcsv($out, ['No', 'Penghuni', 'NIM', 'Kamar', 'Tipe', 'Pengirim', 'Kurir', 'Status', 'Waktu Sampai', 'Waktu Ambil', 'Durasi Ambil (Menit)', 'Dicatat Oleh'], ';');

    $exportQuery = "
        SELECT pk.NamaPengirim, pk.Kurir, pk.JenisPaket, pk.WaktuSampai,
               ph.NamaPenghuni, ph.Nim, k.NomorKamar, pt.NamaPetugas,
               COALESCE(pp.Status, 'Belum Diambil') AS Status, pp.WaktuPengambilan,
               CASE WHEN pp.Status = 'Sudah Diambil' AND pp.WaktuPengambilan IS NOT NULL AND pk.WaktuSampai IS NOT NULL
                    THEN TIMESTAMPDIFF(MINUTE, pk.WaktuSampai, pp.WaktuPengambilan) ELSE NULL END AS Durasi
        FROM paket pk
        JOIN penghuni ph    ON pk.PenghuniID = ph.PenghuniID
        LEFT JOIN kamar k   ON ph.KamarID    = k.KamarID
        LEFT JOIN petugas pt ON pk.PetugasID = pt.PetugasID
        $latestPickupJoin
        WHERE $whereDate
        ORDER BY pk.WaktuSampai DESC
    ";
    $exportRes = mysqli_query($db, $exportQuery);
    $no = 1;
    while ($row = mysqli_fetch_assoc($exportRes)) {
        fputcsv($out, [
            $no++,
            $row['NamaPenghuni'],
            $row['Nim'],
            $row['NomorKamar'] ?? '-',
            paket_type_label($row['JenisPaket'] ?? null),
            $row['NamaPengirim'],
            $row['Kurir'],
            $statusMeta[$row['Status']]['label'] ?? $row['Status'],
            $row['WaktuSampai'] ? date('d/m/Y H:i', strtotime($row['WaktuSampai'])) : '-',
            $row['WaktuPengambilan'] ? date('d/m/Y H:i', strtotime($row['WaktuPengambilan'])) : '-',
            $row['Durasi'] ?? '-',
            $row['NamaPetugas'] ?? '-',
        ], ';');
    }
    mysqli_free_result($exportRes);
    fclose($out);
    exit;
}

$res = mysqli_query($db, "
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN COALESCE(pp.Status, 'Belum Diambil') = 'Sudah Diambil' THEN 1 ELSE 0 END) AS sudah,
        SUM(CASE WHEN COALESCE(pp.Status, 'Belum Diambil') = 'Belum Diambil' THEN 1 ELSE 0 END) AS belum,
        SUM(CASE WHEN COALESCE(pp.Status, 'Belum Diambil') = 'TERTUKAR' THEN 1 ELSE 0 END) AS tertukar,
        ROUND(AVG(CASE WHEN pp.Status = 'Sudah Diambil' AND pp.WaktuPengambilan IS NOT NULL AND pk.WaktuSampai IS NOT NULL
            THEN TIMESTAMPDIFF(MINUTE, pk.WaktuSampai, pp.WaktuPengambilan) ELSE NULL END)) AS avg_menit
    FROM paket pk
    $latestPickupJoin
    WHERE $whereDate
");
$stats = mysqli_fetch_assoc($res);
mysqli_free_result($res);
$avgMinutes = $stats['avg_menit'] !== null ? (int) $stats['avg_menit'] : null;

// ── Status distribution (doughnut) ──────────────────────────────────────────
$statusOrder = ['Sudah Diambil', 'Belum Diambil', 'TERTUKAR'];
$statusColors = ['Sudah Diambil' => '#10b981', 'Belum Diambil' => '#f59e0b', 'TERTUKAR' => '#ef4444'];
$statusData = ['Sudah Diambil' => 0, 'Belum Diambil' => 0, 'TERTUKAR' => 0];
$res = mysqli_query($db, "SELECT COALESCE(pp.Status, 'Belum Diambil') AS s, COUNT(*) AS n FROM paket pk $latestPickupJoin WHERE $whereDate GROUP BY s");
while ($row = mysqli_fetch_assoc($res)) {
    if (array_key_exists($row['s'], $statusData)) {
        $statusData[$row['s']] = (int) $row['n'];
    }
}
mysqli_free_result($res);
$statusLabels = array_map(fn($k) => $statusMeta[$k]['label'], $statusOrder);
$statusValues = array_map(fn($k) => $statusData[$k], $statusOrder);
$statusBgColors = array_map(fn($k) => $statusColors[$k], $statusOrder);

// ── Trend (line) ─────────────────────────────────────────────────────────────
if (in_array($range, ['7d', '30d'])) {
    $days = $range === '7d' ? 7 : 30;
    $res = mysqli_query($db, "SELECT DATE(pk.WaktuSampai) AS d, COUNT(*) AS n FROM paket pk WHERE $whereDate AND pk.WaktuSampai IS NOT NULL GROUP BY d ORDER BY d ASC");
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
    $res = mysqli_query($db, "SELECT DATE_FORMAT(pk.WaktuSampai, '%Y-%m') AS m, COUNT(*) AS n FROM paket pk WHERE $whereDate AND pk.WaktuSampai IS NOT NULL GROUP BY m ORDER BY m ASC");
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
        $trendLabels = array_map(fn($k) => date('M Y', strtotime($k . '-01')), array_keys($trendRaw));
        $trendValues = array_values($trendRaw);
        if (empty($trendLabels)) {
            $trendLabels = [date('M Y')];
            $trendValues = [0];
        }
    }
}

// ── Tipe kiriman distribution (doughnut) ─────────────────────────────────────
$tipeData = ['Paket' => 0, 'Dokumen' => 0];
$res = mysqli_query($db, "SELECT pk.JenisPaket AS j, COUNT(*) AS n FROM paket pk WHERE $whereDate GROUP BY j");
while ($row = mysqli_fetch_assoc($res)) {
    $label = paket_type_label($row['j']);
    if (array_key_exists($label, $tipeData)) {
        $tipeData[$label] += (int) $row['n'];
    }
}
mysqli_free_result($res);

// ── Top 5 kurir (horizontal bar) ─────────────────────────────────────────────
$topKurirLabels = [];
$topKurirValues = [];
$res = mysqli_query($db, "SELECT pk.Kurir, COUNT(*) AS n FROM paket pk WHERE $whereDate AND pk.Kurir <> '' GROUP BY pk.Kurir ORDER BY n DESC LIMIT 5");
while ($row = mysqli_fetch_assoc($res)) {
    $topKurirLabels[] = $row['Kurir'];
    $topKurirValues[] = (int) $row['n'];
}
mysqli_free_result($res);

// ── Jam sibuk paket datang (bar) ─────────────────────────────────────────────
$hourRaw = array_fill(0, 24, 0);
$res = mysqli_query($db, "SELECT HOUR(pk.WaktuSampai) AS h, COUNT(*) AS n FROM paket pk WHERE $whereDate AND pk.WaktuSampai IS NOT NULL GROUP BY h");
while ($row = mysqli_fetch_assoc($res)) {
    $hourRaw[(int) $row['h']] = (int) $row['n'];
}
mysqli_free_result($res);
$hourLabels = [];
$hourValues = [];
for ($h = 0; $h < 24; $h++) {
    $hourLabels[] = sprintf('%02d', $h);
    $hourValues[] = $hourRaw[$h];
}

// ── Top 5 penghuni penerima terbanyak (horizontal bar) ───────────────────────
$topPenghuniLabels = [];
$topPenghuniValues = [];
$res = mysqli_query($db, "
    SELECT ph.NamaPenghuni, COUNT(*) AS n
    FROM paket pk
    JOIN penghuni ph ON pk.PenghuniID = ph.PenghuniID
    WHERE $whereDate
    GROUP BY pk.PenghuniID, ph.NamaPenghuni
    ORDER BY n DESC LIMIT 5
");
while ($row = mysqli_fetch_assoc($res)) {
    $topPenghuniLabels[] = $row['NamaPenghuni'];
    $topPenghuniValues[] = (int) $row['n'];
}
mysqli_free_result($res);

// ── Petugas SIGAP ranking (PENGURUS only) ────────────────────────────────────
$petugasPerforma = [];
if ($role === 'PENGURUS') {
    $res = mysqli_query($db, "
        SELECT pt.NamaPetugas,
               COUNT(*) AS total,
               SUM(CASE WHEN COALESCE(pp.Status, 'Belum Diambil') = 'Sudah Diambil' THEN 1 ELSE 0 END) AS sudah,
               SUM(CASE WHEN COALESCE(pp.Status, 'Belum Diambil') = 'TERTUKAR' THEN 1 ELSE 0 END) AS tertukar
        FROM paket pk
        JOIN petugas pt ON pk.PetugasID = pt.PetugasID
        $latestPickupJoin
        WHERE $whereDate AND pt.Jabatan = 'SIGAP' AND pt.IsDeleted = 0
        GROUP BY pk.PetugasID, pt.NamaPetugas
        ORDER BY total DESC, sudah DESC
    ");
    while ($row = mysqli_fetch_assoc($res)) {
        $petugasPerforma[] = $row;
    }
    mysqli_free_result($res);
}

// ── Detail rows ──────────────────────────────────────────────────────────────
$detailRows = [];
$res = mysqli_query($db, "
    SELECT pk.PaketID, pk.NamaPengirim, pk.Kurir, pk.JenisPaket, pk.WaktuSampai,
           ph.NamaPenghuni, ph.Nim, k.NomorKamar, pt.NamaPetugas,
           COALESCE(pp.Status, 'Belum Diambil') AS Status, pp.WaktuPengambilan,
           CASE WHEN pp.Status = 'Sudah Diambil' AND pp.WaktuPengambilan IS NOT NULL AND pk.WaktuSampai IS NOT NULL
                THEN TIMESTAMPDIFF(MINUTE, pk.WaktuSampai, pp.WaktuPengambilan) ELSE NULL END AS Durasi
    FROM paket pk
    JOIN penghuni ph     ON pk.PenghuniID = ph.PenghuniID
    LEFT JOIN kamar k    ON ph.KamarID    = k.KamarID
    LEFT JOIN petugas pt ON pk.PetugasID  = pt.PetugasID
    $latestPickupJoin
    WHERE $whereDate
    ORDER BY pk.WaktuSampai DESC
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
        <div class="tw:pt-28 tw:md:pt-8 tw:px-8 tw:mb-8 tw:flex-1 tw:w-dvw tw:md:w-full" id="report-content">

            <h1 class="page-title" data-kicker="Laporan &amp; Analitik"
                data-subtitle="Statistik dan ringkasan seluruh distribusi paket serta pengambilannya dalam periode yang dipilih.">
                <i class="fa-solid fa-chart-bar tw:mr-[10px] tw:text-[#146c94]!"></i>Laporan Paket
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

            <div class="report-stat-grid tw:grid tw:grid-cols-1 tw:md:grid-cols-2 tw:lg:grid-cols-4 tw:gap-5 tw:mb-8">
                <div data-gsap="stat-card"
                    class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.85)] tw:shadow-sm">
                    <div class="tw:flex tw:items-center tw:gap-4">
                        <div
                            class="tw:w-[3.4rem] tw:h-[3.4rem] tw:inline-flex tw:items-center tw:justify-center tw:rounded-[20px] tw:flex-shrink-0 tw:text-primary tw:bg-accent/80">
                            <i class="fa-solid fa-boxes-stacked tw:text-[22px]"></i>
                        </div>
                        <div>
                            <span
                                class="tw:block tw:text-slate-500 tw:text-[0.72rem] tw:font-extrabold tw:tracking-[0.08em] tw:uppercase">Total
                                Paket</span>
                            <strong
                                class="tw:block tw:mt-[0.3rem] tw:text-[1.9rem] tw:leading-none tw:text-slate-900 tw:font-bold"><?= (int) ($stats['total'] ?? 0) ?></strong>
                        </div>
                    </div>
                </div>
                <div data-gsap="stat-card"
                    class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.85)] tw:shadow-sm">
                    <div class="tw:flex tw:items-center tw:gap-4">
                        <div
                            class="tw:w-[3.4rem] tw:h-[3.4rem] tw:inline-flex tw:items-center tw:justify-center tw:rounded-[20px] tw:flex-shrink-0 tw:text-emerald-800 tw:bg-[rgba(220,244,239,0.82)]">
                            <i class="fa-solid fa-circle-check tw:text-[22px]"></i>
                        </div>
                        <div>
                            <span
                                class="tw:block tw:text-slate-500 tw:text-[0.72rem] tw:font-extrabold tw:tracking-[0.08em] tw:uppercase">Sudah
                                Diambil</span>
                            <strong
                                class="tw:block tw:mt-[0.3rem] tw:text-[1.9rem] tw:leading-none tw:text-slate-900 tw:font-bold"><?= (int) ($stats['sudah'] ?? 0) ?></strong>
                        </div>
                    </div>
                </div>
                <div data-gsap="stat-card"
                    class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.85)] tw:shadow-sm">
                    <div class="tw:flex tw:items-center tw:gap-4">
                        <div
                            class="tw:w-[3.4rem] tw:h-[3.4rem] tw:inline-flex tw:items-center tw:justify-center tw:rounded-[20px] tw:flex-shrink-0 tw:text-amber-700 tw:bg-[rgba(250,236,207,0.82)]">
                            <i class="fa-solid fa-box-open tw:text-[22px]"></i>
                        </div>
                        <div>
                            <span
                                class="tw:block tw:text-slate-500 tw:text-[0.72rem] tw:font-extrabold tw:tracking-[0.08em] tw:uppercase">Belum
                                Diambil</span>
                            <strong
                                class="tw:block tw:mt-[0.3rem] tw:text-[1.9rem] tw:leading-none tw:text-slate-900 tw:font-bold"><?= (int) ($stats['belum'] ?? 0) ?></strong>
                        </div>
                    </div>
                </div>
                <div data-gsap="stat-card"
                    class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.85)] tw:shadow-sm">
                    <div class="tw:flex tw:items-center tw:gap-4">
                        <div
                            class="tw:w-[3.4rem] tw:h-[3.4rem] tw:inline-flex tw:items-center tw:justify-center tw:rounded-[20px] tw:flex-shrink-0 tw:text-red-700 tw:bg-[rgba(245,221,218,0.82)]">
                            <i class="fa-solid fa-triangle-exclamation tw:text-[22px]"></i>
                        </div>
                        <div>
                            <span
                                class="tw:block tw:text-slate-500 tw:text-[0.72rem] tw:font-extrabold tw:tracking-[0.08em] tw:uppercase">Tertukar</span>
                            <strong
                                class="tw:block tw:mt-[0.3rem] tw:text-[1.9rem] tw:leading-none tw:text-slate-900 tw:font-bold"><?= (int) ($stats['tertukar'] ?? 0) ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tw:grid tw:grid-cols-1 tw:lg:grid-cols-2 tw:gap-6 tw:mb-6">

                <div data-gsap="panel"
                    class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm">
                    <h5 class="tw:m-0 tw:text-[1.2rem] tw:text-slate-900 tw:flex tw:items-center tw:gap-2">
                        <i class="fa-solid fa-chart-pie"></i> Distribusi Status Paket
                    </h5>
                    <p class="tw:m-0 tw:text-slate-500 tw:leading-[1.75] tw:text-sm">Proporsi paket yang sudah diambil,
                        belum diambil, dan tertukar dalam periode <?= $rangeLabel ?>.</p>
                    <div class="tw:inline-flex tw:items-center tw:gap-2 tw:mt-2 tw:text-xs tw:font-bold tw:px-[10px] tw:py-[4px] tw:rounded-full tw:bg-accent/50 tw:text-primary">
                        <i class="fa-regular fa-clock"></i> Rata-rata waktu pengambilan:
                        <?= $avgMinutes !== null ? htmlspecialchars(paket_format_duration($avgMinutes)) : '—' ?>
                    </div>
                    <div class="tw:h-[230px] tw:mt-2">
                        <canvas id="chartStatus"></canvas>
                    </div>
                </div>

                <div data-gsap="panel"
                    class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm">
                    <h5 class="tw:m-0 tw:text-[1.2rem] tw:text-slate-900 tw:flex tw:items-center tw:gap-2">
                        <i class="fa-solid fa-arrow-trend-up"></i> Trend Paket Masuk
                    </h5>
                    <p class="tw:m-0 tw:text-slate-500 tw:leading-[1.75] tw:text-sm">Jumlah paket yang sampai per periode
                        dalam rentang <?= $rangeLabel ?>.</p>
                    <div class="tw:h-[265px] tw:mt-2">
                        <canvas id="chartTrend"></canvas>
                    </div>
                </div>

            </div>

            <div class="tw:grid tw:grid-cols-1 tw:lg:grid-cols-2 tw:gap-6 tw:mb-6">

                <div data-gsap="panel"
                    class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm">
                    <h5 class="tw:m-0 tw:text-[1.2rem] tw:text-slate-900 tw:flex tw:items-center tw:gap-2">
                        <i class="fa-solid fa-layer-group"></i> Distribusi Tipe Kiriman
                    </h5>
                    <p class="tw:m-0 tw:text-slate-500 tw:leading-[1.75] tw:text-sm">Perbandingan jumlah paket dan dokumen
                        yang diterima dalam periode <?= $rangeLabel ?>.</p>
                    <div class="tw:h-[220px] tw:mt-2">
                        <canvas id="chartTipe"></canvas>
                    </div>
                </div>

                <div data-gsap="panel"
                    class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm">
                    <h5 class="tw:m-0 tw:text-[1.2rem] tw:text-slate-900 tw:flex tw:items-center tw:gap-2">
                        <i class="fa-solid fa-truck-fast"></i> Top 5 Kurir Terbanyak
                    </h5>
                    <p class="tw:m-0 tw:text-slate-500 tw:leading-[1.75] tw:text-sm">Jasa kurir yang paling sering
                        mengirim paket dalam periode <?= $rangeLabel ?>.</p>
                    <div class="tw:h-[220px] tw:mt-2">
                        <canvas id="chartKurir"></canvas>
                    </div>
                </div>

            </div>

            <div class="tw:grid tw:grid-cols-1 tw:lg:grid-cols-2 tw:gap-6 tw:mb-6">

                <div data-gsap="panel"
                    class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm">
                    <h5 class="tw:m-0 tw:text-[1.2rem] tw:text-slate-900 tw:flex tw:items-center tw:gap-2">
                        <i class="fa-regular fa-clock"></i> Jam Sibuk Paket Datang
                    </h5>
                    <p class="tw:m-0 tw:text-slate-500 tw:leading-[1.75] tw:text-sm">Sebaran waktu kedatangan paket
                        berdasarkan jam dalam periode <?= $rangeLabel ?>.</p>
                    <div class="tw:h-[240px] tw:mt-2">
                        <canvas id="chartHour"></canvas>
                    </div>
                </div>

                <div data-gsap="panel"
                    class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm">
                    <h5 class="tw:m-0 tw:text-[1.2rem] tw:text-slate-900 tw:flex tw:items-center tw:gap-2">
                        <i class="fa-solid fa-user-tag"></i> Top 5 Penerima Terbanyak
                    </h5>
                    <p class="tw:m-0 tw:text-slate-500 tw:leading-[1.75] tw:text-sm">Penghuni dengan jumlah paket diterima
                        terbanyak dalam periode <?= $rangeLabel ?>.</p>
                    <div class="tw:h-[240px] tw:mt-2">
                        <canvas id="chartPenghuni"></canvas>
                    </div>
                </div>

            </div>

            <?php if ($role === 'PENGURUS' && count($petugasPerforma) > 0): ?>
                <div class="tw:mb-6">
                    <div data-gsap="panel"
                        class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm">
                        <h5 class="tw:m-0 tw:text-[1.2rem] tw:text-slate-900 tw:flex tw:items-center tw:gap-2">
                            <i class="fa-solid fa-ranking-star"></i> Ranking Petugas SIGAP
                        </h5>
                        <p class="tw:m-0 tw:text-slate-500 tw:leading-[1.75] tw:text-sm">Peringkat petugas berdasarkan
                            jumlah paket yang dicatat dalam <?= $rangeLabel ?>.</p>
                        <div class="table-panel tw:mt-2">
                            <div class="doremi-table-wrapper">
                                <table class="table doremi-table tw:w-full report-card-table">
                                    <thead>
                                        <tr>
                                            <th class="tw:w-[50px]">Rank</th>
                                            <th>Petugas</th>
                                            <th>Total Dicatat</th>
                                            <th>Sudah Diambil</th>
                                            <th>Tertukar</th>
                                            <th>Tingkat Pengambilan</th>
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
                                            $pickupRate = $p['total'] > 0 ? round(($p['sudah'] / $p['total']) * 100) : 0;
                                            $rateColor = $pickupRate >= 80 ? '#16a34a' : ($pickupRate >= 50 ? '#d97706' : '#dc2626');
                                            ?>
                                            <tr>
                                                <td class="text-center" data-label="Rank"><?= $rankIcon ?></td>
                                                <td data-label="Petugas"><strong><?= htmlspecialchars($p['NamaPetugas']) ?></strong></td>
                                                <td class="text-center" data-label="Total Dicatat"><?= (int) $p['total'] ?></td>
                                                <td class="text-center" data-label="Sudah Diambil"><span
                                                        class="tw:bg-emerald-100 tw:text-green-700 tw:px-[10px] tw:py-[2px] tw:rounded-full tw:text-xs tw:font-semibold"><?= (int) $p['sudah'] ?></span>
                                                </td>
                                                <td class="text-center" data-label="Tertukar"><span
                                                        class="tw:bg-red-100 tw:text-red-700 tw:px-[10px] tw:py-[2px] tw:rounded-full tw:text-xs tw:font-semibold"><?= (int) $p['tertukar'] ?></span>
                                                </td>
                                                <td class="text-center" data-label="Tingkat Pengambilan">
                                                    <div class="tw:flex tw:items-center tw:gap-[6px] tw:justify-center">
                                                        <div
                                                            class="tw:bg-gray-200 tw:rounded tw:h-[6px] tw:w-20 tw:overflow-hidden">
                                                            <div
                                                                style="background:<?= $rateColor ?>; height:100%; width:<?= $pickupRate ?>%; border-radius:4px;">
                                                            </div>
                                                        </div>
                                                        <span
                                                            style="font-size:12px; font-weight:700; color:<?= $rateColor ?>;"><?= $pickupRate ?>%</span>
                                                    </div>
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
                            <i class="fa-solid fa-table-list"></i> Daftar Paket
                        </h5>
                        <p class="tw:m-0 tw:text-slate-500 tw:leading-[1.75] tw:text-sm"><?= count($detailRows) ?>
                            paket ditemukan dalam periode <?= $rangeLabel ?>.</p>
                    </div>
                </div>
                <div class="doremi-table-wrapper print-table-wrapper">
                    <table id="reportTable"
                        class="table doremi-table text-center align-middle tw:mb-0 tw:w-full print-table report-card-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Penghuni</th>
                                <th>Tipe</th>
                                <th>Pengirim</th>
                                <th>Kurir</th>
                                <th>Status</th>
                                <th>Waktu Sampai</th>
                                <th>Waktu Ambil</th>
                                <th>Durasi</th>
                                <?php if ($role === 'PENGURUS'): ?>
                                    <th>Dicatat Oleh</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detailRows as $i => $r):
                                $statusStyle = $statusMeta[$r['Status']]['badge'] ?? 'background:#fef9c3; color:#854d0e;';
                                $statusLabel = $statusMeta[$r['Status']]['label'] ?? $r['Status'];
                                $tipeLabel = paket_type_label($r['JenisPaket'] ?? null);
                                $tipeStyle = $tipeLabel === 'Dokumen' ? 'background:#dbeafe; color:#1d4ed8;' : 'background:#e2e8f0; color:#475569;';
                                $durasi = $r['Durasi'] !== null ? paket_format_duration((int) $r['Durasi']) : '—';
                                ?>
                                <tr>
                                    <td class="row-number-cell" data-label="No"></td>
                                    <td data-label="Penghuni">
                                        <div class="tw:font-semibold"><?= htmlspecialchars($r['NamaPenghuni']) ?></div>
                                        <div class="tw:text-xs tw:text-gray-500">
                                            <?= htmlspecialchars($r['Nim']) ?><?= !empty($r['NomorKamar']) ? ' · Kamar ' . htmlspecialchars($r['NomorKamar']) : '' ?>
                                        </div>
                                    </td>
                                    <td data-label="Tipe"><span
                                            style="font-size:11px; font-weight:600; padding:2px 8px; border-radius:20px; <?= $tipeStyle ?>"><?= htmlspecialchars($tipeLabel) ?></span>
                                    </td>
                                    <td data-label="Pengirim"><?= htmlspecialchars($r['NamaPengirim']) ?></td>
                                    <td data-label="Kurir"><?= htmlspecialchars($r['Kurir']) ?></td>
                                    <td data-label="Status"><span
                                            style="font-size:11px; font-weight:600; padding:2px 8px; border-radius:20px; <?= $statusStyle ?>"><?= htmlspecialchars($statusLabel) ?></span>
                                    </td>
                                    <td data-label="Waktu Sampai"><?= $r['WaktuSampai'] ? date('d M Y, H:i', strtotime($r['WaktuSampai'])) : '—' ?>
                                    </td>
                                    <td data-label="Waktu Ambil"><?= $r['WaktuPengambilan'] ? date('d M Y, H:i', strtotime($r['WaktuPengambilan'])) : '—' ?>
                                    </td>
                                    <td data-label="Durasi"><?= htmlspecialchars($durasi) ?></td>
                                    <?php if ($role === 'PENGURUS'): ?>
                                        <td data-label="Dicatat Oleh"><?= htmlspecialchars($r['NamaPetugas'] ?? '—') ?></td><?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <?php require '../../bootstrap.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            const primary = '#146c94';
            const baseOpts = { responsive: true, maintainAspectRatio: false };
            const gridOpts = { color: 'rgba(0,0,0,0.05)' };

            // Doughnut — Status distribution
            new Chart(document.getElementById('chartStatus'), {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode($statusLabels) ?>,
                    datasets: [{
                        data: <?= json_encode($statusValues) ?>,
                        backgroundColor: <?= json_encode($statusBgColors) ?>,
                        borderWidth: 2,
                        borderColor: '#fff',
                    }]
                },
                options: {
                    ...baseOpts,
                    cutout: '62%',
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, padding: 14, font: { size: 12 } } },
                        tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed} paket` } }
                    }
                }
            });

            // Line — Trend
            new Chart(document.getElementById('chartTrend'), {
                type: 'line',
                data: {
                    labels: <?= json_encode($trendLabels) ?>,
                    datasets: [{ label: 'Paket', data: <?= json_encode($trendValues) ?>, borderColor: primary, backgroundColor: 'rgba(20,108,148,0.08)', borderWidth: 2.5, pointRadius: 4, pointBackgroundColor: primary, fill: true, tension: 0.4 }]
                },
                options: { ...baseOpts, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } }, grid: gridOpts }, x: { ticks: { font: { size: 11 }, maxRotation: 45, maxTicksLimit: 10 }, grid: { display: false } } } }
            });

            // Doughnut — Tipe kiriman
            new Chart(document.getElementById('chartTipe'), {
                type: 'doughnut',
                data: {
                    labels: ['Paket', 'Dokumen'],
                    datasets: [{
                        data: [<?= (int) $tipeData['Paket'] ?>, <?= (int) $tipeData['Dokumen'] ?>],
                        backgroundColor: ['#146c94', '#2F7FF0'],
                        borderWidth: 2,
                        borderColor: '#fff',
                    }]
                },
                options: {
                    ...baseOpts,
                    cutout: '62%',
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, padding: 14, font: { size: 12 } } },
                        tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed} kiriman` } }
                    }
                }
            });

            // Bar Horizontal — Top kurir
            new Chart(document.getElementById('chartKurir'), {
                type: 'bar',
                data: {
                    labels: <?= json_encode($topKurirLabels ?: ['Tidak ada data']) ?>,
                    datasets: [{ label: 'Paket', data: <?= json_encode($topKurirValues ?: [0]) ?>, backgroundColor: 'rgba(20,108,148,0.75)', borderRadius: 6, borderSkipped: false }]
                },
                options: { ...baseOpts, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } }, grid: gridOpts }, y: { ticks: { font: { size: 11 } }, grid: { display: false } } } }
            });

            // Bar — Jam sibuk
            new Chart(document.getElementById('chartHour'), {
                type: 'bar',
                data: {
                    labels: <?= json_encode($hourLabels) ?>,
                    datasets: [{ label: 'Paket', data: <?= json_encode($hourValues) ?>, backgroundColor: '#10b981', borderRadius: 4, borderSkipped: false }]
                },
                options: { ...baseOpts, plugins: { legend: { display: false }, tooltip: { callbacks: { title: items => `Pukul ${items[0].label}:00`, label: ctx => ` ${ctx.parsed.y} paket` } } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } }, grid: gridOpts }, x: { ticks: { font: { size: 9 }, maxRotation: 0, autoSkip: true, maxTicksLimit: 12 }, grid: { display: false } } } }
            });

            // Bar Horizontal — Top penghuni
            new Chart(document.getElementById('chartPenghuni'), {
                type: 'bar',
                data: {
                    labels: <?= json_encode($topPenghuniLabels ?: ['Tidak ada data']) ?>,
                    datasets: [{ label: 'Paket', data: <?= json_encode($topPenghuniValues ?: [0]) ?>, backgroundColor: '#2F7FF0', borderRadius: 6, borderSkipped: false }]
                },
                options: { ...baseOpts, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } }, grid: gridOpts }, y: { ticks: { font: { size: 11 } }, grid: { display: false } } } }
            });
        })();
    </script>


    <style>
        @media print {

            .no-print,
            .dashboard-sidebar,
            .sidebar,
            .dashboard-topbar,
            .dashboard-overlay,
            nav {
                display: none !important;
            }

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

            .print-only {
                display: none;
            }

            .print-header-block {
                margin-bottom: 14px;
            }

            .dashboard-stat-card,
            .dashboard-side-panel {
                box-shadow: none !important;
                border: 1px solid #e2e8f0 !important;
            }

            canvas {
                max-height: 170px !important;
            }

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

            .print-table-wrapper {
                overflow: visible !important;
                width: 100% !important;
            }

            .print-table {
                width: 100% !important;
                table-layout: fixed !important;
                font-size: 9px !important;
                border-collapse: collapse !important;
                page-break-inside: auto !important;
            }

            .print-table thead {
                display: table-header-group !important;
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

            .table-panel {
                page-break-before: auto;
            }
        }
    </style>

</body>

</html>
