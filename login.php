<?php
session_start();
require './db.php';
require 'auth/config.php';

use Respect\Validation\Validator as v;


if (isset($_SESSION['userId'])) {
    header("Location: dashboard/index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($db, htmlspecialchars(trim($_POST['email'] ?? "")));
    $password = mysqli_real_escape_string($db, htmlspecialchars(trim($_POST['password'] ?? "")));


    if (!v::length(5)->email()->validate($email)) {
        header("Location: " . $_SERVER['PHP_SELF'] . '?status=error&message=Email atau Password Tidak Valid!');
        exit;
    }
    if (!v::alnum()->length(5)->validate($password)) {
        header("Location: " . $_SERVER['PHP_SELF'] . '?status=error&message=Email atau Password Tidak Valid!');
        exit;
    }

    $stmt = mysqli_prepare($db, "SELECT PetugasID, NamaPetugas, Password, Jabatan FROM petugas WHERE Email = ? AND IsDeleted = 0 LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $petugas = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($petugas) {
        if (!password_verify($password, $petugas['Password'])) {
            header("Location: " . $_SERVER['PHP_SELF'] . '?status=error&message=Email atau Password Tidak Valid!');
            exit;
        }

        $_SESSION['userId'] = $petugas['PetugasID'];
        $_SESSION['userName'] = $petugas['NamaPetugas'];
        $_SESSION['userRole'] = $petugas['Jabatan'];

        header("Location: /doremi-app/dashboard");
        exit;
    }

    $stmt = mysqli_prepare($db, "SELECT PenghuniID, NamaPenghuni, Password FROM penghuni WHERE Email = ? AND IsDeleted = 0 LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $penghuni = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($penghuni) {
        if (!password_verify($password, $penghuni['Password'])) {
            header("Location: " . $_SERVER['PHP_SELF'] . '?status=error&message=Email atau Password Tidak Valid!');
            exit;
        }

        $_SESSION['userId'] = $penghuni['PenghuniID'];
        $_SESSION['userName'] = $penghuni['NamaPenghuni'];
        $_SESSION['userRole'] = 'PENGHUNI';

        header("Location: /doremi-app/dashboard");
        exit;
    }

    header("Location: " . $_SERVER['PHP_SELF'] . '?status=error&message=Email atau Password Tidak Valid!');
    exit;
}

$login_url = $client->createAuthUrl();
?>

<!DOCTYPE html>
<html lang="en">

<?php include 'head.php'; ?>

<body>
    <?php include 'header.php'; ?>
    <div class="tw:pt-16 tw:flex-1 tw:h-full tw:w-full tw:relative tw:overflow-y-auto tw:overflow-x-hidden">
        <div id="login-container"
            class="tw:min-h-full tw:flex tw:items-center tw:justify-center tw:bg-background tw:px-4 tw:sm:px-6 tw:lg:px-8 tw:py-12 tw:relative tw:overflow-hidden">
            <div
                class="tw:absolute tw:top-1/4 tw:left-1/4 tw:w-72 tw:h-72 tw:bg-accent tw:rounded-full tw:mix-blend-multiply tw:filter tw:blur-2xl tw:opacity-60">
            </div>
            <div
                class="tw:absolute tw:bottom-1/4 tw:right-1/4 tw:w-80 tw:h-80 tw:bg-secondary tw:rounded-full tw:mix-blend-multiply tw:filter tw:blur-2xl tw:opacity-40">
            </div>

            <div class="tw:max-w-md tw:w-full tw:space-y-8 glass-panel tw:p-10 tw:rounded-3xl tw:z-10 tw:relative">
                <div>
                    <h2 class="tw:mt-2 tw:text-center tw:text-3xl tw:font-extrabold tw:text-primary">
                        Selamat Datang Kembali
                    </h2>
                    <p class="tw:mt-2 tw:text-center tw:text-sm tw:text-gray-600">
                        Silakan masuk ke akun DOREMI Anda
                    </p>
                </div>
                <form class="tw:mt-8 tw:w-full tw:space-y-2 tw:mb-2" method="POST">
                    <div class="tw:rounded-md tw:shadow-sm tw:space-y-4">
                        <div class="tw:w-full">
                            <label for="email" class="tw:sr-only">Email</label>
                            <div class="tw:relative tw:w-full">
                                <i class="iconsax tw:size-9 tw:text-2xl z-[9999] tw:left-0 tw:top-1/2 tw:-translate-y-1/2 tw:pl-3 tw:absolute tw:pointer-events-none tw:text-gray-400"
                                    icon-name="user-1"></i>
                                <input id="email" name="email" type="email" required
                                    class="tw:appearance-none tw:w-full tw:rounded-xl tw:relative tw:block tw:px-3 tw:py-3 tw:ps-10 tw:border tw:border-gray-300 tw:placeholder-gray-500 tw:text-gray-900 tw:focus:outline-none tw:focus:ring-secondary tw:focus:border-secondary tw:focus:z-10 tw:sm:text-sm tw:transition-colors"
                                    placeholder="email" />
                            </div>
                        </div>
                        <div class="tw:w-full" x-data="{ hidden: true }">
                            <label for="password" class="tw:sr-only">Password</label>
                            <div class="tw:relative">
                                <div
                                    class="tw:absolute tw:inset-y-0 tw:left-0 tw:pl-3 tw:top-1/2 tw:-translate-y-1/2 tw:flex tw:items-center tw:pointer-events-none">
                                    <i class="iconsax  tw:size-9 tw:text-2xl tw:text-gray-400" icon-name="lock-1"></i>
                                </div>
                                <input id="password" name="password" :type="hidden ? 'password' : 'text'" required
                                    class="tw:appearance-none tw:w-full tw:rounded-xl z-[9] tw:me-8 tw:relative tw:block tw:px-3 tw:py-3 tw:pl-10 tw:border tw:border-gray-300 tw:placeholder-gray-500 tw:text-gray-900 tw:focus:outline-none tw:focus:ring-secondary tw:focus:border-secondary tw:focus:z-10 tw:sm:text-sm tw:transition-colors"
                                    placeholder="Password" />
                                <button class="tw:absolute tw:right-2 z-[99] tw:top-1/2 tw:-translate-y-1/2"
                                    type="button" @click="hidden = !hidden">
                                    <i class="iconsax  tw:size-9 tw:text-lg tw:text-gray-400"
                                        :icon-name="hidden ? 'eye' : 'eye-slash'"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="tw:flex tw:items-center tw:justify-between">
                    </div>

                    <div>
                        <button type="submit"
                            class="tw:group tw:relative tw:w-full tw:flex tw:justify-center tw:py-3 tw:px-4 tw:border tw:border-transparent tw:text-sm tw:font-medium tw:rounded-xl tw:text-white tw:bg-primary tw:hover:bg-opacity-90 tw:focus:outline-none tw:focus:ring-2 tw:focus:ring-offset-2 tw:focus:ring-primary tw:shadow-lg tw:transition-all tw:transform tw:hover:-translate-y-0.5">
                            Masuk
                        </button>
                    </div>
                </form>
                <a href="<?php echo $login_url; ?>" type="button"
                    class="tw:w-full tw:gap-1 tw:flex tw:justify-center tw:py-3 tw:items-center tw:px-4 tw:border tw:border-transparent tw:text-sm tw:font-medium tw:rounded-xl tw:text-white tw:bg-primary tw:hover:bg-opacity-90 tw:focus:outline-none tw:focus:ring-2 tw:focus:ring-offset-2 tw:focus:ring-primary tw:shadow-lg tw:transition-all tw:transform tw:hover:-translate-y-0.5">
                    <i class="fa-brands fa-google tw:text-white tw:text-2xl"></i>
                    Masuk Dengan Google
                </a>
            </div>
        </div>
    </div>
    <?php require 'validation_alert.php' ?>
</body>

</html>
