<?php
session_start();
if (!isset($_SESSION['userId'])) {
    header("Location: /doremi-app/login.php");
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<?php require '../head.php'; ?>

<body class="tw:p-0 tw:m-0 relative">
    <?php require 'components/sidebar.php'; ?>
    <main>

    </main>
</body>

</html>