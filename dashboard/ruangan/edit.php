<?php
require '../../vendor/autoload.php';

use Respect\Validation\Validator as v;
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: /doremi-app/login.php");
    exit;
}
if ($_SESSION['userRole'] !== 'PENGURUS') {
    header("Location: /doremi-app/dashboard/");
    exit;
}
require '../../csrf.php';
require '../../db.php';
require '../../utils/old_input.php';
require_once '../../utils/validation_helpers.php';

$ruanganTypes = ['Ruang Ibadah', 'Ruang Publik', 'Ruang Jemur', 'Lapangan Olahraga', 'Balkon', 'Kamar Mandi'];

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: /doremi-app/dashboard/ruangan/");
    exit;
}

$stmt = mysqli_prepare($db, "CALL sp_getRuanganById(?)");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$ruangan = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$ruangan) {
    header("Location: /doremi-app/dashboard/ruangan/");
    exit;
}

if (!in_array($ruangan['JenisRuangan'], $ruanganTypes, true)) {
    $ruanganTypes[] = $ruangan['JenisRuangan'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate($_SERVER['PHP_SELF'] . '?id=' . $id);
    $nama = trim($_POST['namaRuangan'] ?? '');
    $jenis = trim($_POST['jenisRuangan'] ?? '');
    $lantai = trim($_POST['lantaiRuangan'] ?? '');
    $keterangan = trim($_POST['keteranganRuangan'] ?? '');

    $postData = [
        'nama' => $nama,
        'jenis' => $jenis,
        'lantai' => $lantai,
        'keterangan' => $keterangan,
    ];

    $fieldError = firstFieldError($postData, [
        'nama' => ['label' => 'Nama Ruangan', 'rule' => v::stringType()->length(1, 100)],
        'jenis' => ['label' => 'Jenis Ruangan', 'rule' => v::in($ruanganTypes)],
        'lantai' => ['label' => 'Lantai', 'rule' => v::in(['1', '2', '3', '4', '5', '6', '7', '1 Gedung Sekretariat', '2 Gedung Sekretariat'])],
        'keterangan' => ['label' => 'Keterangan', 'rule' => v::stringType()->length(0, 500)],
    ]);

    if ($fieldError !== null) {
        setOldFormInput($postData);
        header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $id . '&status=error&message=' . urlencode($fieldError));
        exit;
    }

    $now = date('Y-m-d H:i:s');

    $stmt = mysqli_prepare($db, "UPDATE ruangan SET NamaRuangan = ?, JenisRuangan = ?, Lantai = ?, Keterangan = ?, UpdatedAt = ? WHERE RuanganID = ?");
    mysqli_stmt_bind_param($stmt, 'sssssi', $nama, $jenis, $lantai, $keterangan, $now, $id);

    if (!mysqli_stmt_execute($stmt)) {
        setOldFormInput($postData);
        header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $id . '&status=error&message=Terjadi Kesalahan saat mengupdate data!');
        mysqli_stmt_close($stmt);
        exit;
    }

    mysqli_stmt_close($stmt);

    header("Location: /doremi-app/dashboard/ruangan/?status=success&message=Ruangan Berhasil Diupdate!");
    exit;
}

