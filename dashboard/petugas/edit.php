<?php
require '../../vendor/autoload.php';

use Respect\Validation\Validator as v;
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: /doremi-app/login.php");
    exit;
}
require '../../db.php';
require '../../database/petugas.php';
require '../../utils/old_input.php';
require_once '../../utils/validation_helpers.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: /doremi-app/dashboard/petugas/");
    exit;
}

$petugas = fetchPetugasById($db, (int) $id);

if (!$petugas) {
    header("Location: /doremi-app/dashboard/petugas/");
    exit;
}

$isSelfEdit = (int) ($_SESSION['userId'] ?? 0) === (int) $id;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['namaPetugas'] ?? '');
    $email = trim($_POST['emailPetugas'] ?? '');
    $no = (string) preg_replace('/\D+/', '', trim($_POST['noPetugas'] ?? ''));
    $jabatan = $isSelfEdit ? (string) $petugas['Jabatan'] : trim($_POST['jabatanPetugas'] ?? '');
    $password = trim($_POST['passwordPetugas'] ?? '');
    $confirmPassword = trim($_POST['confirmPasswordPetugas'] ?? '');

    $isChangingPassword = $password !== '' || $confirmPassword !== '';

    $postData = [
        'nama' => $nama,
        'email' => $email,
        'no' => $no,
        'jabatan' => $jabatan,
        'password' => $password,
        'confirmPassword' => $confirmPassword,
    ];

    // Password fields are excluded from the flashed old-input so a plaintext
    // password never gets echoed back into the page after a failed submit.
    $oldInput = ['nama' => $nama, 'email' => $email, 'no' => $no, 'jabatan' => $jabatan];

    $fieldError = firstFieldError($postData, [
        'nama' => ['label' => 'Nama Petugas', 'rule' => v::stringType()->length(3, 100)],
        'email' => ['label' => 'Email Petugas', 'rule' => v::email()->length(3, 100)],
        'no' => ['label' => 'No. HP', 'rule' => v::digit()->length(10, 16)],
        'jabatan' => ['label' => 'Jabatan', 'rule' => v::alpha()->in(["PENGURUS", "SIGAP", "SERVANDA", "MAINTENANCE"])],
        'password' => ['label' => 'Password', 'rule' => v::optional(v::length(8, 100))],
        'confirmPassword' => ['label' => 'Konfirmasi Password', 'rule' => v::optional(v::length(8, 100))],
    ]);

    if ($fieldError !== null) {
        setOldFormInput($oldInput);
        header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $id . '&status=error&message=' . urlencode($fieldError));
        exit;
    }

    if ($isChangingPassword && $password !== $confirmPassword) {
        setOldFormInput($oldInput);
        header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $id . '&status=error&message=Password Tidak Cocok!');
        exit;
    }

    $duplicatePetugas = findPetugasDuplicateExcluding($db, (int) $id, $email, $no);

    if ($duplicatePetugas) {
        setOldFormInput($oldInput);

        if (($duplicatePetugas['Email'] ?? '') === $email) {
            header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $id . '&status=error&message=Email petugas sudah terdaftar!');
            exit;
        }

        header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $id . '&status=error&message=No. HP petugas sudah terdaftar!');
        exit;
    }

    if ($isChangingPassword) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        updatePetugasWithPassword($db, (int) $id, $nama, $email, $jabatan, $no, $hashedPassword);
    } else {
        updatePetugasWithoutPassword($db, (int) $id, $nama, $email, $jabatan, $no);
    }

    header("Location: /doremi-app/dashboard/petugas/?status=success&message=Petugas Berhasil Diupdate!");
    exit;
}

$old = pullOldFormInput();
$formData = [
    'nama' => $old['nama'] ?? $petugas['NamaPetugas'],
    'email' => $old['email'] ?? $petugas['Email'],
    'no' => $old['no'] ?? $petugas['NoHP'],
    'jabatan' => $old['jabatan'] ?? $petugas['Jabatan'],
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
            <h1 class="page-title" data-kicker="Perbarui Data" data-subtitle="Ubah profil petugas, jabatan, dan kredensial login tanpa keluar dari alur kerja master data.">
                Edit Petugas
            </h1>
            <div class="page-toolbar" data-note="Kosongkan password jika tidak ingin mengubah akses login">
                <a href="index.php" class="tw:inline-flex tw:items-center tw:justify-center tw:gap-2 tw:min-h-12 tw:px-4 tw:py-[0.85rem] tw:rounded-2xl tw:border tw:border-[rgba(22,60,122,0.12)] tw:font-extrabold tw:no-underline tw:text-slate-900 tw:bg-[rgba(255,255,255,0.82)] tw:hover:bg-gray-50 tw:transition-all tw:text-sm">
                    <i class="iconsax" icon-name="arrow-left"></i>
                    <span>Kembali ke daftar</span>
                </a>
            </div>

            <form method="POST" class="tw:grid tw:grid-cols-1 tw:lg:grid-cols-2 tw:gap-4 tw:p-[1.45rem] tw:rounded-[24px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm">
                <div class="mb-3">
                    <label for="namaPetugas" class="form-label">Nama Petugas</label>
                    <input type="text" name="namaPetugas" class="form-control" id="namaPetugas"
                        value="<?= htmlspecialchars($formData['nama']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="emailPetugas" class="form-label">Email Petugas</label>
                    <input type="email" name="emailPetugas" class="form-control" id="emailPetugas"
                        value="<?= htmlspecialchars($formData['email']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="noPetugas" class="form-label">No. HP</label>
                    <input type="text" name="noPetugas" class="form-control" id="noPetugas" inputmode="numeric"
                        pattern="[0-9]{10,16}" maxlength="16"
                        value="<?= htmlspecialchars($formData['no']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="jabatanPetugas" class="form-label">Jabatan</label>
                    <select class="form-select" name="jabatanPetugas" id="jabatanPetugas" <?= $isSelfEdit ? 'disabled' : 'required' ?>>
                        <option disabled>Pilih Salah Satu</option>
                        <?php foreach (["PENGURUS", "SIGAP", "SERVANDA", "MAINTENANCE"] as $role): ?>
                            <option value="<?= $role ?>" <?= $formData['jabatan'] === $role ? 'selected' : '' ?>>
                                <?= $role ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($isSelfEdit): ?>
                        <span class="form-hint">Jabatan akun yang sedang aktif tidak bisa diubah dari sesi yang sama.</span>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label for="passwordPetugas" class="form-label">Password Baru <span
                            class="tw:text-gray-400 tw:text-sm">(kosongkan jika tidak ingin mengubah)</span></label>
                    <input type="password" name="passwordPetugas" class="form-control" id="passwordPetugas" minlength="8" autocomplete="new-password">
                    <span class="form-hint">Saran: pakai minimal 8 karakter dengan kombinasi huruf besar, huruf kecil, dan angka.</span>
                </div>
                <div class="mb-3">
                    <label for="confirmPasswordPetugas" class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" name="confirmPasswordPetugas" class="form-control" minlength="8" autocomplete="new-password"
                        id="confirmPasswordPetugas">
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
