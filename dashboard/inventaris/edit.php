<?php
require '../../vendor/autoload.php';

use Respect\Validation\Validator as v;
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: /doremi-app/login.php");
    exit;
}
require '../../db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: /doremi-app/dashboard/inventaris/");
    exit;
}

$stmt = mysqli_prepare($db, "SELECT * FROM inventaris WHERE InventarisID = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$inventaris = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$inventaris) {
    header("Location: /doremi-app/dashboard/inventaris/");
    exit;
}

// Fetch rooms and spaces
$kamars = mysqli_fetch_all(mysqli_query($db, "SELECT KamarID, NomorKamar FROM kamar WHERE IsDeleted = 0"), MYSQLI_ASSOC);
$ruangans = mysqli_fetch_all(mysqli_query($db, "SELECT RuanganID, NamaRuangan FROM ruangan WHERE IsDeleted = 0"), MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['namaBarang'] ?? '');
    $jumlah = trim($_POST['jumlahBarang'] ?? '');
    $lokasi = $_POST['lokasiBarang'] ?? '';
    $keterangan = trim($_POST['keteranganBarang'] ?? '');

    $inventarisSchema = v::keySet(
        v::key('nama', v::stringType()->length(1, 100)),
        v::key('jumlah', v::numericVal()->min(0)),
        v::key('lokasi', v::stringType()->length(1, 50)),
        v::key('keterangan', v::stringType())
    );

    if (!$inventarisSchema->validate(['nama' => $nama, 'jumlah' => $jumlah, 'lokasi' => $lokasi, 'keterangan' => $keterangan])) {
        header("Location: " . $_SERVER['PHP_SELF'] . '?id=' . $id . '&status=error&message=Data Inventaris Tidak Valid!');
        exit;
    }

    $kamarId = null;
    $ruanganId = null;

    if (str_starts_with($lokasi, 'kamar:')) {
        $kamarId = explode(':', $lokasi)[1];
    } elseif (str_starts_with($lokasi, 'ruangan:')) {
        $ruanganId = explode(':', $lokasi)[1];
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

    header("Location: /doremi-app/dashboard/inventaris/?status=success&message=Inventaris Berhasil Diupdate!");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<?php require '../../head.php'; ?>

<body class="tw:p-0 tw:m-0 relative tw:flex">
    <?php require '../components/sidebar.php'; ?>
    <main class="tw:ml-75 tw:grow">
        <div class="tw:pt-5 tw:px-5 tw:flex-1 tw:w-full">
            <h1 class="tw:font-bold tw:mb-5 tw:text-4xl tw:text-black">
                Edit Inventaris
            </h1>

            <form method="POST">
                <div class="mb-3">
                    <label for="inventarisID" class="form-label">ID Inventaris</label>
                    <input type="text" class="form-control" id="inventarisID"
                        value="<?= htmlspecialchars($inventaris['InventarisID']) ?>" disabled>
                </div>
                <div class="mb-3">
                    <label for="namaBarang" class="form-label">Nama Barang</label>
                    <input type="text" name="namaBarang" class="form-control" id="namaBarang"
                        value="<?= htmlspecialchars($inventaris['NamaBarang']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="jumlahBarang" class="form-label">Jumlah</label>
                    <input type="number" name="jumlahBarang" class="form-control" id="jumlahBarang"
                        value="<?= htmlspecialchars($inventaris['Jumlah']) ?>" required>
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
                    <textarea name="keteranganBarang" class="form-control" id="keteranganBarang"
                        rows="3"><?= htmlspecialchars($inventaris['Keterangan']) ?></textarea>
                </div>
                <div class="tw:w-full tw:flex tw:justify-end tw:mt-2">
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
