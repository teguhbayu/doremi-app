<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

require_once __DIR__ . '/query.php';

function fetchPetugasByEmail(mysqli $db, string $email): ?array
{
    return dbFetchOne($db, "CALL sp_getPetugasByEmail(?)", 's', [$email]);
}

function fetchPenghuniByEmail(mysqli $db, string $email): ?array
{
    return dbFetchOne($db, "CALL sp_getPenghuniByEmail(?)", 's', [$email]);
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
