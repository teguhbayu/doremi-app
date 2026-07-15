<?php
session_start();
require 'helpers.php';
paket_require_roles(['SIGAP']);
require '../../db.php';
require '../../utils/old_input.php';
require_once '../../database/paket.php';
require 'validation.php';

$paketId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$paketId) {
        paket_redirect('dashboard/paket/', 'error', 'Data paket tidak valid.');
}

$paket = fetchPaketDetail($db, $paketId);

if (!$paket) {
        paket_redirect('dashboard/paket/', 'error', 'Data paket tidak ditemukan.');
}

$penghuniList = fetchActivePenghuniOptions($db);
$selectedPenghuniLabel = '';
foreach ($penghuniList as $penghuniOption) {
    if ((int) $paket['PenghuniID'] === (int) $penghuniOption['PenghuniID']) {
        $selectedPenghuniLabel = paket_penghuni_option_label($penghuniOption);
        break;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paketInput = collectPaketInput($_POST);
    $validationMessage = validatePaketInput($db, $paketInput);
    if ($validationMessage !== null) {
        setOldFormInput($_POST);
        paket_redirect($_SERVER['PHP_SELF'] . '?id=' . $paketId, 'error', $validationMessage);
    }

    try {
        updatePaket(
            $db,
            $paketId,
            $paketInput['namaPengirim'],
            $paketInput['kurir'],
            $paketInput['jenisPaket'],
            $paketInput['waktuSampai'],
            (int) $paketInput['penghuniId']
        );
    } catch (RuntimeException) {
        setOldFormInput($_POST);
        paket_redirect($_SERVER['PHP_SELF'] . '?id=' . $paketId, 'error', 'Gagal memperbarui data paket.');
    }

        paket_redirect('dashboard/paket/', 'success', 'Data paket berhasil diperbarui.');
}

$old = pullOldFormInput();
if (!empty($old)) {
    $selectedPenghuniLabel = '';
    foreach ($penghuniList as $penghuniOption) {
        if ((int) ($old['penghuniId'] ?? 0) === (int) $penghuniOption['PenghuniID']) {
            $selectedPenghuniLabel = paket_penghuni_option_label($penghuniOption);
            break;
        }
    }
}
$formData = [
    'waktuSampai' => $old['waktuSampai'] ?? paket_datetime_input_value($paket['WaktuSampai']),
    'penghuniId' => $old['penghuniId'] ?? (int) $paket['PenghuniID'],
    'jenisPaket' => $old['jenisPaket'] ?? paket_type_label($paket['JenisPaket'] ?? null),
    'namaPengirim' => $old['namaPengirim'] ?? $paket['NamaPengirim'],
    'kurir' => $old['kurir'] ?? $paket['Kurir'],
];
?>

<!DOCTYPE html>
<html lang="en">
<?php require '../../head.php'; ?>

<body class="dashboard-body tw:p-0 tw:m-0 tw:relative tw:flex tw:min-h-screen">
    <?php require '../components/sidebar.php'; ?>
    <main class="tw:md:ml-75 tw:grow">
        <div class="tw:pt-20 tw:md:pt-8 tw:px-8 tw:mb-8 tw:flex-1 tw:w-dvw tw:md:w-full">
            <?php require dirname(__DIR__) . '/components/breadcrumb.php'; ?>
            <h1 class="page-title" data-kicker="Perbarui Data" data-subtitle="Sesuaikan penghuni tujuan, pengirim, kurir, atau waktu tiba paket tanpa meninggalkan alur kerja distribusi.">
                Edit Paket
            </h1>

            <div class="page-toolbar" data-note="Perubahan akan langsung memperbarui master paket">
                <a href="index.php" class="tw:inline-flex tw:items-center tw:justify-center tw:gap-2 tw:min-h-12 tw:px-4 tw:py-[0.85rem] tw:rounded-2xl tw:border tw:border-[rgba(22,60,122,0.12)] tw:font-extrabold tw:no-underline tw:text-slate-900 tw:bg-[rgba(255,255,255,0.82)] tw:hover:bg-gray-50 tw:transition-all tw:text-sm">
                    <i class="iconsax tw:text-xl" icon-name="arrow-left"></i>
                    <span>Kembali</span>
                </a>
            </div>

            <form method="POST" class="tw:grid tw:grid-cols-1 tw:lg:grid-cols-2 tw:gap-4 tw:p-[1.45rem] tw:rounded-[24px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm">
                <div class="mb-3">
                    <label for="petugasPencatat" class="form-label">Petugas Pencatat</label>
                    <input type="text" class="form-control" id="petugasPencatat"
                        value="<?= htmlspecialchars($paket['NamaPetugas']) ?>" disabled>
                </div>
                <div class="mb-3">
                    <label for="waktuSampai" class="form-label">Waktu Sampai</label>
                    <input type="datetime-local" name="waktuSampai" class="form-control" id="waktuSampai"
                        value="<?= htmlspecialchars($formData['waktuSampai']) ?>" required>
                </div>

                <div class="mb-3 tw:col-span-full">
                    <label for="penghuniId" class="form-label">Penghuni Tujuan</label>
                    <select name="penghuniId" id="penghuniId" class="form-select" required>
                        <option value="" disabled <?= empty($formData['penghuniId']) ? 'selected' : '' ?>>Pilih Penghuni</option>
                        <?php foreach ($penghuniList as $penghuni): ?>
                            <option value="<?= (int) $penghuni['PenghuniID'] ?>" <?= (int) $formData['penghuniId'] === (int) $penghuni['PenghuniID'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars(paket_penghuni_option_label($penghuni)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="form-hint">Pilih penghuni penerima paket dari daftar.</span>
                </div>

                <div class="mb-3">
                    <label for="jenisPaket" class="form-label">Tipe Kiriman</label>
                    <select name="jenisPaket" class="form-select" id="jenisPaket" required>
                        <?php foreach (paket_allowed_types() as $type): ?>
                            <option value="<?= htmlspecialchars($type) ?>" <?= $type === $formData['jenisPaket'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($type) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="form-hint">Pilih apakah kiriman ini berupa paket biasa atau dokumen.</span>
                </div>
                <div class="mb-3">
                    <label for="namaPengirim" class="form-label">Nama Pengirim</label>
                    <input type="text" name="namaPengirim" class="form-control" id="namaPengirim" maxlength="100"
                        value="<?= htmlspecialchars($formData['namaPengirim']) ?>" required>
                    <span class="form-hint">Maksimal 100 karakter</span>
                </div>
                <div class="mb-3">
                    <label for="kurir" class="form-label">Kurir</label>
                    <input type="text" name="kurir" class="form-control" id="kurir" maxlength="50"
                        value="<?= htmlspecialchars($formData['kurir']) ?>" required>
                    <span class="form-hint">Maksimal 50 karakter</span>
                </div>

                <div class="tw:col-span-full tw:flex tw:justify-end tw:mt-2">
                    <button type="submit"
                        class="tw:bg-secondary tw:w-full tw:text-white tw:px-3 tw:py-3 tw:rounded-xl tw:justify-center tw:hover:bg-accent tw:duration-300 tw:transition-all tw:inline-flex tw:items-center tw:gap-2">
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </main>
    <?php require '../../bootstrap.php'; ?>
    <?php require '../../validation_alert.php'; ?>

</body>

</html>
