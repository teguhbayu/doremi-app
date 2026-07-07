<?php
session_start();
require_once '../db.php';
require_once 'config.php';
require_once '../database/auth.php';
require_once 'helpers.php';

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    if (!isset($token['error'])) {
        $client->setAccessToken($token['access_token']);

        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();

        $email = $google_account_info->email;

        $authUser = authAttemptEmailLogin($db, $email);
        if ($authUser !== null) {
            authSetUserSession($authUser);
            authRedirectToDashboard();
        }

        authRedirectToLoginError('Email tidak Terdaftar!');
    }
}
