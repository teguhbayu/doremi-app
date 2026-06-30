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
            ["title" => "Lapor Kerusakan", "target" => "/doremi-app/dashboard/maintenance/", "icon" => "setting-2"],
            ["title" => "Laporan Maintenance", "target" => "/doremi-app/dashboard/maintenance/report.php", "icon" => "fa-solid fa-chart-bar"],
        ];
        break;
    case "PENGHUNI":
        $menus = [
            ["title" => "Home", "target" => "/doremi-app/dashboard/", "icon" => "home-2"],
            ["title" => "Izin Keluar", "target" => "/doremi-app/dashboard/inout/", "icon" => "send-diagonal-up"],
            ["title" => "Paket", "target" => "/doremi-app/dashboard/paket/", "icon" => "box"],
            ["title" => "Lapor Kerusakan", "target" => "/doremi-app/dashboard/maintenance/", "icon" => "setting-2"],
        ];
        break;
    case "SIGAP":
        $menus = [
            ["title" => "Home", "target" => "/doremi-app/dashboard/", "icon" => "home-2"],
            ["title" => "Konfirmasi In/Out", "target" => "/doremi-app/dashboard/inout/", "icon" => "shield-tick"],
            ["title" => "Paket", "target" => "/doremi-app/dashboard/paket/", "icon" => "box"],
            ["title" => "Lapor Kerusakan", "target" => "/doremi-app/dashboard/maintenance/", "icon" => "setting-2"],
        ];
        break;
    case "SERVANDA":
        $menus = [
            ["title" => "Home", "target" => "/doremi-app/dashboard/", "icon" => "home-2"],
            ["title" => "Lapor Kerusakan", "target" => "/doremi-app/dashboard/maintenance/", "icon" => "setting-2"],
        ];
        break;
    case "MAINTENANCE":
        $menus = [
            ["title" => "Home", "target" => "/doremi-app/dashboard/", "icon" => "home-2"],
            ["title" => "Maintenance", "target" => "/doremi-app/dashboard/maintenance/", "icon" => "setting-2"],
            ["title" => "Laporan Maintenance", "target" => "/doremi-app/dashboard/maintenance/report.php", "icon" => "fa-solid fa-chart-bar"],
        ];
        break;
}
?>

<div x-data="{ sidebarOpen: false }">
    <div class="dashboard-topbar">
        <div class="tw:flex tw:flex-col tw:leading-[1.1]">
            <strong class="tw:font-bold tw:text-slate-900">DOREMI</strong>
            <span class="tw:text-slate-500 tw:text-xs">Dashboard Operasional Asrama</span>
        </div>
        <button @click="sidebarOpen = !sidebarOpen"
            class="tw:w-12 tw:h-12 tw:inline-flex tw:items-center tw:justify-center tw:rounded-2xl tw:bg-[rgba(47,127,240,0.08)] tw:text-primary"
            type="button" aria-label="Buka menu">
            <i class="iconsax tw:text-2xl" :icon-name="sidebarOpen ? 'x-circle' : 'hamburger-menu'"></i>
        </button>
    </div>

    <aside class="dashboard-sidebar" :class="{ 'is-open': sidebarOpen }">
        <div class="dashboard-sidebar__panel">
            <a class="tw:flex tw:items-center  tw:gap-3 tw:p-[0.4rem] tw:no-underline" href="/doremi-app/dashboard/">
                <span
                    class="tw:flex tw:justify-center tw:items-center tw:bg-white/10! tw:rounded-[18px] tw:p-2 tw:size-fit">
                    <img src="/doremi-app/images/logo.png" alt="Logo DOREMI"
                        class="tw:size-8 tw:rounded-full tw:object-cover tw:aspect-square">
                </span>
                <span>
                    <strong class="tw:block tw:text-white tw:text-[1.1rem] tw:font-bold">DOREMI</strong>
                    <span class="tw:text-[rgba(255,255,255,0.60)] tw:text-[1rem]">Dormitory Control Center</span>
                </span>
            </a>

            <div class="tw:flex-1 tw:overflow-y-auto tw:py-[35px] tw:px-[15px] tw:-my-[35px] tw:-mx-[15px]"
                style="-webkit-mask-image: linear-gradient(to bottom, transparent 0%, black 20px, black calc(100% - 35px), transparent 100%); mask-image: linear-gradient(to bottom, transparent 0%, black 20px, black calc(100% - 35px), transparent 100%);">
                <p
                    class="tw:text-[0.76rem] tw:font-bold tw:tracking-[0.08em] tw:uppercase tw:text-[rgba(255,255,255,0.55)] tw:mt-1 tw:mb-1 tw:pt-[5px]">
                    Menu
                    <?= htmlspecialchars($_SESSION["userRole"]) ?>
                </p>
                <nav class="tw:grid tw:gap-2">
                    <?php
                    $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

                    $bestMatchIndex = -1;
                    $longestMatchLen = 0;
                    foreach ($menus as $idx => $menu) {
                        $target = $menu["target"];
                        $isMatch = false;
                        if ($target == "/doremi-app/dashboard/") {
                            $isMatch = ($currentPath == "/doremi-app/dashboard/" || $currentPath == "/doremi-app/dashboard/index.php");
                        } else {
                            $isMatch = str_starts_with($currentPath, $target);
                        }

                        if ($isMatch) {
                            $matchLen = strlen($target);
                            if ($matchLen > $longestMatchLen) {
                                $longestMatchLen = $matchLen;
                                $bestMatchIndex = $idx;
                            }
                        }
                    }

                    foreach ($menus as $idx => $menu) {
                        $isActive = ($idx === $bestMatchIndex);
                        ?>
                        <a @click="sidebarOpen = false" class="dashboard-sidebar__link <?= $isActive ? 'is-active' : '' ?>"
                            href="<?= htmlspecialchars($menu["target"]) ?>">
                            <?php if (str_starts_with($menu["icon"], 'fa-')) { ?>
                                <i class="<?= htmlspecialchars($menu["icon"]) ?>"></i>
                            <?php } else { ?>
                                <i class="iconsax" icon-name="<?= htmlspecialchars($menu["icon"]) ?>"></i>
                            <?php } ?>
                            <span><?= htmlspecialchars($menu["title"]) ?></span>
                        </a>
                    <?php } ?>
                </nav>
            </div>

            <div
                class="tw:mt-auto tw:p-4 tw:rounded-[18px] tw:bg-[rgba(255,255,255,0.08)] tw:border tw:border-[rgba(255,255,255,0.08)]">
                <div class="tw:mb-3">
                    <strong
                        class="tw:block tw:text-white tw:font-bold"><?= htmlspecialchars($_SESSION["userName"]) ?></strong>
                    <small
                        class="tw:text-[rgba(255,255,255,0.60)] tw:text-[0.875em]"><?= htmlspecialchars($_SESSION["userRole"]) ?></small>
                </div>
                <form method="post" action="/doremi-app/logout.php">
                    <button
                        class="tw:w-full tw:min-h-[2.9rem] tw:rounded-2xl tw:text-white tw:font-extrabold tw:bg-[rgba(188,79,69,0.90)] tw:hover:bg-[rgba(188,79,69,1)] tw:transition-colors tw:font-extrabold"
                        type="submit">Logout</button>
                </form>
            </div>
        </div>
    </aside>

    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="dashboard-overlay"
        x-transition:enter="tw:transition tw:opacity-0" x-transition:enter-end="tw:opacity-100"
        x-transition:leave="tw:transition tw:opacity-100" x-transition:leave-end="tw:opacity-0"></div>
</div>