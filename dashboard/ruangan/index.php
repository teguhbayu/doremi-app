<?php
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: /doremi-app/login.php");
    exit;
}
require '../../db.php';

$query = mysqli_query($db, "SELECT * FROM ruangan WHERE IsDeleted = 0;");
$totalRuangan = mysqli_num_rows($query);
?>


<!DOCTYPE html>
<html lang="en">
<?php require '../../head.php'; ?>

<body class="dashboard-body tw:p-0 tw:m-0 relative tw:flex">
    <?php require '../components/sidebar.php'; ?>
    <main class="dashboard-main tw:md:ml-75 tw:grow">
        <div class="dashboard-page tw:pt-20 tw:md:pt-5 tw:px-5 tw:mb-8 tw:flex-1 tw:w-dvw tw:md:w-full">
            <?php require dirname(__DIR__) . '/components/breadcrumb.php'; ?>
            <h1 class="page-title" data-kicker="Master Ruangan" data-subtitle="Susun area bersama, lantai, dan kategori ruangan agar operasional asrama lebih mudah dipantau.">
                Kelola Ruangan
            </h1>
            <div class="page-toolbar" data-note="<?= $totalRuangan ?> ruangan aktif">

                <a href="create.php"
                    class="page-primary-btn">
                    <i class="iconsax tw:text-2xl " icon-name="add-square"></i>
                    <span>
                        Tambah Ruangan
                    </span>
                </a>
            </div>
            <div class="table-panel">
                <div class="doremi-table-wrapper">
                <table id="ruanganTable" class="table doremi-table text-center align-middle tw:mb-0 tw:w-full">
                <thead>
                    <tr>
                        <th scope="col" class="text-center align-middle" style="width: 40%;">Nama Ruangan</th>
                        <th scope="col" class="text-center align-middle" style="width: 25%;">Jenis</th>
                        <th scope="col" class="text-center align-middle" style="width: 15%;">Lantai</th>
                        <th scope="col" class="text-center align-middle" style="width: 20%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($ruangan = mysqli_fetch_assoc($query)) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($ruangan["NamaRuangan"]); ?></td>
                            <td><?php echo htmlspecialchars($ruangan["JenisRuangan"]); ?></td>
                            <td>Lantai <?php echo htmlspecialchars($ruangan["Lantai"]); ?></td>
                            <td>
                                <div class="tw:inline-flex tw:justify-center tw:items-center tw:gap-2 tw:text-black">
                                    <button type="button" class="detail-action-btn"
                                        data-bs-toggle="modal" data-bs-target="#detailModal"
                                        data-bs-nama="<?php echo htmlspecialchars($ruangan["NamaRuangan"]); ?>"
                                        data-bs-jenis="<?php echo htmlspecialchars($ruangan["JenisRuangan"]); ?>"
                                        data-bs-lantai="Lantai <?php echo htmlspecialchars($ruangan["Lantai"]); ?>"
                                        data-bs-keterangan="<?php echo htmlspecialchars($ruangan["Keterangan"]); ?>"
                                        title="Detail Ruangan">
                                        <i class="iconsax tw:text-lg" icon-name="document-text-1"></i>
                                        <span>Detail</span>
                                    </button>
                                    <a href="edit.php?id=<?php echo $ruangan["RuanganID"] ?>" class="icon-action" title="Edit Ruangan">
                                        <i class="iconsax tw:text-lg" icon-name="edit-2"></i>
                                    </a>
                                    <button type="button" class="icon-action icon-action--danger"
                                        data-bs-toggle="modal" data-bs-target="#deleteModal"
                                        data-bs-id="<?php echo $ruangan["RuanganID"] ?>" title="Hapus Ruangan">
                                        <i class="iconsax tw:text-lg" icon-name="trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
            </div>
     </main>
    <!-- Modal -->
    <!-- Modal Hapus -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Apakah Anda yakin ingin menghapus ruangan ini?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <a href="#" id="confirmDelete" class="btn btn-danger">Hapus</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal fade text-start" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Ruangan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="tw:flex tw:flex-col tw:gap-3">
                        <div>
                            <label class="tw:text-xs tw:text-slate-500">Nama Ruangan</label>
                            <p id="detailNama" class="tw:font-semibold tw:mb-0"></p>
                        </div>
                        <div>
                            <label class="tw:text-xs tw:text-slate-500">Jenis Ruangan</label>
                            <p id="detailJenis" class="tw:font-semibold tw:mb-0"></p>
                        </div>
                        <div>
                            <label class="tw:text-xs tw:text-slate-500">Lantai</label>
                            <p id="detailLantai" class="tw:font-semibold tw:mb-0"></p>
                        </div>
                        <div>
                            <label class="tw:text-xs tw:text-slate-500">Keterangan</label>
                            <p id="detailKeterangan" class="tw:text-slate-700 tw:whitespace-pre-line tw:break-words tw:mb-0" style="word-break: break-word; overflow-wrap: break-word;"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require '../../bootstrap.php'; ?>
    <?php require '../../validation_alert.php'; ?>

    <script>
        const deleteModal = document.getElementById('deleteModal')
        if (deleteModal) {
            deleteModal.addEventListener('show.bs.modal', event => {
                const button = event.relatedTarget
                const id = button.getAttribute('data-bs-id')
                const confirmDelete = deleteModal.querySelector('#confirmDelete')
                confirmDelete.href = `delete.php?id=${id}`
            })
        }

        const detailModal = document.getElementById('detailModal')
        if (detailModal) {
            detailModal.addEventListener('show.bs.modal', event => {
                const button = event.relatedTarget
                const nama = button.getAttribute('data-bs-nama')
                const jenis = button.getAttribute('data-bs-jenis')
                const lantai = button.getAttribute('data-bs-lantai')
                const keterangan = button.getAttribute('data-bs-keterangan') || '-'

                detailModal.querySelector('#detailNama').textContent = nama
                detailModal.querySelector('#detailJenis').textContent = jenis
                detailModal.querySelector('#detailLantai').textContent = lantai
                detailModal.querySelector('#detailKeterangan').textContent = keterangan
            })
        }

        document.addEventListener('DOMContentLoaded', () => {
            new DataTable('#ruanganTable', {
                autoWidth: false,
                ordering: true,
                searching: true,
                paging: true,
                info: true,
                columnDefs: [
                    {
                        targets: [3],
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
        });
    </script>

</body>

</html>
