<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<?php include 'head.php'; ?>

<body>
    <?php include 'header.php'; ?>
    <div class="tw:pt-16 tw:flex-1 tw:h-full tw:w-full tw:relative tw:overflow-y-auto tw:overflow-x-hidden">
        <div id="landing-container" class="tw:min-h-full tw:flex tw:flex-col">
            <section
                class="tw:relative tw:bg-background tw:overflow-hidden tw:flex-1 tw:flex tw:flex-col tw:justify-center">
                <div class="tw:absolute tw:inset-0 tw:z-0">
                    <div
                        class="tw:absolute tw:top-0 tw:left-0 tw:w-96 tw:h-96 tw:bg-accent tw:rounded-full tw:mix-blend-multiply tw:filter tw:blur-3xl tw:opacity-50 tw:animate-blob">
                    </div>
                    <div
                        class="tw:absolute tw:top-0 tw:right-0 tw:w-96 tw:h-96 tw:bg-secondary tw:rounded-full tw:mix-blend-multiply tw:filter tw:blur-3xl tw:opacity-30 tw:animate-blob tw:animation-delay-2000">
                    </div>
                    <div
                        class="tw:absolute -tw:bottom-8 tw:left-20 tw:w-96 tw:h-96 tw:bg-primary tw:rounded-full tw:mix-blend-multiply tw:filter tw:blur-3xl tw:opacity-20 tw:animate-blob tw:animation-delay-4000">
                    </div>
                </div>

                <div
                    class="tw:max-w-7xl tw:mx-auto tw:px-4 tw:sm:tw:px-6 tw:lg:tw:px-8 tw:relative tw:z-10 tw:py-16 tw:sm:tw:py-24">
                    <div class="tw:text-center">
                        <h1
                            class="tw:text-4xl tw:tracking-tight tw:font-extrabold tw:text-gray-900 tw:sm:tw:text-5xl tw:md:tw:text-6xl">
                            <span class="tw:block tw:text-primary">DOREMI</span>
                            <span class="tw:block tw:text-secondary tw:mt-2">Kenyamanan & Keamanan Terpadu</span>
                        </h1>
                        <p
                            class="tw:mt-3 tw:max-w-md tw:mx-auto tw:text-base tw:text-gray-500 tw:sm:tw:text-lg tw:md:tw:mt-5 tw:md:tw:text-xl tw:md:tw:max-w-3xl">
                            Sistem manajemen asrama modern yang menghubungkan penghuni,
                            pengurus, keamanan (Sigap), kebersihan (Virtus), dan teknisi
                            dalam satu platform yang mudah digunakan.
                        </p>
                        <div
                            class="tw:mt-8 tw:no-underline tw:max-w-md tw:mx-auto tw:sm:tw:flex tw:sm:tw:justify-center tw:md:tw:mt-10 tw:gap-4">
                            <a href="login.php"
                                class="tw:w-full tw:no-underline tw:flex tw:items-center tw:justify-center tw:px-8 tw:py-3 tw:border tw:border-transparent tw:text-base tw:font-medium tw:rounded-full tw:text-white tw:bg-primary tw:hover:bg-opacity-90 tw:md:tw:py-4 tw:md:tw:text-lg tw:md:tw:px-10 tw:shadow-lg tw:transform tw:transition tw:hover:-tw:translate-y-1">
                                Masuk Sistem
                            </a>
                            <a href="gallery.php"
                                class="tw:mt-3 tw:no-underline tw:w-full tw:flex tw:items-center tw:justify-center tw:px-8 tw:py-3 tw:border-2 tw:border-secondary tw:text-base tw:font-medium tw:rounded-full tw:text-primary tw:bg-transparent tw:hover:bg-secondary tw:hover:text-white tw:md:tw:py-4 tw:md:tw:text-lg tw:md:tw:px-10 tw:mt-0 tw:sm:tw:mt-0 tw:transition-colors">
                                Lihat Galeri
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Features Section -->
            <section class="tw:py-16 tw:bg-white tw:z-10 tw:relative">
                <div class="tw:max-w-7xl tw:mx-auto tw:px-4 tw:sm:tw:px-6 tw:lg:tw:px-8">
                    <div class="tw:text-center tw:mb-12">
                        <h2 class="tw:text-3xl tw:font-extrabold tw:text-primary">
                            Fasilitas & Layanan Unggulan
                        </h2>
                        <p class="tw:mt-4 tw:text-lg tw:text-gray-500">
                            Semua yang Anda butuhkan untuk kehidupan asrama yang tenang.
                        </p>
                    </div>
                    <div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-3 tw:gap-8">
                        <div
                            class="tw:p-6 tw:bg-background tw:rounded-2xl tw:border tw:border-accent/30 tw:hover:shadow-xl tw:transition-shadow">
                            <div
                                class="tw:w-12 tw:h-12 tw:inline-flex tw:items-center tw:justify-center tw:rounded-xl tw:bg-accent tw:text-primary tw:mb-5">
                                <i class="iconsax tw:text-2xl" icon-name="box"></i>
                            </div>
                            <h3 class="tw:text-xl tw:font-bold tw:text-gray-900 tw:mb-2">
                                Manajemen Paket
                            </h3>
                            <p class="tw:text-gray-600">
                                Pantau paket kiriman Anda secara real-time. Keamanan (Sigap)
                                akan mencatat dan menyimpan paket dengan aman hingga Anda
                                mengambilnya.
                            </p>
                        </div>
                        <!-- Feature 2 -->
                        <div
                            class="tw:p-6 tw:bg-background tw:rounded-2xl tw:border tw:border-accent/30 tw:hover:shadow-xl tw:transition-shadow">
                            <div
                                class="tw:w-12 tw:h-12 tw:inline-flex tw:items-center tw:justify-center tw:rounded-xl tw:bg-secondary tw:text-white tw:mb-5">
                                <i class="iconsax tw:text-2xl" icon-name="document-1"></i>
                            </div>
                            <h3 class="tw:text-xl tw:font-bold tw:text-gray-900 tw:mb-2">
                                Lapor Kerusakan Cepat
                            </h3>
                            <p class="tw:text-gray-600">
                                Fasilitas rusak? Laporkan langsung melalui sistem. Tim Teknisi
                                (Building) dan Kebersihan (Virtus) akan segera
                                menindaklanjuti.
                            </p>
                        </div>
                        <!-- Feature 3 -->
                        <div
                            class="tw:p-6 tw:bg-background tw:rounded-2xl tw:border tw:border-accent/30 tw:hover:shadow-xl tw:transition-shadow">
                            <div
                                class="tw:w-12 tw:h-12 tw:inline-flex tw:items-center tw:justify-center tw:rounded-xl tw:bg-primary tw:text-white tw:mb-5">
                                <i class="iconsax tw:text-2xl" icon-name="shield"></i>
                            </div>
                            <h3 class="tw:text-xl tw:font-bold tw:text-gray-900 tw:mb-2">
                                Keamanan Terjamin
                            </h3>
                            <p class="tw:text-gray-600">
                                Pencatatan keluar masuk penghuni secara digital. Memastikan
                                lingkungan asrama selalu aman dan terpantau 24/7.
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</body>

</html>