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
                pp.PengambilanPaketID, pp.PetugasID AS PickupPetugasID, pp.Status, pp.WaktuPengambilan,
                pp.Keterangan, pp.FotoPengambilan,
                sp.NamaPetugas AS NamaPetugasPengambilan
         FROM paket pk
         JOIN penghuni ph ON pk.PenghuniID = ph.PenghuniID
         LEFT JOIN kamar k ON ph.KamarID = k.KamarID
         JOIN petugas pt ON pk.PetugasID = pt.PetugasID
         $latestPickupSubquery
         LEFT JOIN petugas sp ON pp.PetugasID = sp.PetugasID
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
$sudahDiambil = count(array_filter($pakets, fn($paket) => ($paket['Status'] ?? 'Belum Diambil') === 'Sudah Diambil'));
$belumDiambil = $totalPaket - $sudahDiambil;
?>

<!DOCTYPE html>
<html lang="en">
<?php require '../../head.php'; ?>

<body class="tw:p-0 tw:m-0 relative tw:flex tw:bg-[#f8fafc] tw:min-h-screen">
    <?php require '../components/sidebar.php'; ?>
    <main class="tw:md:ml-75 tw:grow">
        <div class="tw:pt-20 tw:md:pt-8 tw:px-8 tw:mb-8 tw:flex-1 tw:w-dvw tw:md:w-full">
            <div class="tw:flex tw:flex-col tw:gap-4 tw:md:flex-row tw:md:items-center tw:md:justify-between tw:mb-8">
                <div>
                    <h1 class="tw:font-bold tw:text-4xl tw:text-slate-900 tw:m-0">
                        <?= $role === 'SIGAP' ? 'Kelola Paket' : 'Paket Saya' ?>
                    </h1>
                    <p class="tw:text-slate-500 tw:mt-2 tw:mb-0">
                        <?= $role === 'SIGAP'
                            ? 'Catat data paket masuk dan pantau status pengambilannya.'
                            : 'Lihat paket yang ditujukan kepada Anda dan catat pengambilannya.' ?>
                    </p>
                </div>

                <?php if ($role === 'SIGAP'): ?>
                    <a href="create.php"
                        class="tw:bg-secondary tw:text-white tw:px-4 tw:py-3 tw:rounded-xl tw:hover:bg-accent tw:duration-300 tw:transition-all tw:inline-flex tw:items-center tw:gap-2 tw:no-underline tw:font-medium">
                        <i class="iconsax tw:text-2xl" icon-name="add-square"></i>
                        <span>Tambah Paket</span>
                    </a>
                <?php endif; ?>
            </div>

            <div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-3 tw:gap-6 tw:mb-8">
                <div class="tw:bg-white tw:p-6 tw:rounded-[24px] tw:shadow-sm tw:border tw:border-gray-100">
                    <div class="tw:flex tw:items-center tw:gap-4">
                        <div class="tw:p-4 tw:bg-blue-50 tw:text-blue-600 tw:rounded-[18px]">
                            <i class="iconsax tw:text-3xl" icon-name="box-1"></i>
                        </div>
                        <div>
                            <p class="tw:text-sm tw:font-medium tw:text-slate-500 tw:uppercase tw:tracking-wider tw:m-0">
                                Total Paket
                            </p>
                            <h3 class="tw:text-3xl tw:font-bold tw:text-slate-900 tw:m-0"><?= $totalPaket ?></h3>
                        </div>
                    </div>
                </div>
                <div class="tw:bg-white tw:p-6 tw:rounded-[24px] tw:shadow-sm tw:border tw:border-gray-100">
                    <div class="tw:flex tw:items-center tw:gap-4">
                        <div class="tw:p-4 tw:bg-amber-50 tw:text-amber-600 tw:rounded-[18px]">
                            <i class="iconsax tw:text-3xl" icon-name="box-time"></i>
                        </div>
                        <div>
                            <p class="tw:text-sm tw:font-medium tw:text-slate-500 tw:uppercase tw:tracking-wider tw:m-0">
                                Belum Diambil
                            </p>
                            <h3 class="tw:text-3xl tw:font-bold tw:text-slate-900 tw:m-0"><?= $belumDiambil ?></h3>
                        </div>
                    </div>
                </div>
                <div class="tw:bg-white tw:p-6 tw:rounded-[24px] tw:shadow-sm tw:border tw:border-gray-100">
                    <div class="tw:flex tw:items-center tw:gap-4">
                        <div class="tw:p-4 tw:bg-emerald-50 tw:text-emerald-600 tw:rounded-[18px]">
                            <i class="iconsax tw:text-3xl" icon-name="box-tick"></i>
                        </div>
                        <div>
                            <p class="tw:text-sm tw:font-medium tw:text-slate-500 tw:uppercase tw:tracking-wider tw:m-0">
                                Sudah Diambil
                            </p>
                            <h3 class="tw:text-3xl tw:font-bold tw:text-slate-900 tw:m-0"><?= $sudahDiambil ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tw:bg-white tw:p-6 tw:rounded-[24px] tw:shadow-sm tw:border tw:border-gray-100">
                <div class="doremi-table-wrapper">
                    <table id="paketTable" class="table doremi-table text-center align-middle tw:mb-0 tw:w-full">
                        <thead>
                            <tr>
                                <th scope="col" class="text-center align-middle">ID</th>
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
                                <tr>
                                    <th scope="row"><?= (int) $paket['PaketID'] ?></th>
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
                                        <?php if ($status === 'Sudah Diambil'): ?>
                                            <span class="badge bg-success">Sudah Diambil</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Belum Diambil</span>
                                        <?php endif; ?>

                                        <?php if (!empty($paket['WaktuPengambilan'])): ?>
                                            <div class="tw:text-xs tw:text-slate-500 tw:mt-1">
                                                <?= date('d M Y H:i', strtotime($paket['WaktuPengambilan'])) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="tw:inline-flex tw:flex-wrap tw:justify-center tw:items-center tw:gap-2 tw:text-black">
                                            <?php if ($role === 'SIGAP'): ?>
                                                <?php if (!empty($paket['FotoPengambilan'])): ?>
                                                    <a href="<?= htmlspecialchars(paket_photo_url($paket['FotoPengambilan'])) ?>"
                                                        target="_blank" rel="noopener noreferrer"
                                                        class="tw:text-slate-700 tw:no-underline" title="Lihat Foto Pengambilan">
                                                        <i class="iconsax tw:text-lg" icon-name="gallery"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="edit.php?id=<?= (int) $paket['PaketID'] ?>"
                                                    class="tw:text-slate-700 tw:no-underline" title="Edit Paket">
                                                    <i class="iconsax tw:text-lg" icon-name="edit-2"></i>
                                                </a>
                                                <button type="button" class="tw:bg-transparent tw:border-0 tw:p-0 tw:text-slate-700"
                                                    data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                    data-bs-id="<?= (int) $paket['PaketID'] ?>" title="Hapus Paket">
                                                    <i class="iconsax tw:text-lg" icon-name="trash"></i>
                                                </button>
                                            <?php else: ?>
                                                <a href="pickup.php?id=<?= (int) $paket['PaketID'] ?>"
                                                    class="tw:bg-secondary tw:text-white tw:px-3 tw:py-2 tw:rounded-lg tw:no-underline tw:hover:bg-accent tw:transition-all tw:text-sm">
                                                    <?= empty($paket['PengambilanPaketID']) ? 'Catat Pengambilan' : 'Ubah Status' ?>
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
                    order: [[0, 'desc']],
                    columnDefs: [
                        {
                            targets: 6,
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
