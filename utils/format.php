<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

function escapeHtml(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function textLength(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
}

function formatDateTime(?string $value, string $format = 'd M Y H:i', string $fallback = '-'): string
{
    if (empty($value)) {
        return $fallback;
    }

    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return $fallback;
    }

    return date($format, $timestamp);
}

function normalizeDateTimeForSql(?string $value): ?string
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

function normalizeDateTimeInputValue(?string $value): string
{
    return formatDateTime($value, 'Y-m-d\TH:i', '');
}
