<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

require_once __DIR__ . '/query.php';

function fetchAllPetugas(mysqli $db): array
{
    return dbFetchAll($db, "CALL sp_getAllPetugas()");
}

function findPetugasDuplicateActive(mysqli $db, string $email, string $noHp): ?array
{
    return dbFetchOne($db, "CALL sp_findPetugasDuplicateActive(?, ?)", 'ss', [$email, $noHp]);
}

function findPetugasDuplicateDeleted(mysqli $db, string $email, string $noHp): array
{
    return dbFetchAll($db, "CALL sp_findPetugasDuplicateDeleted(?, ?)", 'ss', [$email, $noHp]);
}

function restorePetugas(mysqli $db, int $id, string $nama, string $email, string $passwordHash, string $jabatan, string $noHp): bool
{
    dbExecute($db, "CALL sp_restorePetugas(?, ?, ?, ?, ?, ?)", 'isssss', [$id, $nama, $email, $passwordHash, $jabatan, $noHp]);
    return true;
}

function createPetugas(mysqli $db, string $nama, string $email, string $passwordHash, string $jabatan, string $noHp): bool
{
    dbExecute($db, "CALL sp_createPetugas(?, ?, ?, ?, ?)", 'sssss', [$nama, $email, $passwordHash, $jabatan, $noHp]);
    return true;
}

function fetchPetugasById(mysqli $db, int $id): ?array
{
    return dbFetchOne($db, "CALL sp_getPetugasById(?)", 'i', [$id]);
}

function findPetugasDuplicateExcluding(mysqli $db, int $id, string $email, string $noHp): ?array
{
    return dbFetchOne($db, "CALL sp_findPetugasDuplicateExcluding(?, ?, ?)", 'iss', [$id, $email, $noHp]);
}

function updatePetugasWithPassword(mysqli $db, int $id, string $nama, string $email, string $jabatan, string $noHp, string $passwordHash): bool
{
    dbExecute($db, "CALL sp_updatePetugasWithPassword(?, ?, ?, ?, ?, ?)", 'isssss', [$id, $nama, $email, $jabatan, $noHp, $passwordHash]);
    return true;
}

function updatePetugasWithoutPassword(mysqli $db, int $id, string $nama, string $email, string $jabatan, string $noHp): bool
{
    dbExecute($db, "CALL sp_updatePetugasWithoutPassword(?, ?, ?, ?, ?)", 'issss', [$id, $nama, $email, $jabatan, $noHp]);
    return true;
}

function deletePetugas(mysqli $db, int $id): bool
{
    dbExecute($db, "CALL sp_deletePetugas(?)", 'i', [$id]);
    return true;
}
