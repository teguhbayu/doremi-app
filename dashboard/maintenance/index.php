<?php
session_start();
require 'helpers.php';
maintenance_require_roles(['PENGURUS', 'PENGHUNI', 'SIGAP', 'SERVANDA', 'MAINTENANCE']);
require '../../db.php';

$role = $_SESSION['userRole'];
$userId = (int)$_SESSION['userId'];
session_write_close(); // Lepas session lock — halaman ini hanya baca session

$whereClause = "";
if ($role !== 'MAINTENANCE') {
    if ($role === 'PENGHUNI') {
        $whereClause = "WHERE m.PenghuniID = $userId";
    } else {
        $whereClause = "WHERE m.PetugasID = $userId AND m.PenghuniID IS NULL";
    }
}

$queryStr = "
    SELECT m.*, 
           p.NamaPenghuni, p.Nim,
           pt.NamaPetugas AS NamaReporterPetugas,
           tech.NamaPetugas AS NamaTeknisi,
           r.NamaRuangan, r.Lantai AS LantaiRuangan,
           i.NamaBarang
    FROM maintenance m
    LEFT JOIN penghuni p ON m.PenghuniID = p.PenghuniID
    LEFT JOIN petugas pt ON m.PetugasID = pt.PetugasID
    LEFT JOIN petugas tech ON m.PetugasID = tech.PetugasID
    LEFT JOIN ruangan r ON m.RuanganID = r.RuanganID
    LEFT JOIN inventaris i ON m.InventarisID = i.InventarisID
    $whereClause
    ORDER BY CASE WHEN m.JenisLaporan = 'Kerusakan Darurat / Berat' THEN 1 
                  WHEN m.JenisLaporan = 'Kerusakan Sedang' THEN 2 
                  ELSE 3 END, m.MaintenanceID DESC
";

$query = mysqli_query($db, $queryStr);
$reports = mysqli_fetch_all($query, MYSQLI_ASSOC);
$totalReports = count($reports);
?>

<!DOCTYPE html>
<html lang="en">
<?php require '../../head.php'; ?>

