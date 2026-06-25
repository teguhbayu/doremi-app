<?php
session_start();
require 'helpers.php';
paket_require_roles(['SIGAP']);
require '../../db.php';

$penghuniQuery = mysqli_query(
    $db,
    "SELECT p.PenghuniID, p.NamaPenghuni, p.Nim, k.NomorKamar
     FROM penghuni p
     LEFT JOIN kamar k ON p.KamarID = k.KamarID
     WHERE p.IsDeleted = 0
     ORDER BY p.NamaPenghuni"
);
$penghuniList = mysqli_fetch_all($penghuniQuery, MYSQLI_ASSOC);

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
        paket_redirect($_SERVER['PHP_SELF'], 'error', 'Data paket tidak valid.');
    }

    $stmt = mysqli_prepare($db, "SELECT PenghuniID FROM penghuni WHERE PenghuniID = ? AND IsDeleted = 0 LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $penghuniId);
    mysqli_stmt_execute($stmt);
    $penghuniResult = mysqli_stmt_get_result($stmt);
    $penghuni = mysqli_fetch_assoc($penghuniResult);
    mysqli_stmt_close($stmt);

    if (!$penghuni) {
        paket_redirect($_SERVER['PHP_SELF'], 'error', 'Penghuni tujuan tidak ditemukan.');
    }

    $petugasId = (int) $_SESSION['userId'];

    $stmt = mysqli_prepare($db, "INSERT INTO paket (PetugasID, NamaPengirim, Kurir, JenisPaket, WaktuSampai, PenghuniID) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'issssi', $petugasId, $namaPengirim, $kurir, $jenisPaket, $waktuSampai, $penghuniId);

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        paket_redirect($_SERVER['PHP_SELF'], 'error', 'Gagal menyimpan data paket.');
    }

    mysqli_stmt_close($stmt);
    paket_redirect('/doremi-app/dashboard/paket/', 'success', 'Data paket berhasil ditambahkan.');
}
?>

<!DOCTYPE html>
<html lang="en">
<?php require '../../head.php'; ?>

<body class="dashboard-body tw:p-0 tw:m-0 tw:relative tw:flex tw:bg-[#f8fafc] tw:min-h-screen">
    <?php require '../components/sidebar.php'; ?>
    <main class="dashboard-main tw:md:ml-[20.5rem] tw:grow">
        <div class="dashboard-page tw:pt-24 tw:md:pt-9 tw:px-4 tw:md:px-8 tw:pb-8 tw:w-full">
            <?php require dirname(__DIR__) . '/components/breadcrumb.php'; ?>
            <h1 class="page-title" data-kicker="Tambah Data" data-subtitle="Catat paket masuk beserta pengirim, kurir, waktu tiba, dan penghuni tujuan agar distribusi paket tetap rapi.">
                Tambah Paket
            </h1>

            <div class="page-toolbar" data-note="Form paket baru">
                <a href="index.php" class="page-secondary-btn">
                    <i class="iconsax tw:text-xl" icon-name="arrow-left-2"></i>
                    <span>Kembali</span>
                </a>
            </div>

            <form method="POST" class="form-shell">
                <div class="mb-3">
                    <label for="petugasPencatat" class="form-label">Petugas Pencatat</label>
                    <input type="text" class="form-control" id="petugasPencatat"
                        value="<?= htmlspecialchars($_SESSION['userName']) ?>" disabled>
                </div>
                <div class="mb-3">
                    <label for="waktuSampai" class="form-label">Waktu Sampai</label>
                    <input type="datetime-local" name="waktuSampai" class="form-control" id="waktuSampai"
                        value="<?= date('Y-m-d\TH:i') ?>" required>
                </div>

                <div class="mb-3 form-shell__full">
                    <label for="penghuniSearch" class="form-label">Penghuni Tujuan</label>
                    <input type="text" class="form-control" id="penghuniSearch" list="penghuniOptions"
                        placeholder="Ketik nama, NIM, atau kamar penghuni" autocomplete="off" required>
                    <input type="hidden" name="penghuniId" id="penghuniId">
                    <datalist id="penghuniOptions">
                        <?php foreach ($penghuniList as $penghuni): ?>
                            <option value="<?= htmlspecialchars(paket_penghuni_option_label($penghuni)) ?>" data-id="<?= (int) $penghuni['PenghuniID'] ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                </div>

                <div class="mb-3">
                    <label for="jenisPaket" class="form-label">Tipe Kiriman</label>
                    <select name="jenisPaket" class="form-select" id="jenisPaket" required>
                        <?php foreach (paket_allowed_types() as $type): ?>
                            <option value="<?= htmlspecialchars($type) ?>" <?= $type === 'Paket' ? 'selected' : '' ?>>
                                <?= htmlspecialchars($type) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="form-hint">Pilih apakah kiriman ini berupa paket biasa atau dokumen.</span>
                </div>
                <div class="mb-3">
                    <label for="namaPengirim" class="form-label">Nama Pengirim</label>
                    <input type="text" name="namaPengirim" class="form-control" id="namaPengirim" maxlength="100"
                        required>
                </div>
                <div class="mb-3">
                    <label for="kurir" class="form-label">Kurir</label>
                    <input type="text" name="kurir" class="form-control" id="kurir" maxlength="50" required>
                </div>

                <div class="tw:w-full tw:flex tw:justify-end tw:mt-2">
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
