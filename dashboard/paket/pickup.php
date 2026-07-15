<?php
session_start();
require 'helpers.php';
paket_require_roles(['PENGHUNI']);
require '../../db.php';
require_once '../../database/paket.php';
require_once '../../utils/format.php';
require_once '../../utils/old_input.php';

$paketId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$paketId) {
    paket_redirect('/doremi-app/dashboard/paket/', 'error', 'Data paket tidak valid.');
}

$userId = (int) $_SESSION['userId'];
$paket = fetchPaketWithLatestPickup($db, $paketId, $userId);

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
        setOldFormInput($_POST);
        paket_redirect($_SERVER['PHP_SELF'] . '?id=' . $paketId, 'error', 'Data pengambilan paket tidak valid.');
    }

    try {
        $fotoPengambilan = paket_store_photo(
            $_FILES['fotoPengambilan'] ?? [],
            $paket['FotoPengambilan'] ?? null
        );
    } catch (RuntimeException $exception) {
        setOldFormInput($_POST);
        paket_redirect($_SERVER['PHP_SELF'] . '?id=' . $paketId, 'error', $exception->getMessage());
    }

    $keterangan = $keterangan !== '' ? $keterangan : '-';

    $pengambilanPaketId = !empty($paket['PengambilanPaketID']) ? (int) $paket['PengambilanPaketID'] : null;
    $successMessage = $pengambilanPaketId !== null
        ? 'Catatan pengambilan paket berhasil dilengkapi.'
        : 'Pengambilan paket berhasil dicatat.';

    try {
        savePaketPickup($db, $pengambilanPaketId, $paketId, $userId, $petugasId, $fotoPengambilan, $waktuPengambilan, $status, $keterangan);
    } catch (RuntimeException) {
        setOldFormInput($_POST);
        paket_redirect($_SERVER['PHP_SELF'] . '?id=' . $paketId, 'error', 'Gagal menyimpan data pengambilan paket.');
    }

    paket_redirect('/doremi-app/dashboard/paket/', 'success', $successMessage);
}

$old = pullOldFormInput();
$existingKeterangan = trim((string) ($paket['Keterangan'] ?? ''));
$keteranganValue = $old['keterangan'] ?? ($existingKeterangan === '-' ? '' : $existingKeterangan);
$fotoWajib = empty($paket['FotoPengambilan']);
$statusMeta = paket_status_meta($paket['Status'] ?? 'Belum Diambil');
?>

<!DOCTYPE html>
<html lang="en">
<?php require '../../head.php'; ?>

