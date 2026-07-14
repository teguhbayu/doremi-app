<?php
session_start();
if (!isset($_SESSION['userId'])) {
    header("Location: /doremi-app/login.php");
    exit;
}

require '../../db.php';
$role = $_SESSION['userRole'];
$userId = $_SESSION['userId'];
if ($role === 'PENGHUNI') {
    $historyQuery = mysqli_query($db, "SELECT io.*, p.NamaPetugas 
                                     FROM inoutpenghuni io 
                                     LEFT JOIN petugas p ON io.PetugasID = p.PetugasID 
                                     WHERE io.PenghuniID = $userId 
                                     ORDER BY io.InOutID DESC");

    $activeQuery = mysqli_query($db, "SELECT COUNT(*) as count FROM inoutpenghuni WHERE PenghuniID = $userId AND Status IN ('Pending', 'Keluar')");
    $activeRequestCount = (int) mysqli_fetch_assoc($activeQuery)['count'];
    $hasActiveRequest = $activeRequestCount > 0;
}

if ($role === 'SIGAP') {
    $pendingQuery = mysqli_query($db, "SELECT io.*, pe.NamaPenghuni, pe.Nim, k.NomorKamar 
                                     FROM inoutpenghuni io 
                                     JOIN penghuni pe ON io.PenghuniID = pe.PenghuniID 
                                     JOIN kamar k ON pe.KamarID = k.KamarID 
                                     WHERE io.Status = 'Pending' 
                                     ORDER BY io.InOutID ASC");

    $outsideQuery = mysqli_query($db, "SELECT io.*, pe.NamaPenghuni, pe.Nim, k.NomorKamar 
                                     FROM inoutpenghuni io 
                                     JOIN penghuni pe ON io.PenghuniID = pe.PenghuniID 
                                     JOIN kamar k ON pe.KamarID = k.KamarID 
                                     WHERE io.Status = 'Keluar' 
                                     ORDER BY io.WaktuKeluar ASC");

    $pendingCount = mysqli_num_rows($pendingQuery);
    $outsideCount = mysqli_num_rows($outsideQuery);
}
?>

<!DOCTYPE html>
<html lang="en">
<?php require '../../head.php'; ?>

<body class="dashboard-body tw:p-0 tw:m-0 tw:relative tw:flex tw:min-h-screen">
    <?php require '../components/sidebar.php'; ?>
    <main class="tw:md:ml-75 tw:grow">
        <div class="tw:pt-20 tw:md:pt-8 tw:px-8 tw:mb-8 tw:flex-1 tw:w-dvw tw:md:w-full">
            <?php require dirname(__DIR__) . '/components/breadcrumb.php'; ?>
            <h1 class="page-title" data-kicker="Perizinan Penghuni"
                data-subtitle="<?= htmlspecialchars($role === 'SIGAP' ? 'Konfirmasi permintaan keluar, pantau penghuni yang masih berada di luar area asrama, dan akses log aktivitas dari satu menu yang sama.' : 'Ajukan izin keluar, cek statusnya, dan lihat riwayat aktivitas keluar masuk Anda dalam satu halaman.') ?>">
                <?= $role === 'SIGAP' ? 'Konfirmasi In/Out' : 'Izin Keluar' ?>
            </h1>

            <div class="page-toolbar"
                data-note="<?= htmlspecialchars($role === 'SIGAP' ? $pendingCount . ' permintaan menunggu, ' . $outsideCount . ' penghuni di luar' : ($hasActiveRequest ? $activeRequestCount . ' izin aktif masih berjalan' : 'Belum ada izin aktif')) ?>">
                <?php if ($role === 'SIGAP'): ?>
                    <a href="log.php" class="tw:inline-flex tw:items-center tw:justify-center tw:gap-2 tw:min-h-12 tw:px-4 tw:py-[0.85rem] tw:rounded-2xl tw:border tw:border-[rgba(22,60,122,0.12)] tw:font-extrabold tw:no-underline tw:text-slate-900 tw:bg-[rgba(255,255,255,0.82)] tw:hover:bg-gray-50 tw:transition-all tw:text-sm">
                        <i class="iconsax tw:text-xl" icon-name="document-text-1"></i>
                        <span>Lihat Semua Log</span>
                    </a>
                <?php endif; ?>
            </div>

            <?php if ($role === 'PENGHUNI'): ?>
                <div class="tw:grid tw:grid-cols-1 tw:lg:grid-cols-3 tw:gap-8">
                    <div class="tw:lg:col-span-1">
                        <div class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm">
                            <h5 class="tw:m-0 tw:text-[1.2rem] tw:text-slate-900">Buat Izin Keluar</h5>
                            <p class="tw:m-0 tw:text-slate-500 tw:leading-[1.75] tw:text-sm">Tentukan jadwal keluar dan masuk beserta keperluan agar permintaan bisa dikonfirmasi oleh petugas SIGAP.</p>
                            <?php if ($hasActiveRequest): ?>
                                <div class="alert alert-warning tw:rounded-xl">
                                    Anda masih memiliki izin keluar yang aktif (Pending/Di Luar). Silakan selesaikan terlebih
                                    dahulu sebelum membuat yang baru.
                                </div>
                            <?php else: ?>
                                <form action="process.php" method="POST" class="tw:grid tw:gap-4">
                                    <input type="hidden" name="action" value="create_request">
                                    <?php $currentTime = date('H:i'); ?>
                                    <div>
                                        <label class="form-label">Rencana Keluar (Waktu)</label>
                                        <input type="time" name="waktuKeluar" class="form-control" min="<?= $currentTime ?>"
                                            max="22:00" required>
                                    </div>
                                    <div>
                                        <label class="form-label">Rencana Masuk (Waktu)</label>
                                        <input type="time" name="waktuMasuk" class="form-control" min="<?= $currentTime ?>"
                                            max="22:00" required>
                                    </div>
                                    <div>
                                        <label class="form-label">Keperluan</label>
                                        <textarea name="keperluan" class="form-control" rows="3"
                                            placeholder="Contoh: Belanja, Fotokopi" maxlength="20" required></textarea>
                                        <span class="form-hint">Keperluan wajib diisi dan dibatasi maksimal 20 karakter.</span>
                                    </div>
                                    <button type="submit"
                                        class="tw:bg-secondary tw:w-full tw:text-white tw:py-3 tw:rounded-xl tw:hover:bg-accent tw:transition-all tw:font-semibold">
                                        Kirim Permintaan
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="tw:lg:col-span-2">
                        <div class="table-panel">
                            <h5 class="tw:font-bold tw:mb-4">Riwayat Izin Keluar</h5>
                            <div class="doremi-table-wrapper">
                                <table id="historyTable" class="table doremi-table text-center align-middle tw:mb-0 tw:w-full">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="text-center align-middle">Status</th>
                                            <th scope="col" class="text-center align-middle">Keperluan
                                            </th>
                                            <th scope="col" class="text-center align-middle">Waktu
                                                Keluar</th>
                                            <th scope="col" class="text-center align-middle">Waktu Masuk
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = mysqli_fetch_assoc($historyQuery)): ?>
                                            <tr>
                                                <td>
                                                    <?php if ($row['Status'] === 'Pending'): ?>
                                                        <span class="badge bg-warning">Pending</span>
                                                    <?php elseif ($row['Status'] === 'Keluar'): ?>
                                                        <span class="badge bg-danger">Di Luar</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">Selesai</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="tw:max-w-48 tw:truncate" title="<?= htmlspecialchars($row['Keperluan']) ?>"><?= htmlspecialchars($row['Keperluan']) ?></td>
                                                <td><?= $row['WaktuKeluar'] ? date('H:i, d M', strtotime($row['WaktuKeluar'])) : '-' ?>
                                                </td>
                                                <td><?= $row['WaktuMasuk'] ? date('H:i, d M', strtotime($row['WaktuMasuk'])) : '-' ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            <?php elseif ($role === 'SIGAP'): ?>
                <div class="tw:flex tw:flex-col tw:gap-8">
                    <div class="table-panel">
                        <div class="tw:flex tw:items-center tw:gap-3 tw:mb-6">
                            <div class="tw:w-[3.4rem] tw:h-[3.4rem] tw:inline-flex tw:items-center tw:justify-center tw:rounded-[20px] tw:flex-shrink-0 tw:text-amber-700 tw:bg-[rgba(250,236,207,0.82)]">
                                <i class="fa-solid fa-arrow-up text-2xl"></i>
                            </div>
                            <h5 class="tw:font-bold tw:m-0">Akan Keluar</h5>
                        </div>
                        <div class="doremi-table-wrapper">
                            <table id="pendingTable" class="table doremi-table text-center align-middle tw:mb-0 tw:w-full">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-center align-middle">Penghuni</th>
                                        <th scope="col" class="text-center align-middle">Kamar</th>
                                        <th scope="col" class="text-center align-middle">Keperluan</th>
                                        <th scope="col" class="text-center align-middle">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($pendingQuery)): ?>
                                        <tr>
                                            <td class="tw:max-w-40 tw:truncate" title="<?= htmlspecialchars($row['NamaPenghuni']) ?> (<?= $row['Nim'] ?>)">
                                                <div class="tw:font-bold"><?= htmlspecialchars($row['NamaPenghuni']) ?></div>
                                                <div class="tw:text-xs tw:text-gray-500"><?= $row['Nim'] ?></div>
                                            </td>
                                            <td><?= $row['NomorKamar'] ?></td>
                                            <td class="tw:max-w-48 tw:truncate" title="<?= htmlspecialchars($row['Keperluan']) ?>"><?= htmlspecialchars($row['Keperluan']) ?></td>
                                            <td>
                                                <form action="process.php" method="POST" class="tw:inline">
                                                    <input type="hidden" name="action" value="confirm_exit">
                                                    <input type="hidden" name="id" value="<?= $row['InOutID'] ?>">
                                                    <button type="submit"
                                                        class="btn btn-primary btn-sm tw:rounded-lg">Konfirmasi Keluar</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="table-panel">
                        <div class="tw:flex tw:items-center tw:gap-3 tw:mb-6">
                            <div class="tw:w-[3.4rem] tw:h-[3.4rem] tw:inline-flex tw:items-center tw:justify-center tw:rounded-[20px] tw:flex-shrink-0 tw:text-primary tw:bg-accent/80">
                                <i class="fa-solid fa-arrow-down text-2xl"></i>
                            </div>
                            <h5 class="tw:font-bold tw:m-0">Di Luar</h5>
                        </div>
                        <div class="doremi-table-wrapper">
                            <table id="outsideTable" class="table doremi-table text-center align-middle tw:mb-0 tw:w-full">
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-center align-middle">Penghuni</th>
                                        <th scope="col" class="text-center align-middle">Kamar</th>
                                        <th scope="col" class="text-center align-middle">Waktu Keluar
                                        </th>
                                        <th scope="col" class="text-center align-middle">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($outsideQuery)): ?>
                                        <tr>
                                            <td class="tw:max-w-40 tw:truncate" title="<?= htmlspecialchars($row['NamaPenghuni']) ?> (<?= $row['Nim'] ?>)">
                                                <div class="tw:font-bold"><?= htmlspecialchars($row['NamaPenghuni']) ?></div>
                                                <div class="tw:text-xs tw:text-gray-500"><?= $row['Nim'] ?></div>
                                            </td>
                                            <td><?= $row['NomorKamar'] ?></td>
                                            <td><?= date('H:i, d M', strtotime($row['WaktuKeluar'])) ?></td>
                                            <td>
                                                <form action="process.php" method="POST" class="tw:inline">
                                                    <input type="hidden" name="action" value="confirm_entry">
                                                    <input type="hidden" name="id" value="<?= $row['InOutID'] ?>">
                                                    <button type="submit"
                                                        class="btn btn-success btn-sm tw:rounded-lg">Konfirmasi Masuk</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <?php require '../../bootstrap.php'; ?>
    <?php require '../../validation_alert.php'; ?>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
    <link href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const dtConfig = {
                autoWidth: false,
                ordering: true,
                searching: true,
                paging: true,
                info: true,
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
            };

            <?php if ($role === 'PENGHUNI'): ?>
                if (document.getElementById('historyTable')) {
                    new DataTable('#historyTable', {
                        ...dtConfig,
                        order: [[3, 'desc']]
                    });
                }
            <?php elseif ($role === 'SIGAP'): ?>
                if (document.getElementById('pendingTable')) {
                    new DataTable('#pendingTable', {
                        ...dtConfig,
                        columnDefs: [
                            { targets: 4, orderable: false },
                            { targets: '_all', className: 'text-center align-middle' }
                        ]
                    });
                }
                if (document.getElementById('outsideTable')) {
                    new DataTable('#outsideTable', {
                        ...dtConfig,
                        order: [[3, 'asc']],
                        columnDefs: [
                            { targets: 4, orderable: false },
                            { targets: '_all', className: 'text-center align-middle' }
                        ]
                    });
                }
            <?php endif; ?>
        });
    </script>
</body>

</html>
