<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

require_once __DIR__ . '/query.php';

function fetchPaketsForRole(mysqli $db, string $role, int $userId): array
{
    return dbFetchAll($db, "SELECT pk.*, ph.NamaPenghuni, ph.Nim, k.NomorKamar, pt.NamaPetugas, pp.PengambilanPaketID, pp.Status, pp.WaktuPengambilan, pp.Keterangan, pp.HasFotoPengambilan FROM paket pk JOIN penghuni ph ON pk.PenghuniID = ph.PenghuniID LEFT JOIN kamar k ON ph.KamarID = k.KamarID JOIN petugas pt ON pk.PetugasID = pt.PetugasID LEFT JOIN (SELECT pp1.PengambilanPaketID, pp1.PaketID, pp1.PenghuniID, pp1.PetugasID, pp1.FotoPengambilan, pp1.WaktuPengambilan, pp1.Status, pp1.Keterangan, (pp1.FotoPengambilan IS NOT NULL AND pp1.FotoPengambilan != '') AS HasFotoPengambilan FROM pengambilanpaket pp1 INNER JOIN (SELECT PaketID, MAX(PengambilanPaketID) AS LatestPengambilanPaketID FROM pengambilanpaket GROUP BY PaketID) latest ON latest.LatestPengambilanPaketID = pp1.PengambilanPaketID) pp ON pp.PaketID = pk.PaketID WHERE ? IN ('SIGAP', 'PENGURUS') OR pk.PenghuniID = ? ORDER BY pk.PaketID DESC", 'si', [$role, $userId]);
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
    return dbFetchAll($db, 'SELECT p.PenghuniID, p.NamaPenghuni, p.Nim, k.NomorKamar, udf_formatPenghuniLabel(p.NamaPenghuni, p.Nim, k.NomorKamar) AS OptionLabel FROM penghuni p LEFT JOIN kamar k ON p.KamarID = k.KamarID WHERE p.IsDeleted = 0 ORDER BY p.NamaPenghuni');
}

function fetchPaketDetail(mysqli $db, int $paketId): ?array
{
    return dbFetchOne($db, 'SELECT pk.*, pt.NamaPetugas FROM paket pk JOIN petugas pt ON pk.PetugasID = pt.PetugasID WHERE pk.PaketID = ? LIMIT 1', 'i', [$paketId]);
}

function fetchPaketWithLatestPickup(mysqli $db, int $paketId, ?int $penghuniId = null): ?array
{
    $penghuniId ??= 0;
    return dbFetchOne($db, "SELECT pk.*, ph.NamaPenghuni, ph.Nim, k.NomorKamar, pt.NamaPetugas AS NamaPetugasPaket, pp.PengambilanPaketID, pp.PetugasID AS PickupPetugasID, pp.FotoPengambilan, pp.WaktuPengambilan, pp.Status, pp.Keterangan FROM paket pk JOIN penghuni ph ON pk.PenghuniID = ph.PenghuniID LEFT JOIN kamar k ON ph.KamarID = k.KamarID JOIN petugas pt ON pk.PetugasID = pt.PetugasID LEFT JOIN (SELECT pp1.PengambilanPaketID, pp1.PaketID, pp1.PenghuniID, pp1.PetugasID, pp1.FotoPengambilan, pp1.WaktuPengambilan, pp1.Status, pp1.Keterangan FROM pengambilanpaket pp1 INNER JOIN (SELECT PaketID, MAX(PengambilanPaketID) AS LatestPengambilanPaketID FROM pengambilanpaket GROUP BY PaketID) latest ON latest.LatestPengambilanPaketID = pp1.PengambilanPaketID) pp ON pp.PaketID = pk.PaketID WHERE pk.PaketID = ? AND (? = 0 OR pk.PenghuniID = ?) LIMIT 1", 'iii', [$paketId, $penghuniId, $penghuniId]);
}

function checkPenghuniExists(mysqli $db, int $penghuniId): bool
{
    return dbFetchOne($db, 'SELECT PenghuniID FROM penghuni WHERE PenghuniID = ? AND IsDeleted = 0 LIMIT 1', 'i', [$penghuniId]) !== null;
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
        'INSERT INTO paket (PetugasID, NamaPengirim, Kurir, JenisPaket, WaktuSampai, PenghuniID) VALUES (?, ?, ?, ?, ?, ?)',
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
        'UPDATE paket SET NamaPengirim = ?, Kurir = ?, JenisPaket = ?, WaktuSampai = ?, PenghuniID = ? WHERE PaketID = ?',
        'ssssii',
        [$namaPengirim, $kurir, $jenisPaket, $waktuSampai, $penghuniId, $paketId]
    );

    return true;
}

function countPackagePickupsByPaketId(mysqli $db, int $paketId): int
{
    return (int) dbFetchValue($db, 'SELECT COUNT(*) AS total FROM pengambilanpaket WHERE PaketID = ?', 'i', [$paketId]);
}

function deletePaket(mysqli $db, int $paketId): bool
{
    dbExecute($db, 'DELETE FROM paket WHERE PaketID = ?', 'i', [$paketId]);
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
    $row = dbFetchOne($db, 'SELECT FotoPengambilan FROM pengambilanpaket WHERE PaketID = ?', 'i', [$paketId]);
    if ($row === null) {
        return null;
    }

    return reset($row) ?: null;
}
