<?php
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: /doremi-app/login.php");
    exit;
}

require '../../db.php';

$query = mysqli_query($db, "SELECT * FROM kamar WHERE IsDeleted = 0 ORDER BY NomorKamar ASC;");
?>

<!DOCTYPE html>
<html lang="en">
<?php require '../../head.php'; ?>

<body class="tw:p-0 tw:m-0 relative tw:flex">
    <?php require '../components/sidebar.php'; ?>

    <main class="tw:md:ml-75 tw:grow">
        <div class="tw:pt-20 tw:md:pt-5 tw:px-5 tw:mb-8 tw:flex-1 tw:w-dvw tw:md:w-full">
            <h1 class="tw:font-bold tw:mb-5 tw:text-4xl tw:text-black">
                Kelola Kamar
            </h1>

            <div class="tw:w-full tw:flex tw:justify-end">
                <a href="create.php"
                    class="tw:bg-secondary tw:text-white tw:px-3 tw:py-2 tw:rounded-lg tw:hover:bg-accent tw:duration-300 tw:transition-all tw:inline-flex tw:items-center tw:gap-2">
                    <i class="iconsax tw:text-2xl" icon-name="add-square"></i>
                    <span>Tambah</span>
                </a>
            </div>

            <div class="tw:mt-3 tw:overflow-x-auto tw:rounded-lg tw:border tw:border-gray-300">
                <table id="kamarTable" class="table doremi-table text-center align-middle tw:mb-0">
                    <thead>
                        <tr>
                            <th scope="col" class="text-center align-middle" style="width: 10%;">No</th>
                            <th scope="col" class="text-center align-middle" style="width: 25%;">Nomor Kamar</th>
                            <th scope="col" class="text-center align-middle" style="width: 25%;">Jumlah Penghuni</th>
                            <th scope="col" class="text-center align-middle" style="width: 20%;">Lantai</th>
                            <th scope="col" class="text-center align-middle" style="width: 20%;">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $nomorUrut = 1; ?>
                        <?php while ($kamar = mysqli_fetch_assoc($query)) { ?>
                            <tr>
                                <th scope="row"><?php echo $nomorUrut++; ?></th>
                                <td><?php echo $kamar["NomorKamar"]; ?></td>
                                <td><?php echo $kamar["KapasitasPenghuni"]; ?> Orang</td>
                                <td>Lantai <?php echo $kamar["Lantai"]; ?></td>
                                <td>
                                    <div class="tw:inline-flex tw:justify-center tw:items-center tw:gap-1 tw:text-black">
                                        <a href="edit.php?id=<?php echo $kamar["KamarID"]; ?>">
                                            <i class="iconsax tw:text-lg" icon-name="edit-2"></i>
                                        </a>

                                        <button type="button" class="tw:bg-transparent tw:border-0 tw:p-0"
                                            data-bs-toggle="modal" data-bs-target="#deleteModal"
                                            data-bs-id="<?php echo $kamar["KamarID"]; ?>">
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

    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    Apakah Anda yakin ingin menghapus kamar ini?
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <a href="#" id="confirmDelete" class="btn btn-danger">
                        Hapus
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php require '../../bootstrap.php'; ?>
    <?php require '../../validation_alert.php'; ?>

    <script>
        const deleteModal = document.getElementById('deleteModal');

        if (deleteModal) {
            deleteModal.addEventListener('show.bs.modal', event => {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-bs-id');
                const confirmDelete = deleteModal.querySelector('#confirmDelete');

                confirmDelete.href = `delete.php?id=${id}`;
            });
        }

        document.addEventListener('DOMContentLoaded', () => {

            new DataTable('#kamarTable', {
                autoWidth: false,
                ordering: true,
                searching: true,
                paging: true,
                info: true,
                columnDefs: [
                    {
                        targets: [0, 4],
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