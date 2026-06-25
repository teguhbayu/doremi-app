<?php
session_start();
require 'helpers.php';
// Memastikan semua peran yang diizinkan bisa membuat laporan baru
maintenance_require_roles(['PENGURUS', 'PENGHUNI', 'SIGAP', 'SERVANDA', 'MAINTENANCE']);
require '../../db.php';

$role = $_SESSION['userRole'];
$userId = (int)$_SESSION['userId'];

// 1. Mengambil Data Ruangan untuk Pilihan Lokasi
$roomsQuery = mysqli_query($db, "SELECT RuanganID, NamaRuangan, Lantai FROM ruangan ORDER BY Lantai ASC, NamaRuangan ASC");
$rooms = mysqli_fetch_all($roomsQuery, MYSQLI_ASSOC);

// 2. Mengambil Data Inventaris untuk Pilihan Target Barang
$inventoryQuery = mysqli_query($db, "SELECT InventarisID, NamaBarang FROM inventaris ORDER BY NamaBarang ASC");
$inventory = mysqli_fetch_all($inventoryQuery, MYSQLI_ASSOC);

// 3. Memproses Form Saat Disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jenisLaporan = mysqli_real_escape_string($db, trim($_POST['skala_prioritas'] ?? ''));
    $deskripsi = mysqli_real_escape_string($db, trim($_POST['deskripsi'] ?? ''));
    $targetTipe = $_POST['target_tipe'] ?? 'ruangan';
    
    $ruanganId = null;
    $inventarisId = null;

    if ($targetTipe === 'ruangan') {
        $ruanganId = filter_input(INPUT_POST, 'ruangan_id', FILTER_VALIDATE_INT);
    } else {
        $inventarisId = filter_input(INPUT_POST, 'inventaris_id', FILTER_VALIDATE_INT);
    }

    $tanggalLapor = date('Y-m-d');
    
    // Logika Kepemilikan Laporan berdasarkan Peran Pengguna
    $penghuniId = null;
    $petugasId = null;
    if ($role === 'PENGHUNI') {
        $penghuniId = $userId;
    } else {
        $petugasId = $userId;
    }

    // Mengonversi Foto Laporan ke Base64 (jika ada file diunggah)
    $fotoBase64 = "";
    if (isset($_FILES['foto_laporan']) && $_FILES['foto_laporan']['error'] === UPLOAD_ERR_OK) {
        $fileSize = $_FILES['foto_laporan']['size'];
        if ($fileSize > 2 * 1024 * 1024) { // Batasan 2MB
            maintenance_redirect('create.php', 'error', 'Ukuran foto laporan maksimal 2MB.');
        }

        $path = $_FILES['foto_laporan']['tmp_name'];
        $type = pathinfo($_FILES['foto_laporan']['name'], PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $fotoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
    }

    // Menyisipkan Data Laporan Baru ke Database
    $stmt = mysqli_prepare($db, "
        INSERT INTO maintenance (
            PenghuniID, PetugasID, RuanganID, InventarisID, 
            JenisLaporan, Deskripsi, FotoLaporan, TanggalLapor, StatusMaintenance
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Diajukan')
    ");
    
    mysqli_stmt_bind_param(
        $stmt, 
        'iiiissss', 
        $penghuniId, 
        $petugasId, 
        $ruanganId, 
        $inventarisId, 
        $jenisLaporan, 
        $deskripsi, 
        $fotoBase64, 
        $tanggalLapor
    );

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        maintenance_redirect('index.php', 'success', 'Laporan kerusakan berhasil dibuat.');
    } else {
        mysqli_stmt_close($stmt);
        maintenance_redirect('create.php', 'error', 'Terjadi kesalahan sistem saat mengirim laporan.');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<?php require '../../head.php'; ?>

<body class="dashboard-body tw:p-0 tw:m-0 relative tw:flex tw:bg-[#f8fafc] tw:min-h-screen">
    <?php require '../components/sidebar.php'; ?>

    <main class="dashboard-main tw:md:ml-75 tw:grow">
        <div class="dashboard-page tw:pt-20 tw:md:pt-8 tw:px-8 tw:mb-8 tw:flex-1 tw:w-dvw tw:md:w-full">
            
            <?php require dirname(__DIR__) . '/components/breadcrumb.php'; ?>

            <h1 class="page-title tw:mb-6" data-kicker="Form Laporan" data-subtitle="Buat laporan kerusakan baru secara terperinci agar segera ditindaklanjuti oleh tim pemeliharaan asrama.">
                Ajukan Laporan Maintenance
            </h1>

            
            <div class="tw:grid tw:grid-cols-1 tw:lg:grid-cols-3 tw:gap-8">
                
               
                <div class="tw:lg:col-span-2">
                    <form action="create.php" method="POST" enctype="multipart/form-data" class="form-shell" style="max-width: 100%;">
                        
                        
                        <div class="mb-3 form-shell__full">
                            <label for="skala_prioritas" class="form-label">Skala Prioritas (Berbasis Kriteria OSHA)</label>
                            <select name="skala_prioritas" id="skala_prioritas" class="form-select" required onchange="verifyPriority()">
                                <option value="" disabled selected>-- Pilih Kategori Kerusakan --</option>
                                <option value="Kerusakan Ringan" data-osha="convenience">Kerusakan Ringan (Non-Urgent / Kenyamanan)</option>
                                <option value="Kerusakan Sedang" data-osha="serious">Kerusakan Sedang (Urgent / Keamanan & Sanitasi Dasar)</option>
                                <option value="Kerusakan Darurat / Berat" data-osha="imminent">Darurat (Emergency / Ancaman Keselamatan Jiwa & Fisik)</option>
                            </select>
                            
                        
                            <div id="osha-helper" class="tw:mt-2 tw:p-3 tw:rounded-xl tw:text-xs tw:hidden tw:border"></div>
                        </div>

                        
                        <div class="mb-3 form-shell__full">
                            <label class="form-label">Target Lokasi Laporan</label>
                            <div class="tw:flex tw:gap-4 tw:mt-1 tw:mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="target_tipe" id="target_ruangan" value="ruangan" checked onchange="toggleTargetType('ruangan')">
                                    <label class="form-check-label" for="target_ruangan">Ruangan</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="target_tipe" id="target_inventaris" value="inventaris" onchange="toggleTargetType('inventaris')">
                                    <label class="form-check-label" for="target_inventaris">Inventaris / Barang</label>
                                </div>
                            </div>

                        
                            <div id="ruangan-container">
                                <select name="ruangan_id" id="ruangan_id" class="form-select" required>
                                    <option value="" disabled selected>Pilih Ruangan</option>
                                    <?php foreach ($rooms as $r): ?>
                                        <option value="<?= $r['RuanganID'] ?>">
                                            <?= htmlspecialchars($r['NamaRuangan']) ?> (Lantai <?= htmlspecialchars($r['Lantai']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div id="inventaris-container" class="tw:hidden">
                                <select name="inventaris_id" id="inventaris_id" class="form-select">
                                    <option value="" disabled selected>Pilih Inventaris / Barang</option>
                                    <?php foreach ($inventory as $i): ?>
                                        <option value="<?= $i['InventarisID'] ?>">
                                            <?= htmlspecialchars($i['NamaBarang']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        
                        <div class="mb-3 form-shell__full">
                            <label for="deskripsi" class="form-label">Deskripsi Masalah</label>
                            <textarea name="deskripsi" id="deskripsi" maxlength="500" class="form-control" rows="4" required 
                                      placeholder="Jelaskan kronologi dan letak kerusakan..." oninput="updateCharCount(this)"></textarea>
                            <div class="tw:text-xs tw:text-gray-500 tw:text-right tw:mt-1">
                                <span id="charCount">0</span> / 500 karakter
                            </div>
                        </div>

                        
                        <div class="mb-4 form-shell__full">
                            <label for="foto_laporan" class="form-label">Foto Masalah / Kerusakan</label>
                            <input type="file" name="foto_laporan" id="foto_laporan" class="form-control" accept="image/png,image/jpeg,image/webp">
                            <div class="form-text">Format file didukung: JPG, PNG, atau WEBP (Maksimal 2MB).</div>
                        </div>

                        
                        <div class="tw:w-full tw:flex tw:justify-end tw:gap-3 form-shell__full">
                            <a href="index.php" class="page-secondary-btn">Batal</a>
                            <button type="submit" class="page-primary-btn">Kirim Laporan</button>
                        </div>
                    </form>
                </div>

                
                <div class="tw:lg:col-span-1">
                    <div class="dashboard-side-panel">
                        <h5 class="dashboard-side-panel__title tw:flex tw:items-center tw:gap-2 tw:mb-3">
                            <i class="iconsax tw:text-lg" icon-name="info-circle"></i>
                            <span>Panduan OSHA</span>
                        </h5>
                        <p class="dashboard-side-panel__copy tw:mb-4">
                            Gunakan panduan standar OSHA berikut untuk menentukan skala prioritas secara objektif, demi kelancaran prioritas pengerjaan oleh tim teknisi.
                        </p>
                        <div class="dashboard-guide-list">
                            <div class="dashboard-guide-item dashboard-guide-item--danger">
                                <strong class="tw:text-red-700">Darurat <i>(Imminent Danger)</i></strong>
                                <p class="tw:text-xs">Kondisi bahaya nyata yang mengancam keselamatan fisik segera (misal: korsleting aktif, kebocoran gas, kebakaran, banjir besar).</p>
                            </div>
                            <div class="dashboard-guide-item dashboard-guide-item--warning">
                                <strong class="tw:text-amber-700">Sedang <i>(Serious Hazard)</i></strong>
                                <p class="tw:text-xs">Mengganggu fungsi hidup harian atau keamanan mendesak (air mati total, kunci pintu luar rusak, toilet mampet).</p>
                            </div>
                            
                            <div class="dashboard-guide-item" style="border-left-color: #166534; background-color: #f0fdf4;">
                                <strong style="color: #166534;">Ringan <i>(Other-than-Serious)</i></strong>
                                <p class="tw:text-xs" style="color: #15803d;">Kerusakan minor/kosmetik yang tidak mengancam keselamatan fisik (keran menetes, engsel longgar, lampu redup).</p>
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
                helper.innerHTML = "<strong>⚠️ Peringatan Darurat:</strong> Kondisi ini harus merupakan ancaman keselamatan fisik segera. Laporan palsu atau penyalahgunaan kategori ini dapat dikenakan sanksi administratif.";
                
                // Konfirmasi Pengguna untuk Mencegah Abuse
                const confirmCheck = confirm("Peringatan: Kategori 'Darurat' hanya untuk kondisi berbahaya yang mengancam keselamatan fisik penghuni segera (seperti korsleting aktif, kebocoran gas, atau banjir bandang). Apakah kerusakan ini benar-benar darurat?");
                if (!confirmCheck) {
                    select.value = "Kerusakan Ringan";
                    verifyPriority();
                }
            }
        }

        // Fungsi Toggling Pilihan Lokasi Target (Ruangan vs Inventaris)
        function toggleTargetType(type) {
            const ruanganContainer = document.getElementById('ruangan-container');
            const inventarisContainer = document.getElementById('inventaris-container');
            const ruanganSelect = document.getElementById('ruangan_id');
            const inventarisSelect = document.getElementById('inventaris_id');

            if (type === 'ruangan') {
                ruanganContainer.classList.remove('tw:hidden');
                inventarisContainer.classList.add('tw:hidden');
                ruanganSelect.setAttribute('required', 'required');
                inventarisSelect.removeAttribute('required');
                inventarisSelect.value = "";
            } else {
                ruanganContainer.classList.add('tw:hidden');
                inventarisContainer.classList.remove('tw:hidden');
                inventarisSelect.setAttribute('required', 'required');
                ruanganSelect.removeAttribute('required');
                ruanganSelect.value = "";
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