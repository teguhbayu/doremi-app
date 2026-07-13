<?php
require '../../vendor/autoload.php';

use Respect\Validation\Validator as v;
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: /doremi-app/login.php");
    exit;
}
require '../../db.php';
require '../../utils/old_input.php';
require_once '../../utils/validation_helpers.php';
require 'helpers.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: /doremi-app/dashboard/kamar/");
    exit;
}

$stmt = mysqli_prepare($db, "SELECT * FROM kamar WHERE KamarID = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$kamar = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$kamar) {
    header("Location: /doremi-app/dashboard/kamar/");
    exit;
}

$occupancyStmt = mysqli_prepare($db, "SELECT COUNT(*) AS total FROM penghuni WHERE KamarID = ? AND IsDeleted = 0");
mysqli_stmt_bind_param($occupancyStmt, 'i', $id);
mysqli_stmt_execute($occupancyStmt);
$occupancyResult = mysqli_stmt_get_result($occupancyStmt);
$currentOccupancy = (int) (mysqli_fetch_assoc($occupancyResult)['total'] ?? 0);
mysqli_stmt_close($occupancyStmt);

$normalizedCurrentNomor = kamar_normalize_segment((string) ($kamar['NomorKamar'] ?? ''));
$currentLantai = trim((string) ($kamar['Lantai'] ?? ''));
$usesGeneratedNomor = kamar_has_lantai_prefix($normalizedCurrentNomor, $currentLantai);
$bagianKamarValue = kamar_extract_bagian($normalizedCurrentNomor, $currentLantai);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bagianKamar = kamar_normalize_segment((string) ($_POST['bagianKamar'] ?? ''));
    $kapasitas = trim($_POST['kapasitasKamar'] ?? '');
    $lantai = trim($_POST['lantaiKamar'] ?? '');
    $nomor = kamar_build_nomor($lantai, $bagianKamar);

    if (!$usesGeneratedNomor && $bagianKamar === $normalizedCurrentNomor && $lantai === $currentLantai) {
        $nomor = $normalizedCurrentNomor;
    }

    $postData = [
        'bagian' => $bagianKamar,
        'kapasitas' => $kapasitas,
        'lantai' => $lantai,
    ];

    $fieldError = firstFieldError($postData, [
        'lantai' => ['label' => 'Lantai', 'rule' => v::in(kamar_allowed_floors())],
        'bagian' => ['label' => 'Bagian Kamar', 'rule' => v::regex('/^[A-Z0-9-]{1,19}$/')],
        'kapasitas' => ['label' => 'Kapasitas Kamar', 'rule' => v::digit()],
    ]);

    if ($fieldError !== null) {
        setOldFormInput($_POST);
        header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $id . '&status=error&message=' . urlencode($fieldError));
        exit;
    }

    $kapasitasInt = (int) $kapasitas;
    if ($kapasitasInt < 1 || $kapasitasInt > 4) {
        setOldFormInput($_POST);
        header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $id . '&status=error&message=Kapasitas kamar minimal 1 dan maksimal 4 penghuni!');
        exit;
    }

    if ($kapasitasInt < $currentOccupancy) {
        setOldFormInput($_POST);
        header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $id . '&status=error&message=Kapasitas kamar tidak boleh lebih kecil dari jumlah penghuni saat ini!');
        exit;
    }

    $checkStmt = mysqli_prepare($db, "SELECT KamarID FROM kamar WHERE IsDeleted = 0 AND UPPER(REPLACE(NomorKamar, ' ', '')) = ? AND KamarID != ? LIMIT 1");
    mysqli_stmt_bind_param($checkStmt, 'si', $nomor, $id);
    mysqli_stmt_execute($checkStmt);
    $checkResult = mysqli_stmt_get_result($checkStmt);
    $existingKamar = mysqli_fetch_assoc($checkResult);
    mysqli_stmt_close($checkStmt);

    if ($existingKamar) {
        setOldFormInput($_POST);
        header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $id . '&status=error&message=Nomor kamar sudah terdaftar!');
        exit;
    }

    $now = date('Y-m-d H:i:s');

    $stmt = mysqli_prepare($db, "UPDATE kamar SET NomorKamar = ?, KapasitasPenghuni = ?, Lantai = ?, UpdatedAt = ? WHERE KamarID = ?");
    mysqli_stmt_bind_param($stmt, 'sissi', $nomor, $kapasitasInt, $lantai, $now, $id);

    if (!mysqli_stmt_execute($stmt)) {
        setOldFormInput($_POST);
        header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $id . '&status=error&message=Terjadi Kesalahan saat mengupdate data!');
        mysqli_stmt_close($stmt);
        exit;
    }

    mysqli_stmt_close($stmt);

    header("Location: /doremi-app/dashboard/kamar/?status=success&message=Kamar Berhasil Diupdate!");
    exit;
}

