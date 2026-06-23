<?php
session_start();
require 'helpers.php';
paket_require_roles(['SIGAP', 'PENGHUNI']);
require '../../db.php';

$role = $_SESSION['userRole'];
$userId = (int) $_SESSION['userId'];
$latestPickupSubquery = "
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

if ($role === 'SIGAP') {
    $query = mysqli_query(
        $db,
        "SELECT pk.*, ph.NamaPenghuni, ph.Nim, k.NomorKamar, pt.NamaPetugas,
                pp.PengambilanPaketID, pp.Status, pp.WaktuPengambilan, pp.Keterangan, pp.FotoPengambilan
         FROM paket pk
         JOIN penghuni ph ON pk.PenghuniID = ph.PenghuniID
         LEFT JOIN kamar k ON ph.KamarID = k.KamarID
         JOIN petugas pt ON pk.PetugasID = pt.PetugasID
         $latestPickupSubquery
         ORDER BY pk.PaketID DESC"
    );
    $pakets = mysqli_fetch_all($query, MYSQLI_ASSOC);
} else {
    $stmt = mysqli_prepare(
        $db,
        "SELECT pk.*, ph.NamaPenghuni, ph.Nim, k.NomorKamar,
                pt.NamaPetugas AS NamaPetugasPaket,
                pp.PengambilanPaketID, pp.Status, pp.WaktuPengambilan,
                pp.Keterangan, pp.FotoPengambilan
         FROM paket pk
         JOIN penghuni ph ON pk.PenghuniID = ph.PenghuniID
         LEFT JOIN kamar k ON ph.KamarID = k.KamarID
         JOIN petugas pt ON pk.PetugasID = pt.PetugasID
         $latestPickupSubquery
         WHERE pk.PenghuniID = ?
         ORDER BY pk.PaketID DESC"
    );
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $pakets = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
}

$totalPaket = count($pakets);
$tertukar = count(array_filter($pakets, fn($paket) => ($paket['Status'] ?? 'Belum Diambil') === 'TERTUKAR'));
$sudahDiambil = count(array_filter($pakets, fn($paket) => ($paket['Status'] ?? 'Belum Diambil') === 'Sudah Diambil'));
$belumDiambil = count(array_filter($pakets, fn($paket) => ($paket['Status'] ?? 'Belum Diambil') === 'Belum Diambil'));
?>

<!DOCTYPE html>
<html lang="en">
<?php require '../../head.php'; ?>

