<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

require_once __DIR__ . '/query.php';

function fetchAllPetugas(mysqli $db): array
{
    return dbFetchAll($db, 'SELECT * FROM petugas WHERE IsDeleted = 0');
}

function findPetugasDuplicateActive(mysqli $db, string $email, string $noHp): ?array
{
    return dbFetchOne($db, 'SELECT PetugasID, Email, NoHP FROM petugas WHERE IsDeleted = 0 AND (Email = ? OR NoHP = ?) LIMIT 1', 'ss', [$email, $noHp]);
}

function findPetugasDuplicateDeleted(mysqli $db, string $email, string $noHp): array
{
    return dbFetchAll($db, 'SELECT PetugasID, Email, NoHP FROM petugas WHERE IsDeleted = 1 AND (Email = ? OR NoHP = ?)', 'ss', [$email, $noHp]);
}

function restorePetugas(mysqli $db, int $id, string $nama, string $email, string $passwordHash, string $jabatan, string $noHp): bool
{
    dbExecute($db, 'UPDATE petugas SET NamaPetugas = ?, Email = ?, Password = ?, Jabatan = ?, NoHP = ?, IsDeleted = 0 WHERE PetugasID = ?', 'sssssi', [$nama, $email, $passwordHash, $jabatan, $noHp, $id]);
    return true;
}

function createPetugas(mysqli $db, string $nama, string $email, string $passwordHash, string $jabatan, string $noHp): bool
{
    dbExecute($db, 'INSERT INTO petugas (NamaPetugas, Email, Password, Jabatan, NoHP) VALUES (?, ?, ?, ?, ?)', 'sssss', [$nama, $email, $passwordHash, $jabatan, $noHp]);
    return true;
}

function fetchPetugasById(mysqli $db, int $id): ?array
{
    return dbFetchOne($db, 'SELECT PetugasID, NamaPetugas, Email, Jabatan, NoHP FROM petugas WHERE PetugasID = ? AND IsDeleted = 0 LIMIT 1', 'i', [$id]);
}

function findPetugasDuplicateExcluding(mysqli $db, int $id, string $email, string $noHp): ?array
{
    return dbFetchOne($db, 'SELECT PetugasID, Email, NoHP FROM petugas WHERE IsDeleted = 0 AND PetugasID != ? AND (Email = ? OR NoHP = ?) LIMIT 1', 'iss', [$id, $email, $noHp]);
}

function updatePetugasWithPassword(mysqli $db, int $id, string $nama, string $email, string $jabatan, string $noHp, string $passwordHash): bool
{
    dbExecute($db, 'UPDATE petugas SET NamaPetugas = ?, Email = ?, Jabatan = ?, NoHP = ?, Password = ? WHERE PetugasID = ?', 'sssssi', [$nama, $email, $jabatan, $noHp, $passwordHash, $id]);
    return true;
}

function updatePetugasWithoutPassword(mysqli $db, int $id, string $nama, string $email, string $jabatan, string $noHp): bool
{
    dbExecute($db, 'UPDATE petugas SET NamaPetugas = ?, Email = ?, Jabatan = ?, NoHP = ? WHERE PetugasID = ?', 'ssssi', [$nama, $email, $jabatan, $noHp, $id]);
    return true;
}

function deletePetugas(mysqli $db, int $id): bool
{
    dbExecute($db, 'UPDATE petugas SET IsDeleted = 1 WHERE PetugasID = ?', 'i', [$id]);
    return true;
}
