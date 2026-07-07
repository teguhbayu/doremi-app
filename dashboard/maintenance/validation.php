<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

use Respect\Validation\Validator as v;

function collectMaintenanceReportInput(array $source): array
{
    $targetType = trim($source['targetType'] ?? $source['target_tipe'] ?? 'ruangan');
    $targetValue = trim($source['targetValue'] ?? '');

    if ($targetValue === '') {
        $targetValue = $targetType === 'ruangan'
            ? trim($source['ruangan_id'] ?? '')
            : trim($source['inventaris_id'] ?? '');
    }

    return [
        'jenisLaporan' => trim($source['jenisLaporan'] ?? $source['skala_prioritas'] ?? ''),
        'targetType' => $targetType,
        'targetValue' => $targetValue,
        'deskripsi' => trim($source['deskripsi'] ?? ''),
    ];
}

function validateMaintenanceReportInput(mysqli $db, array $input): ?string
{
    $schema = v::keySet(
        v::key('jenisLaporan', v::in(['Kerusakan Ringan', 'Kerusakan Sedang', 'Kerusakan Darurat / Berat'])),
        v::key('targetType', v::in(['ruangan', 'inventaris'])),
        v::key('targetValue', v::numericVal()),
        v::key('deskripsi', v::stringType()->length(1, 1000))
    );

    if (!$schema->validate($input)) {
        return 'Data input tidak valid.';
    }

    if (!checkMaintenanceTargetExists($db, $input['targetType'], (int) $input['targetValue'])) {
        return $input['targetType'] === 'ruangan'
            ? 'Ruangan yang dipilih tidak ditemukan.'
            : 'Inventaris yang dipilih tidak ditemukan.';
    }

    return null;
}

function resolveMaintenanceTargetIds(array $input): array
{
    return [
        'ruanganId' => $input['targetType'] === 'ruangan' ? (int) $input['targetValue'] : null,
        'inventarisId' => $input['targetType'] === 'inventaris' ? (int) $input['targetValue'] : null,
    ];
}
