<?php
session_start();
require 'helpers.php';
paket_require_roles(['SIGAP']);
require '../../db.php';
require '../../utils/old_input.php';
require_once '../../database/paket.php';
require 'validation.php';

$penghuniList = fetchActivePenghuniOptions($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paketInput = collectPaketInput($_POST);
    $validationMessage = validatePaketInput($db, $paketInput);
    if ($validationMessage !== null) {
        setOldFormInput($_POST);
        paket_redirect($_SERVER['PHP_SELF'], 'error', $validationMessage);
    }

    $petugasId = (int) $_SESSION['userId'];

    try {
        createPaket(
            $db,
            $petugasId,
            $paketInput['namaPengirim'],
            $paketInput['kurir'],
            $paketInput['jenisPaket'],
            $paketInput['waktuSampai'],
            (int) $paketInput['penghuniId']
        );
    } catch (RuntimeException) {
        setOldFormInput($_POST);
        paket_redirect($_SERVER['PHP_SELF'], 'error', 'Gagal menyimpan data paket.');
    }

    paket_redirect('/doremi-app/dashboard/paket/', 'success', 'Data paket berhasil ditambahkan.');
}

$old = pullOldFormInput();
$oldPenghuniLabel = '';
if (!empty($old['penghuniId'])) {
    foreach ($penghuniList as $penghuni) {
        if ((int) $penghuni['PenghuniID'] === (int) $old['penghuniId']) {
            $oldPenghuniLabel = paket_penghuni_option_label($penghuni);
            break;
        }
    }
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
            <h1 class="page-title" data-kicker="Tambah Data" data-subtitle="Catat paket masuk beserta pengirim, kurir, waktu tiba, dan penghuni tujuan agar distribusi paket tetap rapi.">
                Tambah Paket
            </h1>

            <div class="page-toolbar" data-note="Form paket baru">
                <a href="index.php" class="tw:inline-flex tw:items-center tw:justify-center tw:gap-2 tw:min-h-12 tw:px-4 tw:py-[0.85rem] tw:rounded-2xl tw:border tw:border-[rgba(22,60,122,0.12)] tw:font-extrabold tw:no-underline tw:text-slate-900 tw:bg-[rgba(255,255,255,0.82)] tw:hover:bg-gray-50 tw:transition-all tw:text-sm">
                    <i class="iconsax tw:text-xl" icon-name="arrow-left"></i>
                    <span>Kembali</span>
                </a>
            </div>

            <form method="POST" class="tw:grid tw:grid-cols-1 tw:lg:grid-cols-2 tw:gap-4 tw:p-[1.45rem] tw:rounded-[24px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm">
                <div class="mb-3">
                    <label for="petugasPencatat" class="form-label">Petugas Pencatat</label>
                    <input type="text" class="form-control" id="petugasPencatat"
                        value="<?= htmlspecialchars($_SESSION['userName']) ?>" disabled>
                </div>
                <div class="mb-3">
                    <label for="waktuSampai" class="form-label">Waktu Sampai</label>
                    <input type="datetime-local" name="waktuSampai" class="form-control" id="waktuSampai"
                        value="<?= htmlspecialchars($old['waktuSampai'] ?? date('Y-m-d\TH:i')) ?>" required>
                </div>

                <div class="mb-3 tw:col-span-full">
                    <label for="penghuniId" class="form-label">Penghuni Tujuan</label>
                    <select name="penghuniId" id="penghuniId" class="form-select" required>
                        <option value="" disabled <?= empty($old['penghuniId']) ? 'selected' : '' ?>>Pilih Penghuni</option>
                        <?php foreach ($penghuniList as $penghuni): ?>
                            <option value="<?= (int) $penghuni['PenghuniID'] ?>" <?= (string) ($old['penghuniId'] ?? '') === (string) $penghuni['PenghuniID'] ? 'selected' : '' ?>>
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
                            <option value="<?= htmlspecialchars($type) ?>" <?= ($old['jenisPaket'] ?? 'Paket') === $type ? 'selected' : '' ?>>
                                <?= htmlspecialchars($type) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="form-hint">Pilih apakah kiriman ini berupa paket biasa atau dokumen.</span>
                </div>
                <div class="mb-3">
                    <label for="namaPengirim" class="form-label">Nama Pengirim</label>
                    <input type="text" name="namaPengirim" class="form-control" id="namaPengirim" maxlength="100"
                        value="<?= htmlspecialchars($old['namaPengirim'] ?? '') ?>" required>
                    <span class="form-hint">Maksimal 100 karakter</span>
                </div>
                <div class="mb-3">
                    <label for="kurir" class="form-label">Kurir</label>
                    <input type="text" name="kurir" class="form-control" id="kurir" maxlength="50"
                        value="<?= htmlspecialchars($old['kurir'] ?? '') ?>" required>
                    <span class="form-hint">Maksimal 50 karakter</span>
                </div>

                <div class="tw:col-span-full tw:flex tw:justify-end tw:mt-2">
                    <button type="submit"
                        class="tw:bg-secondary tw:w-full tw:text-white tw:px-3 tw:py-3 tw:rounded-xl tw:justify-center tw:hover:bg-accent tw:duration-300 tw:transition-all tw:inline-flex tw:items-center tw:gap-2">
                        <span>Simpan Paket</span>
                    </button>
                </div>
            </form>
        </div>
    </main>
    <?php require '../../bootstrap.php'; ?>
    <?php require '../../validation_alert.php'; ?>

</body>

</html>
