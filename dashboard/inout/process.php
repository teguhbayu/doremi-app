<?php
session_start();
if (!isset($_SESSION['userId'])) {
    header("Location: /doremi-app/login.php");
    exit;
}

require '../../db.php';
$userId = $_SESSION['userId'];
$action = $_POST['action'] ?? '';

if ($action === 'create_request') {
    $keperluan = mysqli_real_escape_string($db, trim($_POST['keperluan'] ?? ''));
    $waktuKeluarTime = $_POST['waktuKeluar'] ?? '';
    $waktuMasukTime = $_POST['waktuMasuk'] ?? '';
    
    if (empty($keperluan) || empty($waktuKeluarTime) || empty($waktuMasukTime)) {
        header("Location: index.php?status=error&message=Semua field harus diisi!");
        exit;
    }

    $currentTime = date('H:i');
    $maxTime = '22:00';

    if ($waktuKeluarTime < $currentTime || $waktuKeluarTime > $maxTime) {
        header("Location: index.php?status=error&message=Waktu keluar harus antara sekarang dan 22:00!");
        exit;
    }

    if ($waktuMasukTime < $currentTime || $waktuMasukTime > $maxTime) {
        header("Location: index.php?status=error&message=Waktu masuk harus antara sekarang dan 22:00!");
        exit;
    }

    if ($waktuMasukTime <= $waktuKeluarTime) {
        header("Location: index.php?status=error&message=Waktu masuk harus setelah waktu keluar!");
        exit;
    }

    $today = date('Y-m-d');
    $waktuKeluar = $today . ' ' . $waktuKeluarTime . ':00';
    $waktuMasuk = $today . ' ' . $waktuMasukTime . ':00';

    $activeQuery = mysqli_query($db, "SELECT COUNT(*) as count FROM inoutpenghuni WHERE PenghuniID = $userId AND Status IN ('Pending', 'Keluar')");
    if (mysqli_fetch_assoc($activeQuery)['count'] > 0) {
        header("Location: index.php?status=error&message=Anda masih memiliki izin keluar yang aktif!");
        exit;
    }

    $stmt = mysqli_prepare($db, "INSERT INTO inoutpenghuni (PenghuniID, Keperluan, Status, WaktuKeluar, WaktuMasuk) VALUES (?, ?, 'Pending', ?, ?)");
    mysqli_stmt_bind_param($stmt, 'isss', $userId, $keperluan, $waktuKeluar, $waktuMasuk);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: index.php?status=success&message=Permintaan izin keluar berhasil dikirim!");
    } else {
        header("Location: index.php?status=error&message=Gagal mengirim permintaan!");
    }
    mysqli_stmt_close($stmt);
}

elseif ($action === 'confirm_exit') {
    $id = $_POST['id'] ?? '';
    $now = date('Y-m-d H:i:s');
    
    $stmt = mysqli_prepare($db, "UPDATE inoutpenghuni SET Status = 'Keluar', WaktuKeluar = ?, PetugasID = ? WHERE InOutID = ?");
    mysqli_stmt_bind_param($stmt, 'sii', $now, $userId, $id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: index.php?status=success&message=Konfirmasi keluar berhasil!");
    } else {
        header("Location: index.php?status=error&message=Gagal konfirmasi keluar!");
    }
    mysqli_stmt_close($stmt);
}

elseif ($action === 'confirm_entry') {
    $id = $_POST['id'] ?? '';
    $now = date('Y-m-d H:i:s');
    
    $stmt = mysqli_prepare($db, "UPDATE inoutpenghuni SET Status = 'Masuk', WaktuMasuk = ? WHERE InOutID = ?");
    mysqli_stmt_bind_param($stmt, 'si', $now, $id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: index.php?status=success&message=Konfirmasi masuk berhasil!");
    } else {
        header("Location: index.php?status=error&message=Gagal konfirmasi masuk!");
    }
    mysqli_stmt_close($stmt);
}

else {
    header("Location: index.php");
}
exit;
