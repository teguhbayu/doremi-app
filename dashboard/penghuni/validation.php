<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

use Respect\Validation\Validator as v;

function collectPenghuniInput(array $source): array
{
    return [
        'nama' => trim($source['namaPenghuni'] ?? ''),
        'nim' => penghuni_normalize_nim($source['nimPenghuni'] ?? ''),
        'email' => penghuni_normalize_email($source['emailPenghuni'] ?? ''),
        'no' => penghuni_normalize_phone($source['noPenghuni'] ?? ''),
        'jk' => trim($source['jkPenghuni'] ?? ''),
        'kamarId' => trim($source['kamarPenghuni'] ?? ''),
        'alamat' => trim($source['alamatPenghuni'] ?? ''),
        'password' => trim($source['passwordPenghuni'] ?? ''),
        'confirmPassword' => trim($source['confirmPasswordPenghuni'] ?? ''),
    ];
}

function penghuniFormData(array $input): array
{
    return [
        'nama' => $input['nama'] ?? '',
        'nim' => $input['nim'] ?? '',
        'email' => $input['email'] ?? '',
        'no' => $input['no'] ?? '',
        'jk' => $input['jk'] ?? '',
        'kamarId' => $input['kamarId'] ?? '',
        'alamat' => $input['alamat'] ?? '',
    ];
}

function validatePenghuniInputSchema(array $input, bool $requirePassword): ?string
{
    $passwordValidator = $requirePassword
        ? v::length(8, 100)
        : v::optional(v::length(8, 100));

    $schema = v::keySet(
        v::key('nama', v::stringType()->length(3, 100)),
        v::key('nim', v::stringType()),
        v::key('email', v::email()->length(3, 100)),
        v::key('no', v::stringType()),
        v::key('jk', v::in(['L', 'P'])),
        v::key('kamarId', v::numericVal()),
        v::key('alamat', v::stringType()->length(3, 255)),
        v::key('password', $passwordValidator),
        v::key('confirmPassword', $passwordValidator)
    );

    return $schema->validate($input) ? null : 'Data Penghuni tidak Valid!';
}

function validatePenghuniCommonInput(
    mysqli $db,
    array $input,
    array $kamarMap,
    bool $requirePassword,
    ?int $excludePenghuniId = null
): ?string {
    $schemaMessage = validatePenghuniInputSchema($input, $requirePassword);
    if ($schemaMessage !== null) {
        return $schemaMessage;
    }

    if (!penghuni_is_valid_nim($input['nim'])) {
        return penghuni_nim_validation_message();
    }

    if (!penghuni_is_valid_phone($input['no'])) {
        return 'No. HP harus 10-16 digit angka yang valid!';
    }

    if (($requirePassword || $input['password'] !== '' || $input['confirmPassword'] !== '')
        && $input['password'] !== $input['confirmPassword']
    ) {
        return 'Password Tidak Cocok!';
    }

    if (!isset($kamarMap[(int) $input['kamarId']])) {
        return 'Kamar yang dipilih tidak ditemukan!';
    }

    $duplicateMatches = penghuni_find_identity_matches(
        $db,
        $input['nim'],
        $input['email'],
        $input['no'],
        0,
        $excludePenghuniId
    );
    $duplicatePenghuni = $duplicateMatches[0] ?? null;
    if ($duplicatePenghuni) {
        return penghuni_duplicate_identity_message($duplicatePenghuni, $input['nim'], $input['email'], $input['no']);
    }

    return penghuni_validate_room_assignment(
        $db,
        (int) $input['kamarId'],
        $input['jk'],
        $excludePenghuniId
    );
}

function validateCreatePenghuniInput(mysqli $db, array $input, array $kamarMap): ?string
{
    return validatePenghuniCommonInput($db, $input, $kamarMap, true);
}

function validateEditPenghuniInput(mysqli $db, array $input, array $kamarMap, int $penghuniId): ?string
{
    return validatePenghuniCommonInput($db, $input, $kamarMap, false, $penghuniId);
}
