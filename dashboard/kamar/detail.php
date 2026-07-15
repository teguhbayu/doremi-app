<?php
require_once '../../utils/url.php';
session_start();

if (!isset($_SESSION['userId'])) {
    app_redirect('login.php');
}

require '../../db.php';
require_once '../../database/inventaris.php';
require_once '../../utils/format.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: ' . app_url('dashboard/kamar/'));
    exit;
}

$kamarStmt = mysqli_prepare($db, "SELECT k.KamarID, k.NomorKamar, k.KapasitasPenghuni, k.Lantai, COUNT(p.PenghuniID) AS JumlahPenghuniAktual FROM kamar k LEFT JOIN penghuni p ON p.KamarID = k.KamarID AND p.IsDeleted = 0 WHERE k.KamarID = ? AND k.IsDeleted = 0 GROUP BY k.KamarID, k.NomorKamar, k.KapasitasPenghuni, k.Lantai LIMIT 1");
mysqli_stmt_bind_param($kamarStmt, 'i', $id);
mysqli_stmt_execute($kamarStmt);
$kamarResult = mysqli_stmt_get_result($kamarStmt);
$kamar = mysqli_fetch_assoc($kamarResult);
mysqli_stmt_close($kamarStmt);

if (!$kamar) {
    header('Location: ' . app_url('dashboard/kamar/?status=error&message=Data kamar tidak ditemukan!'));
    exit;
}

$penghuniStmt = mysqli_prepare($db, "SELECT PenghuniID, NamaPenghuni, Nim, JenisKelamin, NoHP, Email FROM penghuni WHERE KamarID = ? AND IsDeleted = 0 ORDER BY NamaPenghuni ASC");
mysqli_stmt_bind_param($penghuniStmt, 'i', $id);
mysqli_stmt_execute($penghuniStmt);
$penghuniResult = mysqli_stmt_get_result($penghuniStmt);
$penghunis = mysqli_fetch_all($penghuniResult, MYSQLI_ASSOC);
mysqli_stmt_close($penghuniStmt);

$inventaris = fetchInventarisByKamar($db, $id);

$sisaKapasitas = max(0, (int) $kamar['KapasitasPenghuni'] - (int) $kamar['JumlahPenghuniAktual']);
?>

<!DOCTYPE html>
<html lang="en">
<?php require '../../head.php'; ?>

