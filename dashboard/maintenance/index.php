<?php
session_start();
require 'helpers.php';
maintenance_require_roles(['PENGURUS', 'PENGHUNI', 'SIGAP', 'SERVANDA', 'MAINTENANCE']);
require '../../csrf.php';
require '../../db.php';
require_once '../../database/maintenance.php';
require_once '../../utils/format.php';

$role = $_SESSION['userRole'];
$userId = (int)$_SESSION['userId'];
session_write_close(); // Lepas session lock halaman ini hanya baca session

$reports = fetchMaintenanceReportsForRole($db, $role, $userId);
$totalReports = count($reports);
?>

<!DOCTYPE html>
<html lang="en">
<?php require '../../head.php'; ?>

<body class="dashboard-body tw:p-0 tw:m-0 tw:relative tw:flex tw:min-h-screen">
    <?php require '../components/sidebar.php'; ?>
    <main class="tw:md:ml-75 tw:grow">
        <div class="tw:pt-20 tw:md:pt-8 tw:px-8 tw:mb-8 tw:flex-1 tw:w-dvw tw:md:w-full">
            <?php require dirname(__DIR__) . '/components/breadcrumb.php'; ?>
            <h1 class="page-title" data-kicker="Kelola Fasilitas"
                data-subtitle="<?= htmlspecialchars($role === 'MAINTENANCE' ? 'Daftar semua laporan asrama yang diurutkan berdasarkan skala prioritas agar teknisi fokus pada kerusakan yang paling mendesak.' : 'Pantau seluruh laporan kerusakan fasilitas yang Anda ajukan beserta progres penanganannya.') ?>">
                Laporan Maintenance
            </h1>

            <div class="page-toolbar" data-note="<?= $totalReports ?> laporan tercatat">
                <a href="create.php" class="tw:inline-flex tw:items-center tw:justify-center tw:gap-2 tw:min-h-12 tw:px-4 tw:py-[0.85rem] tw:rounded-2xl tw:border tw:border-transparent tw:font-extrabold tw:no-underline tw:text-white tw:bg-secondary tw:shadow-md tw:hover:bg-primary tw:transition-all tw:text-sm">
                    <i class="iconsax tw:text-xl" icon-name="add-square"></i>
                    <span>Buat Laporan</span>
                </a>
            </div>

            <div class="table-panel">
                <div class="doremi-table-wrapper">
                    <table id="maintenanceTable" class="table doremi-table text-center align-middle tw:mb-0 tw:w-full">
                        <thead>
                            <tr>
                                <th scope="col">Pelapor</th>
                                <th scope="col">Lokasi / Target</th>
                                <th scope="col">Tingkat Urgensi</th>
                                <th scope="col">Tanggal Lapor</th>
                                <th scope="col">Status</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reports as $r): ?>
                                <?php 
                                    $statusMeta = maintenance_status_meta($r['StatusMaintenance']); 
                                    $severityMeta = maintenance_severity_meta($r['JenisLaporan']);
                                ?>
                                <tr class="<?= $severityMeta['borderClass'] ?>">
                                    <td>
                                        <?php if (!empty($r['NamaPenghuni'])): ?>
                                            <div class="tw:font-semibold text-primary"><?= htmlspecialchars($r['NamaPenghuni']) ?></div>
                                            <div class="tw:text-xs tw:text-slate-500">Penghuni (<?= htmlspecialchars($r['Nim']) ?>)</div>
                                        <?php elseif (!empty($r['NamaReporterPetugas'])): ?>
                                            <div class="tw:font-semibold text-success"><?= htmlspecialchars($r['NamaReporterPetugas']) ?></div>
                                            <div class="tw:text-xs tw:text-slate-500">Staff</div>
                                        <?php else: ?>
                                            <div class="tw:text-xs tw:text-slate-500">-</div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="truncate-text" title="<?php
                                         if (!empty($r['NamaRuangan'])) {
                                             echo 'Ruangan: ' . htmlspecialchars($r['NamaRuangan']) . ' (Lantai ' . htmlspecialchars($r['LantaiRuangan']) . ')';
                                         } elseif (!empty($r['NamaBarang'])) {
                                             $loc = 'Inventaris: ' . htmlspecialchars($r['NamaBarang']);
                                             if (!empty($r['InvRuanganNama'])) {
                                                 $loc .= ' (Ruangan ' . htmlspecialchars($r['InvRuanganNama']) . ')';
                                             } elseif (!empty($r['InvKamarNomor'])) {
                                                 $loc .= ' (Kamar ' . htmlspecialchars($r['InvKamarNomor']) . ')';
                                             }
                                             echo $loc;
                                         } elseif (!empty($r['InvKamarNomor'])) {
                                             echo 'Kamar: ' . htmlspecialchars($r['InvKamarNomor']);
                                         } else {
                                             echo '-';
                                         }
                                     ?>">
                                        <?php if (!empty($r['NamaRuangan'])): ?>
                                            <div>Ruangan: <strong><?= htmlspecialchars($r['NamaRuangan']) ?></strong></div>
                                            <div class="tw:text-xs tw:text-slate-500">Lantai <?= htmlspecialchars($r['LantaiRuangan']) ?></div>
                                        <?php elseif (!empty($r['NamaBarang'])): ?>
                                            <div>Inventaris: <strong><?= htmlspecialchars($r['NamaBarang']) ?></strong></div>
                                            <?php if (!empty($r['InvKamarNomor'])): ?>
                                                <div class="tw:text-xs tw:text-slate-500">Kamar <?= htmlspecialchars($r['InvKamarNomor']) ?></div>
                                            <?php endif; ?>
                                        <?php elseif (!empty($r['InvKamarNomor'])): ?>
                                            <div>Kamar: <strong><?= htmlspecialchars($r['InvKamarNomor']) ?></strong></div>
                                        <?php else: ?>
                                            <div class="tw:text-xs tw:text-slate-500">-</div>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td>
                                        <?php
                                            $urgencyBadgeClass = match ($r['JenisLaporan']) {
                                                'Kerusakan Darurat / Berat' => 'badge bg-danger text-white tw:text-xs',
                                                'Kerusakan Sedang' => 'badge bg-warning text-dark tw:text-xs',
                                                default => 'badge bg-success text-white tw:text-xs',
                                            };
                                            $urgencyLabel = match ($r['JenisLaporan']) {
                                                'Kerusakan Darurat / Berat' => 'Darurat',
                                                'Kerusakan Sedang' => 'Sedang',
                                                default => 'Ringan',
                                            };
                                            $canEditUrgency = $role === 'MAINTENANCE' && in_array($r['StatusMaintenance'], ['Diajukan', 'Diproses'], true);
                                        ?>
                                        <?php if ($canEditUrgency): ?>
                                            <button type="button" class="<?= $urgencyBadgeClass ?> tw:border-0 tw:cursor-pointer tw:inline-flex tw:items-center tw:gap-1 tw:hover:opacity-80 tw:transition-opacity"
                                                    data-bs-toggle="modal" data-bs-target="#urgencyModal<?= $r['MaintenanceID'] ?>" title="Klik untuk ubah tingkat urgensi">
                                                <?= $urgencyLabel ?>
                                                <i class="iconsax tw:text-[0.7rem]" icon-name="edit-2"></i>
                                            </button>
                                        <?php else: ?>
                                            <span class="<?= $urgencyBadgeClass ?>"><?= $urgencyLabel ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= formatDateTime($r['TanggalLapor'] ?? null, 'd M Y') ?></td>
                                    <td>
                                        <span class="badge <?= $statusMeta['class'] ?>"><?= $statusMeta['label'] ?></span>
                                    </td>
                                    <td>
                                        <div class="tw:inline-flex tw:gap-2">
                                            <button type="button" class="tw:inline-flex tw:items-center tw:justify-center tw:gap-2 tw:min-h-10 tw:px-[0.95rem] tw:py-[0.7rem] tw:rounded-[14px] tw:border tw:border-[rgba(22,60,122,0.18)] tw:bg-accent/40 tw:text-primary tw:font-extrabold tw:no-underline tw:hover:bg-accent/70 tw:transition-all tw:text-sm"
                                                    data-bs-toggle="modal" data-bs-target="#detailModal<?= $r['MaintenanceID'] ?>">
                                                <i class="iconsax tw:text-lg" icon-name="document-text-1"></i>
                                                <span>Detail</span>
                                            </button>

                                            
                                            <?php if ($role === 'MAINTENANCE'): ?>
                                                <?php if ($r['StatusMaintenance'] === 'Diajukan'): ?>
                                                    <form action="process.php" method="POST" class="tw:inline">
                                                        <?php echo csrf_field(); ?>
                                                        <input type="hidden" name="action" value="claim">
                                                        <input type="hidden" name="id" value="<?= $r['MaintenanceID'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-info text-white tw:rounded-lg">Proses</button>
                                                    </form>
                                                <?php elseif ($r['StatusMaintenance'] === 'Diproses'): ?>
                                                    <button type="button" class="btn btn-sm btn-success tw:rounded-lg"
                                                            data-bs-toggle="modal" data-bs-target="#completeModal<?= $r['MaintenanceID'] ?>">
                                                        Selesai
                                                    </button>
                                                <?php endif; ?>
                                            <?php endif; ?>

                                            
                                            <?php if ($r['StatusMaintenance'] === 'Diajukan'): ?>
                                                <?php 
                                                    $isOwner = isMaintenanceReportOwner($r, $role, $userId);
                                                ?>
                                                <?php if ($isOwner): ?>
                                                    <a href="edit.php?id=<?= $r['MaintenanceID'] ?>" class="tw:w-9 tw:h-9 tw:inline-flex tw:items-center tw:justify-center tw:rounded-[12px] tw:bg-[rgba(47,127,240,0.08)] tw:text-primary tw:no-underline tw:hover:bg-[rgba(47,127,240,0.16)] tw:transition-all">
                                                        <i class="iconsax tw:text-lg" icon-name="edit-2"></i>
                                                    </a>
                                                    <button type="button" class="tw:w-9 tw:h-9 tw:inline-flex tw:items-center tw:justify-center tw:rounded-[12px] tw:bg-[rgba(188,79,69,0.08)] tw:text-red-600 tw:no-underline tw:hover:bg-[rgba(188,79,69,0.16)] tw:transition-all"
                                                            data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                            data-bs-id="<?= $r['MaintenanceID'] ?>">
                                                        <i class="iconsax tw:text-lg" icon-name="trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>

                                
                                <div class="modal fade text-start" id="detailModal<?= $r['MaintenanceID'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Detail Laporan</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="tw:flex tw:flex-col tw:gap-3">
                                                     <div>
                                                         <label class="tw:text-xs tw:text-slate-500">Tingkat Kerusakan</label>
                                                         <p class="tw:font-semibold">
                                                             <?php if ($r['JenisLaporan'] === 'Kerusakan Darurat / Berat'): ?>
                                                                 <span class="badge bg-danger text-white">Darurat</span>
                                                             <?php elseif ($r['JenisLaporan'] === 'Kerusakan Sedang'): ?>
                                                                 <span class="badge bg-warning text-dark">Sedang</span>
                                                             <?php else: ?>
                                                                 <span class="badge bg-success text-white">Ringan</span>
                                                             <?php endif; ?>
                                                         </p>
                                                     </div>
                                                     <div>
                                                         <label class="tw:text-xs tw:text-slate-500">Target Lokasi</label>
                                                         <p class="tw:font-semibold tw:mb-0">
                                                             <?php if (!empty($r['NamaRuangan'])): ?>
                                                                 Ruangan: <?= htmlspecialchars($r['NamaRuangan']) ?> (Lantai <?= htmlspecialchars($r['LantaiRuangan']) ?>)
                                                             <?php elseif (!empty($r['NamaBarang'])): ?>
                                                                 Inventaris: <?= htmlspecialchars($r['NamaBarang']) ?>
                                                                 <?php if (!empty($r['InvRuanganNama'])): ?>
                                                                     (Lokasi: Ruangan <?= htmlspecialchars($r['InvRuanganNama']) ?>)
                                                                 <?php elseif (!empty($r['InvKamarNomor'])): ?>
                                                                     (Lokasi: Kamar <?= htmlspecialchars($r['InvKamarNomor']) ?>)
                                                                 <?php endif; ?>
                                                             <?php else: ?>
                                                                 -
                                                             <?php endif; ?>
                                                         </p>
                                                     </div>
                                                     <div>
                                                         <label class="tw:text-xs tw:text-slate-500">Deskripsi Masalah</label>
                                                         <p class="tw:text-slate-700 tw:whitespace-pre-line tw:break-words"><?= htmlspecialchars($r['Deskripsi']) ?></p>
                                                     </div>
                                                     <?php if (!empty($r['HasFotoLaporan'])): ?>
                                                        <div>
                                                            <label class="tw:text-xs tw:text-slate-500 tw:mb-1 tw:block">Foto Masalah</label>
                                                            <div class="tw:relative tw:w-full tw:h-60 tw:bg-slate-100 tw:rounded-lg tw:overflow-hidden tw:border">
                                                                <div class="image-skeleton tw:absolute tw:inset-0 tw:bg-slate-100 tw:animate-pulse tw:flex tw:items-center tw:justify-center">
                                                                    <i class="iconsax tw:text-4xl tw:text-slate-400" icon-name="picture"></i>
                                                                </div>
                                                                <img data-src="../get_photo.php?type=maintenance_laporan&id=<?= $r['MaintenanceID'] ?>" src="" alt="Foto Laporan" class="image-target tw:absolute tw:inset-0 tw:w-full tw:h-full tw:object-cover tw:opacity-0 tw:transition-opacity tw:duration-300">
                                                            </div>
                                                        </div>
                                                     <?php endif; ?>
                                                    
                                                    <?php if ($r['StatusMaintenance'] === 'Selesai'): ?>
                                                        <hr class="tw:my-3">
                                                        <div class="tw:bg-emerald-50/50 tw:p-4 tw:rounded-2xl tw:border tw:border-emerald-100">
                                                            <h6 class="tw:font-bold tw:text-emerald-800 tw:mb-3">Informasi Penyelesaian Unit Maintenance</h6>
                                                            <div class="tw:mb-2">
                                                                <label class="tw:text-xs tw:text-emerald-600">Teknisi Penanggung Jawab</label>
                                                                <p class="tw:font-semibold tw:text-emerald-950 tw:mb-0"><?= htmlspecialchars($r['NamaTeknisi'] ?? 'Tim Teknisi') ?></p>
                                                            </div>
                                                            <div class="tw:mb-2">
                                                                <label class="tw:text-xs tw:text-emerald-600">Tanggal Selesai</label>
                                                                <p class="tw:font-semibold tw:text-emerald-950 tw:mb-0"><?= formatDateTime($r['TanggalSelesai'] ?? null, 'd M Y') ?></p>
                                                            </div>
                                                            <div class="tw:mb-3">
                                                                <label class="tw:text-xs tw:text-emerald-600">Keterangan Hasil Kerja</label>
                                                                <p class="tw:text-emerald-950 tw:mb-0 tw:break-words"><?= nl2br(htmlspecialchars($r['Keterangan'] ?? '-')) ?></p>
                                                            </div>
                                                             <?php if (!empty($r['HasFotoMaintenance'])): ?>
                                                                <div>
                                                                    <label class="tw:text-xs tw:text-emerald-600 tw:mb-1 tw:block">Foto Bukti Perbaikan</label>
                                                                    <div class="tw:relative tw:w-full tw:h-60 tw:bg-slate-100 tw:rounded-lg tw:overflow-hidden tw:border tw:border-emerald-100">
                                                                        <div class="image-skeleton tw:absolute tw:inset-0 tw:bg-slate-100 tw:animate-pulse tw:flex tw:items-center tw:justify-center">
                                                                            <i class="iconsax tw:text-4xl tw:text-slate-400" icon-name="picture"></i>
                                                                        </div>
                                                                        <img data-src="../get_photo.php?type=maintenance_perbaikan&id=<?= $r['MaintenanceID'] ?>" src="" alt="Foto Perbaikan" class="image-target tw:absolute tw:inset-0 tw:w-full tw:h-full tw:object-cover tw:opacity-0 tw:transition-opacity tw:duration-300">
                                                                    </div>
                                                                </div>
                                                             <?php endif; ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <hr class="tw:my-3">
                                                        <p class="tw:text-xs tw:text-slate-400 tw:m-0">Laporan ini saat ini sedang diantrekan atau dalam masa perbaikan.</p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                               
                                <?php if ($role === 'MAINTENANCE' && $r['StatusMaintenance'] === 'Diproses'): ?>
                                    <div class="modal fade text-start" id="completeModal<?= $r['MaintenanceID'] ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Konfirmasi Penyelesaian Masalah</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="process.php" method="POST" enctype="multipart/form-data">
                                                    <div class="modal-body">
                                                        <?php echo csrf_field(); ?>
                                                        <input type="hidden" name="action" value="complete">
                                                        <input type="hidden" name="id" value="<?= $r['MaintenanceID'] ?>">
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Keterangan Penyelesaian (Akan dilihat oleh Pelapor)</label>
                                                            <textarea name="keterangan" class="form-control" rows="3" required placeholder="Jelaskan tindakan perbaikan yang telah dilakukan..."></textarea>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Foto Bukti Hasil Perbaikan (Disimpan sebagai Base64)</label>
                                                            <input type="file" name="fotoMaintenance" class="form-control" accept="image/png,image/jpeg,image/webp" required>
                                                            <div class="form-text">Maksimal resolusi file 2MB (JPG/PNG).</div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary tw:rounded-lg" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-success tw:rounded-lg">Simpan Selesai</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>


                                <?php if ($role === 'MAINTENANCE' && in_array($r['StatusMaintenance'], ['Diajukan', 'Diproses'], true)): ?>
                                    <div class="modal fade text-start" id="urgencyModal<?= $r['MaintenanceID'] ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Ubah Tingkat Urgensi</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="process.php" method="POST">
                                                    <div class="modal-body">
                                                        <?php echo csrf_field(); ?>
                                                        <input type="hidden" name="action" value="update_urgency">
                                                        <input type="hidden" name="id" value="<?= $r['MaintenanceID'] ?>">

                                                        <div class="mb-3">
                                                            <label class="form-label">Skala Prioritas / Tingkat Kerusakan</label>
                                                            <select name="jenisLaporan" class="form-select" required>
                                                                <option value="Kerusakan Ringan" <?= $r['JenisLaporan'] === 'Kerusakan Ringan' ? 'selected' : '' ?>>Kerusakan Ringan (Low Priority)</option>
                                                                <option value="Kerusakan Sedang" <?= $r['JenisLaporan'] === 'Kerusakan Sedang' ? 'selected' : '' ?>>Kerusakan Sedang (Medium Priority)</option>
                                                                <option value="Kerusakan Darurat / Berat" <?= $r['JenisLaporan'] === 'Kerusakan Darurat / Berat' ? 'selected' : '' ?>>Kerusakan Darurat / Berat (EMERGENCY)</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary tw:rounded-lg" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-primary tw:rounded-lg">Simpan Urgensi</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>


    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Apakah Anda yakin ingin menghapus laporan kerusakan ini? Tindakan ini tidak dapat dibatalkan.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary tw:rounded-lg" data-bs-dismiss="modal">Batal</button>
                    <form id="deleteForm" method="POST" action="delete.php" class="tw:inline">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="id" id="deleteId" value="">
                        <button type="submit" class="btn btn-danger tw:rounded-lg">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php require '../../bootstrap.php'; ?>
    <?php require '../../validation_alert.php'; ?>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
    <link href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            new DataTable('#maintenanceTable', {
                autoWidth: false,
                ordering: true,
                searching: true,
                paging: true,
                info: true,
                order: [],
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

            const deleteModal = document.getElementById('deleteModal');
            if (deleteModal) {
                deleteModal.addEventListener('show.bs.modal', event => {
                    const button = event.relatedTarget;
                    const id = button.getAttribute('data-bs-id');
                    document.getElementById('deleteId').value = id;
                });
            }

            // Load images in detail modals dynamically when the modal is shown
            const detailModals = document.querySelectorAll('[id^="detailModal"]');
            detailModals.forEach(modal => {
                modal.addEventListener('show.bs.modal', () => {
                    const imgs = modal.querySelectorAll('img[data-src]');
                    imgs.forEach(img => {
                        if (img.dataset.src && !img.getAttribute('src')) {
                            img.setAttribute('src', img.dataset.src);

                            // Handle skeleton transitions
                            img.addEventListener('load', () => {
                                img.classList.remove('tw:opacity-0');
                                img.classList.add('tw:opacity-100');
                                const skeleton = img.previousElementSibling;
                                if (skeleton && skeleton.classList.contains('image-skeleton')) {
                                    skeleton.classList.add('tw:hidden');
                                }
                            });

                            img.addEventListener('error', () => {
                                const skeleton = img.previousElementSibling;
                                if (skeleton && skeleton.classList.contains('image-skeleton')) {
                                    skeleton.innerHTML = '<span class="tw:text-xs tw:text-slate-400">Gagal memuat gambar</span>';
                                    skeleton.classList.remove('tw:animate-pulse');
                                }
                            });
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>
