<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}
require_once __DIR__ . '/../../utils/url.php';

function paket_redirect(string $path, ?string $status = null, ?string $message = null): void
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

function paket_require_roles(array $allowedRoles): void
{
    if (!isset($_SESSION['userId'])) {
        app_redirect('login.php');
    }

    if (!in_array($_SESSION['userRole'] ?? '', $allowedRoles, true)) {
        app_redirect('dashboard/');
    }
}

function paket_is_valid_length(string $value, int $min, int $max): bool
{
    $length = function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
    return $length >= $min && $length <= $max;
}

function paket_allowed_types(): array
{
    return ['Paket', 'Dokumen'];
}

function paket_normalize_type(?string $value): ?string
{
    $normalizedValue = ucfirst(strtolower(trim((string) $value)));
    return in_array($normalizedValue, paket_allowed_types(), true) ? $normalizedValue : null;
}

function paket_type_label(?string $value): string
{
    return paket_normalize_type($value) ?? 'Paket';
}

function paket_type_badge_class(?string $value): string
{
    return paket_type_label($value) === 'Dokumen'
        ? 'bg-primary-subtle text-primary-emphasis'
        : 'bg-secondary-subtle text-secondary-emphasis';
}

function paket_normalize_datetime(?string $value): ?string
{
    if (empty($value)) {
        return null;
    }

    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return null;
    }

    return date('Y-m-d H:i:s', $timestamp);
}

function paket_datetime_input_value(?string $value): string
{
    if (empty($value)) {
        return '';
    }

    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return '';
    }

    return date('Y-m-d\TH:i', $timestamp);
}

function paket_photo_url(?string $path): string
{
    if (empty($path)) {
        return '#';
    }

    if (
        str_starts_with($path, 'data:image/')
        || str_starts_with($path, 'data:application/octet-stream;base64,')
    ) {
        return $path;
    }

    if (
        str_starts_with($path, 'http://')
        || str_starts_with($path, 'https://')
        || str_starts_with($path, '/')
    ) {
        return $path;
    }

    return app_url($path);
}

function paket_status_meta(?string $status): array
{
    return match ($status ?? 'Belum Diambil') {
        'Sudah Diambil' => [
            'label' => 'Sudah Diambil',
            'class' => 'bg-success',
        ],
        'TERTUKAR' => [
            'label' => 'PAKET TERTUKAR',
            'class' => 'bg-danger',
        ],
        default => [
            'label' => 'Belum Diambil',
            'class' => 'bg-warning text-dark',
        ],
    };
}

function paket_is_final_status(?string $status): bool
{
    return in_array($status, ['Sudah Diambil', 'TERTUKAR'], true);
}

function paket_penghuni_option_label(array $penghuni): string
{
    $label = trim((string) ($penghuni['NamaPenghuni'] ?? ''));

    if (!empty($penghuni['Nim'])) {
        $label .= ' (' . trim((string) $penghuni['Nim']) . ')';
    }

    if (!empty($penghuni['NomorKamar'])) {
        $label .= ' - Kamar ' . trim((string) $penghuni['NomorKamar']);
    }

    return $label;
}

function paket_cleanup_legacy_photo(?string $currentPath): void
{
    if (empty($currentPath) || !str_starts_with($currentPath, 'assets/uploads/paket/')) {
        return;
    }

    $baseDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'paket';
    $baseRealPath = realpath($baseDir);
    $oldFilePath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($currentPath, '/\\'));
    $oldRealPath = realpath($oldFilePath);

    if (
        $baseRealPath !== false
        && $oldRealPath !== false
        && str_starts_with($oldRealPath, $baseRealPath)
        && is_file($oldRealPath)
    ) {
        @unlink($oldRealPath);
    }
}

function paket_store_photo(array $file, ?string $currentPath = null): string
{
    $uploadError = $file['error'] ?? UPLOAD_ERR_NO_FILE;

    if ($uploadError === UPLOAD_ERR_NO_FILE) {
        if (!empty($currentPath)) {
            return $currentPath;
        }

        throw new RuntimeException('Foto pengambilan wajib diunggah.');
    }

    if ($uploadError !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Gagal mengunggah foto pengambilan.');
    }

    if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
        throw new RuntimeException('Ukuran foto pengambilan maksimal 2MB.');
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
        throw new RuntimeException('Foto pengambilan gagal diproses.');
    }

    paket_cleanup_legacy_photo($currentPath);

    return 'data:' . $mimeType . ';base64,' . base64_encode($rawBinary);
}
