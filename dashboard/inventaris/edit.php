<?php
require_once '../../utils/url.php';
require '../../vendor/autoload.php';

use Respect\Validation\Validator as v;
session_start();

if (!isset($_SESSION['userId'])) {
    app_redirect('login.php');
}
if ($_SESSION['userRole'] !== 'PENGURUS') {
    app_redirect('dashboard/');
}
require '../../csrf.php';
require '../../db.php';
require_once '../../database/inventaris.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: ' . app_url('dashboard/inventaris/'));
    exit;
}

$stmt = mysqli_prepare($db, "SELECT * FROM inventaris WHERE InventarisID = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$inventaris = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$inventaris) {
    header('Location: ' . app_url('dashboard/inventaris/'));
    exit;
}

$kamars = fetchActiveKamarOptions($db);
$ruangans = fetchActiveRuanganOptions($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate($_SERVER['PHP_SELF'] . '?id=' . $id);
    if (isset($_POST['keteranganBarang'])) {
        $_POST['keteranganBarang'] = str_replace("\r\n", "\n", $_POST['keteranganBarang']);
    }
    $nama = trim($_POST['namaBarang'] ?? '');
    $jumlah = trim($_POST['jumlahBarang'] ?? '');
    $lokasi = $_POST['lokasiBarang'] ?? '';
    $keterangan = trim($_POST['keteranganBarang'] ?? '');

    if (!v::stringType()->length(2, 100)->notEmpty()->regex('/^[A-Za-z0-9\s\-\.\/()&]+$/')->validate($nama)) {
        header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $id . '&status=error&message=Nama barang harus 2-100 karakter dan hanya boleh mengandung huruf, angka, spasi, dan simbol (-()./&)!');
        exit;
    }

    if (!str_starts_with($lokasi, 'kamar:') && !str_starts_with($lokasi, 'ruangan:')) {
        header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $id . '&status=error&message=Lokasi wajib dipilih!');
        exit;
    }

    if (!v::numericVal()->min(0)->max(999999)->validate($jumlah)) {
        header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $id . '&status=error&message=Jumlah harus berupa angka antara 0 dan 999999!');
        exit;
    }

    if (!v::stringType()->length(0, 500)->validate($keterangan)) {
        header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $id . '&status=error&message=Keterangan maksimal 500 karakter!');
        exit;
    }

    $kamarId = null;
    $ruanganId = null;

    if (str_starts_with($lokasi, 'kamar:')) {
        $kamarId = (int) explode(':', $lokasi)[1];
        $chk = mysqli_prepare($db, "SELECT KamarID FROM kamar WHERE KamarID = ? AND IsDeleted = 0 LIMIT 1");
        mysqli_stmt_bind_param($chk, 'i', $kamarId);
        mysqli_stmt_execute($chk);
        if (!mysqli_fetch_assoc(mysqli_stmt_get_result($chk))) {
            mysqli_stmt_close($chk);
            header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $id . '&status=error&message=Kamar tidak ditemukan!');
            exit;
        }
        mysqli_stmt_close($chk);
    } elseif (str_starts_with($lokasi, 'ruangan:')) {
        $ruanganId = (int) explode(':', $lokasi)[1];
        $chk = mysqli_prepare($db, "SELECT RuanganID FROM ruangan WHERE RuanganID = ? AND IsDeleted = 0 LIMIT 1");
        mysqli_stmt_bind_param($chk, 'i', $ruanganId);
        mysqli_stmt_execute($chk);
        if (!mysqli_fetch_assoc(mysqli_stmt_get_result($chk))) {
            mysqli_stmt_close($chk);
            header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $id . '&status=error&message=Ruangan tidak ditemukan!');
            exit;
        }
        mysqli_stmt_close($chk);
    }

    $now = date('Y-m-d H:i:s');

    $stmt = mysqli_prepare($db, "UPDATE inventaris SET RuanganID = ?, KamarID = ?, NamaBarang = ?, Jumlah = ?, Keterangan = ?, UpdatedAt = ? WHERE InventarisID = ?");
    mysqli_stmt_bind_param($stmt, 'iisissi', $ruanganId, $kamarId, $nama, $jumlah, $keterangan, $now, $id);

    if (!mysqli_stmt_execute($stmt)) {
        header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $id . '&status=error&message=Terjadi Kesalahan saat mengupdate data!');
        mysqli_stmt_close($stmt);
        exit;
    }

    mysqli_stmt_close($stmt);

        header('Location: ' . app_url('dashboard/inventaris/?status=success&message=Inventaris Berhasil Diupdate!'));
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<?php require '../../head.php'; ?>

