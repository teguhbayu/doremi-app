<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

require_once __DIR__ . '/query.php';

function fetchPaketsForRole(mysqli $db, string $role, int $userId): array
{
    return dbFetchAll($db, "CALL sp_getPaketListForRole(?, ?)", 'si', [$role, $userId]);
}

function summarizePaketStatuses(array $pakets): array
{
    $summary = [
        'total' => count($pakets),
        'tertukar' => 0,
        'sudahDiambil' => 0,
        'belumDiambil' => 0,
    ];

    foreach ($pakets as $paket) {
        $status = $paket['Status'] ?? 'Belum Diambil';

        if ($status === 'TERTUKAR') {
            $summary['tertukar']++;
        } elseif ($status === 'Sudah Diambil') {
            $summary['sudahDiambil']++;
        } else {
            $summary['belumDiambil']++;
        }
    }

    return $summary;
}

function fetchActivePenghuniOptions(mysqli $db): array
{
    return dbFetchAll($db, "CALL sp_getActivePenghuniForSelect()");
}

function fetchPaketDetail(mysqli $db, int $paketId): ?array
{
    return dbFetchOne($db, "CALL sp_getPaketDetail(?)", 'i', [$paketId]);
}

function fetchPaketWithLatestPickup(mysqli $db, int $paketId, ?int $penghuniId = null): ?array
{
    return dbFetchOne($db, "CALL sp_getPaketWithLatestPickup(?, ?)", 'ii', [$paketId, $penghuniId ?? 0]);
}

function checkPenghuniExists(mysqli $db, int $penghuniId): bool
{
    return dbFetchOne($db, "CALL sp_checkPenghuniExist(?)", 'i', [$penghuniId]) !== null;
}

function createPaket(
    mysqli $db,
    int $petugasId,
    string $namaPengirim,
    string $kurir,
    string $jenisPaket,
    string $waktuSampai,
    int $penghuniId
): bool {
    dbExecute(
        $db,
        "CALL sp_createPaket(?, ?, ?, ?, ?, ?)",
        'issssi',
        [$petugasId, $namaPengirim, $kurir, $jenisPaket, $waktuSampai, $penghuniId]
    );

    return true;
}

function updatePaket(
    mysqli $db,
    int $paketId,
    string $namaPengirim,
    string $kurir,
    string $jenisPaket,
    string $waktuSampai,
    int $penghuniId
): bool {
    dbExecute(
        $db,
        "CALL sp_updatePaket(?, ?, ?, ?, ?, ?)",
        'issssi',
        [$paketId, $namaPengirim, $kurir, $jenisPaket, $waktuSampai, $penghuniId]
    );

    return true;
}

function countPackagePickupsByPaketId(mysqli $db, int $paketId): int
{
    return (int) dbFetchValue($db, "CALL sp_countPackagePickupByPaketId(?)", 'i', [$paketId]);
}

function deletePaket(mysqli $db, int $paketId): bool
{
    dbExecute($db, "CALL sp_deletePaket(?)", 'i', [$paketId]);
    return true;
}

function savePaketPickup(
    mysqli $db,
    ?int $pengambilanPaketId,
    int $paketId,
    int $penghuniId,
    int $petugasId,
    string $fotoPengambilan,
    string $waktuPengambilan,
    string $status,
    ?string $keterangan
): bool {
    if ($pengambilanPaketId !== null) {
        dbExecute(
            $db,
            "CALL sp_updatePaketPickup(?, ?, ?, ?, ?, ?, ?)",
            'iiissss',
            [$pengambilanPaketId, $penghuniId, $petugasId, $fotoPengambilan, $waktuPengambilan, $status, $keterangan]
        );

        return true;
    }

    dbExecute(
        $db,
        "CALL sp_insertPaketPickup(?, ?, ?, ?, ?, ?, ?)",
        'iiissss',
        [$paketId, $penghuniId, $petugasId, $fotoPengambilan, $waktuPengambilan, $status, $keterangan]
    );

    return true;
}

function updatePaketPickupReview(mysqli $db, int $pengambilanPaketId, int $petugasId, string $status, string $keterangan): bool
{
    dbExecute($db, "CALL sp_updatePaketPickupReview(?, ?, ?, ?)", 'isss', [$pengambilanPaketId, $petugasId, $status, $keterangan]);
    return true;
}

function fetchPaketPickupPhoto(mysqli $db, int $paketId): ?string
{
    $row = dbFetchOne($db, "CALL sp_getFotoPengambilanFromPengambilanPaket(?)", 'i', [$paketId]);
    if ($row === null) {
        return null;
    }

    return reset($row) ?: null;
}
