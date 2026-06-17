<?php
session_start();
require 'helpers.php';
paket_require_roles(['SIGAP']);
require '../../db.php';

$paketId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$paketId) {
    paket_redirect('/doremi-app/dashboard/paket/', 'error', 'Data paket tidak valid.');
}

$stmt = mysqli_prepare(
    $db,
    "SELECT pk.*, ph.NamaPenghuni, ph.Nim, k.NomorKamar,
            pt.NamaPetugas AS NamaPetugasPaket,
            pp.PengambilanPaketID, pp.PetugasID AS PickupPetugasID, pp.FotoPengambilan, pp.WaktuPengambilan, pp.Status, pp.Keterangan
     FROM paket pk
     JOIN penghuni ph ON pk.PenghuniID = ph.PenghuniID
     LEFT JOIN kamar k ON ph.KamarID = k.KamarID
     JOIN petugas pt ON pk.PetugasID = pt.PetugasID
     LEFT JOIN (
         SELECT pp1.*
         FROM pengambilanpaket pp1
         INNER JOIN (
             SELECT PaketID, MAX(PengambilanPaketID) AS LatestPengambilanPaketID
             FROM pengambilanpaket
             GROUP BY PaketID
         ) latest ON latest.LatestPengambilanPaketID = pp1.PengambilanPaketID
     ) pp ON pp.PaketID = pk.PaketID
     WHERE pk.PaketID = ?
     LIMIT 1"
);
mysqli_stmt_bind_param($stmt, 'i', $paketId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$paket = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$paket) {
    paket_redirect('/doremi-app/dashboard/paket/', 'error', 'Data paket tidak ditemukan.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($paket['PengambilanPaketID'])) {
        paket_redirect($_SERVER['PHP_SELF'] . '?id=' . $paketId, 'error', 'Belum ada bukti pengambilan untuk direview.');
    }

    $status = trim($_POST['status'] ?? '');
    $keterangan = trim($_POST['keterangan'] ?? '');
    $petugasSigapId = (int) $_SESSION['userId'];

    if ($petugasSigapId < 1 || !in_array($status, ['Sudah Diambil', 'TERTUKAR'], true)) {
        paket_redirect($_SERVER['PHP_SELF'] . '?id=' . $paketId, 'error', 'Status review paket tidak valid.');
    }

    $keterangan = $keterangan !== '' ? $keterangan : '-';
    $pengambilanPaketId = (int) $paket['PengambilanPaketID'];

    $stmt = mysqli_prepare(
        $db,
        "UPDATE pengambilanpaket
         SET PetugasID = ?, Status = ?, Keterangan = ?
         WHERE PengambilanPaketID = ?"
    );
    mysqli_stmt_bind_param(
        $stmt,
        'issi',
        $petugasSigapId,
        $status,
        $keterangan,
        $pengambilanPaketId
    );

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        paket_redirect($_SERVER['PHP_SELF'] . '?id=' . $paketId, 'error', 'Gagal memperbarui status paket.');
    }

    mysqli_stmt_close($stmt);
    paket_redirect('/doremi-app/dashboard/paket/', 'success', 'Status paket berhasil diperbarui.');
}

$selectedStatus = in_array($paket['Status'] ?? '', ['Sudah Diambil', 'TERTUKAR'], true)
    ? $paket['Status']
    : 'Sudah Diambil';