<body class="dashboard-body tw:p-0 tw:m-0 tw:relative tw:flex tw:min-h-screen">
    <?php require '../components/sidebar.php'; ?>
    <main class="tw:md:ml-75 tw:grow">
        <div class="tw:pt-20 tw:md:pt-8 tw:px-8 tw:mb-8 tw:flex-1 tw:w-dvw tw:md:w-full">
            <?php require dirname(__DIR__) . '/components/breadcrumb.php'; ?>
            <h1 class="page-title" data-kicker="Pengambilan Paket" data-subtitle="<?= $isLocked ? 'Lihat kembali bukti foto dan catatan pengambilan paket yang sudah tercatat.' : 'Lengkapi status, bukti foto, dan catatan pengambilan agar riwayat paket penghuni tercatat rapi.' ?>">
                <?= $isLocked ? 'Detail Pengambilan Paket' : 'Catat Pengambilan Paket' ?>
            </h1>

            <div class="page-toolbar" data-note="<?= $isLocked ? 'Catatan pengambilan sudah final' : 'Pencatatan pengambilan pertama' ?>">
                <a href="index.php" class="tw:inline-flex tw:items-center tw:justify-center tw:gap-2 tw:min-h-12 tw:px-4 tw:py-[0.85rem] tw:rounded-2xl tw:border tw:border-[rgba(22,60,122,0.12)] tw:font-extrabold tw:no-underline tw:text-slate-900 tw:bg-[rgba(255,255,255,0.82)] tw:hover:bg-gray-50 tw:transition-all tw:text-sm">
                    <i class="iconsax tw:text-xl" icon-name="arrow-left"></i>
                    <span>Kembali</span>
                </a>
            </div>

            <div class="tw:grid tw:grid-cols-1 tw:lg:grid-cols-<?= $isLocked ? '1' : '3' ?> tw:gap-8">
                <div class="tw:lg:col-span-1">
                    <div class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm tw:h-full">
                        <h5 class="tw:m-0 tw:text-[1.2rem] tw:text-slate-900">Informasi Paket</h5>
                        <div class="tw:grid tw:gap-[0.85rem] <?= $isLocked ? 'tw:grid-cols-1 tw:sm:grid-cols-2' : 'tw:grid-cols-1' ?>">
                            <div class="tw:p-4 tw:rounded-[18px] tw:bg-[rgba(255,255,255,0.80)] tw:border tw:border-[rgba(22,60,122,0.08)]">
                                <span class="tw:block tw:mb-[0.3rem] tw:text-slate-500 tw:text-xs tw:font-bold">Tipe Kiriman</span>
                                <strong><?= htmlspecialchars(paket_type_label($paket['JenisPaket'] ?? null)) ?></strong>
                            </div>
                            <div class="tw:p-4 tw:rounded-[18px] tw:bg-[rgba(255,255,255,0.80)] tw:border tw:border-[rgba(22,60,122,0.08)]">
                                <span class="tw:block tw:mb-[0.3rem] tw:text-slate-500 tw:text-xs tw:font-bold">Pengirim</span>
                                <strong><?= htmlspecialchars($paket['NamaPengirim']) ?></strong>
                                <p><?= htmlspecialchars($paket['Kurir']) ?></p>
                            </div>
                            <div class="tw:p-4 tw:rounded-[18px] tw:bg-[rgba(255,255,255,0.80)] tw:border tw:border-[rgba(22,60,122,0.08)]">
                                <span class="tw:block tw:mb-[0.3rem] tw:text-slate-500 tw:text-xs tw:font-bold">Waktu Sampai</span>
                                <strong><?= formatDateTime($paket['WaktuSampai'] ?? null) ?></strong>
                                <p>Pencatat: <?= htmlspecialchars($paket['NamaPetugasPaket']) ?></p>
                            </div>
                            <div class="tw:p-4 tw:rounded-[18px] tw:bg-[rgba(255,255,255,0.80)] tw:border tw:border-[rgba(22,60,122,0.08)]">
                                <span class="tw:block tw:mb-[0.3rem] tw:text-slate-500 tw:text-xs tw:font-bold">Penghuni</span>
                                <strong><?= htmlspecialchars($paket['NamaPenghuni']) ?></strong>
                                <p><?= htmlspecialchars($paket['Nim']) ?><?= !empty($paket['NomorKamar']) ? ' | Kamar ' . htmlspecialchars($paket['NomorKamar']) : '' ?></p>
                            </div>
                            <div class="tw:p-4 tw:rounded-[18px] tw:bg-[rgba(255,255,255,0.80)] tw:border tw:border-[rgba(22,60,122,0.08)]">
                                <span class="tw:block tw:mb-[0.3rem] tw:text-slate-500 tw:text-xs tw:font-bold">Status Saat Ini</span>
                                <span class="badge <?= htmlspecialchars($statusMeta['class']) ?>">
                                    <?= htmlspecialchars($statusMeta['label']) ?>
                                </span>
                                <p><?= !empty($paket['WaktuPengambilan']) ? formatDateTime($paket['WaktuPengambilan']) : 'Belum ada waktu pengambilan tercatat' ?></p>
                            </div>
                            <?php if (!empty($paket['FotoPengambilan'])): ?>
                                <div class="tw:p-4 tw:rounded-[18px] tw:bg-[rgba(255,255,255,0.80)] tw:border tw:border-[rgba(22,60,122,0.08)]">
                                    <span class="tw:block tw:mb-[0.3rem] tw:text-slate-500 tw:text-xs tw:font-bold">Foto Pengambilan</span>
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

                <?php if (!$isLocked): ?>
                    <div class="tw:lg:col-span-2">
                        <form method="POST" enctype="multipart/form-data" class="tw:grid tw:grid-cols-1 tw:lg:grid-cols-2 tw:gap-4 tw:p-[1.45rem] tw:rounded-[24px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm">
                                <div class="mb-3 tw:col-span-full">
                                    <label class="form-label">Status Paket</label>
                                    <input type="text" class="form-control" value="Sudah Diambil" disabled>
                                    <span class="form-hint">Status akan langsung disimpan sebagai <strong>Sudah Diambil</strong> setelah formulir dikirim.</span>
                                </div>

                                <div class="mb-3 tw:col-span-full">
                                    <label class="form-label">Waktu Pengambilan</label>
                                    <input type="text" class="form-control"
                                        value="Akan diisi otomatis saat data disimpan" disabled>
                                </div>

                                <div class="mb-3 tw:col-span-full">
                                    <label for="fotoPengambilan" class="form-label">Foto Pengambilan</label>
                                    <input type="file" name="fotoPengambilan" class="form-control" id="fotoPengambilan"
                                        accept="image/png,image/jpeg,image/webp" <?= $fotoWajib ? 'required' : '' ?>>
                                    <div class="form-text">
                                        Upload JPG, PNG, atau WEBP maksimal 2MB.
                                        <?= $fotoWajib ? 'Foto wajib diunggah untuk pencatatan pertama.' : 'Kosongkan jika tidak ingin mengganti foto.' ?>
                                    </div>
                                </div>

                                <div class="mb-3 tw:col-span-full">
                                    <label for="keterangan" class="form-label">Keterangan <span class="tw:text-gray-400 tw:text-sm">(opsional)</span></label>
                                    <textarea name="keterangan" class="form-control" id="keterangan" rows="4"
                                        placeholder="Tambahkan catatan pengambilan paket jika diperlukan."><?= htmlspecialchars($keteranganValue) ?></textarea>
                                    <span class="form-hint">Boleh dikosongkan, tidak wajib diisi.</span>
                                </div>

                                <div class="tw:col-span-full tw:flex tw:justify-end tw:mt-2">
                                    <button type="submit"
                                        class="tw:bg-secondary tw:w-full tw:text-white tw:px-3 tw:py-3 tw:rounded-xl tw:justify-center tw:hover:bg-accent tw:duration-300 tw:transition-all tw:inline-flex tw:items-center tw:gap-2">
                                        <span>Simpan Pengambilan</span>
                                    </button>
                                </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <?php require '../../bootstrap.php'; ?>
    <?php require '../../validation_alert.php'; ?>
</body>

</html>
