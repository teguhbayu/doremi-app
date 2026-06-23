<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<?php include 'head.php'; ?>

<body class="public-body">
    <?php include 'header.php'; ?>
    <div class="public-main tw:pt-16 tw:flex-1 tw:h-full tw:w-full tw:relative tw:overflow-y-auto tw:overflow-x-hidden">
        <div id="gallery-container" class="public-gallery-shell tw:min-h-full tw:bg-background tw:pt-8 tw:pb-16">
            <div class="tw:max-w-7xl tw:mx-auto tw:px-4 tw:sm:px-6 tw:lg:px-8">
                <div class="public-section-heading tw:text-center tw:mb-10">
                    <h2 class="tw:text-3xl tw:font-extrabold tw:text-primary">Galeri DOREMI</h2>
                    <p class="public-section-copy tw:mt-4 tw:text-lg tw:text-gray-500">
                        Intip kenyamanan fasilitas asrama kami.
                    </p>
                </div>

                <div class="public-gallery-grid tw:grid tw:grid-cols-1 tw:sm:grid-cols-2 tw:md:grid-cols-3 tw:gap-6">
                    <div
                        class="public-gallery-card tw:group tw:relative tw:overflow-hidden tw:rounded-2xl tw:shadow-md tw:hover:shadow-xl tw:transition-all tw:duration-300">
                        <img src="images/kamar.png" alt="Kamar"
                            class="public-gallery-card__image tw:w-full tw:h-64 tw:object-cover tw:transform tw:group-hover:scale-105 tw:transition-transform tw:duration-500" />
                        <div
                            class="public-gallery-card__overlay tw:absolute tw:inset-0 tw:bg-gradient-to-t tw:from-primary/80 tw:to-transparent tw:opacity-0 tw:group-hover:opacity-100 tw:transition-opacity tw:flex tw:items-end tw:p-4">
                            <span class="public-gallery-card__title tw:text-white tw:font-medium tw:text-lg">Kamar</span>
                        </div>
                    </div>
                    <div
                        class="public-gallery-card tw:group tw:relative tw:overflow-hidden tw:rounded-2xl tw:shadow-md tw:hover:shadow-xl tw:transition-all tw:duration-300">
                        <img src="images/badminton.png" alt="Ruang Belajar"
                            class="public-gallery-card__image tw:w-full tw:h-64 tw:object-cover tw:transform tw:group-hover:scale-105 tw:transition-transform tw:duration-500" />
                        <div
                            class="public-gallery-card__overlay tw:absolute tw:inset-0 tw:bg-gradient-to-t tw:from-primary/80 tw:to-transparent tw:opacity-0 tw:group-hover:opacity-100 tw:transition-opacity tw:flex tw:items-end tw:p-4">
                            <span class="public-gallery-card__title tw:text-white tw:font-medium tw:text-lg">Lapangan Badminton</span>
                        </div>
                    </div>
                    <div
                        class="public-gallery-card tw:group tw:relative tw:overflow-hidden tw:rounded-2xl tw:shadow-md tw:hover:shadow-xl tw:transition-all tw:duration-300">
                        <img src="images/basket.png" alt="Lobby"
                            class="public-gallery-card__image tw:w-full tw:h-64 tw:object-cover tw:transform tw:group-hover:scale-105 tw:transition-transform tw:duration-500" />
                        <div
                            class="public-gallery-card__overlay tw:absolute tw:inset-0 tw:bg-gradient-to-t tw:from-primary/80 tw:to-transparent tw:opacity-0 tw:group-hover:opacity-100 tw:transition-opacity tw:flex tw:items-end tw:p-4">
                            <span class="public-gallery-card__title tw:text-white tw:font-medium tw:text-lg">Lapangan Basket</span>
                        </div>
                    </div>
                    <div
                        class="public-gallery-card tw:group tw:relative tw:overflow-hidden tw:rounded-2xl tw:shadow-md tw:hover:shadow-xl tw:transition-all tw:duration-300">
                        <img src="images/kantin.png" alt="Parkir"
                            class="public-gallery-card__image tw:w-full tw:h-64 tw:object-cover tw:transform tw:group-hover:scale-105 tw:transition-transform tw:duration-500" />
                        <div
                            class="public-gallery-card__overlay tw:absolute tw:inset-0 tw:bg-gradient-to-t tw:from-primary/80 tw:to-transparent tw:opacity-0 tw:group-hover:opacity-100 tw:transition-opacity tw:flex tw:items-end tw:p-4">
                            <span class="public-gallery-card__title tw:text-white tw:font-medium tw:text-lg">Kantin</span>
                        </div>
                    </div>
                    <div
                        class="public-gallery-card tw:group tw:relative tw:overflow-hidden tw:rounded-2xl tw:shadow-md tw:hover:shadow-xl tw:transition-all tw:duration-300">
                        <img src="images/market.png" alt="Pantry"
                            class="public-gallery-card__image tw:w-full tw:h-64 tw:object-cover tw:transform tw:group-hover:scale-105 tw:transition-transform tw:duration-500" />
                        <div
                            class="public-gallery-card__overlay tw:absolute tw:inset-0 tw:bg-gradient-to-t tw:from-primary/80 tw:to-transparent tw:opacity-0 tw:group-hover:opacity-100 tw:transition-opacity tw:flex tw:items-end tw:p-4">
                            <span class="public-gallery-card__title tw:text-white tw:font-medium tw:text-lg">Mini Market</span>
                        </div>
                    </div>
                    <div
                        class="public-gallery-card tw:group tw:relative tw:overflow-hidden tw:rounded-2xl tw:shadow-md tw:hover:shadow-xl tw:transition-all tw:duration-300">
                        <img src="images/organisasi.png" alt="Taman"
                            class="public-gallery-card__image tw:w-full tw:h-64 tw:object-cover tw:transform tw:group-hover:scale-105 tw:transition-transform tw:duration-500" />
                        <div
                            class="public-gallery-card__overlay tw:absolute tw:inset-0 tw:bg-gradient-to-t tw:from-primary/80 tw:to-transparent tw:opacity-0 tw:group-hover:opacity-100 tw:transition-opacity tw:flex tw:items-end tw:p-4">
                            <span class="public-gallery-card__title tw:text-white tw:font-medium tw:text-lg">Ruang Organisasi</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
