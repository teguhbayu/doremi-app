<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

require_once __DIR__ . '/../../utils/validation_helpers.php';

use Respect\Validation\Validator as v;

function collectMaintenanceReportInput(array $source): array
{
    $locationType = trim($source['location_type'] ?? '');
    $ruanganId = trim($source['ruangan_id'] ?? '');
    $kamarId = trim($source['kamar_id'] ?? '');
    $inventarisId = trim($source['inventaris_id'] ?? '');

    if ($inventarisId !== '') {
        $targetType = 'inventaris';
        $targetValue = $inventarisId;
    } else {
        $targetType = $locationType !== '' ? $locationType : ($ruanganId !== '' ? 'ruangan' : ($kamarId !== '' ? 'kamar' : 'ruangan'));
        $targetValue = $targetType === 'ruangan' ? $ruanganId : $kamarId;
    }

    return [
        'jenisLaporan' => trim($source['jenisLaporan'] ?? $source['skala_prioritas'] ?? ''),
        'targetType' => $targetType,
        'targetValue' => $targetValue,
        'deskripsi' => str_replace("\r\n", "\n", trim($source['deskripsi'] ?? '')),
        'location_type' => $locationType,
        'ruangan_id' => $ruanganId,
        'kamar_id' => $kamarId,
        'inventaris_id' => $inventarisId,
    ];
}

function validateMaintenanceReportInput(mysqli $db, array $input): ?string
{
    $fieldError = firstFieldError($input, [
        'jenisLaporan' => ['label' => 'Skala Prioritas', 'rule' => v::in(['Kerusakan Ringan', 'Kerusakan Sedang', 'Kerusakan Darurat / Berat'])],
        'targetType' => ['label' => 'Tipe Target', 'rule' => v::in(['ruangan', 'kamar', 'inventaris'])],
        'targetValue' => ['label' => 'Target Lokasi', 'rule' => v::numericVal()],
        'deskripsi' => ['label' => 'Deskripsi', 'rule' => v::stringType()->length(1, 1000)],
    ]);

    if ($fieldError !== null) {
        return $fieldError;
    }

    if (!checkMaintenanceTargetExists($db, $input['targetType'], (int) $input['targetValue'])) {
        return $input['targetType'] === 'ruangan'
            ? 'Ruangan yang dipilih tidak ditemukan.'
            : ($input['targetType'] === 'kamar' ? 'Kamar yang dipilih tidak ditemukan.' : 'Inventaris yang dipilih tidak ditemukan.');
    }

    return null;
}

function validateMaintenanceUrgencyInput(string $jenisLaporan): ?string
{
    if (!in_array($jenisLaporan, ['Kerusakan Ringan', 'Kerusakan Sedang', 'Kerusakan Darurat / Berat'], true)) {
        return 'Kolom Tingkat Urgensi tidak valid.';
    }

    return null;
}

function resolveMaintenanceTargetIds(array $input): array
{
    return [
        'ruanganId' => $input['targetType'] === 'ruangan' ? (int) $input['targetValue'] : null,
        'kamarId' => $input['targetType'] === 'kamar' ? (int) $input['targetValue'] : null,
        'inventarisId' => $input['targetType'] === 'inventaris' ? (int) $input['targetValue'] : null,
    ];
}
