<?php
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: /doremi-app/login.php");
    exit;
}
require '../../db.php';

$query = mysqli_query($db, "SELECT p.*, k.NomorKamar FROM penghuni p LEFT JOIN kamar k ON p.KamarID = k.KamarID WHERE p.IsDeleted = 0;");
$totalPenghuni = mysqli_num_rows($query);
?>


<!DOCTYPE html>
<html lang="en">
<?php require '../../head.php'; ?>

<body class="dashboard-body tw:p-0 tw:m-0 tw:relative tw:flex">
    <?php require '../components/sidebar.php'; ?>
    <main class="tw:md:ml-75 tw:grow">
        <div class="tw:pt-20 tw:md:pt-5 tw:px-5 tw:mb-8 tw:flex-1 tw:w-dvw tw:md:w-full">
            <?php require dirname(__DIR__) . '/components/breadcrumb.php'; ?>
            <h1 class="page-title" data-kicker="Master Penghuni" data-subtitle="Pantau identitas penghuni, penempatan kamar, dan pengelolaan akun dengan tampilan yang lebih modern.">
                Kelola Penghuni
            </h1>

            <div class="page-toolbar" data-note="<?= $totalPenghuni ?> penghuni aktif">
                <a href="create.php"
                    class="tw:inline-flex tw:items-center tw:justify-center tw:gap-2 tw:min-h-12 tw:px-4 tw:py-[0.85rem] tw:rounded-2xl tw:border tw:border-transparent tw:font-extrabold tw:no-underline tw:text-white tw:bg-secondary tw:shadow-md tw:hover:bg-primary tw:transition-all tw:text-sm">
                    <i class="iconsax tw:text-2xl " icon-name="add-square"></i>
                    <span>
                        Tambah Penghuni
                    </span>
                </a>
            </div>

            <div class="table-panel">
                <div class="doremi-table-wrapper">
                <table id="penghuniTable" class="table doremi-table text-center align-middle tw:mb-0 tw:w-full">
                <thead>
                    <tr>
                        <th scope="col" class="text-center align-middle">NIM</th>
                        <th scope="col" class="text-center align-middle">Nama</th>
                        <th scope="col" class="text-center align-middle">Kamar</th>
                        <th scope="col" class="text-center align-middle">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($penghuni = mysqli_fetch_assoc($query)) { ?>
                        <tr>
                            <td><?php echo $penghuni["Nim"]; ?></td>
                            <td class="tw:text-left"><?php echo $penghuni["NamaPenghuni"]; ?></td>
                            <td><?php echo $penghuni["NomorKamar"] ?? 'N/A'; ?></td>
                            <td>
                                <div class="tw:inline-flex tw:justify-center tw:items-center tw:gap-1 tw:text-black">

                                    <a href="edit.php?id=<?php echo $penghuni["PenghuniID"] ?>" class="tw:w-9 tw:h-9 tw:inline-flex tw:items-center tw:justify-center tw:rounded-[12px] tw:bg-[rgba(47,127,240,0.08)] tw:text-primary tw:no-underline tw:hover:bg-[rgba(47,127,240,0.16)] tw:transition-all" title="Edit Penghuni">
                                        <i class="iconsax tw:text-lg" icon-name="edit-2"></i>
                                    </a>
                                    <button type="button" class="tw:w-9 tw:h-9 tw:inline-flex tw:items-center tw:justify-center tw:rounded-[12px] tw:bg-[rgba(188,79,69,0.08)] tw:text-red-600 tw:no-underline tw:hover:bg-[rgba(188,79,69,0.16)] tw:transition-all"
                                        data-bs-toggle="modal" data-bs-target="#deleteModal"
                                        data-bs-id="<?php echo $penghuni["PenghuniID"] ?>" title="Hapus Penghuni">
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
                    Apakah Anda yakin ingin menghapus penghuni ini?
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

        new DataTable('#penghuniTable', {
                autoWidth: false,
                ordering: true,
                searching: true,
                paging: true,
                info: true,
                columnDefs: [
                    {
                        targets: [3, 4],
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
    </script>
</body>

</html>
