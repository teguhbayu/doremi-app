<?php
/**
 * CSRF Protection Utility
 * Include this file (after session_start()) wherever CSRF tokens are needed.
 */
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}
require_once __DIR__ . '/utils/url.php';

/**
 * Generate (or retrieve existing) CSRF token for the current session.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Render a hidden CSRF input field.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES) . '">';
}

/**
 * Validate the CSRF token from a POST or GET request.
 * Redirects to $redirectOn failure if the token is missing or invalid.
 */
function csrf_validate(string $redirectOnFail = '/'): void
{
    $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
    if (!hash_equals(csrf_token(), $token)) {
        header('Location: ' . app_url($redirectOnFail));
        exit;
    }
}
