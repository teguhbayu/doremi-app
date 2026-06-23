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

<nav class="page-breadcrumb" aria-label="Breadcrumb">
    <div class="page-breadcrumb__trail">
        <?php foreach ($breadcrumbs as $index => $crumb): ?>
            <?php if ($index > 0): ?>
                <span class="page-breadcrumb__separator">
                    <i class="iconsax" icon-name="chevron-right"></i>
                </span>
            <?php endif; ?>

            <?php if (!empty($crumb['href'])): ?>
                <a href="<?= htmlspecialchars($crumb['href']) ?>" class="page-breadcrumb__link">
                    <?= htmlspecialchars($crumb['label']) ?>
                </a>
            <?php else: ?>
                <span class="page-breadcrumb__current" aria-current="page">
                    <?= htmlspecialchars($crumb['label']) ?>
                </span>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <?php if ($isModuleIndex): ?>
        <div class="page-breadcrumb__back">
            <a href="<?= htmlspecialchars($dashboardBasePath . '/') ?>" class="page-secondary-btn">
                <i class="iconsax" icon-name="arrow-left-2"></i>
                <span>Kembali ke Home</span>
            </a>
        </div>
    <?php endif; ?>
</nav>