<body class="dashboard-body tw:p-0 tw:m-0 tw:relative tw:flex tw:bg-[#f8fafc] tw:min-h-screen">
    <?php require '../components/sidebar.php'; ?>
    <main class="dashboard-main tw:md:ml-[20.5rem] tw:grow">
        <div class="dashboard-page tw:pt-24 tw:md:pt-9 tw:px-4 tw:md:px-8 tw:pb-8 tw:w-full">
            <?php require dirname(__DIR__) . '/components/breadcrumb.php'; ?>
            <h1 class="page-title" data-kicker="Kelola Fasilitas"
                data-subtitle="<?= htmlspecialchars($role === 'MAINTENANCE' ? 'Daftar semua laporan asrama yang diurutkan berdasarkan skala prioritas agar teknisi fokus pada kerusakan yang paling mendesak.' : 'Pantau seluruh laporan kerusakan fasilitas yang Anda ajukan beserta progres penanganannya.') ?>">
                Laporan Maintenance
            </h1>

            <div class="page-toolbar" data-note="<?= $totalReports ?> laporan tercatat">
                <a href="create.php" class="page-primary-btn">
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
                                    <td>
                                        <?php if (!empty($r['NamaRuangan'])): ?>
                                            <div>Ruangan: <strong><?= htmlspecialchars($r['NamaRuangan']) ?></strong></div>
                                            <div class="tw:text-xs tw:text-slate-500">Lantai <?= htmlspecialchars($r['LantaiRuangan']) ?></div>
                                        <?php elseif (!empty($r['NamaBarang'])): ?>
                                            <div>Inventaris: <strong><?= htmlspecialchars($r['NamaBarang']) ?></strong></div>
                                        <?php else: ?>
                                            <div class="tw:text-xs tw:text-slate-500">-</div>
                                        <?php endif; ?>
                                    </td>
                                    <!-- PENYESUAIAN WARNA TINGKAT URGENSI SECARA DINAMIS -->
                                    <td>
                                        <?php if ($r['JenisLaporan'] === 'Kerusakan Darurat / Berat'): ?>
                                            <span class="badge bg-danger text-white tw:text-xs">Darurat</span>
                                        <?php elseif ($r['JenisLaporan'] === 'Kerusakan Sedang'): ?>
                                            <span class="badge bg-warning text-dark tw:text-xs">Sedang</span>
                                        <?php else: ?>
                                            <span class="badge bg-success text-white tw:text-xs">Ringan</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d M Y', strtotime($r['TanggalLapor'])) ?></td>
                                    <td>
                                        <span class="badge <?= $statusMeta['class'] ?>"><?= $statusMeta['label'] ?></span>
                                    </td>
                                    <td>
                                        <div class="tw:inline-flex tw:gap-2">
                                            <button type="button" class="detail-action-btn"
                                                    data-bs-toggle="modal" data-bs-target="#detailModal<?= $r['MaintenanceID'] ?>">
                                                <i class="iconsax tw:text-lg" icon-name="document-text-1"></i>
                                                <span>Detail</span>
                                            </button>

                                            <!-- FILTER AKSES: Hanya role MAINTENANCE yang dapat memproses dan menyelesaikan laporan -->
                                            <?php if ($role === 'MAINTENANCE'): ?>
                                                <?php if ($r['StatusMaintenance'] === 'Diajukan'): ?>
                                                    <form action="process.php" method="POST" class="tw:inline">
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

                                            <!-- Tombol Edit/Hapus dinamis hanya untuk pembuat laporan yang sah -->
                                            <?php if ($r['StatusMaintenance'] === 'Diajukan'): ?>
                                                <?php 
                                                    $isOwner = false;
                                                    if ($role === 'PENGHUNI' && (int)$r['PenghuniID'] === $userId) {
                                                        $isOwner = true;
                                                    } elseif ($role !== 'PENGHUNI' && (int)$r['PetugasID'] === $userId && $r['PenghuniID'] === null) {
                                                        $isOwner = true;
                                                    }
                                                ?>
                                                <?php if ($isOwner): ?>
                                                    <a href="edit.php?id=<?= $r['MaintenanceID'] ?>" class="icon-action">
                                                        <i class="iconsax tw:text-lg" icon-name="edit-2"></i>
                                                    </a>
                                                    <button type="button" class="icon-action icon-action--danger"
                                                            data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                            data-bs-id="<?= $r['MaintenanceID'] ?>">
                                                        <i class="iconsax tw:text-lg" icon-name="trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Details Modal -->
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
                                                        <label class="tw:text-xs tw:text-slate-500">Deskripsi Masalah</label>
                                                        <p class="tw:text-slate-700 tw:whitespace-pre-line tw:break-words" style="word-break: break-word; overflow-wrap: break-word;"><?= htmlspecialchars($r['Deskripsi']) ?></p>
                                                    </div>
                                                    <?php if (!empty($r['FotoLaporan'])): ?>
                                                        <div>
                                                            <label class="tw:text-xs tw:text-slate-500 tw:mb-1 tw:block">Foto Masalah</label>
                                                            <img src="<?= $r['FotoLaporan'] ?>" alt="Foto Laporan" class="tw:max-h-60 tw:rounded-lg tw:object-cover tw:border tw:w-full">
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
                                                                <p class="tw:font-semibold tw:text-emerald-950 tw:mb-0"><?= date('d M Y', strtotime($r['TanggalSelesai'])) ?></p>
                                                            </div>
                                                            <div class="tw:mb-3">
                                                                <label class="tw:text-xs tw:text-emerald-600">Keterangan Hasil Kerja</label>
                                                                <p class="tw:text-emerald-950 tw:mb-0 tw:break-words" style="word-break: break-word; overflow-wrap: break-word;"><?= nl2br(htmlspecialchars($r['Keterangan'] ?? '-')) ?></p>
                                                            </div>
                                                            <?php if (!empty($r['FotoMaintenance'])): ?>
                                                                <div>
                                                                    <label class="tw:text-xs tw:text-emerald-600 tw:mb-1 tw:block">Foto Bukti Perbaikan</label>
                                                                    <img src="<?= $r['FotoMaintenance'] ?>" alt="Foto Perbaikan" class="tw:max-h-60 tw:rounded-lg tw:object-cover tw:border tw:border-emerald-100 tw:w-full">
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

                                <!-- Completion Form Modal -->
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

                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Delete Confirmation Modal -->
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
                    <a href="#" id="confirmDelete" class="btn btn-danger tw:rounded-lg">Hapus</a>
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
                    const confirmDelete = deleteModal.querySelector('#confirmDelete');
                    confirmDelete.href = `delete.php?id=${id}`;
                });
            }
        });
    </script>
</body>
</html>