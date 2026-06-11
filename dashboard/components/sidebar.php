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
        ];
        break;
    case "SIGAP":
        $menus = [
            ["title" => "Home", "target" => "/doremi-app/dashboard/", "icon" => "home-2"],
            ["title" => "Konfirmasi In/Out", "target" => "/doremi-app/dashboard/inout/", "icon" => "shield-tick"],
        ];
        break;
}

?>

<nav class="tw:fixed tw:top-0 tw:left-0 tw:bg-primary tw:!w-[300px] tw:h-dvh tw:px-6 tw:py-8.5">
    <div class="tw:flex tw:min-h-[70%] tw:flex-col tw:justify-between tw:item-start tw:items-stretch tw:h-full">
        <a class="tw:w-fit tw:no-underline tw:m-0 tw:p-0 tw:leading-none tw:flex tw:flex-col tw:gap-[5px]"
            href="index.php">
            <h2 class="tw:font-bold tw:w-fit tw:text-[30px] tw:text-white tw:m-0 tw:leading-none">DOREMI</h2>
            <p class="tw:font-medium tw:w-fit tw:text-[13px] tw:text-accent tw:m-0 tw:leading-none">ASTRATech Dormitory
            </p>
        </a>
        <div class=" tw:flex tw:flex-col tw:gap-2 ">

            <?php 
            $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            foreach ($menus as $menu) { 
                // Better matching logic: 
                // 1. If it's the home dashboard, match exactly (or index.php)
                // 2. Otherwise, check if current path starts with the menu target
                $isActive = false;
                if ($menu["target"] == "/doremi-app/dashboard/") {
                    $isActive = ($currentPath == "/doremi-app/dashboard/" || $currentPath == "/doremi-app/dashboard/index.php");
                } else {
                    $isActive = str_starts_with($currentPath, $menu["target"]);
                }
            ?>
                <a class="tw:no-underline tw:p-2 tw:rounded-lg tw:inline-flex tw:items-center tw:gap-2 <?php echo $isActive ? "tw:bg-white tw:text-primary" : "tw:bg-primary tw:hover:bg-tertiary tw:text-white"; ?>  tw:transition-all tw:duration-500"
                    href="<?php echo $menu["target"]; ?>">
                    <i class="iconsax tw:text-2xl" icon-name="<?php echo $menu["icon"]; ?>"></i>
                    <span>
                        <?php echo $menu["title"]; ?>
                    </span>
                </a>

            <?php } ?>
        </div>
        <div class="tw:rounded-[16px] tw:py-[21px] tw:flex tw:flex-col tw:gap-1 tw:px-5 tw:bg-tertiary w-full">
            <h3 class="tw:font-semibold tw:text-white tw:text-[16px] tw:m-0 tw:p-0"><?php echo $_SESSION["userName"]; ?>
            </h3>
            <h3 class="tw:font-regular tw:text-accent tw:text-[12px] tw:m-0 tw:p-0"><?php echo $_SESSION["userRole"]; ?>
            </h3>
            <form method="post" action="/doremi-app/logout.php" class="tw:mt-2">
                <button
                    class="tw:p-2 tw:w-full tw:bg-red-500 tw:rounded-[16px] tw:text-white tw:hover:bg-red-400 tw:transition-all tw:duration-500 tw:border-none tw:hover:border-none">Logout</button>
            </form>
        </div>
    </div>
</nav>