$old = pullOldFormInput();
$kamarState = [
    'lantai' => $old['lantaiKamar'] ?? $currentLantai,
    'bagian' => $old['bagianKamar'] ?? $bagianKamarValue,
    'initialNomor' => $normalizedCurrentNomor,
    'initialBagian' => $bagianKamarValue,
    'initialLantai' => $currentLantai,
    'preserveLegacy' => !$usesGeneratedNomor,
];
?>

<!DOCTYPE html>
<html lang="en">
<?php require '../../head.php'; ?>

<body class="dashboard-body tw:p-0 tw:m-0 tw:relative tw:flex">
    <?php require '../components/sidebar.php'; ?>
    <main class="tw:md:ml-75 tw:grow">
        <div class="tw:pt-20 tw:md:pt-5 tw:px-5 tw:mb-8 tw:flex-1 tw:w-dvw tw:md:w-full">
            <?php require dirname(__DIR__) . '/components/breadcrumb.php'; ?>
            <h1 class="page-title" data-kicker="Perbarui Data" data-subtitle="Atur ulang nomor, kapasitas, atau lantai kamar sambil tetap menjaga konsistensi okupansi.">
                Edit Kamar
            </h1>
            <div class="page-toolbar" data-note="Perubahan tidak boleh lebih kecil dari okupansi saat ini">
                <a href="index.php" class="tw:inline-flex tw:items-center tw:justify-center tw:gap-2 tw:min-h-12 tw:px-4 tw:py-[0.85rem] tw:rounded-2xl tw:border tw:border-[rgba(22,60,122,0.12)] tw:font-extrabold tw:no-underline tw:text-slate-900 tw:bg-[rgba(255,255,255,0.82)] tw:hover:bg-gray-50 tw:transition-all tw:text-sm">
                    <i class="iconsax" icon-name="arrow-left"></i>
                    <span>Kembali ke daftar</span>
                </a>
            </div>

            <form method="POST" class="tw:grid tw:grid-cols-1 tw:lg:grid-cols-2 tw:gap-4 tw:p-[1.45rem] tw:rounded-[24px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm"
                x-data='<?= json_encode($kamarState, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>
                <div class="mb-3">
                    <label for="lantaiKamar" class="form-label">Lantai</label>
                    <select class="form-select" name="lantaiKamar" id="lantaiKamar" x-model="lantai" required>
                        <option value="" disabled>Pilih Lantai</option>
                        <option value="1">Lantai 1</option>
                        <option value="2">Lantai 2</option>
                        <option value="3">Lantai 3</option>
                        <option value="4">Lantai 4</option>
                        <option value="5">Lantai 5</option>
                        <option value="6">Lantai 6</option>
                        <option value="7">Lantai 7</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="bagianKamar" class="form-label">Bagian Kamar</label>
                    <input type="text" name="bagianKamar" class="form-control" id="bagianKamar" maxlength="19"
                        x-model="bagian" @input="bagian = bagian.replace(/\s+/g, '').toUpperCase()" required>
                </div>
                <div class="mb-3">
                    <label for="nomorKamarPreview" class="form-label">Nomor Kamar</label>
                    <input type="text" class="form-control" id="nomorKamarPreview"
                        :value="(preserveLegacy && bagian === initialBagian && lantai === initialLantai) ? initialNomor : (lantai && bagian ? lantai + bagian : '')"
                        readonly>
                </div>
                <div class="mb-3">
                    <label for="kapasitasKamar" class="form-label">Kapasitas Kamar</label>
                    <input type="number" name="kapasitasKamar" class="form-control" id="kapasitasKamar"
                        value="<?= htmlspecialchars($old['kapasitasKamar'] ?? $kamar['KapasitasPenghuni']) ?>" min="1" max="4" required>
                    <div class="form-text">
                        Saat ini kamar ditempati <?= $currentOccupancy ?> penghuni. Kapasitas minimal 1 dan maksimal 4
                        penghuni.
                    </div>
                </div>
                <div class="tw:col-span-full tw:flex tw:justify-end tw:mt-2">
                    <button type="submit"
                        class="tw:bg-secondary tw:w-full tw:text-white tw:px-3 tw:py-2 tw:rounded-xl tw:justify-center tw:hover:bg-accent tw:duration-300 tw:transition-all tw:inline-flex tw:items-center tw:gap-2">
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
