<?php
require_once '../../utils/url.php';
session_start();
if (!isset($_SESSION['userId'])) {
    app_redirect('login.php');
}

require '../../db.php';
require '../../utils/old_input.php';
require_once '../../database/inout.php';
require_once '../../utils/format.php';
require_once 'validation.php';
$userId = (int) $_SESSION['userId'];
$action = $_POST['action'] ?? '';

if ($action === 'create_request') {
    $requestInput = collectInOutRequestInput($_POST);
    $validationMessage = validateInOutRequestInput($requestInput);
    if ($validationMessage !== null) {
        setOldFormInput($_POST);
        header("Location: index.php?status=error&message=" . urlencode($validationMessage));
        exit;
    }

    $dateTimes = buildInOutDateTimes($requestInput);

    if (countActiveInOutRequests($db, $userId) > 0) {
        header("Location: index.php?status=error&message=Anda masih memiliki izin keluar yang aktif!");
        exit;
    }

    try {
        createInOutRequest($db, $userId, $requestInput['keperluan'], $dateTimes['waktuKeluar'], $dateTimes['waktuMasuk']);
        header("Location: index.php?status=success&message=Permintaan izin keluar berhasil dikirim!");
    } catch (RuntimeException) {
        setOldFormInput($_POST);
        header("Location: index.php?status=error&message=Gagal mengirim permintaan!");
    }
}

elseif ($action === 'confirm_exit') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $now = date('Y-m-d H:i:s');

    if (!$id) {
        header("Location: index.php?status=error&message=Data izin keluar tidak valid!");
        exit;
    }
    
    try {
        confirmInOutExit($db, $id, $now, $userId);
        header("Location: index.php?status=success&message=Konfirmasi keluar berhasil!");
    } catch (RuntimeException) {
        header("Location: index.php?status=error&message=Gagal konfirmasi keluar!");
    }
}

elseif ($action === 'confirm_entry') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $now = date('Y-m-d H:i:s');

    if (!$id) {
        header("Location: index.php?status=error&message=Data izin masuk tidak valid!");
        exit;
    }

    try {
        confirmInOutEntry($db, $id, $now);
        header("Location: index.php?status=success&message=Konfirmasi masuk berhasil!");
    } catch (RuntimeException) {
        header("Location: index.php?status=error&message=Gagal konfirmasi masuk!");
    }
}

else {
    header("Location: index.php");
}
exit;
