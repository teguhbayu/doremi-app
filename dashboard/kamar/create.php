<?php
require '../../vendor/autoload.php';

use Respect\Validation\Validator as v;
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: /doremi-app/login.php");
    exit;
}
require '../../db.php';
require 'helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bagianKamar = strtoupper(substr((string) preg_replace('/[^A-Za-z]/', '', (string) ($_POST['bagianKamar'] ?? '')), 0, 1));
    $kapasitas = trim($_POST['kapasitasKamar'] ?? '');
    $lantai = trim($_POST['lantaiKamar'] ?? '');
    $nomor = kamar_build_nomor($lantai, $bagianKamar);

    $kamarSchema = v::keySet(
        v::key('bagian', v::regex('/^[A-Z]$/')),
        v::key('kapasitas', v::digit()),
        v::key('lantai', v::in(kamar_allowed_floors()))
    );

    $postData = [
        'bagian' => $bagianKamar,
        'kapasitas' => $kapasitas,
        'lantai' => $lantai,
    ];

    if (!$kamarSchema->validate($postData)) {
        header("Location: " . $_SERVER['PHP_SELF'] . '?status=error&message=Data Kamar tidak Valid!');
        exit;
    }

    $kapasitasInt = (int) $kapasitas;
    if ($kapasitasInt < 1 || $kapasitasInt > 4) {
        header("Location: " . $_SERVER['PHP_SELF'] . '?status=error&message=Kapasitas kamar minimal 1 dan maksimal 4 penghuni!');
        exit;
    }

    $checkStmt = mysqli_prepare($db, "SELECT KamarID FROM kamar WHERE IsDeleted = 0 AND UPPER(REPLACE(NomorKamar, ' ', '')) = ? LIMIT 1");
    mysqli_stmt_bind_param($checkStmt, 's', $nomor);
    mysqli_stmt_execute($checkStmt);
    $checkResult = mysqli_stmt_get_result($checkStmt);
    $existingKamar = mysqli_fetch_assoc($checkResult);
    mysqli_stmt_close($checkStmt);

    if ($existingKamar) {
        header("Location: " . $_SERVER['PHP_SELF'] . '?status=error&message=Nomor kamar sudah terdaftar!');
        exit;
    }

    $now = date('Y-m-d H:i:s');

    $stmt = mysqli_prepare($db, "INSERT INTO kamar (NomorKamar, KapasitasPenghuni, Lantai, UpdatedAt, IsDeleted) VALUES (?, ?, ?, ?, 0)");
    mysqli_stmt_bind_param($stmt, 'siss', $nomor, $kapasitasInt, $lantai, $now);

    if (!mysqli_stmt_execute($stmt)) {
        header("Location: " . $_SERVER['PHP_SELF'] . '?status=error&message=Terjadi Kesalahan saat menyimpan data!');
        mysqli_stmt_close($stmt);
        exit;
    }

    mysqli_stmt_close($stmt);

    header("Location: " . '/doremi-app/dashboard/kamar/' . '?status=success&message=Kamar Berhasil Ditambahkan!');
    exit;
}

?>


<!DOCTYPE html>
<html lang="en">
<?php require '../../head.php'; ?>

<body class="dashboard-body tw:p-0 tw:m-0 tw:relative tw:flex">
    <?php require '../components/sidebar.php'; ?>
    <main class="dashboard-main tw:md:ml-[20.5rem] tw:grow">
        <div class="dashboard-page tw:pt-24 tw:md:pt-9 tw:px-4 tw:md:px-8 tw:pb-8 tw:w-full">
            <?php require dirname(__DIR__) . '/components/breadcrumb.php'; ?>
            <h1 class="page-title" data-kicker="Tambah Data" data-subtitle="Daftarkan kamar baru dengan nomor, kapasitas, dan lantai agar struktur hunian tetap konsisten.">
                Tambah Kamar
            </h1>
            <div class="page-toolbar" data-note="Form kamar baru">
                <a href="index.php" class="page-secondary-btn">
                    <i class="iconsax" icon-name="arrow-left-2"></i>
                    <span>Kembali ke daftar</span>
                </a>
            </div>

            <form method="POST" class="form-shell">
                <div class="mb-3">
                    <label for="lantaiKamar" class="form-label">Lantai</label>
                    <select class="form-select" name="lantaiKamar" id="lantaiKamar" required>
                        <option value="" disabled selected>Pilih Lantai</option>
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
                    <label for="bagianKamar" class="form-label">Kamar</label>
                    <input type="text" name="bagianKamar" class="form-control" id="bagianKamar" maxlength="1"
                        pattern="[A-Za-z]" title="Kolom kamar hanya boleh diisi 1 huruf." required>
                </div>
                <div class="mb-3">
                    <label for="nomorKamarPreview" class="form-label">Nomor Kamar</label>
                    <input type="text" class="form-control" id="nomorKamarPreview" placeholder="Nomor kamar otomatis" readonly>
                </div>
                <div class="mb-3">
                    <label for="kapasitasKamar" class="form-label">Kapasitas Kamar</label>
                    <input type="number" name="kapasitasKamar" class="form-control" id="kapasitasKamar" min="1"
                        max="4" required>
                    <div class="form-text">Kapasitas kamar minimal 1 dan maksimal 4 penghuni.</div>
                </div>
                <div class="tw:w-full tw:flex tw:justify-end tw:mt-2">
                    <button type="submit"
                        class="tw:bg-secondary tw:w-full tw:text-white tw:px-3 tw:py-2 tw:rounded-xl tw:justify-center tw:hover:bg-accent tw:duration-300 tw:transition-all tw:inline-flex tw:items-center tw:gap-2">
                        <span>
                            Simpan
                        </span>
                    </button>
                </div>

            </form>
        </div>
    </main>
    <?php require '../../bootstrap.php'; ?>
    <?php require '../../validation_alert.php'; ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const lantaiInput = document.getElementById('lantaiKamar');
            const bagianInput = document.getElementById('bagianKamar');
            const nomorPreviewInput = document.getElementById('nomorKamarPreview');

            const syncNomorKamar = () => {
                const lantai = (lantaiInput?.value || '').trim();
                const bagian = ((bagianInput?.value || '').match(/[A-Za-z]/)?.[0] || '').toUpperCase();

                if (bagianInput) {
                    bagianInput.value = bagian;
                }

                if (nomorPreviewInput) {
                    nomorPreviewInput.value = lantai && bagian ? `${lantai}${bagian}` : '';
                }
            };

            lantaiInput?.addEventListener('change', syncNomorKamar);
            bagianInput?.addEventListener('input', syncNomorKamar);
            syncNomorKamar();
        });
    </script>
</body>

</html>
