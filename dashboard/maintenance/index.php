<?php
session_start();
require 'helpers.php';
maintenance_require_roles(['PENGURUS', 'PENGHUNI', 'SIGAP', 'SERVANDA', 'MAINTENANCE']);
require '../../db.php';

$role = $_SESSION['userRole'];
$userId = (int)$_SESSION['userId'];

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
                    <h1 class="tw:font-bold tw:text-4xl tw:text-slate-900 tw:m-0">Laporan Maintenance</h1>
                    <p class="tw:text-slate-500 tw:mt-2 tw:mb-0">
                        <?= $role === 'MAINTENANCE' ? 'Daftar semua laporan asrama yang diurutkan berdasarkan skala prioritas.' : 'Daftar laporan kerusakan fasilitas yang Anda ajukan.' ?>
                    </p>
                </div>

                <!-- Everybody, including the MAINTENANCE role, can file reports -->
                <a href="create.php"
                    class="tw:bg-secondary tw:text-white tw:px-4 tw:py-3 tw:rounded-xl tw:hover:bg-accent tw:duration-300 tw:transition-all tw:inline-flex tw:items-center tw:gap-2 tw:no-underline tw:font-medium">
                    <i class="iconsax tw:text-2xl" icon-name="add-square"></i>
                    <span>Buat Laporan</span>
                </a>
            </div>

            <div class="tw:bg-white tw:p-6 tw:rounded-[24px] tw:shadow-sm tw:border tw:border-gray-100">
                <div class="tw:overflow-x-auto tw:rounded-lg tw:border tw:border-gray-300">
                    <table id="maintenanceTable" class="table text-center align-middle tw:mb-0 tw:w-full">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">Pelapor</th>
                                <th scope="col">Lokasi / Target</th>
                                <th scope="col">Tingkat Urgensi</th>
                                <th scope="col">Deskripsi</th>
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
                                    <td><?= $r['MaintenanceID'] ?></td>
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
                                    <td>
                                        <span class="badge <?= $severityMeta['class'] ?> tw:text-xs"><?= $severityMeta['label'] ?></span>
                                    </td>
                                    <td>
                                        <span class="tw:text-sm tw:inline-block tw:max-w-xs tw:truncate" title="<?= htmlspecialchars($r['Deskripsi']) ?>">
                                            <?= htmlspecialchars($r['Deskripsi']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('d M Y', strtotime($r['TanggalLapor'])) ?></td>
                                    <td>
                                        <span class="badge <?= $statusMeta['class'] ?>"><?= $statusMeta['label'] ?></span>
                                    </td>
                                    <td>
                                        <div class="tw:inline-flex tw:gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-primary tw:rounded-lg"
                                                    data-bs-toggle="modal" data-bs-target="#detailModal<?= $r['MaintenanceID'] ?>">
                                                Detail
                                            </button>

                                            <?php if ($role === 'MAINTENANCE' || $role === 'PENGURUS'): ?>
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

                                            <!-- Dynamic Edit/Delete display for ticket owners (even if they are MAINTENANCE role) -->
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
                                                    <a href="edit.php?id=<?= $r['MaintenanceID'] ?>" class="btn btn-sm btn-outline-secondary tw:rounded-lg">
                                                        Edit
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger tw:rounded-lg"
                                                            data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                            data-bs-id="<?= $r['MaintenanceID'] ?>">
                                                        Hapus
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
                                                <h5 class="modal-title">Detail Laporan #<?= $r['MaintenanceID'] ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="tw:flex tw:flex-col tw:gap-3">
                                                    <div>
                                                        <label class="tw:text-xs tw:text-slate-500">Tingkat Kerusakan</label>
                                                        <p class="tw:font-semibold">
                                                            <span class="badge <?= $severityMeta['class'] ?>"><?= $severityMeta['label'] ?></span>
                                                        </p>
                                                    </div>
                                                    <div>
                                                        <label class="tw:text-xs tw:text-slate-500">Deskripsi Masalah</label>
                                                        <p class="tw:text-slate-700"><?= nl2br(htmlspecialchars($r['Deskripsi'])) ?></p>
                                                    </div>
                                                    <?php if (!empty($r['FotoLaporan'])): ?>
                                                        <div>
                                                            <label class="tw:text-xs tw:text-slate-500 tw:mb-1 tw:block">Foto Masalah (Base64 String)</label>
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
                                                                <p class="tw:text-emerald-950 tw:mb-0"><?= nl2br(htmlspecialchars($r['Keterangan'] ?? '-')) ?></p>
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
                                <?php if ($r['StatusMaintenance'] === 'Diproses'): ?>
                                    <div class="modal fade text-start" id="completeModal<?= $r['MaintenanceID'] ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Konfirmasi Penyelesaian Masalah #<?= $r['MaintenanceID'] ?></h5>
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
                order: [[0, 'desc']],
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