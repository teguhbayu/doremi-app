<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

use Respect\Validation\Validator as v;

function collectLoginInput(array $source): array
{
    return [
        'email' => trim($source['email'] ?? ''),
        'password' => trim($source['password'] ?? ''),
    ];
}

function validateLoginInput(array $input): ?string
{
    if (!v::length(5)->email()->validate($input['email'] ?? '')) {
        return 'Email atau Password Tidak Valid!';
    }

    if (!v::length(5)->validate($input['password'] ?? '')) {
        return 'Email atau Password Tidak Valid!';
    }

    return null;
}
