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
        <div class="dashboard-topbar__brand">
            <strong>DOREMI</strong>
            <span>Dashboard Operasional Asrama</span>
        </div>
        <button @click="sidebarOpen = !sidebarOpen" class="dashboard-topbar__toggle" type="button"
            aria-label="Buka menu">
            <i class="iconsax tw:text-2xl" :icon-name="sidebarOpen ? 'x-circle' : 'hamburger-menu'"></i>
        </button>
    </div>

    <aside class="dashboard-sidebar" :class="{ 'is-open': sidebarOpen }">
        <div class="dashboard-sidebar__panel">
            <a class="dashboard-sidebar__brand" href="/doremi-app/dashboard/">
                <span
                    class="dashboard-sidebar__brand-mark tw:flex tw:justify-center tw:items-center tw:p-2 tw:size-fit">
                    <img src="/doremi-app/images/logo.png" alt="Logo DOREMI" class="tw:size-8 tw:aspect-square">
                </span>
                <span>
                    <strong>DOREMI</strong>
                    <span>Dormitory Control Center</span>
                </span>
            </a>

            <!-- 
              SMOOTHED CONTAINER:
              - Flex layout restricts it to remaining screen space.
              - Padding + Negative Margin allows active button shadows to display without clipping.
              - Mask-image creates a subtle gradient fade at the top and bottom borders.
            -->
            <div
                style="flex: 1; overflow-y: auto; padding: 15px 15px 35px 15px; margin: -15px -15px -35px -15px; -webkit-mask-image: linear-gradient(to bottom, transparent 0%, black 20px, black calc(100% - 35px), transparent 100%); mask-image: linear-gradient(to bottom, transparent 0%, black 20px, black calc(100% - 35px), transparent 100%);">
                <p class="dashboard-sidebar__eyebrow" style="padding-top: 5px;">Menu
                    <?= htmlspecialchars($_SESSION["userRole"]) ?>
                </p>
                <nav class="dashboard-sidebar__nav">
                    <?php
                    $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

                    // Cari menu dengan kecocokan target terpanjang (paling spesifik)
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

            <div class="dashboard-sidebar__footer">
                <div class="dashboard-sidebar__user">
                    <strong><?= htmlspecialchars($_SESSION["userName"]) ?></strong>
                    <small><?= htmlspecialchars($_SESSION["userRole"]) ?></small>
                </div>
                <form method="post" action="/doremi-app/logout.php">
                    <button class="dashboard-sidebar__logout" type="submit">Logout</button>
                </form>
            </div>
        </div>
    </aside>

    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="dashboard-overlay"
        x-transition:enter="tw:transition tw:opacity-0" x-transition:enter-end="tw:opacity-100"
        x-transition:leave="tw:transition tw:opacity-100" x-transition:leave-end="tw:opacity-0"></div>
</div>