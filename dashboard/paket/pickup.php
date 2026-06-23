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

$isLocked = !empty($paket['PengambilanPaketID']) && paket_is_final_status($paket['Status'] ?? null);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($isLocked) {
        paket_redirect($_SERVER['PHP_SELF'] . '?id=' . $paketId, 'error', 'Status paket yang sudah diambil tidak dapat diubah lagi.');
    }

    $petugasId = !empty($paket['PickupPetugasID']) ? (int) $paket['PickupPetugasID'] : (int) $paket['PetugasID'];
    $status = 'Sudah Diambil';
    $waktuPengambilan = date('Y-m-d H:i:s');
    $keterangan = trim($_POST['keterangan'] ?? '');

    if (
        $petugasId < 1
        || !in_array($status, ['Sudah Diambil'], true)
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

    $keterangan = $keterangan !== '' ? $keterangan : null;

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
        $successMessage = 'Catatan pengambilan paket berhasil dilengkapi.';
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

$keteranganValue = trim((string) ($paket['Keterangan'] ?? ''));
$fotoWajib = empty($paket['FotoPengambilan']);
$statusMeta = paket_status_meta($paket['Status'] ?? 'Belum Diambil');
?>

<!DOCTYPE html>
<html lang="en">
<?php require '../../head.php'; ?>

<body class="dashboard-body tw:p-0 tw:m-0 relative tw:flex tw:bg-[#f8fafc] tw:min-h-screen">
    <?php require '../components/sidebar.php'; ?>
    <main class="dashboard-main tw:md:ml-75 tw:grow">
        <div class="dashboard-page tw:pt-20 tw:md:pt-8 tw:px-8 tw:mb-8 tw:flex-1 tw:w-dvw tw:md:w-full">
            <?php require dirname(__DIR__) . '/components/breadcrumb.php'; ?>
            <h1 class="page-title" data-kicker="Pengambilan Paket" data-subtitle="Lengkapi status, bukti foto, dan catatan pengambilan agar riwayat paket penghuni tercatat rapi dan siap direview SIGAP.">
                <?= $isLocked ? 'Detail Pengambilan Paket' : 'Catat Pengambilan Paket' ?>
            </h1>

            <div class="page-toolbar" data-note="<?= $isLocked ? 'Catatan pengambilan sudah final' : 'Pencatatan pengambilan pertama' ?>">
                <a href="index.php" class="page-secondary-btn">
                    <i class="iconsax tw:text-xl" icon-name="arrow-left-2"></i>
                    <span>Kembali</span>
                </a>
            </div>

            <div class="tw:grid tw:grid-cols-1 tw:lg:grid-cols-3 tw:gap-8">
                <div class="tw:lg:col-span-1">
                    <div class="dashboard-side-panel tw:h-full">
                        <h5 class="dashboard-side-panel__title">Informasi Paket</h5>
                        <div class="dashboard-info-list">
                            <div class="dashboard-info-item">
                                <span class="dashboard-info-item__label">Tipe Kiriman</span>
                                <strong><?= htmlspecialchars(paket_type_label($paket['JenisPaket'] ?? null)) ?></strong>
                            </div>
                            <div class="dashboard-info-item">
                                <span class="dashboard-info-item__label">Pengirim</span>
                                <strong><?= htmlspecialchars($paket['NamaPengirim']) ?></strong>
                                <p><?= htmlspecialchars($paket['Kurir']) ?></p>
                            </div>
                            <div class="dashboard-info-item">
                                <span class="dashboard-info-item__label">Waktu Sampai</span>
                                <strong><?= $paket['WaktuSampai'] ? date('d M Y H:i', strtotime($paket['WaktuSampai'])) : '-' ?></strong>
                                <p>Pencatat: <?= htmlspecialchars($paket['NamaPetugasPaket']) ?></p>
                            </div>
                            <div class="dashboard-info-item">
                                <span class="dashboard-info-item__label">Penghuni</span>
                                <strong><?= htmlspecialchars($paket['NamaPenghuni']) ?></strong>
                                <p><?= htmlspecialchars($paket['Nim']) ?><?= !empty($paket['NomorKamar']) ? ' | Kamar ' . htmlspecialchars($paket['NomorKamar']) : '' ?></p>
                            </div>
                            <div class="dashboard-info-item">
                                <span class="dashboard-info-item__label">Status Saat Ini</span>
                                <span class="badge <?= htmlspecialchars($statusMeta['class']) ?>">
                                    <?= htmlspecialchars($statusMeta['label']) ?>
                                </span>
                                <p><?= !empty($paket['WaktuPengambilan']) ? date('d M Y H:i', strtotime($paket['WaktuPengambilan'])) : 'Belum ada waktu pengambilan tercatat' ?></p>
                            </div>
                            <?php if (!empty($paket['FotoPengambilan'])): ?>
                                <div class="dashboard-info-item">
                                    <span class="dashboard-info-item__label">Foto Pengambilan</span>
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
                    <?php if ($isLocked): ?>
                        <div class="dashboard-empty-state">
                            Catatan pengambilan paket ini sudah final. Status, foto, dan keterangan tidak bisa diubah lagi dari akun penghuni.
                        </div>
                    <?php else: ?>
                        <form method="POST" enctype="multipart/form-data" class="form-shell">
                                <div class="mb-3 form-shell__full">
                                    <label class="form-label">Status Paket</label>
                                    <input type="text" class="form-control" value="Sudah Diambil" disabled>
                                    <span class="form-hint">Status akan langsung disimpan sebagai <strong>Sudah Diambil</strong> setelah formulir dikirim.</span>
                                </div>

                                <div class="mb-3 form-shell__full">
                                    <label class="form-label">Waktu Pengambilan</label>
                                    <input type="text" class="form-control"
                                        value="Akan diisi otomatis saat data disimpan" disabled>
                                </div>

                                <div class="mb-3 form-shell__full">
                                    <label for="fotoPengambilan" class="form-label">Foto Pengambilan</label>
                                    <input type="file" name="fotoPengambilan" class="form-control" id="fotoPengambilan"
                                        accept="image/png,image/jpeg,image/webp" <?= $fotoWajib ? 'required' : '' ?>>
                                    <div class="form-text">
                                        Upload JPG, PNG, atau WEBP maksimal 2MB.
                                        <?= $fotoWajib ? 'Foto wajib diunggah untuk pencatatan pertama.' : 'Kosongkan jika tidak ingin mengganti foto.' ?>
                                    </div>
                                </div>

                                <div class="mb-3 form-shell__full">
                                    <label for="keterangan" class="form-label">Keterangan <span class="tw:text-gray-400 tw:text-sm">(opsional)</span></label>
                                    <textarea name="keterangan" class="form-control" id="keterangan" rows="4"
                                        placeholder="Tambahkan catatan pengambilan paket jika diperlukan."><?= htmlspecialchars($keteranganValue) ?></textarea>
                                </div>

                                <div class="tw:w-full tw:flex tw:justify-end tw:mt-2">
                                    <button type="submit"
                                        class="tw:bg-secondary tw:w-full tw:text-white tw:px-3 tw:py-3 tw:rounded-xl tw:justify-center tw:hover:bg-accent tw:duration-300 tw:transition-all tw:inline-flex tw:items-center tw:gap-2">
                                        <span>Simpan Pengambilan</span>
                                    </button>
                                </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    <?php require '../../bootstrap.php'; ?>
    <?php require '../../validation_alert.php'; ?>
</body>

</html>
