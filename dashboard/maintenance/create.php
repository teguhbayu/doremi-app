<?php
session_start();
require 'helpers.php';
// Memastikan semua peran yang diizinkan bisa membuat laporan baru
maintenance_require_roles(['PENGURUS', 'PENGHUNI', 'SIGAP', 'SERVANDA', 'MAINTENANCE']);
require '../../csrf.php';
require '../../db.php';
require '../../utils/old_input.php';
require_once '../../database/maintenance.php';
require_once '../../database/penghuni.php';
require 'validation.php';

$role = $_SESSION['userRole'];
$userId = (int)$_SESSION['userId'];

$rooms = fetchMaintenanceRooms($db, false);
$kamars = fetchActiveKamarWithOccupancy($db);
$inventory = fetchMaintenanceInventory($db, false);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate('create.php');
    $reportInput = collectMaintenanceReportInput($_POST);
    $validationMessage = validateMaintenanceReportInput($db, $reportInput);
    if ($validationMessage !== null) {
        setOldFormInput($_POST);
        maintenance_redirect('create.php', 'error', $validationMessage);
    }
    $targetIds = resolveMaintenanceTargetIds($reportInput);

    $tanggalLapor = date('Y-m-d');
    
    $penghuniId = null;
    $petugasId = null;
    if ($role === 'PENGHUNI') {
        $penghuniId = $userId;
    } else {
        $petugasId = $userId;
    }

    try {
        $fotoBase64 = maintenance_store_photo($_FILES['foto_laporan'] ?? []);
    } catch (RuntimeException $e) {
        setOldFormInput($_POST);
        maintenance_redirect('create.php', 'error', $e->getMessage());
    }

    try {
        createMaintenanceReport(
            $db,
            $penghuniId,
            $petugasId,
            $targetIds['ruanganId'],
            $targetIds['inventarisId'],
            $reportInput['jenisLaporan'],
            $reportInput['deskripsi'],
            $fotoBase64,
            $tanggalLapor
        );
        maintenance_redirect('index.php', 'success', 'Laporan kerusakan berhasil dibuat.');
    } catch (RuntimeException) {
        setOldFormInput($_POST);
        maintenance_redirect('create.php', 'error', 'Terjadi kesalahan sistem saat mengirim laporan.');
    }
}

$old = pullOldFormInput();

$initialLocationType = !empty($old['kamar_id']) ? 'kamar' : 'ruangan';
?>

<!DOCTYPE html>
<html lang="en">
<?php require '../../head.php'; ?>

