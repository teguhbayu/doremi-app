<?php
?>

<!DOCTYPE html>
<html lang="en">
<?php include 'head.php'; ?>
<body>
    <?php include 'header.php'; ?>
    <div class="pt-16 flex-1 h-full w-full relative overflow-y-auto overflow-x-hidden">
        <div id="gallery-container" class="min-h-full bg-background pt-8 pb-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-10">
                    <h2 class="text-3xl font-extrabold text-primary">Galeri DOREMI</h2>
                    <p class="mt-4 text-lg text-gray-500">
                        Intip kenyamanan fasilitas asrama kami.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                    <div
                        class="group relative overflow-hidden rounded-2xl shadow-md hover:shadow-xl transition-all duration-300">
                        <img src="images/kamar.png" alt="Kamar"
                            class="w-full h-64 object-cover transform group-hover:scale-105 transition-transform duration-500" />
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-primary/80 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-4">
                            <span class="text-white font-medium text-lg">Kamar</span>
                        </div>
                    </div>
                    <div
                        class="group relative overflow-hidden rounded-2xl shadow-md hover:shadow-xl transition-all duration-300">
                        <img src="images/badminton.png" alt="Ruang Belajar"
                            class="w-full h-64 object-cover transform group-hover:scale-105 transition-transform duration-500" />
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-primary/80 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-4">
                            <span class="text-white font-medium text-lg">Lapangan Badminton</span>
                        </div>
                    </div>
                    <div
                        class="group relative overflow-hidden rounded-2xl shadow-md hover:shadow-xl transition-all duration-300">
                        <img src="images/basket.png" alt="Lobby"
                            class="w-full h-64 object-cover transform group-hover:scale-105 transition-transform duration-500" />
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-primary/80 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-4">
                            <span class="text-white font-medium text-lg">Lapangan Basket</span>
                        </div>
                    </div>
                    <div
                        class="group relative overflow-hidden rounded-2xl shadow-md hover:shadow-xl transition-all duration-300">
                        <img src="images/kantin.png" alt="Parkir"
                            class="w-full h-64 object-cover transform group-hover:scale-105 transition-transform duration-500" />
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-primary/80 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-4">
                            <span class="text-white font-medium text-lg">Kantin</span>
                        </div>
                    </div>
                    <div
                        class="group relative overflow-hidden rounded-2xl shadow-md hover:shadow-xl transition-all duration-300">
                        <img src="images/market.png" alt="Pantry"
                            class="w-full h-64 object-cover transform group-hover:scale-105 transition-transform duration-500" />
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-primary/80 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-4">
                            <span class="text-white font-medium text-lg">Mini Market</span>
                        </div>
                    </div>
                    <div
                        class="group relative overflow-hidden rounded-2xl shadow-md hover:shadow-xl transition-all duration-300">
                        <img src="images/organisasi.png" alt="Taman"
                            class="w-full h-64 object-cover transform group-hover:scale-105 transition-transform duration-500" />
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-primary/80 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-4">
                            <span class="text-white font-medium text-lg">Ruang Organisasi</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>