$old = pullOldFormInput();
$formData = [
    'nama' => $old['nama'] ?? $ruangan['NamaRuangan'],
    'jenis' => $old['jenis'] ?? $ruangan['JenisRuangan'],
    'lantai' => $old['lantai'] ?? $ruangan['Lantai'],
    'keterangan' => $old['keterangan'] ?? $ruangan['Keterangan'],
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
            <h1 class="page-title" data-kicker="Perbarui Data" data-subtitle="Sesuaikan identitas ruangan, lantai, dan keterangan agar data ruang tetap akurat.">
                Edit Ruangan
            </h1>
            <div class="page-toolbar" data-note="Perubahan tersimpan ke data ruangan">
                <a href="index.php" class="tw:inline-flex tw:items-center tw:justify-center tw:gap-2 tw:min-h-12 tw:px-4 tw:py-[0.85rem] tw:rounded-2xl tw:border tw:border-[rgba(22,60,122,0.12)] tw:font-extrabold tw:no-underline tw:text-slate-900 tw:bg-[rgba(255,255,255,0.82)] tw:hover:bg-gray-50 tw:transition-all tw:text-sm">
                    <i class="iconsax" icon-name="arrow-left"></i>
                    <span>Kembali ke daftar</span>
                </a>
            </div>

            <form method="POST" class="tw:grid tw:grid-cols-1 tw:lg:grid-cols-2 tw:gap-4 tw:p-[1.45rem] tw:rounded-[24px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm" x-data="<?= htmlspecialchars(json_encode(['nama' => $formData['nama'] ?? '', 'keterangan' => $formData['keterangan'] ?? ''])) ?>">
                <?php echo csrf_field(); ?>
              <div class="mb-3">
                    <label for="namaRuangan" class="form-label">Nama Ruangan</label>
                    <input type="text" name="namaRuangan" class="form-control" id="namaRuangan" x-model="nama" maxlength="100" required>
                    <div class="tw:text-xs tw:text-slate-400 tw:mt-1 tw:text-right">
                        <span :class="nama.length >= 100 ? 'tw:text-red-600 tw:font-semibold' : (nama.length >= 90 ? 'tw:text-amber-700 tw:font-semibold' : '')" x-text="nama.length">0</span>/100 karakter
                    </div>
                </div>
                <div class="mb-3">
                    <label for="jenisRuangan" class="form-label">Jenis Ruangan</label>
                    <select class="form-select" name="jenisRuangan" id="jenisRuangan" required>
                        <?php foreach ($ruanganTypes as $jenisRuangan): ?>
                            <option value="<?= htmlspecialchars($jenisRuangan) ?>" <?= $formData['jenis'] === $jenisRuangan ? 'selected' : '' ?>>
                                <?= htmlspecialchars($jenisRuangan) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="lantaiRuangan" class="form-label">Lantai</label>
                    <select class="form-select" name="lantaiRuangan" id="lantaiRuangan" required>
                        <option value="" disabled <?= empty($formData['lantai']) ? 'selected' : '' ?>>Pilih Lantai</option>
                        <option value="1" <?= $formData['lantai'] == '1' ? 'selected' : '' ?>>Lantai 1</option>
                        <option value="2" <?= $formData['lantai'] == '2' ? 'selected' : '' ?>>Lantai 2</option>
                        <option value="3" <?= $formData['lantai'] == '3' ? 'selected' : '' ?>>Lantai 3</option>
                        <option value="4" <?= $formData['lantai'] == '4' ? 'selected' : '' ?>>Lantai 4</option>
                        <option value="5" <?= $formData['lantai'] == '5' ? 'selected' : '' ?>>Lantai 5</option>
                        <option value="6" <?= $formData['lantai'] == '6' ? 'selected' : '' ?>>Lantai 6</option>
                        <option value="7" <?= $formData['lantai'] == '7' ? 'selected' : '' ?>>Lantai 7</option>
                        <option value="1 Gedung Sekretariat" <?= $formData['lantai'] == '1 Gedung Sekretariat' ? 'selected' : '' ?>>Lantai 1 Gedung Sekretariat</option>
                        <option value="2 Gedung Sekretariat" <?= $formData['lantai'] == '2 Gedung Sekretariat' ? 'selected' : '' ?>>Lantai 2 Gedung Sekretariat</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="keteranganRuangan" class="form-label">Keterangan</label>
                    <textarea name="keteranganRuangan" class="form-control" id="keteranganRuangan" x-model="keterangan" maxlength="500" rows="3"></textarea>
                    <div class="tw:text-xs tw:text-slate-400 tw:mt-1 tw:text-right">
                        <span :class="keterangan.length >= 500 ? 'tw:text-red-600 tw:font-semibold' : (keterangan.length >= 450 ? 'tw:text-amber-700 tw:font-semibold' : '')" x-text="keterangan.length">0</span>/500 karakter
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