<body class="dashboard-body tw:p-0 tw:m-0 tw:relative tw:flex tw:min-h-screen">
    <?php require '../components/sidebar.php'; ?>

    <main class="tw:md:ml-75 tw:grow">
        <div class="tw:pt-20 tw:md:pt-8 tw:px-8 tw:mb-8 tw:flex-1 tw:w-dvw tw:md:w-full">
            <?php require dirname(__DIR__) . '/components/breadcrumb.php'; ?>
            <div class="tw:flex tw:flex-col tw:gap-4 tw:md:flex-row tw:md:items-center tw:md:justify-between tw:mb-8">
                <div>
                    <h1 class="tw:font-bold tw:text-4xl tw:text-slate-900 tw:m-0">
                        Detail Kamar <?= htmlspecialchars($kamar['NomorKamar']) ?>
                    </h1>
                    <p class="tw:text-slate-500 tw:mt-2 tw:mb-0">
                        Informasi kamar dan daftar penghuni yang saat ini terdaftar.
                    </p>
                </div>

                <div class="tw:flex tw:flex-wrap tw:gap-3">
                    <a href="index.php"
                        class="tw:bg-white tw:text-slate-700 tw:px-4 tw:py-3 tw:rounded-xl tw:border tw:border-slate-200 tw:hover:bg-slate-50 tw:transition-all tw:inline-flex tw:items-center tw:gap-2 tw:no-underline">
                        <i class="iconsax tw:text-xl" icon-name="arrow-left"></i>
                        <span>Kembali</span>
                    </a>
                    <a href="edit.php?id=<?= (int) $kamar['KamarID'] ?>"
                        class="tw:bg-secondary tw:text-white tw:px-4 tw:py-3 tw:rounded-xl tw:hover:bg-accent tw:transition-all tw:inline-flex tw:items-center tw:gap-2 tw:no-underline">
                        <i class="iconsax tw:text-xl" icon-name="edit-2"></i>
                        <span>Edit Kamar</span>
                    </a>
                </div>
            </div>

            <div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-3 tw:gap-6 tw:mb-8">
                <div class="tw:bg-white tw:p-6 tw:rounded-[24px] tw:shadow-sm tw:border tw:border-gray-100">
                    <p class="tw:text-sm tw:font-medium tw:text-slate-500 tw:uppercase tw:tracking-wider tw:m-0">
                        Nomor Kamar
                    </p>
                    <h3 class="tw:text-3xl tw:font-bold tw:text-slate-900 tw:mt-3 tw:mb-0">
                        <?= htmlspecialchars($kamar['NomorKamar']) ?>
                    </h3>
                </div>
                <div class="tw:bg-white tw:p-6 tw:rounded-[24px] tw:shadow-sm tw:border tw:border-gray-100">
                    <p class="tw:text-sm tw:font-medium tw:text-slate-500 tw:uppercase tw:tracking-wider tw:m-0">
                        Okupansi
                    </p>
                    <h3 class="tw:text-3xl tw:font-bold tw:text-slate-900 tw:mt-3 tw:mb-0">
                        <?= (int) $kamar['JumlahPenghuniAktual'] ?>/<?= (int) $kamar['KapasitasPenghuni'] ?>
                    </h3>
                    <p class="tw:text-sm tw:text-slate-500 tw:mt-2 tw:mb-0">
                        Sisa kapasitas <?= $sisaKapasitas ?> penghuni
                    </p>
                </div>
                <div class="tw:bg-white tw:p-6 tw:rounded-[24px] tw:shadow-sm tw:border tw:border-gray-100">
                    <p class="tw:text-sm tw:font-medium tw:text-slate-500 tw:uppercase tw:tracking-wider tw:m-0">
                        Lantai
                    </p>
                    <h3 class="tw:text-3xl tw:font-bold tw:text-slate-900 tw:mt-3 tw:mb-0">
                        <?= !empty($kamar['Lantai']) ? 'Lantai ' . htmlspecialchars($kamar['Lantai']) : '-' ?>
                    </h3>
                </div>
            </div>

            <div class="tw:bg-white tw:p-6 tw:rounded-[24px] tw:shadow-sm tw:border tw:border-gray-100">
                <div class="tw:flex tw:flex-col tw:gap-2 tw:md:flex-row tw:md:items-center tw:md:justify-between tw:mb-4">
                    <div>
                        <h5 class="tw:text-xl tw:font-bold tw:text-slate-900 tw:m-0">Daftar Penghuni</h5>
                        <p class="tw:text-slate-500 tw:mt-1 tw:mb-0">
                            Total <?= count($penghunis) ?> penghuni terdaftar pada kamar ini.
                        </p>
                    </div>
                </div>

                <?php if (!$penghunis): ?>
                    <div class="tw:rounded-2xl tw:border tw:border-dashed tw:border-gray-300 tw:p-8 tw:text-center tw:text-gray-500">
                        Belum ada penghuni yang terdaftar pada kamar ini.
                    </div>
                <?php else: ?>
                    <div class="doremi-table-wrapper">
                        <table id="penghuniKamarTable" class="table doremi-table text-center align-middle tw:mb-0 tw:w-full">
                            <thead>
                                <tr>
                                    <th scope="col" class="text-center align-middle">NIM</th>
                                    <th scope="col" class="text-center align-middle">Nama</th>
                                    <th scope="col" class="text-center align-middle">Jenis Kelamin</th>
                                    <th scope="col" class="text-center align-middle">No. HP</th>
                                    <th scope="col" class="text-center align-middle">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($penghunis as $penghuni): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($penghuni['Nim']) ?></td>
                                        <td class="tw:text-start">
                                            <div class="tw:font-semibold"><?= htmlspecialchars($penghuni['NamaPenghuni']) ?></div>
                                            <div class="tw:text-sm tw:text-slate-500"><?= htmlspecialchars($penghuni['Email']) ?></div>
                                        </td>
                                        <td><?= $penghuni['JenisKelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
                                        <td><?= htmlspecialchars($penghuni['NoHP']) ?></td>
                                        <td>
                                <a href="<?= app_url('dashboard/penghuni/edit.php') ?>?id=<?= (int) $penghuni['PenghuniID'] ?>"
                                                class="tw:text-slate-700 tw:no-underline" title="Edit Penghuni">
                                                <i class="iconsax tw:text-lg" icon-name="edit-2"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div class="tw:bg-white tw:p-6 tw:rounded-[24px] tw:shadow-sm tw:border tw:border-gray-100 tw:mt-6">
                <div class="tw:flex tw:flex-col tw:gap-2 tw:md:flex-row tw:md:items-center tw:md:justify-between tw:mb-4">
                    <div>
                        <h5 class="tw:text-xl tw:font-bold tw:text-slate-900 tw:m-0">Daftar Inventaris</h5>
                        <p class="tw:text-slate-500 tw:mt-1 tw:mb-0">
                            Total <?= count($inventaris) ?> barang inventaris tercatat pada kamar ini.
                        </p>
                    </div>
                </div>

                <?php if (!$inventaris): ?>
                    <div class="tw:rounded-2xl tw:border tw:border-dashed tw:border-gray-300 tw:p-8 tw:text-center tw:text-gray-500">
                        Belum ada inventaris yang tercatat pada kamar ini.
                    </div>
                <?php else: ?>
                    <div class="doremi-table-wrapper">
                        <table id="inventarisKamarTable" class="table doremi-table text-center align-middle tw:mb-0 tw:w-full">
                            <thead>
                                <tr>
                                    <th scope="col" class="text-center align-middle">Nama Barang</th>
                                    <th scope="col" class="text-center align-middle">Jumlah</th>
                                    <th scope="col" class="text-center align-middle">Keterangan</th>
                                    <th scope="col" class="text-center align-middle">Diperbarui</th>
                                    <th scope="col" class="text-center align-middle">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($inventaris as $item): ?>
                                    <tr>
                                        <td class="tw:text-start tw:font-semibold"><?= htmlspecialchars($item['NamaBarang']) ?></td>
                                        <td><?= (int) $item['Jumlah'] ?></td>
                                        <td class="tw:text-start truncate-text" title="<?= htmlspecialchars($item['Keterangan'] ?? '') ?>">
                                            <?= !empty($item['Keterangan']) ? htmlspecialchars($item['Keterangan']) : '<span class="tw:text-slate-400">-</span>' ?>
                                        </td>
                                        <td><?= htmlspecialchars(formatDateTime($item['UpdatedAt'] ?? null)) ?></td>
                                        <td>
                                <a href="<?= app_url('dashboard/inventaris/edit.php') ?>?id=<?= (int) $item['InventarisID'] ?>"
                                                class="tw:text-slate-700 tw:no-underline" title="Edit Inventaris">
                                                <i class="iconsax tw:text-lg" icon-name="edit-2"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php require '../../bootstrap.php'; ?>
    <?php require '../../validation_alert.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const doremiTableLang = {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Tidak ada data",
                zeroRecords: "Data tidak ditemukan",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Berikutnya",
                    previous: "Sebelumnya"
                }
            };

            <?php if ($penghunis): ?>
                new DataTable('#penghuniKamarTable', {
                    autoWidth: false,
                    ordering: true,
                    searching: false,
                    paging: true,
                    info: true,
                    columnDefs: [
                        { targets: 5, orderable: false },
                        { targets: '_all', className: 'text-center align-middle' }
                    ],
                    layout: { topStart: 'pageLength', topEnd: null, bottomStart: 'info', bottomEnd: 'paging' },
                    language: doremiTableLang
                });
            <?php endif; ?>

            <?php if ($inventaris): ?>
                new DataTable('#inventarisKamarTable', {
                    autoWidth: false,
                    ordering: true,
                    searching: false,
                    paging: true,
                    info: true,
                    columnDefs: [
                        { targets: 5, orderable: false },
                        { targets: '_all', className: 'text-center align-middle' }
                    ],
                    layout: { topStart: 'pageLength', topEnd: null, bottomStart: 'info', bottomEnd: 'paging' },
                    language: doremiTableLang
                });
            <?php endif; ?>
        });
    </script>
</body>

</html>
