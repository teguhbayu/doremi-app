<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

function kamar_allowed_floors(): array
{
    return ['1', '2', '3', '4', '5', '6', '7'];
}

function kamar_normalize_segment(string $value): string
{
    return strtoupper((string) preg_replace('/\s+/', '', trim($value)));
}

function kamar_build_nomor(string $lantai, string $bagian): string
{
    return trim($lantai) . kamar_normalize_segment($bagian);
}

function kamar_has_lantai_prefix(string $nomorKamar, string $lantai): bool
{
    $normalizedNomor = kamar_normalize_segment($nomorKamar);
    $normalizedLantai = trim($lantai);

    if ($normalizedLantai === '' || !str_starts_with($normalizedNomor, $normalizedLantai)) {
        return false;
    }

    return substr($normalizedNomor, strlen($normalizedLantai)) !== '';
}

function kamar_extract_bagian(string $nomorKamar, string $lantai): string
{
    $normalizedNomor = kamar_normalize_segment($nomorKamar);
    $normalizedLantai = trim($lantai);

    if (kamar_has_lantai_prefix($normalizedNomor, $normalizedLantai)) {
        return substr($normalizedNomor, strlen($normalizedLantai));
    }

    return $normalizedNomor;
}
