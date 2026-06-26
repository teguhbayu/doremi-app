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
    <main class="tw:md:ml-75 tw:grow">
        <div class="tw:pt-20 tw:md:pt-5 tw:px-5 tw:mb-8 tw:flex-1 tw:w-dvw tw:md:w-full">
            <?php require dirname(__DIR__) . '/components/breadcrumb.php'; ?>
            <h1 class="page-title" data-kicker="Master Inventaris" data-subtitle="Pantau aset, jumlah barang, dan lokasinya dalam tampilan yang lebih bersih dan mudah dipindai.">
                Kelola Inventaris
            </h1>
            <div class="page-toolbar" data-note="<?= $totalInventaris ?> item inventaris aktif">

                <a href="create.php"
                    class="tw:inline-flex tw:items-center tw:justify-center tw:gap-2 tw:min-h-12 tw:px-4 tw:py-[0.85rem] tw:rounded-2xl tw:border tw:border-transparent tw:font-extrabold tw:no-underline tw:text-white tw:bg-secondary tw:shadow-md tw:hover:bg-primary tw:transition-all tw:text-sm">
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
                        <th scope="col" class="text-center align-middle">Nama Barang</th>
                        <th scope="col" class="text-center align-middle">Jumlah</th>
                        <th scope="col" class="text-center align-middle">Lokasi</th>
                        <th scope="col" class="text-center align-middle">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($inventaris = mysqli_fetch_assoc($query)) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($inventaris["NamaBarang"]); ?></td>
                            <td><?php echo htmlspecialchars($inventaris["Jumlah"]); ?></td>
                            <td>
                                <?php 
                                    $lokasiStr = "N/A";
                                    if ($inventaris["NomorKamar"]) {
                                        $lokasiStr = "Kamar: " . $inventaris["NomorKamar"];
                                    } elseif ($inventaris["NamaRuangan"]) {
                                        $lokasiStr = "Ruangan: " . $inventaris["NamaRuangan"];
                                    }
                                    echo htmlspecialchars($lokasiStr);
                                ?>
                            </td>
                            <td>
                                <div class="tw:inline-flex tw:justify-center tw:items-center tw:gap-2 tw:text-black">
                                    <button type="button" class="tw:inline-flex tw:items-center tw:justify-center tw:gap-2 tw:min-h-10 tw:px-[0.95rem] tw:py-[0.7rem] tw:rounded-[14px] tw:border tw:border-[rgba(22,60,122,0.18)] tw:bg-accent/40 tw:text-primary tw:font-extrabold tw:no-underline tw:hover:bg-accent/70 tw:transition-all tw:text-sm"
                                        data-bs-toggle="modal" data-bs-target="#detailModal"
                                        data-bs-nama="<?php echo htmlspecialchars($inventaris["NamaBarang"]); ?>"
                                        data-bs-jumlah="<?php echo htmlspecialchars($inventaris["Jumlah"]); ?>"
                                        data-bs-lokasi="<?php echo htmlspecialchars($lokasiStr); ?>"
                                        data-bs-keterangan="<?php echo htmlspecialchars($inventaris["Keterangan"]); ?>"
                                        title="Detail Inventaris">
                                        <i class="iconsax tw:text-lg" icon-name="document-text-1"></i>
                                        <span>Detail</span>
                                    </button>
                                    <a href="edit.php?id=<?php echo $inventaris["InventarisID"] ?>" class="tw:w-9 tw:h-9 tw:inline-flex tw:items-center tw:justify-center tw:rounded-[12px] tw:bg-[rgba(47,127,240,0.08)] tw:text-primary tw:no-underline tw:hover:bg-[rgba(47,127,240,0.16)] tw:transition-all" title="Edit Inventaris">
                                        <i class="iconsax tw:text-lg" icon-name="edit-2"></i>
                                    </a>
                                    <button type="button" class="tw:w-9 tw:h-9 tw:inline-flex tw:items-center tw:justify-center tw:rounded-[12px] tw:bg-[rgba(188,79,69,0.08)] tw:text-red-600 tw:no-underline tw:hover:bg-[rgba(188,79,69,0.16)] tw:transition-all"
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

    <!-- Detail Modal -->
    <div class="modal fade text-start" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Inventaris</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="tw:flex tw:flex-col tw:gap-3">
                        <div>
                            <label class="tw:text-xs tw:text-slate-500">Nama Barang</label>
                            <p id="detailNama" class="tw:font-semibold tw:mb-0"></p>
                        </div>
                        <div>
                            <label class="tw:text-xs tw:text-slate-500">Jumlah</label>
                            <p id="detailJumlah" class="tw:font-semibold tw:mb-0"></p>
                        </div>
                        <div>
                            <label class="tw:text-xs tw:text-slate-500">Lokasi</label>
                            <p id="detailLokasi" class="tw:font-semibold tw:mb-0"></p>
                        </div>
                        <div>
                            <label class="tw:text-xs tw:text-slate-500">Keterangan</label>
                            <p id="detailKeterangan" class="tw:text-slate-700 tw:whitespace-pre-line tw:break-words tw:mb-0"></p>
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
                const jumlah = button.getAttribute('data-bs-jumlah')
                const lokasi = button.getAttribute('data-bs-lokasi')
                const keterangan = button.getAttribute('data-bs-keterangan') || '-'

                detailModal.querySelector('#detailNama').textContent = nama
                detailModal.querySelector('#detailJumlah').textContent = jumlah + " unit"
                detailModal.querySelector('#detailLokasi').textContent = lokasi
                detailModal.querySelector('#detailKeterangan').textContent = keterangan
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
    </script>
</body>

</html>
