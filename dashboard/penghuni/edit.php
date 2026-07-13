<?php
require '../../vendor/autoload.php';
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: /doremi-app/login.php");
    exit;
}
require '../../db.php';
require '../../utils/old_input.php';
require 'helpers.php';
require 'validation.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: /doremi-app/dashboard/penghuni/");
    exit;
}

$penghuni = fetchPenghuniById($db, (int) $id);

if (!$penghuni) {
    header("Location: /doremi-app/dashboard/penghuni/");
    exit;
}

$kamars = fetchActiveKamarWithOccupancy($db);
$kamarMap = [];
foreach ($kamars as $kamar) {
    $kamarMap[(int) $kamar['KamarID']] = $kamar;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = collectPenghuniInput($_POST);
    $validationMessage = validateEditPenghuniInput($db, $input, $kamarMap, (int) $id);
    if ($validationMessage !== null) {
        setOldFormInput(penghuniFormData($input));
        header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $id . '&status=error&message=' . urlencode($validationMessage));
        exit;
    }

    $passwordHash = $input['password'] !== '' ? password_hash($input['password'], PASSWORD_BCRYPT) : null;
    try {
        updatePenghuni($db, (int) $id, (int) $input['kamarId'], $input['nama'], $input['nim'], $input['jk'], $input['no'], $input['email'], $input['alamat'], $passwordHash);
    } catch (RuntimeException) {
        header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $id . '&status=error&message=Terjadi Kesalahan saat mengupdate data!');
        exit;
    }

    header("Location: /doremi-app/dashboard/penghuni/?status=success&message=Penghuni Berhasil Diupdate!");
    exit;
}

$old = pullOldFormInput();
$formData = [
    'nim' => $old['nim'] ?? $penghuni['Nim'],
    'nama' => $old['nama'] ?? $penghuni['NamaPenghuni'],
    'email' => $old['email'] ?? $penghuni['Email'],
    'no' => $old['no'] ?? $penghuni['NoHP'],
    'jk' => $old['jk'] ?? $penghuni['JenisKelamin'],
    'kamarId' => $old['kamarId'] ?? $penghuni['KamarID'],
    'alamat' => $old['alamat'] ?? $penghuni['Alamat'],
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
            <h1 class="page-title" data-kicker="Perbarui Data" data-subtitle="Perbarui profil penghuni, kamar, dan akses login sambil menjaga validasi kamar tetap konsisten.">
                Edit Penghuni
            </h1>
            <div class="page-toolbar" data-note="Kosongkan password jika hanya ingin mengubah profil penghuni">
                <a href="index.php" class="tw:inline-flex tw:items-center tw:justify-center tw:gap-2 tw:min-h-12 tw:px-4 tw:py-[0.85rem] tw:rounded-2xl tw:border tw:border-[rgba(22,60,122,0.12)] tw:font-extrabold tw:no-underline tw:text-slate-900 tw:bg-[rgba(255,255,255,0.82)] tw:hover:bg-gray-50 tw:transition-all tw:text-sm">
                    <i class="iconsax" icon-name="arrow-left"></i>
                    <span>Kembali ke daftar</span>
                </a>
            </div>

            <form method="POST" class="tw:grid tw:grid-cols-1 tw:lg:grid-cols-2 tw:gap-4 tw:p-[1.45rem] tw:rounded-[24px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm">
                <div class="mb-3">
                    <label for="nimPenghuni" class="form-label">NIM</label>
                    <input type="text" name="nimPenghuni" class="form-control" id="nimPenghuni"
                        minlength="<?= penghuni_nim_min_length() ?>" maxlength="<?= penghuni_nim_max_length() ?>"
                        value="<?= htmlspecialchars($formData['nim']) ?>" required>
                    <span class="form-hint">Gunakan <?= penghuni_nim_min_length() ?>-<?= penghuni_nim_max_length() ?> karakter tanpa spasi agar validasi NIM sesuai batas database.</span>
                </div>
                <div class="mb-3">
                    <label for="namaPenghuni" class="form-label">Nama Penghuni</label>
                    <input type="text" name="namaPenghuni" class="form-control" id="namaPenghuni"
                        value="<?= htmlspecialchars($formData['nama']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="emailPenghuni" class="form-label">Email</label>
                    <input type="email" name="emailPenghuni" class="form-control" id="emailPenghuni"
                        value="<?= htmlspecialchars($formData['email']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="noPenghuni" class="form-label">No. HP</label>
                    <input type="text" name="noPenghuni" class="form-control" id="noPenghuni" inputmode="numeric"
                        pattern="[0-9]{10,16}" maxlength="16"
                        value="<?= htmlspecialchars($formData['no']) ?>" required>
                    <span class="form-hint">Masukkan 10-16 digit angka aktif tanpa spasi atau simbol.</span>
                </div>
                <div class="mb-3">
                    <label for="jkPenghuni" class="form-label">Jenis Kelamin</label>
                    <select class="form-select" name="jkPenghuni" id="jkPenghuni" required>
                        <option value="L" <?= $formData['jk'] === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="P" <?= $formData['jk'] === 'P' ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="kamarPenghuni" class="form-label">Kamar</label>
                    <select class="form-select" name="kamarPenghuni" id="kamarPenghuni" required>
                        <?php foreach ($kamars as $kamar): ?>
                            <option value="<?= $kamar['KamarID'] ?>" <?= $formData['kamarId'] == $kamar['KamarID'] ? 'selected' : '' ?>>
                                <?= $kamar['NomorKamar'] ?> - Lantai <?= $kamar['Lantai'] ?>
                                (<?= $kamar['JumlahPenghuniAktual'] ?>/<?= $kamar['KapasitasPenghuni'] ?> terisi)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="alamatPenghuni" class="form-label">Alamat</label>
                    <textarea name="alamatPenghuni" class="form-control" id="alamatPenghuni" rows="3" required><?= htmlspecialchars($formData['alamat']) ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="passwordPenghuni" class="form-label">Password Baru <span
                            class="tw:text-gray-400 tw:text-sm">(kosongkan jika tidak ingin mengubah)</span></label>
                    <input type="password" name="passwordPenghuni" class="form-control" id="passwordPenghuni" minlength="8" autocomplete="new-password">
                    <span class="form-hint">Saran: pakai minimal 8 karakter dengan kombinasi huruf besar, huruf kecil, dan angka.</span>
                </div>
                <div class="mb-3">
                    <label for="confirmPasswordPenghuni" class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" name="confirmPasswordPenghuni" class="form-control" minlength="8" autocomplete="new-password"
                        id="confirmPasswordPenghuni">
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
