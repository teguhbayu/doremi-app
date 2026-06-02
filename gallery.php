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
                        <img src="https://placehold.co/600x400/AFD3E2/146C94?text=Kamar+Standar" alt="Kamar"
                            class="w-full h-64 object-cover transform group-hover:scale-105 transition-transform duration-500" />
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-primary/80 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-4">
                            <span class="text-white font-medium text-lg">Kamar Tipe Standar</span>
                        </div>
                    </div>
                    <div
                        class="group relative overflow-hidden rounded-2xl shadow-md hover:shadow-xl transition-all duration-300">
                        <img src="https://placehold.co/600x400/19A7CE/F6F1F1?text=Ruang+Belajar" alt="Ruang Belajar"
                            class="w-full h-64 object-cover transform group-hover:scale-105 transition-transform duration-500" />
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-primary/80 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-4">
                            <span class="text-white font-medium text-lg">Area Belajar Bersama</span>
                        </div>
                    </div>
                    <div
                        class="group relative overflow-hidden rounded-2xl shadow-md hover:shadow-xl transition-all duration-300">
                        <img src="https://placehold.co/600x400/146C94/F6F1F1?text=Lobby+Utama" alt="Lobby"
                            class="w-full h-64 object-cover transform group-hover:scale-105 transition-transform duration-500" />
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-primary/80 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-4">
                            <span class="text-white font-medium text-lg">Lobby & Resepsionis</span>
                        </div>
                    </div>
                    <div
                        class="group relative overflow-hidden rounded-2xl shadow-md hover:shadow-xl transition-all duration-300">
                        <img src="https://placehold.co/600x400/F6F1F1/146C94?text=Area+Parkir" alt="Parkir"
                            class="w-full h-64 object-cover transform group-hover:scale-105 transition-transform duration-500" />
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-primary/80 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-4">
                            <span class="text-white font-medium text-lg">Area Parkir Luas</span>
                        </div>
                    </div>
                    <div
                        class="group relative overflow-hidden rounded-2xl shadow-md hover:shadow-xl transition-all duration-300">
                        <img src="https://placehold.co/600x400/AFD3E2/146C94?text=Pantry+Bersama" alt="Pantry"
                            class="w-full h-64 object-cover transform group-hover:scale-105 transition-transform duration-500" />
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-primary/80 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-4">
                            <span class="text-white font-medium text-lg">Dapur & Pantry Bersama</span>
                        </div>
                    </div>
                    <div
                        class="group relative overflow-hidden rounded-2xl shadow-md hover:shadow-xl transition-all duration-300">
                        <img src="https://placehold.co/600x400/19A7CE/F6F1F1?text=Taman" alt="Taman"
                            class="w-full h-64 object-cover transform group-hover:scale-105 transition-transform duration-500" />
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-primary/80 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-4">
                            <span class="text-white font-medium text-lg">Taman Bersantai</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>