<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

function setOldFormInput(array $data): void
{
    $_SESSION['form_data'] = $data;
}

function pullOldFormInput(): array
{
    $data = $_SESSION['form_data'] ?? [];
    unset($_SESSION['form_data']);
    return $data;
}
