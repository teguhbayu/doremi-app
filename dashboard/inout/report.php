<?php
session_start();
if (!isset($_SESSION['userId'])) {
    header('Location: /doremi-app/login.php');
    exit;
}
if (!in_array($_SESSION['userRole'] ?? '', ['SIGAP', 'PENGURUS'], true)) {
    header('Location: /doremi-app/dashboard/');
    exit;
}
require '../../db.php';
require '../../database/inoutReport.php';

$role = $_SESSION['userRole'];
session_write_close();

$allowedRanges = ['7d', '30d', '6m', 'all'];
$range = in_array($_GET['range'] ?? '', $allowedRanges) ? $_GET['range'] : '7d';

$rangeLabel = match ($range) {
    '7d' => '7 Hari Terakhir',
    '30d' => '30 Hari Terakhir',
    '6m' => '6 Bulan Terakhir',
    'all' => 'Semua Waktu',
};

/** Format a duration given in minutes into a human-friendly string. */
function inout_format_duration(?int $minutes): string
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
    $hours = intdiv($minutes, 60);
    $rest = $minutes % 60;
    return $rest ? "{$hours} j {$rest} m" : "{$hours} jam";
}

$statusMeta = [
    'Pending' => ['label' => 'Pending', 'badge' => 'background:#fef9c3; color:#854d0e;'],
    'Keluar' => ['label' => 'Di Luar', 'badge' => 'background:#fee2e2; color:#dc2626;'],
    'Masuk' => ['label' => 'Selesai', 'badge' => 'background:#dcfce7; color:#16a34a;'],
];

if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    $filename = 'laporan-izin-keluar-' . $range . '-' . date('Ymd') . '.csv';
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo "\xEF\xBB\xBF";

    $out = fopen('php://output', 'w');
    fputcsv($out, ['No', 'Penghuni', 'NIM', 'Kamar', 'Keperluan', 'Status', 'Waktu Keluar', 'Waktu Masuk', 'Durasi (Menit)', 'Dikonfirmasi Oleh'], ';');

    $no = 1;
    foreach (fetchInOutReportExport($db, $range) as $row) {
        fputcsv($out, [
            $no++,
            $row['NamaPenghuni'],
            $row['Nim'],
            $row['NomorKamar'],
            $row['Keperluan'],
            $statusMeta[$row['Status']]['label'] ?? $row['Status'],
            $row['WaktuKeluar'] ? date('d/m/Y H:i', strtotime($row['WaktuKeluar'])) : '-',
            $row['WaktuMasuk'] ? date('d/m/Y H:i', strtotime($row['WaktuMasuk'])) : '-',
            $row['Durasi'] ?? '-',
            $row['NamaPetugas'] ?? '-',
        ], ';');
    }
    fclose($out);
    exit;
}

$stats = fetchInOutReportStats($db, $range);
$avgMinutes = ($stats['avg_menit'] ?? null) !== null ? (int) $stats['avg_menit'] : null;

// ── Status distribution (doughnut) ──────────────────────────────────────────
$statusOrder = ['Masuk', 'Keluar', 'Pending'];
$statusColors = ['Masuk' => '#10b981', 'Keluar' => '#ef4444', 'Pending' => '#f59e0b'];
$statusData = ['Masuk' => 0, 'Keluar' => 0, 'Pending' => 0];
foreach (fetchInOutReportStatusDist($db, $range) as $row) {
    if (array_key_exists($row['Status'], $statusData)) {
        $statusData[$row['Status']] = (int) $row['n'];
    }
}
$statusLabels = array_map(fn($k) => $statusMeta[$k]['label'], $statusOrder);
$statusValues = array_map(fn($k) => $statusData[$k], $statusOrder);
$statusBgColors = array_map(fn($k) => $statusColors[$k], $statusOrder);

