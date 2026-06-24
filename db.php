<?php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Access denied');
}
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeload();

date_default_timezone_set($_ENV["APP_TIMEZONE"] ?? 'Asia/Bangkok');

$host = $_ENV["DB_HOST"] ?? '127.0.0.1';
$port = (int) ($_ENV["DB_PORT"] ?? '3306');
$user = $_ENV["DB_USER"];
$pass = $_ENV["DB_PASS"];
$database = $_ENV["DB_DATABASE"];

try {
    $db = mysqli_connect($host, $user, $pass, $database, $port);
} catch (mysqli_sql_exception $e) {
    $db = null;
}

if (!$db || mysqli_connect_error()) {
    http_response_code(503);
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8">
    <title>Koneksi Database Gagal</title>
    <style>
        body { font-family: sans-serif; display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; background:#f8fafc; }
        .box { background:#fff; border-radius:16px; padding:40px 48px; box-shadow:0 4px 24px rgba(0,0,0,.08); text-align:center; max-width:420px; }
        h2 { color:#ef4444; margin:0 0 8px; font-size:22px; }
        p  { color:#64748b; line-height:1.6; margin:0 0 20px; }
        code { background:#f1f5f9; padding:2px 8px; border-radius:6px; font-size:13px; color:#146c94; }
        .retry { display:inline-block; padding:10px 24px; background:#146c94; color:#fff; border-radius:8px; text-decoration:none; font-weight:600; }
    </style></head><body>
    <div class="box">
        <h2>&#x26A0; Koneksi Database Gagal</h2>
        <p>Tidak dapat terhubung ke server database.<br>
        Server <code>' . htmlspecialchars($host) . '</code> tidak bisa dijangkau.</p>
        <p>Coba beberapa saat lagi atau hubungi administrator.</p>
        <a class="retry" href="javascript:location.reload()">&#x21BA; Coba Lagi</a>
    </div></body></html>';
    exit;
}
