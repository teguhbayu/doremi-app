<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

use Respect\Validation\Validator as v;

/**
 * Validates $input against an ordered list of field rules and returns a message
 * naming the first field that fails, e.g. "Kolom Nama Penghuni wajib diisi."
 *
 * $fields shape: ['inputKey' => ['label' => 'Nama Penghuni', 'rule' => v::stringType()->length(3, 100)], ...]
 */
function firstFieldError(array $input, array $fields): ?string
{
    foreach ($fields as $key => $field) {
        $value = $input[$key] ?? null;
        if ($field['rule']->validate($value)) {
            continue;
        }

        if ($value === null || $value === '') {
            return "Kolom {$field['label']} wajib diisi.";
        }

        return "Kolom {$field['label']} tidak valid.";
    }

    return null;
}
