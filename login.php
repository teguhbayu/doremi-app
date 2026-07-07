<?php
session_start();
require './db.php';
require_once 'database/auth.php';
require_once 'auth/helpers.php';
require_once 'auth/validation.php';
require 'auth/config.php';


if (isset($_SESSION['userId'])) {
    header("Location: dashboard/index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginInput = collectLoginInput($_POST);
    $validationMessage = validateLoginInput($loginInput);
    if ($validationMessage !== null) {
        authRedirectToLoginError($validationMessage, $_SERVER['PHP_SELF']);
    }

    $authUser = authAttemptPasswordLogin($db, $loginInput['email'], $loginInput['password']);
    if ($authUser === null) {
        authRedirectToLoginError('Email atau Password Tidak Valid!', $_SERVER['PHP_SELF']);
    }

    authSetUserSession($authUser);
    authRedirectToDashboard();
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
    <script>
    window._headerAnimated = true;
    document.addEventListener('DOMContentLoaded', () => {
        const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });

        tl.from('#global-header', { y: -60, opacity: 0, duration: 0.55 })
          .from('.auth-preview', { x: -50, opacity: 0, duration: 0.65 }, '-=0.2')
          .from('.auth-card', { x: 50, opacity: 0, duration: 0.65 }, '<')
          .from('.auth-preview .eyebrow', { y: 16, opacity: 0, duration: 0.4 }, '-=0.3')
          .from('.auth-preview h1', { y: 20, opacity: 0, duration: 0.4 }, '-=0.3')
          .from('.auth-preview p', { y: 16, opacity: 0, duration: 0.4 }, '-=0.25')
          .from('.auth-preview__list article', { y: 16, opacity: 0, duration: 0.35, stagger: 0.1 }, '-=0.2')
          .from('.auth-card .eyebrow', { y: 12, opacity: 0, duration: 0.35 }, '-=0.4')
          .from('.auth-card h2', { y: 12, opacity: 0, duration: 0.35 }, '-=0.25')
          .from('.auth-field', { y: 12, opacity: 0, duration: 0.3, stagger: 0.08 }, '-=0.2')
          .from('.auth-submit', { y: 10, opacity: 0, duration: 0.3 }, '-=0.1')
          .from('.auth-divider', { opacity: 0, duration: 0.3 }, '-=0.1')
          .from('.auth-google', { y: 10, opacity: 0, duration: 0.3 }, '-=0.15');
    });
    </script>
</body>

</html>
