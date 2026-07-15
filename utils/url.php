<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

function app_base_path(): string
{
    static $basePath = null;

    if ($basePath !== null) {
        return $basePath;
    }

    $configuredPath = $_ENV['APP_BASE_PATH'] ?? getenv('APP_BASE_PATH') ?: '';
    if ($configuredPath === '' && is_file(__DIR__ . '/../.env')) {
        $environment = parse_ini_file(__DIR__ . '/../.env', false, INI_SCANNER_RAW);
        $configuredPath = $environment['APP_BASE_PATH'] ?? '';
    }

    $configuredPath = trim($configuredPath, '/');
    $basePath = $configuredPath === '' ? '' : '/' . $configuredPath;

    return $basePath;
}

function app_url(string $path = ''): string
{
    return app_base_path() . '/' . ltrim($path, '/');
}

function app_redirect(string $path, int $statusCode = 302): never
{
    header('Location: ' . app_url($path), true, $statusCode);
    exit;
}
