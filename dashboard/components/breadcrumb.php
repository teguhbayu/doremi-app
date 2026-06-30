<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
$dashboardBasePath = '/doremi-app/dashboard';
$relativePath = trim((string) str_replace($dashboardBasePath, '', $currentPath), '/');

if ($relativePath === '' || $relativePath === 'index.php') {
    return;
}

$segments = array_values(array_filter(explode('/', $relativePath)));
$moduleMap = [
    'petugas' => 'Petugas',
    'penghuni' => 'Penghuni',
    'kamar' => 'Kamar',
    'ruangan' => 'Ruangan',
    'inventaris' => 'Inventaris',
    'maintenance' => 'Maintenance',
    'paket' => 'Paket',
    'inout' => 'In/Out',
];
$actionMap = [
    'create.php' => 'Tambah',
    'edit.php' => 'Edit',
    'detail.php' => 'Detail',
    'pickup.php' => 'Pengambilan',
    'review.php' => 'Review',
    'log.php' => 'Log',
];

$breadcrumbs = [
    ['label' => 'Home', 'href' => $dashboardBasePath . '/'],
];

$isModuleIndex = count($segments) === 1;
$moduleSlug = $segments[0] ?? '';
$moduleLabel = $moduleMap[$moduleSlug] ?? ucfirst(str_replace('-', ' ', $moduleSlug));

if ($isModuleIndex) {
    $breadcrumbs[] = ['label' => $moduleLabel, 'href' => null];
} else {
    $breadcrumbs[] = ['label' => $moduleLabel, 'href' => $dashboardBasePath . '/' . $moduleSlug . '/'];

    $currentFile = $segments[1] ?? '';
    $currentLabel = $actionMap[$currentFile] ?? ucfirst(str_replace(['.php', '-'], '', $currentFile));
    $breadcrumbs[] = ['label' => $currentLabel, 'href' => null];
}
?>

<nav class="tw:flex tw:items-center tw:justify-between tw:gap-4 tw:mb-4" aria-label="Breadcrumb">
    <div class="tw:inline-flex tw:items-center tw:flex-wrap tw:gap-[0.55rem] tw:min-h-[2.8rem] tw:px-4 tw:py-3 tw:rounded-[18px] tw:bg-[rgba(255,253,248,0.76)] tw:border tw:border-[rgba(255,255,255,0.72)] tw:shadow-sm">
        <?php foreach ($breadcrumbs as $index => $crumb): ?>
            <?php if ($index > 0): ?>
                <span class="tw:inline-flex tw:items-center tw:text-sm tw:leading-[1.2] tw:text-slate-400">
                    <i class="iconsax" icon-name="chevron-right"></i>
                </span>
            <?php endif; ?>

            <?php if (!empty($crumb['href'])): ?>
                <a href="<?= htmlspecialchars($crumb['href']) ?>" class="tw:inline-flex tw:items-center tw:text-sm tw:leading-[1.2] tw:text-slate-500 tw:font-bold tw:no-underline tw:hover:text-primary">
                    <?= htmlspecialchars($crumb['label']) ?>
                </a>
            <?php else: ?>
                <span class="tw:inline-flex tw:items-center tw:text-sm tw:leading-[1.2] tw:text-slate-900 tw:font-extrabold" aria-current="page">
                    <?= htmlspecialchars($crumb['label']) ?>
                </span>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <?php if ($isModuleIndex): ?>
        <div class="tw:inline-flex tw:items-center tw:gap-3 tw:flex-wrap">
            <a href="<?= htmlspecialchars($dashboardBasePath . '/') ?>" class="tw:inline-flex tw:items-center tw:justify-center tw:gap-2 tw:min-h-12 tw:px-4 tw:py-[0.85rem] tw:rounded-2xl tw:border tw:border-[rgba(22,60,122,0.12)] tw:font-extrabold tw:no-underline tw:text-slate-900 tw:bg-[rgba(255,255,255,0.82)] tw:hover:bg-gray-50 tw:transition-all tw:text-sm">
                <i class="iconsax" icon-name="arrow-left-2"></i>
                <span>Kembali ke Home</span>
            </a>
        </div>
    <?php endif; ?>
</nav>
