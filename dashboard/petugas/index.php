<?php
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: /doremi-app/login.php");
    exit;
}
require '../../db.php';

$query = mysqli_query($db, "SELECT * FROM petugas WHERE IsDeleted = 0;");
$totalPetugas = mysqli_num_rows($query);
?>


<!DOCTYPE html>
<html lang="en">
<?php require '../../head.php'; ?>

<body class="dashboard-body tw:p-0 tw:m-0 relative tw:flex">
    <?php require '../components/sidebar.php'; ?>
    <main class="dashboard-main tw:md:ml-75 tw:grow">
        <div class="dashboard-page tw:pt-20 tw:md:pt-5 tw:px-5 tw:mb-8 tw:flex-1 tw:w-dvw tw:md:w-full">
            <?php require dirname(__DIR__) . '/components/breadcrumb.php'; ?>
            <h1 class="page-title" data-kicker="Master Petugas" data-subtitle="Kelola akun petugas, peran kerja, dan akses operasional dalam satu modul yang lebih rapi.">
                Kelola Petugas
            </h1>
            <div class="page-toolbar" data-note="<?= $totalPetugas ?> petugas aktif">

                <a href="create.php"
                    class="page-primary-btn">
                    <i class="iconsax tw:text-2xl " icon-name="add-square"></i>
                    <span>
                        Tambah Petugas
                    </span>
                </a>
            </div>
            
            <div class="table-panel">
                <div class="doremi-table-wrapper">
                <table id="petugasTable" class="table doremi-table text-center align-middle tw:mb-0 tw:w-full">
                <thead>
                    <tr>
                        <th scope="col" class="text-center align-left" style="width: 50%;">Nama</th>
                        <th scope="col" class="text-center align-middle" style="width: 30%;">Jabatan</th>
                        <th scope="col" class="text-center align-middle" style="width: 20%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($petugas = mysqli_fetch_assoc($query)) { ?>
                        <tr>
                            <td class="tw:text-left"><?php echo $petugas["NamaPetugas"]; ?></td>
                            <td><?php echo $petugas["Jabatan"]; ?></td>
                            <td>
                                <div class="tw:inline-flex tw:justify-center tw:items-center tw:gap-1 tw:text-black">

                                    <a href="edit.php?id=<?php echo $petugas["PetugasID"] ?>" class="icon-action" title="Edit Petugas">
                                        <i class="iconsax tw:text-lg" icon-name="edit-2"></i>
                                    </a>
                                    <button type="button" class="icon-action icon-action--danger"
                                        data-bs-toggle="modal" data-bs-target="#deleteModal"
                                        data-bs-id="<?php echo $petugas["PetugasID"] ?>" title="Hapus Petugas">
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
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Apakah Anda yakin ingin menghapus petugas ini?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <a href="#" id="confirmDelete" class="btn btn-danger">Hapus</a>
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

         document.addEventListener('DOMContentLoaded', () => {

            new DataTable('#petugasTable', {
                autoWidth: false,
                ordering: true,
                searching: true,
                paging: true,
                info: true,
                columnDefs: [
                    {
                        targets: 3,
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
