<?php
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: /doremi-app/login.php");
    exit;
}
require '../../db.php';

$query = mysqli_query($db, "SELECT * FROM petugas;");
?>


<!DOCTYPE html>
<html lang="en">
<?php require '../../head.php'; ?>

<body class="tw:p-0 tw:m-0 relative tw:flex">
    <?php require '../components/sidebar.php'; ?>
    <main class="tw-ps-[300px] tw:grow">
        <div class="tw:pt-5 tw:px-5 tw:flex-1 tw:w-full">
            <h1 class="tw:font-bold tw:mb-5 tw:text-4xl tw:text-black">
                Kelola Petugas
            </h1>
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Nama</th>
                        <th scope="col">Jabatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($petugas = mysqli_fetch_assoc($query)) { ?>
                        <tr>
                            <th scope="row"><?php echo $petugas["PetugasID"]; ?></th>
                            <td><?php echo $petugas["NamaPetugas"]; ?></td>
                            <td><?php echo $petugas["Jabatan"]; ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </main>
    <?php require '../../bootstrap.php'; ?>
</body>

</html>