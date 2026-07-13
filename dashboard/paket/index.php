<?php
session_start();
require 'helpers.php';
paket_require_roles(['SIGAP', 'PENGHUNI']);
require '../../db.php';
require_once '../../database/paket.php';
require_once '../../utils/format.php';

$role = $_SESSION['userRole'];
$userId = (int) $_SESSION['userId'];
$pakets = fetchPaketsForRole($db, $role, $userId);
$paketSummary = summarizePaketStatuses($pakets);
$totalPaket = $paketSummary['total'];
$tertukar = $paketSummary['tertukar'];
$sudahDiambil = $paketSummary['sudahDiambil'];
$belumDiambil = $paketSummary['belumDiambil'];
?>

<!DOCTYPE html>
<html lang="en">
<?php require '../../head.php'; ?>

<body class="dashboard-body tw:p-0 tw:m-0 tw:relative tw:flex tw:min-h-screen">
    <?php require '../components/sidebar.php'; ?>
    <main class="tw:md:ml-75 tw:grow">
        <div class="tw:pt-20 tw:md:pt-8 tw:px-8 tw:mb-8 tw:flex-1 tw:w-dvw tw:md:w-full">
            <?php require dirname(__DIR__) . '/components/breadcrumb.php'; ?>
            <h1 class="page-title" data-kicker="Distribusi Paket"
                data-subtitle="<?= htmlspecialchars($role === 'SIGAP' ? 'Catat data paket masuk, pantau bukti pengambilan, dan review kasus paket tertukar dalam satu alur kerja yang konsisten.' : 'Lihat seluruh paket yang ditujukan kepada Anda berikut status pengambilan dan bukti pencatatannya.') ?>">
                <?= $role === 'SIGAP' ? 'Kelola Paket' : 'Paket Saya' ?>
            </h1>

            <div class="page-toolbar" data-note="<?= $totalPaket ?> paket tercatat"></div>

            <div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-2 tw:xl:grid-cols-4 tw:gap-6 tw:mb-8">
                <div class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.85)] tw:shadow-sm">
                    <div class="tw:flex tw:items-center tw:gap-4">
                        <div class="tw:w-[3.4rem] tw:h-[3.4rem] tw:inline-flex tw:items-center tw:justify-center tw:rounded-[20px] tw:flex-shrink-0 tw:text-primary tw:bg-accent/80">
                            <i class="iconsax tw:text-3xl" icon-name="box"></i>
                        </div>
                        <div>
                            <span class="tw:block tw:text-slate-500 tw:text-[0.72rem] tw:font-extrabold tw:tracking-[0.08em] tw:uppercase">Total Paket</span>
                            <strong class="tw:block tw:mt-[0.3rem] tw:text-[1.9rem] tw:leading-none tw:text-slate-900 tw:font-bold"><?= $totalPaket ?></strong>
                        </div>
                    </div>
                </div>
                <div class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.85)] tw:shadow-sm">
                    <div class="tw:flex tw:items-center tw:gap-4">
                        <div class="tw:w-[3.4rem] tw:h-[3.4rem] tw:inline-flex tw:items-center tw:justify-center tw:rounded-[20px] tw:flex-shrink-0 tw:text-amber-700 tw:bg-[rgba(250,236,207,0.82)]">
                            <i class="iconsax tw:text-3xl" icon-name="box-time"></i>
                        </div>
                        <div>
                            <span class="tw:block tw:text-slate-500 tw:text-[0.72rem] tw:font-extrabold tw:tracking-[0.08em] tw:uppercase">Belum Diambil</span>
                            <strong class="tw:block tw:mt-[0.3rem] tw:text-[1.9rem] tw:leading-none tw:text-slate-900 tw:font-bold"><?= $belumDiambil ?></strong>
                        </div>
                    </div>
                </div>
                <div class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.85)] tw:shadow-sm">
                    <div class="tw:flex tw:items-center tw:gap-4">
                        <div class="tw:w-[3.4rem] tw:h-[3.4rem] tw:inline-flex tw:items-center tw:justify-center tw:rounded-[20px] tw:flex-shrink-0 tw:text-emerald-800 tw:bg-[rgba(220,244,239,0.82)]">
                            <i class="iconsax tw:text-3xl" icon-name="box-tick"></i>
                        </div>
                        <div>
                            <span class="tw:block tw:text-slate-500 tw:text-[0.72rem] tw:font-extrabold tw:tracking-[0.08em] tw:uppercase">Sudah Diambil</span>
                            <strong class="tw:block tw:mt-[0.3rem] tw:text-[1.9rem] tw:leading-none tw:text-slate-900 tw:font-bold"><?= $sudahDiambil ?></strong>
                        </div>
                    </div>
                </div>
                <div class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.85)] tw:shadow-sm">
                    <div class="tw:flex tw:items-center tw:gap-4">
                        <div class="tw:w-[3.4rem] tw:h-[3.4rem] tw:inline-flex tw:items-center tw:justify-center tw:rounded-[20px] tw:flex-shrink-0 tw:text-red-700 tw:bg-[rgba(245,221,218,0.82)]">
                            <i class="iconsax tw:text-3xl" icon-name="warning-triangle"></i>
                        </div>
                        <div>
                            <span class="tw:block tw:text-slate-500 tw:text-[0.72rem] tw:font-extrabold tw:tracking-[0.08em] tw:uppercase">Tertukar</span>
                            <strong class="tw:block tw:mt-[0.3rem] tw:text-[1.9rem] tw:leading-none tw:text-slate-900 tw:font-bold"><?= $tertukar ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($role === 'SIGAP'): ?>
                <div class="tw:inline-flex tw:items-center tw:gap-3 tw:flex-wrap tw:mt-4 tw:mb-6">
                    <a href="create.php" class="tw:inline-flex tw:items-center tw:justify-center tw:gap-2 tw:min-h-12 tw:px-4 tw:py-[0.85rem] tw:rounded-2xl tw:border tw:border-transparent tw:font-extrabold tw:no-underline tw:text-white tw:bg-secondary tw:shadow-md tw:hover:bg-primary tw:transition-all tw:text-sm">
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
                                <th scope="col" class="text-center align-middle">Tipe</th>
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
                                    <td>
                                        <span class="badge <?= htmlspecialchars(paket_type_badge_class($paket['JenisPaket'] ?? null)) ?>">
                                            <?= htmlspecialchars(paket_type_label($paket['JenisPaket'] ?? null)) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($paket['NamaPengirim']) ?></td>
                                    <td><?= htmlspecialchars($paket['Kurir']) ?></td>
                                    <td>
                                        <?= formatDateTime($paket['WaktuSampai'] ?? null) ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= htmlspecialchars($statusMeta['class']) ?>">
                                            <?= htmlspecialchars($statusMeta['label']) ?>
                                        </span>

                                        <?php if (!empty($paket['WaktuPengambilan'])): ?>
                                            <div class="tw:text-xs tw:text-slate-500 tw:mt-1">
                                                <?= formatDateTime($paket['WaktuPengambilan']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="tw:inline-flex tw:flex-wrap tw:justify-center tw:items-center tw:gap-2 tw:text-black">
                                            <?php if ($role === 'SIGAP'): ?>
                                                <a href="review.php?id=<?= (int) $paket['PaketID'] ?>"
                                                    class="tw:inline-flex tw:items-center tw:justify-center tw:gap-2 tw:min-h-10 tw:px-[0.95rem] tw:py-[0.7rem] tw:rounded-[14px] tw:border tw:border-[rgba(22,60,122,0.18)] tw:bg-accent/40 tw:text-primary tw:font-extrabold tw:no-underline tw:hover:bg-accent/70 tw:transition-all tw:text-sm" title="Review Status Pengambilan">
                                                    <i class="iconsax tw:text-lg" icon-name="document-text-1"></i>
                                                    <span>Review</span>
                                                </a>
                                                <a href="edit.php?id=<?= (int) $paket['PaketID'] ?>"
                                                    class="tw:w-9 tw:h-9 tw:inline-flex tw:items-center tw:justify-center tw:rounded-[12px] tw:bg-[rgba(47,127,240,0.08)] tw:text-primary tw:no-underline tw:hover:bg-[rgba(47,127,240,0.16)] tw:transition-all" title="Edit Paket">
                                                    <i class="iconsax tw:text-lg" icon-name="edit-2"></i>
                                                </a>
                                                <button type="button" class="tw:w-9 tw:h-9 tw:inline-flex tw:items-center tw:justify-center tw:rounded-[12px] tw:bg-[rgba(188,79,69,0.08)] tw:text-red-600 tw:no-underline tw:hover:bg-[rgba(188,79,69,0.16)] tw:transition-all"
                                                    data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                    data-bs-id="<?= (int) $paket['PaketID'] ?>" title="Hapus Paket">
                                                    <i class="iconsax tw:text-lg" icon-name="trash"></i>
                                                </button>
                                            <?php else: ?>
                                                <a href="pickup.php?id=<?= (int) $paket['PaketID'] ?>"
                                                    class="tw:bg-secondary tw:text-white tw:px-3 tw:py-2 tw:rounded-lg tw:no-underline tw:hover:bg-accent tw:transition-all tw:text-sm">
                                                    <?= empty($paket['PengambilanPaketID']) ? 'Catat Pengambilan' : ($isPickupLocked ? 'Lihat Catatan' : 'Lengkapi Pengambilan') ?>
                                                </a>
                                                <?php if (!empty($paket['HasFotoPengambilan'])): ?>
                                                    <a href="../get_photo.php?type=paket_pengambilan&id=<?= (int) $paket['PaketID'] ?>"
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
