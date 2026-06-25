<?php
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: /doremi-app/login.php");
    exit;
}
require '../../db.php';

$query = mysqli_query($db, "SELECT i.*, k.NomorKamar, r.NamaRuangan 
                            FROM inventaris i 
                            LEFT JOIN kamar k ON i.KamarID = k.KamarID 
                            LEFT JOIN ruangan r ON i.RuanganID = r.RuanganID 
                            WHERE i.IsDeleted = 0;");
$totalInventaris = mysqli_num_rows($query);
?>


<!DOCTYPE html>
<html lang="en">
<?php require '../../head.php'; ?>

<body class="dashboard-body tw:p-0 tw:m-0 tw:relative tw:flex">
    <?php require '../components/sidebar.php'; ?>
    <main class="dashboard-main tw:md:ml-[20.5rem] tw:grow">
        <div class="dashboard-page tw:pt-24 tw:md:pt-9 tw:px-4 tw:md:px-8 tw:pb-8 tw:w-full">
            <?php require dirname(__DIR__) . '/components/breadcrumb.php'; ?>
            <h1 class="page-title" data-kicker="Master Inventaris" data-subtitle="Pantau aset, jumlah barang, dan lokasinya dalam tampilan yang lebih bersih dan mudah dipindai.">
                Kelola Inventaris
            </h1>
            <div class="page-toolbar" data-note="<?= $totalInventaris ?> item inventaris aktif">

                <a href="create.php"
                    class="page-primary-btn">
                    <i class="iconsax tw:text-2xl " icon-name="add-square"></i>
                    <span>
                        Tambah Inventaris
                    </span>
                </a>
            </div>
            <div class="table-panel">
                <div class="doremi-table-wrapper">
                <table id="inventarisTable" class="table doremi-table text-center align-middle tw:mb-0 tw:w-full">
                <thead>
                    <tr>
                        <th scope="col" class="text-center align-middle" style="width: 35%;">Nama Barang</th>
                        <th scope="col" class="text-center align-middle" style="width: 10%;">Jumlah</th>
                        <th scope="col" class="text-center align-middle" style="width: 35%;">Lokasi</th>
                        <th scope="col" class="text-center align-middle" style="width: 20%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($inventaris = mysqli_fetch_assoc($query)) { ?>
                        <tr>
                            <td><?php echo $inventaris["NamaBarang"]; ?></td>
                            <td><?php echo $inventaris["Jumlah"]; ?></td>
                            <td>
                                <?php 
                                    if ($inventaris["NomorKamar"]) {
                                        echo "Kamar: " . $inventaris["NomorKamar"];
                                    } elseif ($inventaris["NamaRuangan"]) {
                                        echo "Ruangan: " . $inventaris["NamaRuangan"];
                                    } else {
                                        echo "N/A";
                                    }
                                ?>
                            </td>
                            <td>
                                <div class="tw:inline-flex tw:justify-center tw:items-center tw:gap-1 tw:text-black">

                                    <a href="edit.php?id=<?php echo $inventaris["InventarisID"] ?>" class="icon-action" title="Edit Inventaris">
                                        <i class="iconsax tw:text-lg" icon-name="edit-2"></i>
                                    </a>
                                    <button type="button" class="icon-action icon-action--danger"
                                        data-bs-toggle="modal" data-bs-target="#deleteModal"
                                        data-bs-id="<?php echo $inventaris["InventarisID"] ?>" title="Hapus Inventaris">
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
                    Apakah Anda yakin ingin menghapus inventaris ini?
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

        new DataTable('#inventarisTable', {
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
