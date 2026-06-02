<?php
?>

<!DOCTYPE html>
<html lang="en">

<?php include 'head.php'; ?>

<body>
    <?php include 'header.php'; ?>
    <div class="pt-16 flex-1 h-full w-full relative overflow-y-auto overflow-x-hidden">
        <div id="landing-container" class="min-h-full flex flex-col">
            <section class="relative bg-background overflow-hidden flex-1 flex flex-col justify-center">
                <div class="absolute inset-0 z-0">
                    <div
                        class="absolute top-0 left-0 w-96 h-96 bg-accent rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-blob">
                    </div>
                    <div
                        class="absolute top-0 right-0 w-96 h-96 bg-secondary rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000">
                    </div>
                    <div
                        class="absolute -bottom-8 left-20 w-96 h-96 bg-primary rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-4000">
                    </div>
                </div>

                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 py-16 sm:py-24">
                    <div class="text-center">
                        <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl">
                            <span class="block text-primary">DOREMI</span>
                            <span class="block text-secondary mt-2">Kenyamanan & Keamanan Terpadu</span>
                        </h1>
                        <p
                            class="mt-3 max-w-md mx-auto text-base text-gray-500 sm:text-lg md:mt-5 md:text-xl md:max-w-3xl">
                            Sistem manajemen asrama modern yang menghubungkan penghuni,
                            pengurus, keamanan (Sigap), kebersihan (Virtus), dan teknisi
                            dalam satu platform yang mudah digunakan.
                        </p>
                        <div class="mt-8 max-w-md mx-auto sm:flex sm:justify-center md:mt-10 gap-4">
                            <a href="login.php"
                                class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-full text-white bg-primary hover:bg-opacity-90 md:py-4 md:text-lg md:px-10 shadow-lg transform transition hover:-translate-y-1">
                                Masuk Sistem
                            </a>
                            <a href="gallery.php"
                                class="mt-3 w-full flex items-center justify-center px-8 py-3 border-2 border-secondary text-base font-medium rounded-full text-primary bg-transparent hover:bg-secondary hover:text-white md:py-4 md:text-lg md:px-10 mt-0 sm:mt-0 transition-colors">
                                Lihat Galeri
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Features Section -->
            <section class="py-16 bg-white z-10 relative">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center mb-12">
                        <h2 class="text-3xl font-extrabold text-primary">
                            Fasilitas & Layanan Unggulan
                        </h2>
                        <p class="mt-4 text-lg text-gray-500">
                            Semua yang Anda butuhkan untuk kehidupan asrama yang tenang.
                        </p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div
                            class="p-6 bg-background rounded-2xl border border-accent/30 hover:shadow-xl transition-shadow">
                            <div
                                class="w-12 h-12 inline-flex items-center justify-center rounded-xl bg-accent text-primary mb-5">
                                <i class="iconsax text-2xl" icon-name="box"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">
                                Manajemen Paket
                            </h3>
                            <p class="text-gray-600">
                                Pantau paket kiriman Anda secara real-time. Keamanan (Sigap)
                                akan mencatat dan menyimpan paket dengan aman hingga Anda
                                mengambilnya.
                            </p>
                        </div>
                        <!-- Feature 2 -->
                        <div
                            class="p-6 bg-background rounded-2xl border border-accent/30 hover:shadow-xl transition-shadow">
                            <div
                                class="w-12 h-12 inline-flex items-center justify-center rounded-xl bg-secondary text-white mb-5">
                                <i class="iconsax text-2xl" icon-name="document-1"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">
                                Lapor Kerusakan Cepat
                            </h3>
                            <p class="text-gray-600">
                                Fasilitas rusak? Laporkan langsung melalui sistem. Tim Teknisi
                                (Building) dan Kebersihan (Virtus) akan segera
                                menindaklanjuti.
                            </p>
                        </div>
                        <!-- Feature 3 -->
                        <div
                            class="p-6 bg-background rounded-2xl border border-accent/30 hover:shadow-xl transition-shadow">
                            <div
                                class="w-12 h-12 inline-flex items-center justify-center rounded-xl bg-primary text-white mb-5">
                                <i class="iconsax text-2xl" icon-name="shield"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">
                                Keamanan Terjamin
                            </h3>
                            <p class="text-gray-600">
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
