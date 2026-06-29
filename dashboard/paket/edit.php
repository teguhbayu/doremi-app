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
    "SELECT pk.*, pt.NamaPetugas
     FROM paket pk
     JOIN petugas pt ON pk.PetugasID = pt.PetugasID
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

$penghuniQuery = mysqli_query(
    $db,
    "SELECT p.PenghuniID, p.NamaPenghuni, p.Nim, k.NomorKamar
     FROM penghuni p
     LEFT JOIN kamar k ON p.KamarID = k.KamarID
     WHERE p.IsDeleted = 0
     ORDER BY p.NamaPenghuni"
);
$penghuniList = mysqli_fetch_all($penghuniQuery, MYSQLI_ASSOC);
$selectedPenghuniLabel = '';
foreach ($penghuniList as $penghuniOption) {
    if ((int) $paket['PenghuniID'] === (int) $penghuniOption['PenghuniID']) {
        $selectedPenghuniLabel = paket_penghuni_option_label($penghuniOption);
        break;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jenisPaket = paket_normalize_type($_POST['jenisPaket'] ?? null);
    $namaPengirim = trim($_POST['namaPengirim'] ?? '');
    $kurir = trim($_POST['kurir'] ?? '');
    $penghuniId = filter_input(INPUT_POST, 'penghuniId', FILTER_VALIDATE_INT);
    $waktuSampai = paket_normalize_datetime($_POST['waktuSampai'] ?? '');

    if (
        $jenisPaket === null
        || !paket_is_valid_length($namaPengirim, 1, 100)
        || !paket_is_valid_length($kurir, 1, 50)
        || $penghuniId === false
        || $penghuniId === null
        || $waktuSampai === null
    ) {
        paket_redirect($_SERVER['PHP_SELF'] . '?id=' . $paketId, 'error', 'Data paket tidak valid.');
    }

    $stmt = mysqli_prepare($db, "SELECT PenghuniID FROM penghuni WHERE PenghuniID = ? AND IsDeleted = 0 LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $penghuniId);
    mysqli_stmt_execute($stmt);
    $penghuniResult = mysqli_stmt_get_result($stmt);
    $penghuni = mysqli_fetch_assoc($penghuniResult);
    mysqli_stmt_close($stmt);

    if (!$penghuni) {
        paket_redirect($_SERVER['PHP_SELF'] . '?id=' . $paketId, 'error', 'Penghuni tujuan tidak ditemukan.');
    }

    $stmt = mysqli_prepare($db, "UPDATE paket SET NamaPengirim = ?, Kurir = ?, JenisPaket = ?, WaktuSampai = ?, PenghuniID = ? WHERE PaketID = ?");
    mysqli_stmt_bind_param($stmt, 'ssssii', $namaPengirim, $kurir, $jenisPaket, $waktuSampai, $penghuniId, $paketId);

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        paket_redirect($_SERVER['PHP_SELF'] . '?id=' . $paketId, 'error', 'Gagal memperbarui data paket.');
    }

    mysqli_stmt_close($stmt);
    paket_redirect('/doremi-app/dashboard/paket/', 'success', 'Data paket berhasil diperbarui.');
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
            <h1 class="page-title" data-kicker="Perbarui Data" data-subtitle="Sesuaikan penghuni tujuan, pengirim, kurir, atau waktu tiba paket tanpa meninggalkan alur kerja distribusi.">
                Edit Paket
            </h1>

            <div class="page-toolbar" data-note="Perubahan akan langsung memperbarui master paket">
                <a href="index.php" class="tw:inline-flex tw:items-center tw:justify-center tw:gap-2 tw:min-h-12 tw:px-4 tw:py-[0.85rem] tw:rounded-2xl tw:border tw:border-[rgba(22,60,122,0.12)] tw:font-extrabold tw:no-underline tw:text-slate-900 tw:bg-[rgba(255,255,255,0.82)] tw:hover:bg-gray-50 tw:transition-all tw:text-sm">
                    <i class="iconsax tw:text-xl" icon-name="arrow-left-2"></i>
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
                        value="<?= htmlspecialchars(paket_datetime_input_value($paket['WaktuSampai'])) ?>" required>
                </div>

                <div class="mb-3 tw:col-span-full">
                    <label for="penghuniSearch" class="form-label">Penghuni Tujuan</label>
                    <input type="text" class="form-control" id="penghuniSearch" list="penghuniOptions"
                        value="<?= htmlspecialchars($selectedPenghuniLabel) ?>" placeholder="Ketik nama, NIM, atau kamar penghuni" autocomplete="off" required>
                    <input type="hidden" name="penghuniId" id="penghuniId" value="<?= (int) $paket['PenghuniID'] ?>">
                    <datalist id="penghuniOptions">
                        <?php foreach ($penghuniList as $penghuni): ?>
                            <option value="<?= htmlspecialchars(paket_penghuni_option_label($penghuni)) ?>" data-id="<?= (int) $penghuni['PenghuniID'] ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                    <span class="form-hint">Ketik untuk mencari penghuni lebih cepat tanpa harus scroll daftar panjang.</span>
                </div>

                <div class="mb-3">
                    <label for="jenisPaket" class="form-label">Tipe Kiriman</label>
                    <select name="jenisPaket" class="form-select" id="jenisPaket" required>
                        <?php $selectedJenisPaket = paket_type_label($paket['JenisPaket'] ?? null); ?>
                        <?php foreach (paket_allowed_types() as $type): ?>
                            <option value="<?= htmlspecialchars($type) ?>" <?= $type === $selectedJenisPaket ? 'selected' : '' ?>>
                                <?= htmlspecialchars($type) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="form-hint">Pilih apakah kiriman ini berupa paket biasa atau dokumen.</span>
                </div>
                <div class="mb-3">
                    <label for="namaPengirim" class="form-label">Nama Pengirim</label>
                    <input type="text" name="namaPengirim" class="form-control" id="namaPengirim" maxlength="100"
                        value="<?= htmlspecialchars($paket['NamaPengirim']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="kurir" class="form-label">Kurir</label>
                    <input type="text" name="kurir" class="form-control" id="kurir" maxlength="50"
                        value="<?= htmlspecialchars($paket['Kurir']) ?>" required>
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

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const penghuniSearch = document.getElementById('penghuniSearch');
            const penghuniId = document.getElementById('penghuniId');
            const penghuniOptions = Array.from(document.querySelectorAll('#penghuniOptions option'));

            const syncPenghuniSelection = () => {
                const match = penghuniOptions.find(option => option.value === penghuniSearch.value);
                penghuniId.value = match ? match.dataset.id : '';
            };

            penghuniSearch.addEventListener('input', syncPenghuniSelection);
            penghuniSearch.form?.addEventListener('submit', syncPenghuniSelection);
        });
    </script>
</body>

</html>