<body class="dashboard-body tw:p-0 tw:m-0 tw:relative tw:flex">
    <?php require '../components/sidebar.php'; ?>
    <main class="tw:md:ml-75 tw:grow">
        <div class="tw:pt-20 tw:md:pt-5 tw:px-5 tw:mb-8 tw:flex-1 tw:w-dvw tw:md:w-full">
            <?php require dirname(__DIR__) . '/components/breadcrumb.php'; ?>
            <h1 class="page-title" data-kicker="Perbarui Data" data-subtitle="Perbaiki nama barang, jumlah, dan penempatan inventaris tanpa meninggalkan halaman edit ini.">
                Edit Inventaris
            </h1>
            <div class="page-toolbar" data-note="Perubahan akan langsung memperbarui data inventaris">
                <a href="index.php" class="tw:inline-flex tw:items-center tw:justify-center tw:gap-2 tw:min-h-12 tw:px-4 tw:py-[0.85rem] tw:rounded-2xl tw:border tw:border-[rgba(22,60,122,0.12)] tw:font-extrabold tw:no-underline tw:text-slate-900 tw:bg-[rgba(255,255,255,0.82)] tw:hover:bg-gray-50 tw:transition-all tw:text-sm">
                    <i class="iconsax" icon-name="arrow-left-2"></i>
                    <span>Kembali ke daftar</span>
                </a>
            </div>

            <form method="POST" class="tw:grid tw:grid-cols-1 tw:lg:grid-cols-2 tw:gap-4 tw:p-[1.45rem] tw:rounded-[24px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm" x-data="<?= htmlspecialchars(json_encode(['nama' => $inventaris['NamaBarang'] ?? '', 'keterangan' => $inventaris['Keterangan'] ?? ''])) ?>">
                <?php echo csrf_field(); ?>
              <div class="mb-3">
                    <label for="namaBarang" class="form-label">Nama Barang</label>
                    <input type="text" name="namaBarang" class="form-control" id="namaBarang" x-model="nama" maxlength="100" minlength="2"
                        pattern="[a-zA-Z0-9\s\-\(\)\.\/&]+" title="Hanya huruf, angka, spasi, dan simbol (-()./&)" required>
                    <div class="tw:text-xs tw:text-slate-400 tw:mt-1 tw:text-right">
                        <span :class="nama.length >= 100 ? 'tw:text-red-600 tw:font-semibold' : (nama.length >= 90 ? 'tw:text-amber-700 tw:font-semibold' : '')" x-text="nama.length">0</span>/100 karakter
                    </div>
                    <div class="tw:text-xs tw:text-slate-500 tw:mt-1">Minimal 2 karakter</div>
                </div>
                <div class="mb-3">
                    <label for="jumlahBarang" class="form-label">Jumlah</label>
                    <input type="number" name="jumlahBarang" class="form-control" id="jumlahBarang"
                        value="<?= htmlspecialchars($inventaris['Jumlah']) ?>" min="0" max="999999" required>
                </div>
                <div class="mb-3">
                    <label for="lokasiBarang" class="form-label">Lokasi</label>
                    <select class="form-select" name="lokasiBarang" id="lokasiBarang" required>
                        <?php 
                            $currentLokasi = "";
                            if ($inventaris['KamarID']) $currentLokasi = "kamar:" . $inventaris['KamarID'];
                            if ($inventaris['RuanganID']) $currentLokasi = "ruangan:" . $inventaris['RuanganID'];
                        ?>
                        <optgroup label="Kamar">
                            <?php foreach ($kamars as $k): ?>
                                <option value="kamar:<?= $k['KamarID'] ?>" <?= $currentLokasi == "kamar:".$k['KamarID'] ? 'selected' : '' ?>>
                                    Kamar <?= $k['NomorKamar'] ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="Ruangan">
                            <?php foreach ($ruangans as $r): ?>
                                <option value="ruangan:<?= $r['RuanganID'] ?>" <?= $currentLokasi == "ruangan:".$r['RuanganID'] ? 'selected' : '' ?>>
                                    <?= $r['NamaRuangan'] ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="keteranganBarang" class="form-label">Keterangan</label>
                    <textarea name="keteranganBarang" class="form-control" id="keteranganBarang" x-model="keterangan" maxlength="500" rows="3"></textarea>
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
