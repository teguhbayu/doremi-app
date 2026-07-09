<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

require_once __DIR__ . '/query.php';

function fetchPenghuniIdentityRows(mysqli $db, int $isDeleted, ?int $excludePenghuniId = null): array
{
    return dbFetchAll($db, "CALL sp_getPenghuniIdentityRows(?, ?)", 'ii', [$isDeleted, $excludePenghuniId ?? 0]);
}

function fetchPenghuniById(mysqli $db, int $penghuniId): ?array
{
    return dbFetchOne($db, "CALL sp_getPenghuniByIdFull(?)", 'i', [$penghuniId]);
}

function fetchActiveKamarWithOccupancy(mysqli $db): array
{
    return dbFetchAll($db, "CALL sp_getActiveKamarWithOccupancy()");
}

function fetchKamarForPenghuniAssignment(mysqli $db, int $kamarId): ?array
{
    return dbFetchOne($db, "CALL sp_getKamarForPenghuniAssignment(?)", 'i', [$kamarId]);
}

function fetchPenghuniRoomOccupantSummary(mysqli $db, int $kamarId, ?int $excludePenghuniId = null): array
{
    return dbFetchOne($db, "CALL sp_getPenghuniRoomOccupantSummary(?, ?)", 'ii', [$kamarId, $excludePenghuniId ?? 0])
        ?? ['total' => 0, 'genders' => ''];
}

function fetchPenghuniList(mysqli $db): array
{
    return dbFetchAll($db, "CALL sp_getPenghuniList()");
}

function createPenghuni(
    mysqli $db,
    int $kamarId,
    string $nama,
    string $nim,
    string $jenisKelamin,
    string $noHp,
    string $email,
    string $passwordHash,
    string $alamat
): bool {
    dbExecute(
        $db,
        "CALL sp_createPenghuni(?, ?, ?, ?, ?, ?, ?, ?)",
        'isssssss',
        [$kamarId, $nama, $nim, $jenisKelamin, $noHp, $email, $passwordHash, $alamat]
    );

    return true;
}

function restorePenghuni(
    mysqli $db,
    int $penghuniId,
    int $kamarId,
    string $nama,
    string $nim,
    string $jenisKelamin,
    string $noHp,
    string $email,
    string $passwordHash,
    string $alamat
): bool {
    dbExecute(
        $db,
        "CALL sp_restorePenghuni(?, ?, ?, ?, ?, ?, ?, ?, ?)",
        'iisssssss',
        [$penghuniId, $kamarId, $nama, $nim, $jenisKelamin, $noHp, $email, $passwordHash, $alamat]
    );

    return true;
}

function updatePenghuni(
    mysqli $db,
    int $penghuniId,
    int $kamarId,
    string $nama,
    string $nim,
    string $jenisKelamin,
    string $noHp,
    string $email,
    string $alamat,
    ?string $passwordHash = null
): bool {
    if ($passwordHash !== null) {
        dbExecute(
            $db,
            "CALL sp_updatePenghuniWithPassword(?, ?, ?, ?, ?, ?, ?, ?, ?)",
            'iisssssss',
            [$penghuniId, $kamarId, $nama, $nim, $jenisKelamin, $noHp, $email, $passwordHash, $alamat]
        );

        return true;
    }

    dbExecute(
        $db,
        "CALL sp_updatePenghuniWithoutPassword(?, ?, ?, ?, ?, ?, ?, ?)",
        'iissssss',
        [$penghuniId, $kamarId, $nama, $nim, $jenisKelamin, $noHp, $email, $alamat]
    );

    return true;
}