// ── Trend (line) ─────────────────────────────────────────────────────────────
if (in_array($range, ['7d', '30d'])) {
    $days = $range === '7d' ? 7 : 30;
    $trendRaw = [];
    foreach (fetchInOutReportTrendDaily($db, $range) as $row) {
        $trendRaw[$row['d']] = (int) $row['n'];
    }
    $trendLabels = [];
    $trendValues = [];
    for ($i = $days - 1; $i >= 0; $i--) {
        $key = date('Y-m-d', strtotime("-$i day"));
        $trendLabels[] = date('d M', strtotime("-$i day"));
        $trendValues[] = $trendRaw[$key] ?? 0;
    }
} else {
    $trendRaw = [];
    foreach (fetchInOutReportTrendMonthly($db, $range) as $row) {
        $trendRaw[$row['m']] = (int) $row['n'];
    }
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

// ── Peak hour distribution (bar) — actual exits only ─────────────────────────
$hourRaw = array_fill(0, 24, 0);
foreach (fetchInOutReportPeakHour($db, $range) as $row) {
    $hourRaw[(int) $row['h']] = (int) $row['n'];
}
$hourLabels = [];
$hourValues = [];
for ($h = 0; $h < 24; $h++) {
    $hourLabels[] = sprintf('%02d', $h);
    $hourValues[] = $hourRaw[$h];
}

// ── Top 5 penghuni paling sering keluar (horizontal bar) ─────────────────────
$topPenghuniLabels = [];
$topPenghuniValues = [];
foreach (fetchInOutReportTopPenghuni($db, $range) as $row) {
    $topPenghuniLabels[] = $row['NamaPenghuni'];
    $topPenghuniValues[] = (int) $row['n'];
}

// ── Gender distribution (doughnut) ───────────────────────────────────────────
$genderData = ['L' => 0, 'P' => 0];
foreach (fetchInOutReportGenderDist($db, $range) as $row) {
    if (array_key_exists($row['g'], $genderData)) {
        $genderData[$row['g']] = (int) $row['n'];
    }
}

// ── Top keperluan (horizontal bar) ───────────────────────────────────────────
$topKeperluanLabels = [];
$topKeperluanValues = [];
foreach (fetchInOutReportTopKeperluan($db, $range) as $row) {
    $topKeperluanLabels[] = $row['Keperluan'];
    $topKeperluanValues[] = (int) $row['n'];
}

// ── Petugas SIGAP ranking (PENGURUS only) ────────────────────────────────────
$petugasPerforma = [];
if ($role === 'PENGURUS') {
    $petugasPerforma = fetchInOutReportPetugasRanking($db, $range);
}

// ── Detail rows ──────────────────────────────────────────────────────────────
$detailRows = fetchInOutReportDetail($db, $range);
?>
<!DOCTYPE html>
<html lang="id">
<?php require '../../head.php'; ?>

<body class="dashboard-body tw:p-0 tw:m-0 tw:relative tw:flex tw:min-h-screen tw:overflow-x-hidden">

    <?php require '../components/sidebar.php'; ?>

    <main class="tw:md:ml-75 tw:grow tw:relative tw:z-10">
        <div class="tw:pt-28 tw:md:pt-8 tw:px-8 tw:mb-8 tw:flex-1 tw:w-dvw tw:md:w-full" id="report-content">

            <h1 class="page-title" data-kicker="Laporan &amp; Analitik"
                data-subtitle="Statistik dan ringkasan seluruh aktivitas izin keluar masuk penghuni dalam periode yang dipilih.">
                <i class="fa-solid fa-chart-bar tw:mr-[10px] tw:text-[#146c94]!"></i>Laporan Izin Keluar
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
                            <i class="fa-solid fa-right-left tw:text-[22px]"></i>
                        </div>
                        <div>
                            <span
                                class="tw:block tw:text-slate-500 tw:text-[0.72rem] tw:font-extrabold tw:tracking-[0.08em] tw:uppercase">Total
                                Izin</span>
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
                                class="tw:block tw:text-slate-500 tw:text-[0.72rem] tw:font-extrabold tw:tracking-[0.08em] tw:uppercase">Selesai
                                (Masuk)</span>
                            <strong
                                class="tw:block tw:mt-[0.3rem] tw:text-[1.9rem] tw:leading-none tw:text-slate-900 tw:font-bold"><?= (int) ($stats['selesai'] ?? 0) ?></strong>
                        </div>
                    </div>
                </div>
                <div data-gsap="stat-card"
                    class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.85)] tw:shadow-sm">
                    <div class="tw:flex tw:items-center tw:gap-4">
                        <div
                            class="tw:w-[3.4rem] tw:h-[3.4rem] tw:inline-flex tw:items-center tw:justify-center tw:rounded-[20px] tw:flex-shrink-0 tw:text-red-700 tw:bg-[rgba(254,226,226,0.82)]">
                            <i class="fa-solid fa-person-walking-arrow-right tw:text-[22px]"></i>
                        </div>
                        <div>
                            <span
                                class="tw:block tw:text-slate-500 tw:text-[0.72rem] tw:font-extrabold tw:tracking-[0.08em] tw:uppercase">Sedang
                                di Luar</span>
                            <strong
                                class="tw:block tw:mt-[0.3rem] tw:text-[1.9rem] tw:leading-none tw:text-slate-900 tw:font-bold"><?= (int) ($stats['diluar'] ?? 0) ?></strong>
                        </div>
                    </div>
                </div>
                <div data-gsap="stat-card"
                    class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.85)] tw:shadow-sm">
                    <div class="tw:flex tw:items-center tw:gap-4">
                        <div
                            class="tw:w-[3.4rem] tw:h-[3.4rem] tw:inline-flex tw:items-center tw:justify-center tw:rounded-[20px] tw:flex-shrink-0 tw:text-primary tw:bg-accent/80">
                            <i class="fa-regular fa-clock tw:text-[22px]"></i>
                        </div>
                        <div>
                            <span
                                class="tw:block tw:text-slate-500 tw:text-[0.72rem] tw:font-extrabold tw:tracking-[0.08em] tw:uppercase">Rata-rata
                                di Luar</span>
                            <strong
                                class="tw:block tw:mt-[0.3rem] tw:text-[1.9rem] tw:leading-none tw:text-slate-900 tw:font-bold"><?= $avgMinutes !== null ? htmlspecialchars(inout_format_duration($avgMinutes)) : '0' ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tw:grid tw:grid-cols-1 tw:lg:grid-cols-2 tw:gap-6 tw:mb-6">

                <div data-gsap="panel"
                    class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm">
                    <h5 class="tw:m-0 tw:text-[1.2rem] tw:text-slate-900 tw:flex tw:items-center tw:gap-2">
                        <i class="fa-solid fa-chart-pie"></i> Distribusi Status Izin
                    </h5>
                    <p class="tw:m-0 tw:text-slate-500 tw:leading-[1.75] tw:text-sm">Proporsi izin yang sudah selesai,
                        masih di luar, dan menunggu konfirmasi dalam periode <?= $rangeLabel ?>.</p>
                    <div class="tw:h-[240px] tw:mt-2">
                        <canvas id="chartStatus"></canvas>
                    </div>
                </div>

                <div data-gsap="panel"
                    class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm">
                    <h5 class="tw:m-0 tw:text-[1.2rem] tw:text-slate-900 tw:flex tw:items-center tw:gap-2">
                        <i class="fa-solid fa-arrow-trend-up"></i> Trend Izin Keluar
                    </h5>
                    <p class="tw:m-0 tw:text-slate-500 tw:leading-[1.75] tw:text-sm">Jumlah izin keluar yang diajukan per
                        periode dalam rentang <?= $rangeLabel ?>.</p>
                    <div class="tw:h-[240px] tw:mt-2">
                        <canvas id="chartTrend"></canvas>
                    </div>
                </div>

            </div>

            <div class="tw:grid tw:grid-cols-1 tw:lg:grid-cols-2 tw:gap-6 tw:mb-6">

                <div data-gsap="panel"
                    class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm">
                    <h5 class="tw:m-0 tw:text-[1.2rem] tw:text-slate-900 tw:flex tw:items-center tw:gap-2">
                        <i class="fa-regular fa-clock"></i> Jam Sibuk Keluar
                    </h5>
                    <p class="tw:m-0 tw:text-slate-500 tw:leading-[1.75] tw:text-sm">Sebaran waktu keluar penghuni
                        berdasarkan jam dalam periode <?= $rangeLabel ?>.</p>
                    <div class="tw:h-[240px] tw:mt-2">
                        <canvas id="chartHour"></canvas>
                    </div>
                </div>

                <div data-gsap="panel"
                    class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm">
                    <h5 class="tw:m-0 tw:text-[1.2rem] tw:text-slate-900 tw:flex tw:items-center tw:gap-2">
                        <i class="fa-solid fa-user-clock"></i> Top 5 Penghuni Paling Sering Keluar
                    </h5>
                    <p class="tw:m-0 tw:text-slate-500 tw:leading-[1.75] tw:text-sm">Penghuni dengan jumlah izin keluar
                        terbanyak dalam periode <?= $rangeLabel ?>.</p>
                    <div class="tw:h-[240px] tw:mt-2">
                        <canvas id="chartPenghuni"></canvas>
                    </div>
                </div>

            </div>

            <div class="tw:grid tw:grid-cols-1 tw:lg:grid-cols-2 tw:gap-6 tw:mb-6">

                <div data-gsap="panel"
                    class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm">
                    <h5 class="tw:m-0 tw:text-[1.2rem] tw:text-slate-900 tw:flex tw:items-center tw:gap-2">
                        <i class="fa-solid fa-venus-mars"></i> Distribusi Jenis Kelamin
                    </h5>
                    <p class="tw:m-0 tw:text-slate-500 tw:leading-[1.75] tw:text-sm">Perbandingan aktivitas izin keluar
                        antara penghuni laki-laki dan perempuan.</p>
                    <div class="tw:h-[220px] tw:mt-2">
                        <canvas id="chartGender"></canvas>
                    </div>
                </div>

                <div data-gsap="panel"
                    class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm">
                    <h5 class="tw:m-0 tw:text-[1.2rem] tw:text-slate-900 tw:flex tw:items-center tw:gap-2">
                        <i class="fa-solid fa-list-check"></i> Keperluan Terbanyak
                    </h5>
                    <p class="tw:m-0 tw:text-slate-500 tw:leading-[1.75] tw:text-sm">Alasan izin keluar yang paling
                        sering dicatat dalam periode <?= $rangeLabel ?>.</p>
                    <div class="tw:h-[220px] tw:mt-2">
                        <canvas id="chartKeperluan"></canvas>
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
                            jumlah konfirmasi izin yang ditangani dalam <?= $rangeLabel ?>.</p>
                        <div class="table-panel tw:mt-2">
                            <div class="doremi-table-wrapper">
                                <table class="table doremi-table tw:w-full report-card-table">
                                    <thead>
                                        <tr>
                                            <th class="tw:w-[50px]">Rank</th>
                                            <th>Petugas</th>
                                            <th>Total Ditangani</th>
                                            <th>Selesai</th>
                                            <th>Masih di Luar</th>
                                            <th>Tingkat Selesai</th>
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
                                                <td class="text-center" data-label="Rank"><?= $rankIcon ?></td>
                                                <td data-label="Petugas"><strong><?= htmlspecialchars($p['NamaPetugas']) ?></strong></td>
                                                <td class="text-center" data-label="Total Ditangani"><?= (int) $p['total'] ?></td>
                                                <td class="text-center" data-label="Selesai"><span
                                                        class="tw:bg-emerald-100 tw:text-green-700 tw:px-[10px] tw:py-[2px] tw:rounded-full tw:text-xs tw:font-semibold"><?= (int) $p['selesai'] ?></span>
                                                </td>
                                                <td class="text-center" data-label="Masih di Luar"><span
                                                        class="tw:bg-red-100 tw:text-red-700 tw:px-[10px] tw:py-[2px] tw:rounded-full tw:text-xs tw:font-semibold"><?= (int) $p['diluar'] ?></span>
                                                </td>
                                                <td class="text-center" data-label="Tingkat Selesai">
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
                            <i class="fa-solid fa-table-list"></i> Daftar Izin Keluar
                        </h5>
                        <p class="tw:m-0 tw:text-slate-500 tw:leading-[1.75] tw:text-sm"><?= count($detailRows) ?>
                            transaksi ditemukan dalam periode <?= $rangeLabel ?>.</p>
                    </div>
                </div>
                <div class="doremi-table-wrapper print-table-wrapper">
                    <table id="reportTable"
                        class="table doremi-table text-center align-middle tw:mb-0 tw:w-full print-table report-card-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Penghuni</th>
                                <th>Kamar</th>
                                <th>Keperluan</th>
                                <th>Status</th>
                                <th>Waktu Keluar</th>
                                <th>Waktu Masuk</th>
                                <th>Durasi</th>
                                <?php if ($role === 'PENGURUS'): ?>
                                    <th>Dikonfirmasi Oleh</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detailRows as $i => $r):
                                $statusStyle = $statusMeta[$r['Status']]['badge'] ?? 'background:#fef9c3; color:#854d0e;';
                                $statusLabel = $statusMeta[$r['Status']]['label'] ?? $r['Status'];
                                $durasi = $r['Durasi'] !== null ? inout_format_duration((int) $r['Durasi']) : '—';
                                ?>
                                <tr>
                                    <td class="row-number-cell" data-label="No"></td>
                                    <td data-label="Penghuni">
                                        <div class="tw:font-semibold"><?= htmlspecialchars($r['NamaPenghuni']) ?></div>
                                        <div class="tw:text-xs tw:text-gray-500"><?= htmlspecialchars($r['Nim']) ?></div>
                                    </td>
                                    <td data-label="Kamar"><?= htmlspecialchars($r['NomorKamar']) ?></td>
                                    <td data-label="Keperluan"><?= htmlspecialchars($r['Keperluan']) ?></td>
                                    <td data-label="Status"><span
                                            style="font-size:11px; font-weight:600; padding:2px 8px; border-radius:20px; <?= $statusStyle ?>"><?= htmlspecialchars($statusLabel) ?></span>
                                    </td>
                                    <td data-label="Waktu Keluar"><?= $r['WaktuKeluar'] ? date('d M Y, H:i', strtotime($r['WaktuKeluar'])) : '—' ?>
                                    </td>
                                    <td data-label="Waktu Masuk"><?= $r['WaktuMasuk'] ? date('d M Y, H:i', strtotime($r['WaktuMasuk'])) : '—' ?>
                                    </td>
                                    <td data-label="Durasi"><?= htmlspecialchars($durasi) ?></td>
                                    <?php if ($role === 'PENGURUS'): ?>
                                        <td data-label="Dikonfirmasi Oleh"><?= htmlspecialchars($r['NamaPetugas'] ?? '—') ?></td><?php endif; ?>
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
                        tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed} izin` } }
                    }
                }
            });

            // Line — Trend
            new Chart(document.getElementById('chartTrend'), {
                type: 'line',
                data: {
                    labels: <?= json_encode($trendLabels) ?>,
                    datasets: [{ label: 'Izin Keluar', data: <?= json_encode($trendValues) ?>, borderColor: primary, backgroundColor: 'rgba(20,108,148,0.08)', borderWidth: 2.5, pointRadius: 4, pointBackgroundColor: primary, fill: true, tension: 0.4 }]
                },
                options: { ...baseOpts, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } }, grid: gridOpts }, x: { ticks: { font: { size: 11 }, maxRotation: 45, maxTicksLimit: 10 }, grid: { display: false } } } }
            });

            // Bar — Peak hour
            new Chart(document.getElementById('chartHour'), {
                type: 'bar',
                data: {
                    labels: <?= json_encode($hourLabels) ?>,
                    datasets: [{ label: 'Keluar', data: <?= json_encode($hourValues) ?>, backgroundColor: 'rgba(20,108,148,0.75)', borderRadius: 4, borderSkipped: false }]
                },
                options: { ...baseOpts, plugins: { legend: { display: false }, tooltip: { callbacks: { title: items => `Pukul ${items[0].label}:00`, label: ctx => ` ${ctx.parsed.y} keluar` } } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } }, grid: gridOpts }, x: { ticks: { font: { size: 9 }, maxRotation: 0, autoSkip: true, maxTicksLimit: 12 }, grid: { display: false } } } }
            });

            // Bar Horizontal — Top penghuni
            new Chart(document.getElementById('chartPenghuni'), {
                type: 'bar',
                data: {
                    labels: <?= json_encode($topPenghuniLabels ?: ['Tidak ada data']) ?>,
                    datasets: [{ label: 'Izin Keluar', data: <?= json_encode($topPenghuniValues ?: [0]) ?>, backgroundColor: '#2F7FF0', borderRadius: 6, borderSkipped: false }]
                },
                options: { ...baseOpts, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } }, grid: gridOpts }, y: { ticks: { font: { size: 11 } }, grid: { display: false } } } }
            });

            // Doughnut — Gender
            new Chart(document.getElementById('chartGender'), {
                type: 'doughnut',
                data: {
                    labels: ['Laki-laki', 'Perempuan'],
                    datasets: [{
                        data: [<?= (int) $genderData['L'] ?>, <?= (int) $genderData['P'] ?>],
                        backgroundColor: ['#2F7FF0', '#ec4899'],
                        borderWidth: 2,
                        borderColor: '#fff',
                    }]
                },
                options: {
                    ...baseOpts,
                    cutout: '62%',
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, padding: 14, font: { size: 12 } } },
                        tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed} izin` } }
                    }
                }
            });

            // Bar Horizontal — Top keperluan
            new Chart(document.getElementById('chartKeperluan'), {
                type: 'bar',
                data: {
                    labels: <?= json_encode($topKeperluanLabels ?: ['Tidak ada data']) ?>,
                    datasets: [{ label: 'Jumlah', data: <?= json_encode($topKeperluanValues ?: [0]) ?>, backgroundColor: '#10b981', borderRadius: 6, borderSkipped: false }]
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
