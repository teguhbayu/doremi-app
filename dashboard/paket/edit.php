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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $namaPengirim = trim($_POST['namaPengirim'] ?? '');
    $kurir = trim($_POST['kurir'] ?? '');
    $penghuniId = filter_input(INPUT_POST, 'penghuniId', FILTER_VALIDATE_INT);
    $waktuSampai = paket_normalize_datetime($_POST['waktuSampai'] ?? '');

    if (
        !paket_is_valid_length($namaPengirim, 1, 100)
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

    $stmt = mysqli_prepare($db, "UPDATE paket SET NamaPengirim = ?, Kurir = ?, WaktuSampai = ?, PenghuniID = ? WHERE PaketID = ?");
    mysqli_stmt_bind_param($stmt, 'sssii', $namaPengirim, $kurir, $waktuSampai, $penghuniId, $paketId);

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

<body class="tw:p-0 tw:m-0 relative tw:flex tw:bg-[#f8fafc] tw:min-h-screen">
    <?php require '../components/sidebar.php'; ?>
    <main class="tw:md:ml-75 tw:grow">
        <div class="tw:pt-20 tw:md:pt-8 tw:px-8 tw:mb-8 tw:flex-1 tw:w-dvw tw:md:w-full">
            <div class="tw:flex tw:flex-col tw:gap-4 tw:md:flex-row tw:md:items-center tw:md:justify-between tw:mb-8">
                <div>
                    <h1 class="tw:font-bold tw:text-4xl tw:text-slate-900 tw:m-0">Edit Paket</h1>
                    <p class="tw:text-slate-500 tw:mt-2 tw:mb-0">Perbarui data paket tanpa mengubah struktur PDM.</p>
                </div>
                <a href="index.php"
                    class="tw:bg-white tw:text-slate-700 tw:px-4 tw:py-3 tw:rounded-xl tw:border tw:border-slate-200 tw:hover:bg-slate-50 tw:transition-all tw:inline-flex tw:items-center tw:gap-2 tw:no-underline">
                    <i class="iconsax tw:text-xl" icon-name="arrow-left-2"></i>
                    <span>Kembali</span>
                </a>
            </div>

            <div class="tw:bg-white tw:p-6 tw:rounded-[24px] tw:shadow-sm tw:border tw:border-gray-100">
                <form method="POST">
                    <div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-3 tw:gap-4">
                        <div class="mb-3">
                            <label for="paketID" class="form-label">ID Paket</label>
                            <input type="text" class="form-control" id="paketID"
                                value="<?= htmlspecialchars($paket['PaketID']) ?>" disabled>
                        </div>
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
                    </div>

                    <div class="mb-3">
                        <label for="penghuniId" class="form-label">Penghuni Tujuan</label>
                        <select class="form-select" name="penghuniId" id="penghuniId" required>
                            <?php foreach ($penghuniList as $penghuni): ?>
                                <option value="<?= (int) $penghuni['PenghuniID'] ?>" <?= (int) $paket['PenghuniID'] === (int) $penghuni['PenghuniID'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($penghuni['NamaPenghuni']) ?>
                                    (<?= htmlspecialchars($penghuni['Nim']) ?>)
                                    <?= !empty($penghuni['NomorKamar']) ? '- Kamar ' . htmlspecialchars($penghuni['NomorKamar']) : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-2 tw:gap-4">
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
                    </div>

                    <div class="tw:w-full tw:flex tw:justify-end tw:mt-2">
                        <button type="submit"
                            class="tw:bg-secondary tw:w-full tw:text-white tw:px-3 tw:py-3 tw:rounded-xl tw:justify-center tw:hover:bg-accent tw:duration-300 tw:transition-all tw:inline-flex tw:items-center tw:gap-2">
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <?php require '../../bootstrap.php'; ?>
    <?php require '../../validation_alert.php'; ?>
</body>

</html>
