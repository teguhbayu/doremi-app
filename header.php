<?php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Access denied');
}
?>

<header id="global-header" x-data="{ open: false }"
    class="tw:bg-[rgba(255,255,255,0.88)] tw:border-b tw:border-[rgba(255,255,255,0.75)] tw:shadow-sm tw:z-40 tw:fixed tw:w-full tw:top-0 tw:transition-all tw:duration-300 tw:backdrop-blur-xl">
    <div class="tw:max-w-7xl tw:mx-auto tw:px-4 tw:sm:px-6 tw:lg:px-8">
        <div class="tw:flex tw:justify-between tw:h-[74px] tw:items-center">
            <div class="tw:flex tw:items-center tw:cursor-pointer tw:gap-2" onclick="navigate('landing')">
                <img src="images/logo.png" alt="Logo DOREMI"
                    class="tw:aspect-[1/1] tw:rounded-full tw:object-cover tw:size-10" />
                <span class="tw:text-2xl tw:font-extrabold tw:text-primary tw:tracking-tight">DOREMI</span>
            </div>

            <div class="tw:hidden tw:md:flex tw:items-center tw:gap-1">
                <a href="index.php"
                    class="public-nav__link tw:text-gray-600 tw:no-underline tw:hover:text-primary tw:px-3 tw:py-2 tw:rounded-xl tw:text-sm tw:transition-colors">
                    Beranda
                </a>
                <a href="gallery.php"
                    class="public-nav__link tw:text-gray-600 tw:no-underline tw:hover:text-primary tw:px-3 tw:py-2 tw:rounded-xl tw:text-sm tw:transition-colors">
                    Galeri
                </a>
                <a href="login.php"
                    class="public-nav__cta tw:no-underline tw:text-white tw:px-5 tw:py-2 tw:rounded-full tw:text-sm">
                    <?php echo isset($_SESSION["userId"]) ? "Dashboard" : "Masuk"; ?>
                </a>
            </div>

            <div class="tw:flex tw:md:hidden tw:items-center">
                <button @click="open = !open"
                    class="tw:text-primary tw:p-2 tw:focus:outline-none tw:rounded-xl tw:bg-[rgba(255,255,255,0.75)] tw:border tw:border-[rgba(22,60,122,0.12)]">
                    <i class="iconsax tw:text-2xl" :icon-name="open ? 'x-circle' : 'hamburger-menu'"></i>
                </button>
            </div>
        </div>
    </div>

    <div x-show="open" x-transition:enter="tw:transition tw:ease-out tw:duration-200"
        x-transition:enter-start="tw:opacity-0 tw:scale-95" x-transition:enter-end="tw:opacity-100 tw:scale-100"
        class="public-mobile-menu tw:md:hidden tw:border-t tw:border-gray-100 tw:pb-4 tw:px-4">
        <div class="tw:flex tw:flex-col tw:gap-2 tw:mt-2">
            <a href="index.php"
                class="public-mobile-menu__link tw:text-gray-600 tw:no-underline tw:px-4 tw:py-3 tw:rounded-xl tw:text-base">Beranda</a>
            <a href="gallery.php"
                class="public-mobile-menu__link tw:text-gray-600 tw:no-underline tw:px-4 tw:py-3 tw:rounded-xl tw:text-base">Galeri</a>
            <a href="login.php"
                class="public-mobile-menu__cta tw:no-underline tw:text-white tw:px-4 tw:py-3 tw:rounded-xl tw:text-base tw:font-bold tw:text-center tw:block">
                <?php echo isset($_SESSION["userId"]) ? "Dashboard" : "Masuk"; ?>
            </a>
        </div>
    </div>
</header>