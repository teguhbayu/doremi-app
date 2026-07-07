<?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('Access denied');
}

function authRedirectToDashboard(): void
{
    header("Location: /doremi-app/dashboard");
    exit;
}

function authRedirectToLoginError(string $message, string $path = '/doremi-app/login.php'): void
{
    header("Location: " . $path . '?status=error&message=' . urlencode($message));
    exit;
}

function authSetUserSession(array $authUser): void
{
    $_SESSION['userId'] = $authUser['id'];
    $_SESSION['userName'] = $authUser['name'];
    $_SESSION['userRole'] = $authUser['role'];
}

function authAttemptPasswordLogin(mysqli $db, string $email, string $password): ?array
{
    $authUser = findAuthUserByEmail($db, $email);
    if ($authUser === null || empty($authUser['password'])) {
        return null;
    }

    return password_verify($password, $authUser['password']) ? $authUser : null;
}

function authAttemptEmailLogin(mysqli $db, string $email): ?array
{
    return findAuthUserByEmail($db, $email);
}
