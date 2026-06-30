<?php
session_start();
require_once '../db.php';
require_once 'config.php';

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    if (!isset($token['error'])) {
        $client->setAccessToken($token['access_token']);

        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();

        $email = $google_account_info->email;

        $stmt = mysqli_prepare($db, "CALL sp_getPetugasByEmail(?)");
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $petugas = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($petugas) {
            $_SESSION['userId'] = $petugas['PetugasID'];
            $_SESSION['userName'] = $petugas['NamaPetugas'];
            $_SESSION['userRole'] = $petugas['Jabatan'];

            header("Location: /doremi-app/dashboard");
            exit;
        }

        $stmt = mysqli_prepare($db, "CALL sp_getPenghuniByEmail(?)");
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $penghuni = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($penghuni) {
            $_SESSION['userId'] = $penghuni['PenghuniID'];
            $_SESSION['userName'] = $penghuni['NamaPenghuni'];
            $_SESSION['userRole'] = 'PENGHUNI';

            header("Location: /doremi-app/dashboard");
            exit;
        }

        header("Location: " . "/doremi-app/login.php" . '?status=error&message=Email tidak Terdaftar!');
        exit;
    }
}
