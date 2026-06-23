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

<body class="public-body">
    <?php include 'header.php'; ?>
    <main class="site-main auth-shell">
        <div class="site-container">
            <div class="auth-layout">
                <section class="auth-preview">
                    <span class="eyebrow">Portal Akses DOREMI</span>
                    <h1>DOREMI</h1>
                    <h4><i>Dormitory Resident Management Information</i></h4>
                    <p>
                        Penghuni dan petugas menggunakan satu pintu masuk yang rapi
                        untuk paket, maintenance, inventaris, kamar, dan izin keluar.
                    </p>

                    <div class="auth-preview__list">
                        <article>
                            <strong>Laporan lebih cepat</strong>
                            <span>Kelola maintenance, paket, dan aktivitas harian dari dashboard yang sama.</span>
                        </article>
                        <article>
                            <strong>Alur petugas jelas</strong>
                            <span>Setiap peran kerja punya akses operasional yang tersusun dan mudah dipantau.</span>
                        </article>
                        <article>
                            <strong>Riwayat tetap tercatat</strong>
                            <span>Semua tindakan penting tersimpan agar koordinasi asrama tetap rapi.</span>
                        </article>
                    </div>
                </section>

                <section class="auth-card">
                    <div class="auth-card__header">
                        <span class="eyebrow">Login</span>
                        <h2>Selamat Datang Kembali</h2>
                        <p>Silakan masuk ke akun DOREMI Anda.</p>
                    </div>

                    <form class="auth-form" method="POST">
                        <div class="auth-field">
                            <label for="email">Email</label>
                            <div class="auth-input-wrap">
                                <i class="iconsax auth-input-icon tw:text-xl" icon-name="user-1"></i>
                                <input id="email" name="email" type="email" required class="auth-input"
                                    placeholder="email" />
                            </div>
                        </div>

                        <div class="auth-field" x-data="{ hidden: true }">
                            <label for="password">Password</label>
                            <div class="auth-input-wrap">
                                <i class="iconsax auth-input-icon tw:text-xl" icon-name="lock-1"></i>
                                <input id="password" name="password" :type="hidden ? 'password' : 'text'" required
                                    class="auth-input auth-input--with-action" placeholder="Password" />
                                <button class="auth-toggle" type="button" @click="hidden = !hidden">
                                    <i class="iconsax tw:text-lg" :icon-name="hidden ? 'eye' : 'eye-slash'"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="auth-submit site-cta--full">
                            Masuk
                        </button>
                    </form>

                    <div class="auth-divider">
                        <span>atau</span>
                    </div>

                    <a href="<?php echo $login_url; ?>" class="auth-google site-cta--full">
                        <i class="fa-brands fa-google tw:text-2xl"></i>
                        Masuk Dengan Google
                    </a>
                </section>
            </div>
        </div>
    </main>
    <?php require 'validation_alert.php' ?>
</body>

</html>
