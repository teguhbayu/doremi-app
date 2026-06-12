<?php
session_start();
if (!isset($_SESSION['userId']) || $_SESSION['userRole'] !== 'SIGAP') {
    header("Location: /doremi-app/dashboard/");
    exit;
}

require '../../db.php';

$logQuery = mysqli_query($db, "SELECT io.*, pe.NamaPenghuni, pe.Nim, k.NomorKamar, pt.NamaPetugas 
                               FROM inoutpenghuni io 
                               JOIN penghuni pe ON io.PenghuniID = pe.PenghuniID 
                               JOIN kamar k ON pe.KamarID = k.KamarID 
                               LEFT JOIN petugas pt ON io.PetugasID = pt.PetugasID 
                               ORDER BY io.InOutID DESC");
?>

<!DOCTYPE html>
<html lang="en">
<?php require '../../head.php'; ?>

<body class="tw:p-0 tw:m-0 relative tw:flex tw:bg-[#f8fafc] tw:min-h-screen">
    <?php require '../components/sidebar.php'; ?>
    <main class="tw:md:ml-75 tw:grow">
        <div class="tw:pt-20 tw:md:pt-8 tw:px-8 tw:mb-8 tw:flex-1 tw:w-dvw tw:md:w-full">
            <div class="tw:flex tw:justify-between tw:items-center tw:mb-6">
                <h1 class="tw:font-bold tw:text-4xl tw:text-black">
                    Log Transaksi In/Out
                </h1>
                <a href="index.php" class="tw:bg-white tw:text-gray-600 tw:px-4 tw:py-2 tw:rounded-lg tw:border tw:border-gray-200 tw:hover:bg-gray-50 tw:transition-all tw:inline-flex tw:items-center tw:gap-2 tw:no-underline">
                    <i class="fa-solid fa-arrow-left text-2xl"></i>
                    <span>Kembali</span>
                </a>
            </div>

            <div class="tw:bg-white tw:p-6 tw:rounded-[24px] tw:shadow-sm tw:border tw:border-gray-100">
                <div class="tw:overflow-x-auto tw:rounded-lg tw:border tw:border-gray-300">
                    <table id="logTable" class="table text-center align-middle tw:mb-0 tw:w-full">
                        <thead>
                            <tr>
                                <th scope="col" class="text-center align-middle">ID</th>
                                <th scope="col" class="text-center align-middle">Penghuni</th>
                                <th scope="col" class="text-center align-middle">Kamar</th>
                                <th scope="col" class="text-center align-middle">Status</th>
                                <th scope="col" class="text-center align-middle">Keperluan</th>
                                <th scope="col" class="text-center align-middle">Waktu Keluar</th>
                                <th scope="col" class="text-center align-middle">Waktu Masuk</th>
                                <th scope="col" class="text-center align-middle">Dikonfirmasi Oleh</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($logQuery)): ?>
                                <tr>
                                    <td><?= $row['InOutID'] ?></td>
                                    <td>
                                        <div class="tw:font-bold"><?= htmlspecialchars($row['NamaPenghuni']) ?></div>
                                        <div class="tw:text-xs tw:text-gray-500"><?= $row['Nim'] ?></div>
                                    </td>
                                    <td><?= $row['NomorKamar'] ?></td>
                                    <td>
                                        <?php if ($row['Status'] === 'Pending'): ?>
                                            <span class="badge bg-warning">Pending</span>
                                        <?php elseif ($row['Status'] === 'Keluar'): ?>
                                            <span class="badge bg-danger">Di Luar</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Masuk</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['Keperluan']) ?></td>
                                    <td><?= $row['WaktuKeluar'] ? date('H:i, d M Y', strtotime($row['WaktuKeluar'])) : '-' ?></td>
                                    <td><?= $row['WaktuMasuk'] ? date('H:i, d M Y', strtotime($row['WaktuMasuk'])) : '-' ?></td>
                                    <td><?= htmlspecialchars($row['NamaPetugas'] ?? '-') ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    <?php require '../../bootstrap.php'; ?>
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
    <link href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            new DataTable('#logTable', {
                autoWidth: false,
                ordering: true,
                searching: true,
                paging: true,
                info: true,
                order: [[0, 'desc']], // Sort by ID descending (newest first)
                columnDefs: [
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