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

    $keperluanLength = function_exists('mb_strlen') ? mb_strlen($keperluan) : strlen($keperluan);
    if ($keperluanLength > 20) {
        header("Location: index.php?status=error&message=Keperluan maksimal 20 karakter!");
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

    $stmt = mysqli_prepare($db, "CALL sp_countActiveInOutRequests(?)");
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $activeQuery = mysqli_stmt_get_result($stmt);
    $count = mysqli_fetch_assoc($activeQuery)['count'];
    mysqli_stmt_close($stmt);
    if ($count > 0) {
        header("Location: index.php?status=error&message=Anda masih memiliki izin keluar yang aktif!");
        exit;
    }

    $stmt = mysqli_prepare($db, "CALL sp_createInOutRequest(?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'isss', $userId, $keperluan, $waktuKeluar, $waktuMasuk);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: index.php?status=success&message=Permintaan izin keluar berhasil dikirim!");
    } else {
        header("Location: index.php?status=error&message=Gagal mengirim permintaan!");
    }
    mysqli_stmt_close($stmt);
}

elseif ($action === 'confirm_exit') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $now = date('Y-m-d H:i:s');

    if (!$id) {
        header("Location: index.php?status=error&message=Data izin keluar tidak valid!");
        exit;
    }
    
    $stmt = mysqli_prepare($db, "CALL sp_confirmInOutExit(?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'isi', $id, $now, $userId);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: index.php?status=success&message=Konfirmasi keluar berhasil!");
    } else {
        header("Location: index.php?status=error&message=Gagal konfirmasi keluar!");
    }
    mysqli_stmt_close($stmt);
}

elseif ($action === 'confirm_entry') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $now = date('Y-m-d H:i:s');

    if (!$id) {
        header("Location: index.php?status=error&message=Data izin masuk tidak valid!");
        exit;
    }

    $stmt = mysqli_prepare($db, "CALL sp_confirmInOutEntry(?, ?)");
    mysqli_stmt_bind_param($stmt, 'is', $id, $now);

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
