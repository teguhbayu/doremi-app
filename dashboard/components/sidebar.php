<?php
$menus = [];

switch ($_SESSION["userRole"]) {
    case "PENGURUS":
        $menus = [
            ["title" => "Home", "target" => "/doremi-app/dashboard/", "icon" => "home-2"],
            ["title" => "Petugas", "target" => "/doremi-app/dashboard/petugas/", "icon" => "user-1"],
            ["title" => "Penghuni", "target" => "/doremi-app/dashboard/penghuni/", "icon" => "group"],
            ["title" => "Kamar", "target" => "/doremi-app/dashboard/kamar/", "icon" => "house-1"],
            ["title" => "Ruangan", "target" => "/doremi-app/dashboard/ruangan/", "icon" => "buildings-1"],
            ["title" => "Inventaris", "target" => "/doremi-app/dashboard/inventaris/", "icon" => "archive-book"],
        ];
        break;
    case "PENGHUNI":
        $menus = [
            ["title" => "Home", "target" => "/doremi-app/dashboard/", "icon" => "home-2"],
            ["title" => "Izin Keluar", "target" => "/doremi-app/dashboard/inout/", "icon" => "export-1"],
            ["title" => "Paket", "target" => "/doremi-app/dashboard/paket/", "icon" => "box"],
        ];
        break;
    case "SIGAP":
        $menus = [
            ["title" => "Home", "target" => "/doremi-app/dashboard/", "icon" => "home-2"],
            ["title" => "Konfirmasi In/Out", "target" => "/doremi-app/dashboard/inout/", "icon" => "shield-tick"],
            ["title" => "Paket", "target" => "/doremi-app/dashboard/paket/", "icon" => "box"],
        ];
        break;
}

?>

<div x-data="{ sidebarOpen: false }">
    <!-- Mobile Top Navbar -->
    <div
        class="tw:md:hidden tw:fixed tw:top-0 tw:left-0 tw:w-dvw tw:h-16 tw:bg-primary tw:flex tw:items-center tw:justify-between tw:px-6 tw:z-50 tw:shadow-md">
        <div class="tw:flex tw:flex-col tw:leading-none">
            <h2 class="tw:font-bold tw:text-xl tw:text-white tw:m-0">DOREMI</h2>
            <p class="tw:text-[10px] tw:text-accent tw:m-0">ASTRATech Dormitory</p>
        </div>
        <button @click="sidebarOpen = !sidebarOpen" class="tw:text-white tw:p-2 tw:focus:outline-none">
            <i class="iconsax tw:text-2xl" :icon-name="sidebarOpen ? 'x-circle' : 'hamburger-menu'"></i>
        </button>
    </div>

    <nav :class="sidebarOpen ? 'tw:translate-y-0' : 'tw:-translate-y-full tw:md:translate-y-0'"
        class="tw:fixed tw:top-0 tw:left-0 tw:bg-primary tw:w-dvw tw:md:w-[300px] tw:h-auto tw:md:h-dvh tw:px-6 tw:pt-20 tw:pb-8 tw:md:py-8.5 tw:z-40 tw:md:z-60 tw:transition-transform tw:duration-300 tw:ease-in-out tw:overflow-y-auto">
        <div class="tw:flex tw:flex-col tw:justify-between tw:items-stretch tw:h-full tw:gap-8 tw:md:gap-0">
            <a class="tw:w-fit tw:no-underline tw:m-0 tw:p-0 tw:leading-none tw:flex tw:flex-col tw:gap-[5px]"
                href="/doremi-app/dashboard/">
                <h2 class="tw:font-bold tw:w-fit tw:text-[30px] tw:text-white tw:m-0 tw:leading-none">DOREMI</h2>
                <p class="tw:font-medium tw:w-fit tw:text-[13px] tw:text-accent tw:m-0 tw:leading-none">ASTRATech
                    Dormitory
                </p>
            </a>

            <div class="tw:flex tw:flex-col tw:gap-2">
                <?php
                $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                foreach ($menus as $menu) {
                    $isActive = false;
                    if ($menu["target"] == "/doremi-app/dashboard/") {
                        $isActive = ($currentPath == "/doremi-app/dashboard/" || $currentPath == "/doremi-app/dashboard/index.php");
                    } else {
                        $isActive = str_starts_with($currentPath, $menu["target"]);
                    }
                    ?>
                    <a @click="sidebarOpen = false"
                        class="tw:no-underline tw:p-2 tw:rounded-lg tw:inline-flex tw:items-center tw:gap-2 <?php echo $isActive ? "tw:bg-white tw:text-primary" : "tw:bg-primary tw:hover:bg-tertiary tw:text-white"; ?>  tw:transition-all tw:duration-500"
                        href="<?php echo $menu["target"]; ?>">
                        <i class="iconsax tw:text-2xl" icon-name="<?php echo $menu["icon"]; ?>"></i>
                        <span>
                            <?php echo $menu["title"]; ?>
                        </span>
                    </a>
                <?php } ?>
            </div>

            <div class="tw:rounded-[16px] tw:py-[21px] tw:flex tw:flex-col tw:gap-1 tw:px-5 tw:bg-tertiary tw:w-full">
                <h3 class="tw:font-semibold tw:text-white tw:text-[16px] tw:m-0 tw:p-0">
                    <?php echo $_SESSION["userName"]; ?>
                </h3>
                <h3 class="tw:font-regular tw:text-accent tw:text-[12px] tw:m-0 tw:p-0">
                    <?php echo $_SESSION["userRole"]; ?>
                </h3>
                <form method="post" action="/doremi-app/logout.php" class="tw:mt-2">
                    <button
                        class="tw:p-2 tw:w-full tw:bg-red-500 tw:rounded-[16px] tw:text-white tw:hover:bg-red-400 tw:transition-all tw:duration-500 tw:border-none">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Overlay for mobile -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false"
        class="tw:md:hidden tw:fixed tw:inset-0 tw:bg-black/50 tw:z-[35]"
        x-transition:enter="tw:transition tw:opacity-0" x-transition:enter-end="tw:opacity-100"
        x-transition:leave="tw:transition tw:opacity-100" x-transition:leave-end="tw:opacity-0">
    </div>
</div>