<body class="dashboard-body tw:p-0 tw:m-0 tw:relative tw:flex tw:min-h-screen">
    <?php require '../components/sidebar.php'; ?>

    <main class="tw:md:ml-75 tw:grow">
        <div class="tw:pt-20 tw:md:pt-8 tw:px-8 tw:mb-8 tw:flex-1 tw:w-dvw tw:md:w-full">
            
            <?php require dirname(__DIR__) . '/components/breadcrumb.php'; ?>

            <h1 class="page-title" data-kicker="Form Laporan" data-subtitle="Buat laporan kerusakan baru secara terperinci agar segera ditindaklanjuti oleh tim pemeliharaan asrama.">
                Ajukan Laporan Maintenance
            </h1>
            <div class="page-toolbar" data-note="Form laporan maintenance baru">
                <a href="index.php" class="page-secondary-btn">
                    <i class="iconsax" icon-name="arrow-left"></i>
                    <span>Kembali ke daftar</span>
                </a>
            </div>

            
            <div class="tw:grid tw:grid-cols-1 tw:lg:grid-cols-3 tw:gap-8">
                
               
                <div class="tw:lg:col-span-2">
                    <form action="create.php" method="POST" enctype="multipart/form-data" class="tw:grid tw:grid-cols-1 tw:lg:grid-cols-2 tw:gap-4 tw:p-[1.45rem] tw:rounded-[24px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm">

                        <?php echo csrf_field(); ?>

                        <div class="mb-3 tw:col-span-full">
                            <label for="skala_prioritas" class="form-label">Skala Prioritas (Berbasis Kriteria OSHA)</label>
                            <select name="skala_prioritas" id="skala_prioritas" class="form-select" required onchange="verifyPriority()">
                                <option value="" disabled <?= empty($old['skala_prioritas']) ? 'selected' : '' ?>>-- Pilih Kategori Kerusakan --</option>
                                <option value="Kerusakan Ringan" data-osha="convenience" <?= ($old['skala_prioritas'] ?? '') === 'Kerusakan Ringan' ? 'selected' : '' ?>>Kerusakan Ringan (Non-Urgent / Kenyamanan)</option>
                                <option value="Kerusakan Sedang" data-osha="serious" <?= ($old['skala_prioritas'] ?? '') === 'Kerusakan Sedang' ? 'selected' : '' ?>>Kerusakan Sedang (Urgent / Keamanan & Sanitasi Dasar)</option>
                                <option value="Kerusakan Darurat / Berat" data-osha="imminent" <?= ($old['skala_prioritas'] ?? '') === 'Kerusakan Darurat / Berat' ? 'selected' : '' ?>>Darurat (Emergency / Ancaman Keselamatan Jiwa & Fisik)</option>
                            </select>


                            <div id="osha-helper" class="tw:mt-2 tw:p-3 tw:rounded-xl tw:text-xs tw:hidden tw:border"></div>
                        </div>


                        <div class="tw:contents" x-data="{
                                locationType: '<?= htmlspecialchars($initialLocationType, ENT_QUOTES) ?>',
                                selectedRuangan: '<?= htmlspecialchars((string) ($old['ruangan_id'] ?? ''), ENT_QUOTES) ?>',
                                selectedKamar: '<?= htmlspecialchars((string) ($old['kamar_id'] ?? ''), ENT_QUOTES) ?>',
                                selectedInventaris: '<?= htmlspecialchars((string) ($old['inventaris_id'] ?? ''), ENT_QUOTES) ?>',
                                inventoryList: <?= htmlspecialchars(json_encode($inventory), ENT_QUOTES, 'UTF-8') ?>,
                                get filteredInventory() {
                                    return this.locationType === 'ruangan'
                                        ? this.inventoryList.filter(item => String(item.RuanganID) === String(this.selectedRuangan))
                                        : this.inventoryList.filter(item => String(item.KamarID) === String(this.selectedKamar));
                                },
                                setLocationType(type) {
                                    this.locationType = type;
                                    this.selectedRuangan = '';
                                    this.selectedKamar = '';
                                    this.selectedInventaris = '';
                                }
                            }">
                            <div class="mb-3">
                                <label class="form-label">Lokasi Kerusakan</label>
                                <div class="tw:flex tw:gap-4 tw:mt-1">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="location_type" id="location_ruangan" value="ruangan" :checked="locationType === 'ruangan'" @change="setLocationType('ruangan')">
                                        <label class="form-check-label" for="location_ruangan">Ruangan (Area Umum)</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="location_type" id="location_kamar" value="kamar" :checked="locationType === 'kamar'" @change="setLocationType('kamar')">
                                        <label class="form-check-label" for="location_kamar">Kamar (Kamar Tidur)</label>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3" x-show="locationType === 'ruangan'" x-cloak>
                                <label for="ruangan_id" class="form-label">Pilih Ruangan</label>
                                <select name="ruangan_id" id="ruangan_id" class="form-select" x-model="selectedRuangan" @change="selectedInventaris = ''" :required="locationType === 'ruangan'">
                                    <option value="" disabled <?= empty($old['ruangan_id']) ? 'selected' : '' ?>>Pilih Ruangan</option>
                                    <?php foreach ($rooms as $r): ?>
                                        <option value="<?= $r['RuanganID'] ?>" <?= (string) ($old['ruangan_id'] ?? '') === (string) $r['RuanganID'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($r['NamaRuangan']) ?> (Lantai <?= htmlspecialchars($r['Lantai']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3" x-show="locationType === 'kamar'" x-cloak>
                                <label for="kamar_id" class="form-label">Kamar</label>
                                <select class="form-select" name="kamar_id" id="kamar_id" x-model="selectedKamar" @change="selectedInventaris = ''" :required="locationType === 'kamar'">
                                    <option value="" <?= empty($old['kamar_id']) ? 'selected' : '' ?> disabled>Pilih Kamar</option>
                                    <?php foreach ($kamars as $k): ?>
                                        <option value="<?= $k['KamarID'] ?>" <?= (string) ($old['kamar_id'] ?? '') === (string) $k['KamarID'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($k['NomorKamar']) ?> - Lantai <?= htmlspecialchars($k['Lantai']) ?>
                                            (<?= (int) $k['JumlahPenghuniAktual'] ?>/<?= (int) $k['KapasitasPenghuni'] ?> terisi)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3" x-show="(locationType === 'ruangan' && selectedRuangan !== '') || (locationType === 'kamar' && selectedKamar !== '')" x-cloak>
                                <label for="inventaris_id" class="form-label" x-text="locationType === 'ruangan' ? 'Inventaris di Ruangan Ini (Opsional)' : 'Pilih Barang yang Rusak'"></label>
                                <select name="inventaris_id" id="inventaris_id" class="form-select" x-model="selectedInventaris" :required="locationType === 'kamar'">
                                    <option value="" :disabled="locationType === 'kamar'" x-text="locationType === 'ruangan' ? '-- Laporkan Ruangan Secara Umum --' : '-- Pilih Barang --'"></option>
                                    <template x-for="item in filteredInventory" :key="item.InventarisID">
                                        <option :value="item.InventarisID" x-text="item.NamaBarang"></option>
                                    </template>
                                </select>
                                <div class="form-text" x-show="filteredInventory.length === 0" x-text="locationType === 'ruangan' ? 'Tidak ada data inventaris tercatat untuk ruangan ini.' : 'Tidak ada data inventaris tercatat untuk kamar ini.'"></div>
                            </div>
                        </div>


                        <div class="mb-3 tw:col-span-full">
                            <label for="deskripsi" class="form-label">Deskripsi Masalah</label>
                            <textarea name="deskripsi" id="deskripsi" maxlength="1000" class="form-control" rows="4" required
                                      placeholder="Jelaskan kronologi dan letak kerusakan..." oninput="updateCharCount(this)"><?= htmlspecialchars($old['deskripsi'] ?? '') ?></textarea>
                            <div class="tw:text-xs tw:text-gray-500 tw:text-right tw:mt-1">
                                <span id="charCount"><?= mb_strlen($old['deskripsi'] ?? '') ?></span> / 1000 karakter
                            </div>
                        </div>

                        
                        <div class="mb-4 tw:col-span-full">
                            <label for="foto_laporan" class="form-label">Foto Masalah / Kerusakan</label>
                            <input type="file" name="foto_laporan" id="foto_laporan" class="form-control" accept="image/png,image/jpeg,image/webp">
                            <div class="form-text">Format file didukung: JPG, PNG, atau WEBP (Maksimal 2MB).</div>
                        </div>

                        
                        <div class="tw:w-full tw:flex tw:justify-end tw:gap-3 tw:col-span-full">
                            <a href="index.php" class="tw:inline-flex tw:items-center tw:justify-center tw:gap-2 tw:min-h-12 tw:px-4 tw:py-[0.85rem] tw:rounded-2xl tw:border tw:border-[rgba(22,60,122,0.12)] tw:font-extrabold tw:no-underline tw:text-slate-900 tw:bg-[rgba(255,255,255,0.82)] tw:hover:bg-gray-50 tw:transition-all tw:text-sm">Batal</a>
                            <button type="submit" class="tw:inline-flex tw:items-center tw:justify-center tw:gap-2 tw:min-h-12 tw:px-4 tw:py-[0.85rem] tw:rounded-2xl tw:border tw:border-transparent tw:font-extrabold tw:no-underline tw:text-white tw:bg-secondary tw:shadow-md tw:hover:bg-primary tw:transition-all tw:text-sm">Kirim Laporan</button>
                        </div>
                    </form>
                </div>

                
                <div class="tw:lg:col-span-1">
                    <div class="tw:relative tw:overflow-hidden tw:p-[1.4rem] tw:rounded-[28px] tw:border tw:border-[rgba(255,255,255,0.75)] tw:bg-[rgba(255,255,255,0.88)] tw:shadow-sm">
                        <h5 class="tw:m-0 tw:text-[1.2rem] tw:text-slate-900 tw:flex tw:items-center tw:gap-2 tw:mb-3">
                            <i class="iconsax tw:text-lg" icon-name="info-circle"></i>
                            <span>Panduan OSHA</span>
                        </h5>
                        <p class="tw:m-0 tw:text-slate-500 tw:leading-[1.75] tw:text-sm tw:mb-4">
                            Gunakan panduan standar OSHA berikut untuk menentukan skala prioritas secara objektif, demi kelancaran prioritas pengerjaan oleh tim teknisi.
                        </p>
                        <div class="tw:grid tw:gap-[0.85rem]">
                            <div class="tw:p-4 tw:rounded-[18px] tw:bg-[rgba(245,221,218,0.55)] tw:border tw:border-l-4 tw:border-[rgba(188,79,69,0.20)] tw:border-l-red-500">
                                <strong class="tw:text-red-700">Darurat <i>(Imminent Danger)</i></strong>
                                <p class="tw:text-xs">Kondisi bahaya nyata yang mengancam keselamatan fisik segera (misal: korsleting aktif, kebocoran gas, kebakaran, banjir besar).</p>
                            </div>
                            <div class="tw:p-4 tw:rounded-[18px] tw:bg-[rgba(250,236,207,0.55)] tw:border tw:border-l-4 tw:border-[rgba(212,141,47,0.20)] tw:border-l-amber-500">
                                <strong class="tw:text-amber-700">Sedang <i>(Serious Hazard)</i></strong>
                                <p class="tw:text-xs">Mengganggu fungsi hidup harian atau keamanan mendesak (air mati total, kunci pintu luar rusak, toilet mampet).</p>
                            </div>
                            
                            <div class="tw:p-4 tw:rounded-[18px] tw:bg-green-50 tw:border tw:border-l-4 tw:border-[rgba(22,101,52,0.20)] tw:border-l-green-700">
                                <strong class="tw:text-green-700">Ringan <i>(Other-than-Serious)</i></strong>
                                <p class="tw:text-xs tw:text-green-600">Kerusakan minor/kosmetik yang tidak mengancam keselamatan fisik (keran menetes, engsel longgar, lampu redup).</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    
    <?php require '../../bootstrap.php'; ?>
    <?php require '../../validation_alert.php'; ?>

    <script>
        // Fungsi Penanganan Box Notifikasi OSHA Dinamis dengan Warna Merah Legam (#7f1d1d)
        function verifyPriority() {
            const select = document.getElementById('skala_prioritas');
            const helper = document.getElementById('osha-helper');
            const selectedOption = select.options[select.selectedIndex];
            const category = selectedOption.getAttribute('data-osha');

            // Reset Kelas bawaan
            helper.className = "tw:mt-2 tw:p-3 tw:rounded-xl tw:text-xs tw:hidden tw:border";
            helper.removeAttribute('style');

            if (category === 'imminent') {
                helper.classList.remove('tw:hidden');
                helper.setAttribute('style', 'background-color: #fef2f2; color: #7f1d1d; border-color: #fee2e2; font-weight: 600;');
                helper.innerHTML = "<strong>Peringatan Darurat:</strong> Kondisi ini harus merupakan ancaman keselamatan fisik segera. Laporan palsu atau penyalahgunaan kategori ini dapat dikenakan sanksi administratif.";
                
                // Konfirmasi Pengguna untuk Mencegah Abuse
                const confirmCheck = confirm("Peringatan: Kategori 'Darurat' hanya untuk kondisi berbahaya yang mengancam keselamatan fisik penghuni segera (seperti korsleting aktif, kebocoran gas, atau banjir bandang). Apakah kerusakan ini benar-benar darurat?");
                if (!confirmCheck) {
                    select.value = "Kerusakan Ringan";
                    verifyPriority();
                }
            }
        }

        // Fungsi Penghitung Panjang Karakter Deskripsi Masalah Real-Time
        function updateCharCount(textarea) {
            const charCountSpan = document.getElementById('charCount');
            if (charCountSpan) {
                charCountSpan.textContent = textarea.value.length;
            }
        }
    </script>
</body>

</html>
