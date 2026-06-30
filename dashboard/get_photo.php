<?php
session_start();
if (!isset($_SESSION['userId'])) {
    http_response_code(403);
    exit('Unauthorized');
}
session_write_close(); // Release session lock early to allow parallel image fetching

require_once '../db.php';

$type = $_GET['type'] ?? '';
$id = (int)($_GET['id'] ?? 0);

if (!$type || !$id) {
    http_response_code(400);
    exit('Bad Request');
}

$photoData = null;

if ($type === 'maintenance_laporan') {
    $stmt = mysqli_prepare($db, "CALL sp_getFotoLaporanFromMaintenance(?)");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $photoData);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
} elseif ($type === 'maintenance_perbaikan') {
    $stmt = mysqli_prepare($db, "CALL sp_getFotoMaintenanceFromMaintenance(?)");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $photoData);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
} elseif ($type === 'paket_pengambilan') {
    $stmt = mysqli_prepare($db, "CALL sp_getFotoPengambilanFromPengambilanPaket(?)");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $photoData);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
}

if (empty($photoData)) {
    http_response_code(404);
    exit('Not Found');
}

// Serve base64 data URIs
if (str_starts_with($photoData, 'data:image/')) {
    $pos = strpos($photoData, ';base64,');
    if ($pos !== false) {
        $header = substr($photoData, 0, $pos);
        $contentType = substr($header, 5); // strip "data:"
        $base64Data = substr($photoData, $pos + 8);
        $binaryData = base64_decode($base64Data);

        header("Content-Type: $contentType");
        header("Content-Length: " . strlen($binaryData));
        header("Cache-Control: public, max-age=86400"); // Cache for 1 day
        echo $binaryData;
        exit;
    }
}

// If it's a web URL or absolute path, redirect to it
if (
    str_starts_with($photoData, 'http://')
    || str_starts_with($photoData, 'https://')
    || str_starts_with($photoData, '/')
) {
    header("Location: $photoData");
    exit;
}

// Otherwise, serve it if it's a local file relative to document root
$filePath = $_SERVER['DOCUMENT_ROOT'] . '/doremi-app/' . ltrim($photoData, '/');
if (file_exists($filePath)) {
    $mimeType = mime_content_type($filePath);
    header("Content-Type: $mimeType");
    header("Content-Length: " . filesize($filePath));
    header("Cache-Control: public, max-age=86400");
    readfile($filePath);
    exit;
}

http_response_code(404);
exit('Not Found');
