<?php
session_start();
require 'helpers.php';
paket_require_roles(['PENGHUNI']);
require '../../db.php';

$paketId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$paketId) {
    paket_redirect('/doremi-app/dashboard/paket/', 'error', 'Data paket tidak valid.');
}

$userId = (int) $_SESSION['userId'];
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
     WHERE pk.PaketID = ? AND pk.PenghuniID = ?
     LIMIT 1"
);
mysqli_stmt_bind_param($stmt, 'ii', $paketId, $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$paket = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$paket) {
    paket_redirect('/doremi-app/dashboard/paket/', 'error', 'Data paket tidak ditemukan.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $petugasId = !empty($paket['PickupPetugasID']) ? (int) $paket['PickupPetugasID'] : (int) $paket['PetugasID'];
    $status = trim($_POST['status'] ?? '');
    $waktuPengambilan = date('Y-m-d H:i:s');
    $keterangan = trim($_POST['keterangan'] ?? '');

    if (
        $petugasId < 1
        || !in_array($status, ['Belum Diambil', 'Sudah Diambil'], true)
    ) {
        paket_redirect($_SERVER['PHP_SELF'] . '?id=' . $paketId, 'error', 'Data pengambilan paket tidak valid.');
    }

    try {
        $fotoPengambilan = paket_store_photo(
            $_FILES['fotoPengambilan'] ?? [],
            $paket['FotoPengambilan'] ?? null
        );
    } catch (RuntimeException $exception) {
        paket_redirect($_SERVER['PHP_SELF'] . '?id=' . $paketId, 'error', $exception->getMessage());
    }

    $keterangan = $keterangan !== '' ? $keterangan : '-';

    if (!empty($paket['PengambilanPaketID'])) {
        $pengambilanPaketId = (int) $paket['PengambilanPaketID'];
        $stmt = mysqli_prepare(
            $db,
            "UPDATE pengambilanpaket
             SET PenghuniID = ?, PetugasID = ?, FotoPengambilan = ?, WaktuPengambilan = ?, Status = ?, Keterangan = ?
             WHERE PengambilanPaketID = ?"
        );
        mysqli_stmt_bind_param(
            $stmt,
            'iissssi',
            $userId,
            $petugasId,
            $fotoPengambilan,
            $waktuPengambilan,
            $status,
            $keterangan,
            $pengambilanPaketId
        );
        $successMessage = 'Status paket berhasil diperbarui.';
    } else {
        $stmt = mysqli_prepare(
            $db,
            "INSERT INTO pengambilanpaket (PaketID, PenghuniID, PetugasID, FotoPengambilan, WaktuPengambilan, Status, Keterangan)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param(
            $stmt,
            'iiissss',
            $paketId,
            $userId,
            $petugasId,
            $fotoPengambilan,
            $waktuPengambilan,
            $status,
            $keterangan
        );
        $successMessage = 'Pengambilan paket berhasil dicatat.';
    }

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        paket_redirect($_SERVER['PHP_SELF'] . '?id=' . $paketId, 'error', 'Gagal menyimpan data pengambilan paket.');
    }

    mysqli_stmt_close($stmt);
    paket_redirect('/doremi-app/dashboard/paket/', 'success', $successMessage);
}

$selectedStatus = in_array($paket['Status'] ?? '', ['Belum Diambil', 'Sudah Diambil'], true)
    ? $paket['Status']
    : 'Sudah Diambil';