$keteranganValue = ($paket['Keterangan'] ?? '-') === '-' ? '' : $paket['Keterangan'];
$statusMeta = paket_status_meta($paket['Status'] ?? 'Belum Diambil');
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
                    <h1 class="tw:font-bold tw:text-4xl tw:text-slate-900 tw:m-0">Review Pengambilan Paket</h1>
                    <p class="tw:text-slate-500 tw:mt-2 tw:mb-0">
                        SIGAP dapat memeriksa bukti foto pengambilan lalu mengubah status menjadi paket tertukar jika diperlukan.
                    </p>
                </div>
                <a href="index.php"
                    class="tw:bg-white tw:text-slate-700 tw:px-4 tw:py-3 tw:rounded-xl tw:border tw:border-slate-200 tw:hover:bg-slate-50 tw:transition-all tw:inline-flex tw:items-center tw:gap-2 tw:no-underline">
                    <i class="iconsax tw:text-xl" icon-name="arrow-left-2"></i>
                    <span>Kembali</span>
                </a>
            </div>

            <div class="tw:grid tw:grid-cols-1 tw:lg:grid-cols-3 tw:gap-8">
                <div class="tw:lg:col-span-1">
                    <div class="tw:bg-white tw:p-6 tw:rounded-[24px] tw:shadow-sm tw:border tw:border-gray-100 tw:h-full">
                        <h5 class="tw:font-bold tw:text-slate-900 tw:mb-4">Informasi Paket</h5>
                        <div class="tw:flex tw:flex-col tw:gap-4">
                            <div>
                                <p class="tw:text-sm tw:text-slate-500 tw:mb-1">Paket ID</p>
                                <p class="tw:font-semibold tw:text-slate-900 tw:mb-0"><?= (int) $paket['PaketID'] ?></p>
                            </div>
                            <div>
                                <p class="tw:text-sm tw:text-slate-500 tw:mb-1">Penghuni</p>
                                <p class="tw:font-semibold tw:text-slate-900 tw:mb-0">
                                    <?= htmlspecialchars($paket['NamaPenghuni']) ?>
                                    <?php if (!empty($paket['NomorKamar'])): ?>
                                        <span class="tw:text-sm tw:text-slate-500">(Kamar <?= htmlspecialchars($paket['NomorKamar']) ?>)</span>
                                    <?php endif; ?>
                                </p>
                                <p class="tw:text-sm tw:text-slate-500 tw:mb-0"><?= htmlspecialchars($paket['Nim']) ?></p>
                            </div>
                            <div>
                                <p class="tw:text-sm tw:text-slate-500 tw:mb-1">Pengirim</p>
                                <p class="tw:font-semibold tw:text-slate-900 tw:mb-0"><?= htmlspecialchars($paket['NamaPengirim']) ?></p>
                            </div>
                            <div>
                                <p class="tw:text-sm tw:text-slate-500 tw:mb-1">Kurir</p>
                                <p class="tw:font-semibold tw:text-slate-900 tw:mb-0"><?= htmlspecialchars($paket['Kurir']) ?></p>
                            </div>
                            <div>
                                <p class="tw:text-sm tw:text-slate-500 tw:mb-1">Pencatat Paket</p>
                                <p class="tw:font-semibold tw:text-slate-900 tw:mb-0"><?= htmlspecialchars($paket['NamaPetugasPaket']) ?></p>
                            </div>
                            <div>
                                <p class="tw:text-sm tw:text-slate-500 tw:mb-1">Status Saat Ini</p>
                                <span class="badge <?= htmlspecialchars($statusMeta['class']) ?>">
                                    <?= htmlspecialchars($statusMeta['label']) ?>
                                </span>
                            </div>
                            <div>
                                <p class="tw:text-sm tw:text-slate-500 tw:mb-1">Waktu Pengambilan</p>
                                <p class="tw:font-semibold tw:text-slate-900 tw:mb-0">
                                    <?= !empty($paket['WaktuPengambilan']) ? date('d M Y H:i', strtotime($paket['WaktuPengambilan'])) : 'Belum ada catatan pengambilan' ?>
                                </p>
                            </div>
                            <?php if (!empty($paket['FotoPengambilan'])): ?>
                                <div>
                                    <p class="tw:text-sm tw:text-slate-500 tw:mb-2">Bukti Foto Pengambilan</p>
                                    <a href="<?= htmlspecialchars(paket_photo_url($paket['FotoPengambilan'])) ?>" target="_blank"
                                        rel="noopener noreferrer" class="tw:block">
                                        <img src="<?= htmlspecialchars(paket_photo_url($paket['FotoPengambilan'])) ?>"
                                            alt="Bukti Foto Pengambilan Paket"
                                            class="tw:w-full tw:h-56 tw:object-cover tw:rounded-2xl tw:border tw:border-slate-200">
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="tw:lg:col-span-2">
                    <div class="tw:bg-white tw:p-6 tw:rounded-[24px] tw:shadow-sm tw:border tw:border-gray-100">
                        <?php if (empty($paket['PengambilanPaketID']) || empty($paket['FotoPengambilan'])): ?>
                            <div class="tw:rounded-2xl tw:border tw:border-dashed tw:border-gray-300 tw:bg-slate-50 tw:p-6 tw:text-slate-600">
                                Status review belum bisa diubah karena penghuni belum memiliki catatan pengambilan lengkap beserta bukti foto.
                            </div>
                        <?php else: ?>
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status Review SIGAP</label>
                                    <select class="form-select" name="status" id="status" required>
                                        <option value="Sudah Diambil" <?= $selectedStatus === 'Sudah Diambil' ? 'selected' : '' ?>>Sudah Diambil</option>
                                        <option value="TERTUKAR" <?= $selectedStatus === 'TERTUKAR' ? 'selected' : '' ?>>PAKET TERTUKAR</option>
                                    </select>
                                    <div class="form-text">
                                        Ubah ke <strong>PAKET TERTUKAR</strong> jika setelah dicek bukti pengambilan ternyata paket diambil oleh penghuni yang salah.
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="keterangan" class="form-label">Keterangan Review</label>
                                    <textarea name="keterangan" class="form-control" id="keterangan" rows="4"
                                        placeholder="Tambahkan catatan review dari SIGAP jika diperlukan."><?= htmlspecialchars($keteranganValue) ?></textarea>
                                </div>

                                <div class="tw:w-full tw:flex tw:justify-end tw:mt-2">
                                    <button type="submit"
                                        class="tw:bg-secondary tw:w-full tw:text-white tw:px-3 tw:py-3 tw:rounded-xl tw:justify-center tw:hover:bg-accent tw:duration-300 tw:transition-all tw:inline-flex tw:items-center tw:gap-2">
                                        <span>Simpan Status Review</span>
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php require '../../bootstrap.php'; ?>
    <?php require '../../validation_alert.php'; ?>
</body>

</html>
