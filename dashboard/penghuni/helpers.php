<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

function penghuni_duplicate_identity_message(array $existingPenghuni, string $nim, string $email, string $noHp): string
{
    if (($existingPenghuni['Nim'] ?? '') === $nim) {
        return 'NIM penghuni sudah terdaftar!';
    }

    if (($existingPenghuni['Email'] ?? '') === $email) {
        return 'Email penghuni sudah terdaftar!';
    }

    if (($existingPenghuni['NoHP'] ?? '') === $noHp) {
        return 'No. HP penghuni sudah terdaftar!';
    }

    return 'Data penghuni sudah terdaftar!';
}

function penghuni_gender_label(string $gender): string
{
    return $gender === 'P' ? 'Perempuan' : 'Laki-laki';
}

function penghuni_allowed_floors(string $gender): array
{
    return $gender === 'P' ? ['3', '4'] : ['5', '6', '7'];
}

function penghuni_floor_zone_label(?string $lantai): string
{
    if (in_array((string) $lantai, ['3', '4'], true)) {
        return 'Zona Perempuan';
    }

    if (in_array((string) $lantai, ['5', '6', '7'], true)) {
        return 'Zona Laki-laki';
    }

    return 'Zona Tidak Valid';
}

function penghuni_validate_room_assignment(mysqli $db, int $kamarId, string $gender, ?int $excludePenghuniId = null): ?string
{
    $roomStmt = mysqli_prepare(
        $db,
        "SELECT KamarID, NomorKamar, KapasitasPenghuni, Lantai
         FROM kamar
         WHERE KamarID = ? AND IsDeleted = 0
         LIMIT 1"
    );
    mysqli_stmt_bind_param($roomStmt, 'i', $kamarId);
    mysqli_stmt_execute($roomStmt);
    $roomResult = mysqli_stmt_get_result($roomStmt);
    $room = mysqli_fetch_assoc($roomResult);
    mysqli_stmt_close($roomStmt);

    if (!$room) {
        return 'Kamar yang dipilih tidak ditemukan!';
    }

    $allowedFloors = penghuni_allowed_floors($gender);
    if (!in_array((string) $room['Lantai'], $allowedFloors, true)) {
        return sprintf(
            '%s hanya boleh menempati lantai %s. Kamar %s berada di lantai %s.',
            penghuni_gender_label($gender),
            implode('-', [$allowedFloors[0], $allowedFloors[count($allowedFloors) - 1]]),
            $room['NomorKamar'],
            $room['Lantai'] ?? '-'
        );
    }

    if ($excludePenghuniId !== null) {
        $occupantStmt = mysqli_prepare(
            $db,
            "SELECT COUNT(*) AS total, GROUP_CONCAT(DISTINCT JenisKelamin ORDER BY JenisKelamin SEPARATOR ',') AS genders
             FROM penghuni
             WHERE KamarID = ? AND IsDeleted = 0 AND PenghuniID != ?"
        );
        mysqli_stmt_bind_param($occupantStmt, 'ii', $kamarId, $excludePenghuniId);
    } else {
        $occupantStmt = mysqli_prepare(
            $db,
            "SELECT COUNT(*) AS total, GROUP_CONCAT(DISTINCT JenisKelamin ORDER BY JenisKelamin SEPARATOR ',') AS genders
             FROM penghuni
             WHERE KamarID = ? AND IsDeleted = 0"
        );
        mysqli_stmt_bind_param($occupantStmt, 'i', $kamarId);
    }

    mysqli_stmt_execute($occupantStmt);
    $occupantResult = mysqli_stmt_get_result($occupantStmt);
    $occupantSummary = mysqli_fetch_assoc($occupantResult);
    mysqli_stmt_close($occupantStmt);

    $occupiedCount = (int) ($occupantSummary['total'] ?? 0);
    $projectedCount = $occupiedCount + 1;
    if ($projectedCount > (int) $room['KapasitasPenghuni']) {
        return 'Kamar yang dipilih sudah penuh!';
    }

    $existingGenders = array_values(array_filter(explode(',', (string) ($occupantSummary['genders'] ?? ''))));
    if (count($existingGenders) > 1) {
        return sprintf(
            'Kamar %s memiliki data penghuni dengan gender campuran. Periksa data kamar tersebut terlebih dahulu.',
            $room['NomorKamar']
        );
    }

    if ($existingGenders && $existingGenders[0] !== $gender) {
        return sprintf(
            'Penghuni %s dan %s tidak boleh berada dalam satu kamar. Kamar %s sudah terisi %s.',
            penghuni_gender_label($gender),
            penghuni_gender_label($existingGenders[0]),
            $room['NomorKamar'],
            penghuni_gender_label($existingGenders[0])
        );
    }

    return null;
}