$keteranganValue = ($paket['Keterangan'] ?? '-') === '-' ? '' : $paket['Keterangan'];
$fotoWajib = empty($paket['FotoPengambilan']);
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
                    <h1 class="tw:font-bold tw:text-4xl tw:text-slate-900 tw:m-0">
                        <?= empty($paket['PengambilanPaketID']) ? 'Catat Pengambilan Paket' : 'Ubah Status Paket' ?>
                    </h1>
                    <p class="tw:text-slate-500 tw:mt-2 tw:mb-0">
                        Lengkapi data pengambilan paket sesuai tabel `pengambilanpaket`.
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
                                <p class="tw:text-sm tw:text-slate-500 tw:mb-1">Pengirim</p>
                                <p class="tw:font-semibold tw:text-slate-900 tw:mb-0"><?= htmlspecialchars($paket['NamaPengirim']) ?></p>
                            </div>
                            <div>
                                <p class="tw:text-sm tw:text-slate-500 tw:mb-1">Kurir</p>
                                <p class="tw:font-semibold tw:text-slate-900 tw:mb-0"><?= htmlspecialchars($paket['Kurir']) ?></p>
                            </div>
                            <div>
                                <p class="tw:text-sm tw:text-slate-500 tw:mb-1">Waktu Sampai</p>
                                <p class="tw:font-semibold tw:text-slate-900 tw:mb-0">
                                    <?= $paket['WaktuSampai'] ? date('d M Y H:i', strtotime($paket['WaktuSampai'])) : '-' ?>
                                </p>
                            </div>
                            <div>
                                <p class="tw:text-sm tw:text-slate-500 tw:mb-1">Pencatat Paket</p>
                                <p class="tw:font-semibold tw:text-slate-900 tw:mb-0"><?= htmlspecialchars($paket['NamaPetugasPaket']) ?></p>
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
                                <p class="tw:text-sm tw:text-slate-500 tw:mb-1">Status Saat Ini</p>
                                <span class="badge <?= htmlspecialchars($statusMeta['class']) ?>">
                                    <?= htmlspecialchars($statusMeta['label']) ?>
                                </span>
                            </div>
                            <div>
                                <p class="tw:text-sm tw:text-slate-500 tw:mb-1">Waktu Pengambilan</p>
                                <p class="tw:font-semibold tw:text-slate-900 tw:mb-0">
                                    <?= !empty($paket['WaktuPengambilan']) ? date('d M Y H:i', strtotime($paket['WaktuPengambilan'])) : 'Belum tercatat' ?>
                                </p>
                            </div>
                            <?php if (!empty($paket['FotoPengambilan'])): ?>
                                <div>
                                    <p class="tw:text-sm tw:text-slate-500 tw:mb-2">Foto Pengambilan</p>
                                    <a href="<?= htmlspecialchars(paket_photo_url($paket['FotoPengambilan'])) ?>" target="_blank"
                                        rel="noopener noreferrer" class="tw:block">
                                        <img src="<?= htmlspecialchars(paket_photo_url($paket['FotoPengambilan'])) ?>"
                                            alt="Foto Pengambilan Paket"
                                            class="tw:w-full tw:h-48 tw:object-cover tw:rounded-2xl tw:border tw:border-slate-200">
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="tw:lg:col-span-2">
                    <div class="tw:bg-white tw:p-6 tw:rounded-[24px] tw:shadow-sm tw:border tw:border-gray-100">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status Paket</label>
                                <select class="form-select" name="status" id="status" required>
                                    <option value="Belum Diambil" <?= $selectedStatus === 'Belum Diambil' ? 'selected' : '' ?>>Belum Diambil</option>
                                    <option value="Sudah Diambil" <?= $selectedStatus === 'Sudah Diambil' ? 'selected' : '' ?>>Sudah Diambil</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Waktu Pengambilan</label>
                                <input type="text" class="form-control"
                                    value="Akan diisi otomatis saat data disimpan" disabled>
                            </div>

                            <div class="mb-3">
                                <label for="fotoPengambilan" class="form-label">Foto Pengambilan</label>
                                <input type="file" name="fotoPengambilan" class="form-control" id="fotoPengambilan"
                                    accept="image/png,image/jpeg,image/webp" <?= $fotoWajib ? 'required' : '' ?>>
                                <div class="form-text">
                                    Upload JPG, PNG, atau WEBP maksimal 2MB. Foto akan disimpan langsung ke database dalam format base64.
                                    <?= $fotoWajib ? 'Foto wajib diunggah untuk pencatatan pertama.' : 'Kosongkan jika tidak ingin mengganti foto.' ?>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="keterangan" class="form-label">Keterangan</label>
                                <textarea name="keterangan" class="form-control" id="keterangan" rows="4"
                                    placeholder="Tambahkan catatan pengambilan paket jika diperlukan."><?= htmlspecialchars($keteranganValue) ?></textarea>
                            </div>

                            <div class="tw:w-full tw:flex tw:justify-end tw:mt-2">
                                <button type="submit"
                                    class="tw:bg-secondary tw:w-full tw:text-white tw:px-3 tw:py-3 tw:rounded-xl tw:justify-center tw:hover:bg-accent tw:duration-300 tw:transition-all tw:inline-flex tw:items-center tw:gap-2">
                                    <span><?= empty($paket['PengambilanPaketID']) ? 'Simpan Pengambilan' : 'Simpan Perubahan' ?></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php require '../../bootstrap.php'; ?>
    <?php require '../../validation_alert.php'; ?>
</body>

</html>
