<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

require_once __DIR__ . '/query.php';

function getPaketLatestPickupJoinSql(): string
{
    return "
        LEFT JOIN (
            SELECT pp1.PengambilanPaketID, pp1.PaketID, pp1.PenghuniID, pp1.PetugasID,
                   pp1.FotoPengambilan, pp1.WaktuPengambilan, pp1.Status, pp1.Keterangan,
                   (pp1.FotoPengambilan IS NOT NULL AND pp1.FotoPengambilan != '') AS HasFotoPengambilan
            FROM pengambilanpaket pp1
            INNER JOIN (
                SELECT PaketID, MAX(PengambilanPaketID) AS LatestPengambilanPaketID
                FROM pengambilanpaket
                GROUP BY PaketID
            ) latest ON latest.LatestPengambilanPaketID = pp1.PengambilanPaketID
        ) pp ON pp.PaketID = pk.PaketID
    ";
}

function fetchPaketsForRole(mysqli $db, string $role, int $userId): array
{
    $latestPickupJoin = getPaketLatestPickupJoinSql();

    if ($role === 'SIGAP') {
        return dbFetchAll(
            $db,
            "SELECT pk.*, ph.NamaPenghuni, ph.Nim, k.NomorKamar, pt.NamaPetugas,
                    pp.PengambilanPaketID, pp.Status, pp.WaktuPengambilan, pp.Keterangan, pp.HasFotoPengambilan
             FROM paket pk
             JOIN penghuni ph ON pk.PenghuniID = ph.PenghuniID
             LEFT JOIN kamar k ON ph.KamarID = k.KamarID
             JOIN petugas pt ON pk.PetugasID = pt.PetugasID
             $latestPickupJoin
             ORDER BY pk.PaketID DESC"
        );
    }

    return dbFetchAll(
        $db,
        "SELECT pk.*, ph.NamaPenghuni, ph.Nim, k.NomorKamar,
                pt.NamaPetugas AS NamaPetugasPaket,
                pp.PengambilanPaketID, pp.Status, pp.WaktuPengambilan,
                pp.Keterangan, pp.HasFotoPengambilan
         FROM paket pk
         JOIN penghuni ph ON pk.PenghuniID = ph.PenghuniID
         LEFT JOIN kamar k ON ph.KamarID = k.KamarID
         JOIN petugas pt ON pk.PetugasID = pt.PetugasID
         $latestPickupJoin
         WHERE pk.PenghuniID = ?
         ORDER BY pk.PaketID DESC",
        'i',
        [$userId]
    );
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
    $latestPickupJoin = getPaketLatestPickupJoinSql();
    $where = 'pk.PaketID = ?';
    $types = 'i';
    $params = [$paketId];

    if ($penghuniId !== null) {
        $where .= ' AND pk.PenghuniID = ?';
        $types .= 'i';
        $params[] = $penghuniId;
    }

    return dbFetchOne(
        $db,
        "SELECT pk.*, ph.NamaPenghuni, ph.Nim, k.NomorKamar,
                pt.NamaPetugas AS NamaPetugasPaket,
                pp.PengambilanPaketID, pp.PetugasID AS PickupPetugasID,
                pp.FotoPengambilan, pp.WaktuPengambilan, pp.Status, pp.Keterangan
         FROM paket pk
         JOIN penghuni ph ON pk.PenghuniID = ph.PenghuniID
         LEFT JOIN kamar k ON ph.KamarID = k.KamarID
         JOIN petugas pt ON pk.PetugasID = pt.PetugasID
         $latestPickupJoin
         WHERE $where
         LIMIT 1",
        $types,
        $params
    );
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
            "UPDATE pengambilanpaket
             SET PenghuniID = ?, PetugasID = ?, FotoPengambilan = ?, WaktuPengambilan = ?, Status = ?, Keterangan = ?
             WHERE PengambilanPaketID = ?",
            'iissssi',
            [$penghuniId, $petugasId, $fotoPengambilan, $waktuPengambilan, $status, $keterangan, $pengambilanPaketId]
        );

        return true;
    }

    dbExecute(
        $db,
        "INSERT INTO pengambilanpaket (PaketID, PenghuniID, PetugasID, FotoPengambilan, WaktuPengambilan, Status, Keterangan)
         VALUES (?, ?, ?, ?, ?, ?, ?)",
        'iiissss',
        [$paketId, $penghuniId, $petugasId, $fotoPengambilan, $waktuPengambilan, $status, $keterangan]
    );

    return true;
}

function updatePaketPickupReview(mysqli $db, int $pengambilanPaketId, int $petugasId, string $status, string $keterangan): bool
{
    dbExecute(
        $db,
        "UPDATE pengambilanpaket
         SET PetugasID = ?, Status = ?, Keterangan = ?
         WHERE PengambilanPaketID = ?",
        'issi',
        [$petugasId, $status, $keterangan, $pengambilanPaketId]
    );

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