<body class="dashboard-body tw:p-0 tw:m-0 relative tw:flex tw:bg-[#f8fafc] tw:min-h-screen">
    <?php require '../components/sidebar.php'; ?>
    <main class="dashboard-main tw:md:ml-75 tw:grow">
        <div class="dashboard-page tw:pt-20 tw:md:pt-8 tw:px-8 tw:mb-8 tw:flex-1 tw:w-dvw tw:md:w-full">
            <?php require dirname(__DIR__) . '/components/breadcrumb.php'; ?>
            <h1 class="page-title" data-kicker="Distribusi Paket"
                data-subtitle="<?= htmlspecialchars($role === 'SIGAP' ? 'Catat data paket masuk, pantau bukti pengambilan, dan review kasus paket tertukar dalam satu alur kerja yang konsisten.' : 'Lihat seluruh paket yang ditujukan kepada Anda berikut status pengambilan dan bukti pencatatannya.') ?>">
                <?= $role === 'SIGAP' ? 'Kelola Paket' : 'Paket Saya' ?>
            </h1>

            <div class="page-toolbar" data-note="<?= $totalPaket ?> paket tercatat"></div>

            <div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-2 tw:xl:grid-cols-4 tw:gap-6 tw:mb-8">
                <div class="dashboard-stat-card">
                    <div class="tw:flex tw:items-center tw:gap-4">
                        <div class="dashboard-stat-card__icon dashboard-stat-card__icon--primary">
                            <i class="iconsax tw:text-3xl" icon-name="box-1"></i>
                        </div>
                        <div>
                            <span class="dashboard-stat-card__eyebrow">Total Paket</span>
                            <strong class="dashboard-stat-card__value"><?= $totalPaket ?></strong>
                        </div>
                    </div>
                </div>
                <div class="dashboard-stat-card">
                    <div class="tw:flex tw:items-center tw:gap-4">
                        <div class="dashboard-stat-card__icon dashboard-stat-card__icon--warning">
                            <i class="iconsax tw:text-3xl" icon-name="box-time"></i>
                        </div>
                        <div>
                            <span class="dashboard-stat-card__eyebrow">Belum Diambil</span>
                            <strong class="dashboard-stat-card__value"><?= $belumDiambil ?></strong>
                        </div>
                    </div>
                </div>
                <div class="dashboard-stat-card">
                    <div class="tw:flex tw:items-center tw:gap-4">
                        <div class="dashboard-stat-card__icon dashboard-stat-card__icon--success">
                            <i class="iconsax tw:text-3xl" icon-name="box-tick"></i>
                        </div>
                        <div>
                            <span class="dashboard-stat-card__eyebrow">Sudah Diambil</span>
                            <strong class="dashboard-stat-card__value"><?= $sudahDiambil ?></strong>
                        </div>
                    </div>
                </div>
                <div class="dashboard-stat-card">
                    <div class="tw:flex tw:items-center tw:gap-4">
                        <div class="dashboard-stat-card__icon dashboard-stat-card__icon--danger">
                            <i class="iconsax tw:text-3xl" icon-name="danger"></i>
                        </div>
                        <div>
                            <span class="dashboard-stat-card__eyebrow">Tertukar</span>
                            <strong class="dashboard-stat-card__value"><?= $tertukar ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($role === 'SIGAP'): ?>
                <div class="page-summary-actions">
                    <a href="create.php" class="page-primary-btn">
                        <i class="iconsax tw:text-xl" icon-name="add-square"></i>
                        <span>Tambah Paket</span>
                    </a>
                </div>
            <?php endif; ?>

            <div class="table-panel">
                <div class="doremi-table-wrapper">
                    <table id="paketTable" class="table doremi-table text-center align-middle tw:mb-0 tw:w-full">
                        <thead>
                            <tr>
                                <th scope="col" class="text-center align-middle"><?= $role === 'SIGAP' ? 'Penghuni' : 'Paket' ?></th>
                                <th scope="col" class="text-center align-middle">Pengirim</th>
                                <th scope="col" class="text-center align-middle">Kurir</th>
                                <th scope="col" class="text-center align-middle">Waktu Sampai</th>
                                <th scope="col" class="text-center align-middle">Status</th>
                                <th scope="col" class="text-center align-middle">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pakets as $paket): ?>
                                <?php $status = $paket['Status'] ?? 'Belum Diambil'; ?>
                                <?php $statusMeta = paket_status_meta($status); ?>
                                <?php $isPickupLocked = !empty($paket['PengambilanPaketID']) && paket_is_final_status($status); ?>
                                <tr>
                                    <td class="tw:text-start">
                                        <?php if ($role === 'SIGAP'): ?>
                                            <div class="tw:font-semibold tw:text-slate-900">
                                                <?= htmlspecialchars($paket['NamaPenghuni']) ?>
                                            </div>
                                            <div class="tw:text-sm tw:text-slate-500">
                                                <?= htmlspecialchars($paket['Nim']) ?>
                                                <?php if (!empty($paket['NomorKamar'])): ?>
                                                    | Kamar <?= htmlspecialchars($paket['NomorKamar']) ?>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="tw:font-semibold tw:text-slate-900">
                                                Dicatat oleh <?= htmlspecialchars($paket['NamaPetugasPaket']) ?>
                                            </div>
                                            <div class="tw:text-sm tw:text-slate-500">
                                                <?= !empty($paket['NomorKamar']) ? 'Kamar ' . htmlspecialchars($paket['NomorKamar']) : 'Kamar belum terdata' ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($paket['NamaPengirim']) ?></td>
                                    <td><?= htmlspecialchars($paket['Kurir']) ?></td>
                                    <td>
                                        <?= $paket['WaktuSampai'] ? date('d M Y H:i', strtotime($paket['WaktuSampai'])) : '-' ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= htmlspecialchars($statusMeta['class']) ?>">
                                            <?= htmlspecialchars($statusMeta['label']) ?>
                                        </span>

                                        <?php if (!empty($paket['WaktuPengambilan'])): ?>
                                            <div class="tw:text-xs tw:text-slate-500 tw:mt-1">
                                                <?= date('d M Y H:i', strtotime($paket['WaktuPengambilan'])) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="tw:inline-flex tw:flex-wrap tw:justify-center tw:items-center tw:gap-2 tw:text-black">
                                            <?php if ($role === 'SIGAP'): ?>
                                                <a href="review.php?id=<?= (int) $paket['PaketID'] ?>"
                                                    class="detail-action-btn" title="Review Status Pengambilan">
                                                    <i class="iconsax tw:text-lg" icon-name="document-text-1"></i>
                                                    <span>Review</span>
                                                </a>
                                                <?php if (!empty($paket['FotoPengambilan'])): ?>
                                                    <a href="<?= htmlspecialchars(paket_photo_url($paket['FotoPengambilan'])) ?>"
                                                        target="_blank" rel="noopener noreferrer"
                                                        class="icon-action" title="Lihat Foto Pengambilan">
                                                        <i class="iconsax tw:text-lg" icon-name="gallery"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="edit.php?id=<?= (int) $paket['PaketID'] ?>"
                                                    class="icon-action" title="Edit Paket">
                                                    <i class="iconsax tw:text-lg" icon-name="edit-2"></i>
                                                </a>
                                                <button type="button" class="icon-action icon-action--danger"
                                                    data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                    data-bs-id="<?= (int) $paket['PaketID'] ?>" title="Hapus Paket">
                                                    <i class="iconsax tw:text-lg" icon-name="trash"></i>
                                                </button>
                                            <?php else: ?>
                                                <a href="pickup.php?id=<?= (int) $paket['PaketID'] ?>"
                                                    class="tw:bg-secondary tw:text-white tw:px-3 tw:py-2 tw:rounded-lg tw:no-underline tw:hover:bg-accent tw:transition-all tw:text-sm">
                                                    <?= empty($paket['PengambilanPaketID']) ? 'Catat Pengambilan' : ($isPickupLocked ? 'Lihat Catatan' : 'Lengkapi Pengambilan') ?>
                                                </a>
                                                <?php if (!empty($paket['FotoPengambilan'])): ?>
                                                    <a href="<?= htmlspecialchars(paket_photo_url($paket['FotoPengambilan'])) ?>"
                                                        target="_blank" rel="noopener noreferrer"
                                                        class="tw:bg-white tw:text-slate-700 tw:px-3 tw:py-2 tw:rounded-lg tw:border tw:border-slate-200 tw:no-underline tw:hover:bg-slate-50 tw:transition-all tw:text-sm">
                                                        Lihat Foto
                                                    </a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <?php if ($role === 'SIGAP'): ?>
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Paket yang sudah memiliki catatan pengambilan tidak dapat dihapus. Lanjutkan hapus data paket ini?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <a href="#" id="confirmDelete" class="btn btn-danger">Hapus</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php require '../../bootstrap.php'; ?>
    <?php require '../../validation_alert.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            <?php if ($role === 'SIGAP'): ?>
                const deleteModal = document.getElementById('deleteModal');
                if (deleteModal) {
                    deleteModal.addEventListener('show.bs.modal', event => {
                        const button = event.relatedTarget;
                        const id = button.getAttribute('data-bs-id');
                        const confirmDelete = deleteModal.querySelector('#confirmDelete');
                        confirmDelete.href = `delete.php?id=${id}`;
                    });
                }
            <?php endif; ?>

            if (document.getElementById('paketTable')) {
                new DataTable('#paketTable', {
                    autoWidth: false,
                    ordering: true,
                    searching: true,
                    paging: true,
                    info: true,
                    order: [],
                    columnDefs: [
                        {
                            targets: 5,
                            orderable: false
                        },
                        {
                            targets: '_all',
                            className: 'text-center align-middle'
                        }
                    ],
                    layout: {
                        topStart: 'pageLength',
                        topEnd: 'search',
                        bottomStart: 'info',
                        bottomEnd: 'paging'
                    },
                    language: {
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
                    }
                });
            }
        });
    </script>
</body>

</html>
