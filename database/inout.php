<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

require_once __DIR__ . '/query.php';

function fetchInOutHistoryForPenghuni(mysqli $db, int $penghuniId): array
{
    return dbFetchAll($db, 'SELECT io.*, p.NamaPetugas FROM inoutpenghuni io LEFT JOIN petugas p ON io.PetugasID = p.PetugasID WHERE io.PenghuniID = ? ORDER BY io.InOutID DESC', 'i', [$penghuniId]);
}

function countActiveInOutRequests(mysqli $db, int $penghuniId): int
{
    return (int) dbFetchValue($db, "SELECT COUNT(*) AS count FROM inoutpenghuni WHERE PenghuniID = ? AND Status IN ('Pending', 'Keluar')", 'i', [$penghuniId], 'count');
}

function fetchPendingInOutRequests(mysqli $db): array
{
    return dbFetchAll($db, "SELECT io.*, pe.NamaPenghuni, pe.Nim, k.NomorKamar FROM inoutpenghuni io JOIN penghuni pe ON io.PenghuniID = pe.PenghuniID JOIN kamar k ON pe.KamarID = k.KamarID WHERE io.Status = 'Pending' ORDER BY io.InOutID ASC");
}

function fetchOutsideInOutRequests(mysqli $db): array
{
    return dbFetchAll($db, "SELECT io.*, pe.NamaPenghuni, pe.Nim, k.NomorKamar FROM inoutpenghuni io JOIN penghuni pe ON io.PenghuniID = pe.PenghuniID JOIN kamar k ON pe.KamarID = k.KamarID WHERE io.Status = 'Keluar' ORDER BY io.WaktuKeluar ASC");
}

function fetchAllInOutLogs(mysqli $db): array
{
    return dbFetchAll($db, 'SELECT io.*, pe.NamaPenghuni, pe.Nim, k.NomorKamar, pt.NamaPetugas FROM inoutpenghuni io JOIN penghuni pe ON io.PenghuniID = pe.PenghuniID JOIN kamar k ON pe.KamarID = k.KamarID LEFT JOIN petugas pt ON io.PetugasID = pt.PetugasID ORDER BY io.InOutID DESC');
}

function createInOutRequest(mysqli $db, int $penghuniId, string $keperluan, string $waktuKeluar, string $waktuMasuk): bool
{
    dbExecute(
        $db,
        "CALL sp_createInOutRequest(?, ?, ?, ?)",
        'isss',
        [$penghuniId, $keperluan, $waktuKeluar, $waktuMasuk]
    );

    return true;
}

function confirmInOutExit(mysqli $db, int $inOutId, string $waktuKeluar, int $petugasId): bool
{
    dbExecute($db, "CALL sp_confirmInOutExit(?, ?, ?)", 'isi', [$inOutId, $waktuKeluar, $petugasId]);
    return true;
}

function confirmInOutEntry(mysqli $db, int $inOutId, string $waktuMasuk): bool
{
    dbExecute($db, "CALL sp_confirmInOutEntry(?, ?)", 'is', [$inOutId, $waktuMasuk]);
    return true;
}
