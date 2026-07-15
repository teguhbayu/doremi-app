<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}
require_once __DIR__ . '/../../utils/url.php';

function maintenance_redirect(string $path, ?string $status = null, ?string $message = null): void
{
    if ($status !== null) {
        $path .= (str_contains($path, '?') ? '&' : '?') . http_build_query([
            'status' => $status,
            'message' => $message ?? '',
        ]);
    }
    header('Location: ' . app_url($path));
    exit;
}

function maintenance_require_roles(array $allowedRoles): void
{
    if (!isset($_SESSION['userId'])) {
        app_redirect('login.php');
    }

    if (!in_array($_SESSION['userRole'] ?? '', $allowedRoles, true)) {
        app_redirect('dashboard/');
    }
}

function maintenance_status_meta(?string $status): array
{
    return match ($status) {
        'Diproses' => [
            'label' => 'Diproses',
            'class' => 'bg-info text-white',
        ],
        'Selesai' => [
            'label' => 'Selesai',
            'class' => 'bg-success text-white',
        ],
        default => [
            'label' => 'Menunggu',
            'class' => 'bg-warning text-dark',
        ],
    };
}

function maintenance_severity_meta(?string $severity): array
{
    return match ($severity) {
        'Kerusakan Darurat / Berat' => [
            'label' => 'Darurat / Berat',
            'class' => 'tw:bg-red-500 tw:text-white',
            'borderClass' => 'tw:border-l-4 tw:border-l-red-500 tw:bg-red-50/20'
        ],
        'Kerusakan Sedang' => [
            'label' => 'Sedang',
            'class' => 'tw:bg-amber-500 tw:text-slate-900',
            'borderClass' => ''
        ],
        default => [
            'label' => 'Ringan',
            'class' => 'tw:bg-slate-400 tw:text-white',
            'borderClass' => ''
        ],
    };
}

function maintenance_store_photo(array $file, ?string $currentPath = null): string
{
    $uploadError = $file['error'] ?? UPLOAD_ERR_NO_FILE;

    if ($uploadError === UPLOAD_ERR_NO_FILE) {
        if (!empty($currentPath)) {
            return $currentPath;
        }
        return '';
    }

    if ($uploadError !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Gagal mengunggah foto.');
    }

    if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
        throw new RuntimeException('Ukuran foto maksimal adalah 2MB.');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = $finfo ? finfo_file($finfo, $file['tmp_name']) : false;
    if ($finfo) {
        finfo_close($finfo);
    }

    $allowedMimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (!$mimeType || !isset($allowedMimeTypes[$mimeType])) {
        throw new RuntimeException('Format foto harus JPG, PNG, atau WEBP.');
    }

    $rawBinary = file_get_contents($file['tmp_name']);
    if ($rawBinary === false) {
        throw new RuntimeException('Foto gagal diproses.');
    }

    return 'data:' . $mimeType . ';base64,' . base64_encode($rawBinary);
}
