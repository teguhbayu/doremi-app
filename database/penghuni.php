<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

require_once __DIR__ . '/query.php';

function fetchPenghuniIdentityRows(mysqli $db, int $isDeleted, ?int $excludePenghuniId = null): array
{
    $sql = "SELECT PenghuniID, Nim, Email, NoHP FROM penghuni WHERE IsDeleted = ?";
    $types = 'i';
    $params = [$isDeleted];

    if ($excludePenghuniId !== null) {
        $sql .= " AND PenghuniID != ?";
        $types .= 'i';
        $params[] = $excludePenghuniId;
    }

    return dbFetchAll($db, $sql, $types, $params);
}

function fetchPenghuniById(mysqli $db, int $penghuniId): ?array
{
    return dbFetchOne(
        $db,
        "SELECT * FROM penghuni WHERE PenghuniID = ? AND IsDeleted = 0 LIMIT 1",
        'i',
        [$penghuniId]
    );
}

function fetchActiveKamarWithOccupancy(mysqli $db): array
{
    return dbFetchAll(
        $db,
        "SELECT
            k.KamarID,
            k.NomorKamar,
            k.Lantai,
            k.KapasitasPenghuni,
            COUNT(p.PenghuniID) AS JumlahPenghuniAktual
        FROM kamar k
        LEFT JOIN penghuni p ON p.KamarID = k.KamarID AND p.IsDeleted = 0
        WHERE k.IsDeleted = 0
        GROUP BY k.KamarID, k.NomorKamar, k.Lantai, k.KapasitasPenghuni
        ORDER BY k.NomorKamar ASC"
    );
}

function fetchKamarForPenghuniAssignment(mysqli $db, int $kamarId): ?array
{
    return dbFetchOne(
        $db,
        "SELECT KamarID, NomorKamar, KapasitasPenghuni, Lantai
         FROM kamar
         WHERE KamarID = ? AND IsDeleted = 0
         LIMIT 1",
        'i',
        [$kamarId]
    );
}

function fetchPenghuniRoomOccupantSummary(mysqli $db, int $kamarId, ?int $excludePenghuniId = null): array
{
    $sql = "SELECT COUNT(*) AS total, GROUP_CONCAT(DISTINCT JenisKelamin ORDER BY JenisKelamin SEPARATOR ',') AS genders
            FROM penghuni
            WHERE KamarID = ? AND IsDeleted = 0";
    $types = 'i';
    $params = [$kamarId];

    if ($excludePenghuniId !== null) {
        $sql .= " AND PenghuniID != ?";
        $types .= 'i';
        $params[] = $excludePenghuniId;
    }

    return dbFetchOne($db, $sql, $types, $params) ?? ['total' => 0, 'genders' => ''];
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
