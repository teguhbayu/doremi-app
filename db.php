<?php
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeload();

$host = $_ENV["DB_HOST"] ?? '127.0.0.1';
$port = (int) ($_ENV["DB_PORT"] ?? '3306');
$user = $_ENV["DB_USER"];
$pass = $_ENV["DB_PASS"];
$database = $_ENV["DB_DATABASE"];

$db = mysqli_connect($host, $user, $pass, $database, $port);

if (mysqli_connect_error()) {
    echo "db Conn error";
    exit;
}
