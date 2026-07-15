<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

require_once __DIR__ . '/query.php';

function fetchPetugasByEmail(mysqli $db, string $email): ?array
{
    return dbFetchOne(
        $db,
        'SELECT PetugasID, NamaPetugas, Password, Jabatan FROM petugas WHERE Email = ? AND IsDeleted = 0 LIMIT 1',
        's',
        [$email]
    );
}

function fetchPenghuniByEmail(mysqli $db, string $email): ?array
{
    return dbFetchOne(
        $db,
        'SELECT PenghuniID, NamaPenghuni, Password FROM penghuni WHERE Email = ? AND IsDeleted = 0 LIMIT 1',
        's',
        [$email]
    );
}

function findAuthUserByEmail(mysqli $db, string $email): ?array
{
    $petugas = fetchPetugasByEmail($db, $email);
    if ($petugas !== null) {
        return [
            'id' => (int) $petugas['PetugasID'],
            'name' => $petugas['NamaPetugas'],
            'role' => $petugas['Jabatan'],
            'password' => $petugas['Password'] ?? null,
            'source' => 'petugas',
        ];
    }

    $penghuni = fetchPenghuniByEmail($db, $email);
    if ($penghuni !== null) {
        return [
            'id' => (int) $penghuni['PenghuniID'],
            'name' => $penghuni['NamaPenghuni'],
            'role' => 'PENGHUNI',
            'password' => $penghuni['Password'] ?? null,
            'source' => 'penghuni',
        ];
    }

    return null;
}
