<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

require_once __DIR__ . '/query.php';

function fetchPenghuniIdentityRows(mysqli $db, int $isDeleted, ?int $excludePenghuniId = null): array
{
    $excludePenghuniId ??= 0;
    return dbFetchAll($db, 'SELECT PenghuniID, Nim, Email, NoHP FROM penghuni WHERE IsDeleted = ? AND (? = 0 OR PenghuniID != ?)', 'iii', [$isDeleted, $excludePenghuniId, $excludePenghuniId]);
}

function findActivePetugasByEmail(mysqli $db, string $email): ?array
{
    return dbFetchOne($db, 'SELECT PetugasID FROM petugas WHERE IsDeleted = 0 AND LOWER(Email) = LOWER(?) LIMIT 1', 's', [$email]);
}

function fetchPenghuniById(mysqli $db, int $penghuniId): ?array
{
    return dbFetchOne($db, 'SELECT * FROM penghuni WHERE PenghuniID = ? AND IsDeleted = 0 LIMIT 1', 'i', [$penghuniId]);
}

function fetchActiveKamarWithOccupancy(mysqli $db): array
{
    return dbFetchAll($db, 'SELECT k.KamarID, k.NomorKamar, k.Lantai, k.KapasitasPenghuni, COUNT(p.PenghuniID) AS JumlahPenghuniAktual FROM kamar k LEFT JOIN penghuni p ON p.KamarID = k.KamarID AND p.IsDeleted = 0 WHERE k.IsDeleted = 0 GROUP BY k.KamarID, k.NomorKamar, k.Lantai, k.KapasitasPenghuni ORDER BY k.NomorKamar ASC');
}

function fetchKamarForPenghuniAssignment(mysqli $db, int $kamarId): ?array
{
    return dbFetchOne($db, 'SELECT KamarID, NomorKamar, KapasitasPenghuni, Lantai FROM kamar WHERE KamarID = ? AND IsDeleted = 0 LIMIT 1', 'i', [$kamarId]);
}

function fetchPenghuniRoomOccupantSummary(mysqli $db, int $kamarId, ?int $excludePenghuniId = null): array
{
    $excludePenghuniId ??= 0;
    return dbFetchOne($db, "SELECT COUNT(*) AS total, GROUP_CONCAT(DISTINCT JenisKelamin ORDER BY JenisKelamin SEPARATOR ',') AS genders FROM penghuni WHERE KamarID = ? AND IsDeleted = 0 AND (? = 0 OR PenghuniID != ?)", 'iii', [$kamarId, $excludePenghuniId, $excludePenghuniId])
        ?? ['total' => 0, 'genders' => ''];
}

function fetchPenghuniList(mysqli $db): array
{
    return dbFetchAll($db, 'SELECT p.*, k.NomorKamar FROM penghuni p LEFT JOIN kamar k ON p.KamarID = k.KamarID WHERE p.IsDeleted = 0');
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
        'INSERT INTO penghuni (KamarID, NamaPenghuni, Nim, JenisKelamin, NoHP, Email, Password, Alamat, IsDeleted) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)',
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
        'UPDATE penghuni SET KamarID = ?, NamaPenghuni = ?, Nim = ?, JenisKelamin = ?, NoHP = ?, Email = ?, Password = ?, Alamat = ?, IsDeleted = 0 WHERE PenghuniID = ?',
        'isssssssi',
        [$kamarId, $nama, $nim, $jenisKelamin, $noHp, $email, $passwordHash, $alamat, $penghuniId]
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
            'UPDATE penghuni SET KamarID = ?, NamaPenghuni = ?, Nim = ?, JenisKelamin = ?, NoHP = ?, Email = ?, Password = ?, Alamat = ? WHERE PenghuniID = ?',
            'isssssssi',
            [$kamarId, $nama, $nim, $jenisKelamin, $noHp, $email, $passwordHash, $alamat, $penghuniId]
        );

        return true;
    }

    dbExecute(
        $db,
        'UPDATE penghuni SET KamarID = ?, NamaPenghuni = ?, Nim = ?, JenisKelamin = ?, NoHP = ?, Email = ?, Alamat = ? WHERE PenghuniID = ?',
        'issssssi',
        [$kamarId, $nama, $nim, $jenisKelamin, $noHp, $email, $alamat, $penghuniId]
    );

    return true;
}
