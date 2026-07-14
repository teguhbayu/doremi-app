-- ============================================================
-- Doremi App — Dummy Data Seed
-- Untuk keperluan sidang / presentasi
-- Generated: 2026-07-14
-- ============================================================
-- Penghuni yang tersedia:
--   1=Teguh Bayu, 2=Rayyan, 3=Nadya, 4=Loey Park, 7=Mingoo Kim,
--   8=Nodshey Yu, 9=Yehs Park, 10=aleyey, 11=yaya,
--   12=Putri Anjani, 13=Yoga Saputra, 14=Reza Firmansyah,
--   15=Salsa Amelia, 16=Fikri Ramadhan, 22=Vino, 23=Wanda,
--   24=Xaverius, 25=Yasmin, 26=Alya, 27=Cici, 28=Elsa,
--   29=Hana, 30=Jihan, 31=Lala, 32=Zaki, 33=Bilal,
--   34=Deni, 35=Fajar, 36=Gio, 37=Irfan, 38=Kevin,
--   39=Marco, 40=Oscar, 41=Rafi
-- Petugas yang tersedia:
--   1=Ganjar(PENGURUS), 2=John Pork(SIGAP), 5=Rapip(MAINTENANCE),
--   6=Anies(SIGAP), 11=Pol Pot(MAINTENANCE), 14=Purbaya(MAINTENANCE),
--   36=Farhan(PENGURUS), 37=Gita(SIGAP), 39=Indah(MAINTENANCE),
--   41=Kartika(SIGAP), 43=Mega(MAINTENANCE), 45=Oki(SIGAP),
--   47=Qori(MAINTENANCE), 49=Salma(SIGAP), 51=Ulfa(MAINTENANCE),
--   53=Winda(SIGAP), 55=Yoga(MAINTENANCE)
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- MAINTENANCE (42 data)
-- JenisLaporan: 'Kerusakan Ringan', 'Kerusakan Sedang', 'Kerusakan Darurat / Berat'
-- StatusMaintenance: 'Diajukan', 'Diproses', 'Selesai', 'Ditolak'
-- ============================================================

INSERT INTO `maintenance`
    (`PenghuniID`, `PetugasID`, `RuanganID`, `InventarisID`, `TanggalLapor`, `JenisLaporan`, `Deskripsi`, `StatusMaintenance`, `TanggalSelesai`, `Keterangan`, `FotoLaporan`, `FotoMaintenance`)
VALUES

-- === STATUS: SELESAI (11 data) ===

-- 1. Lampu lorong mati (ruangan, selesai)
(1,  5,  23, NULL, '2026-01-08 09:15:00', 'Kerusakan Ringan',
 'Lampu di lorong depan toilet pria lantai 2 mati total, sudah lebih dari 3 hari.',
 'Selesai', '2026-01-10 14:00:00', 'Lampu sudah diganti dengan yang baru, kondisi normal.',
 'PLACEHOLDER_FOTO_LAPORAN', 'PLACEHOLDER_FOTO_MAINTENANCE'),

-- 2. Toilet mampet (ruangan, selesai)
(2,  11, 24, 117, '2026-01-15 11:00:00', 'Kerusakan Ringan',
 'Toilet wanita lantai 2 mampet, air tidak mau mengalir dan meluap.',
 'Selesai', '2026-01-16 10:30:00', 'Saluran sudah dibersihkan, toilet berfungsi normal kembali.',
 'PLACEHOLDER_FOTO_LAPORAN', 'PLACEHOLDER_FOTO_MAINTENANCE'),

-- 3. Karpet musholla rusak (ruangan, selesai)
(3,  14, 6,  8,   '2026-02-03 08:00:00', 'Kerusakan Ringan',
 'Karpet musholla lantai 1 sudah usang, ada bagian yang sobek cukup panjang.',
 'Selesai', '2026-02-07 16:00:00', 'Karpet sudah diganti dengan yang baru.',
 'PLACEHOLDER_FOTO_LAPORAN', 'PLACEHOLDER_FOTO_MAINTENANCE'),

-- 4. AC kamar tidak dingin (inventaris kamar, selesai)
(4,  39, NULL, 20, '2026-02-10 21:00:00', 'Kerusakan Sedang',
 'AC kamar 7A tidak dingin sama sekali, freon sepertinya habis. Sudah dicoba restart berkali-kali.',
 'Selesai', '2026-02-14 13:00:00', 'Freon sudah diisi ulang, AC berfungsi normal.',
 'PLACEHOLDER_FOTO_LAPORAN', 'PLACEHOLDER_FOTO_MAINTENANCE'),

-- 5. Genset mati saat pemadaman (ruangan umum, selesai)
(7,  5,  NULL, NULL, '2026-02-20 19:30:00', 'Kerusakan Darurat / Berat',
 'Genset tidak menyala saat PLN padam, seluruh gedung gelap gulita. Ini darurat.',
 'Selesai', '2026-02-21 06:00:00', 'Genset sudah diperbaiki, masalah pada aki yang lemah.',
 'PLACEHOLDER_FOTO_LAPORAN', 'PLACEHOLDER_FOTO_MAINTENANCE'),

-- 6. Wastafel bocor (ruangan, selesai)
(8,  43, 7,  9,   '2026-03-05 10:00:00', 'Kerusakan Sedang',
 'Wastafel kamar mandi pria lantai 1 bocor di bagian bawah, air terus menetes ke lantai.',
 'Selesai', '2026-03-07 15:30:00', 'Selang dan sambungan pipa sudah diganti.',
 'PLACEHOLDER_FOTO_LAPORAN', 'PLACEHOLDER_FOTO_MAINTENANCE'),

-- 7. Ring basket rusak (ruangan, selesai)
(9,  14, 10, 10,  '2026-03-12 14:00:00', 'Kerusakan Sedang',
 'Ring basket di lapangan miring ke bawah sebelah kanan, sambungannya longgar.',
 'Selesai', '2026-03-15 17:00:00', 'Ring sudah dikencangkan dan dilas ulang.',
 'PLACEHOLDER_FOTO_LAPORAN', 'PLACEHOLDER_FOTO_MAINTENANCE'),

-- 8. Jemuran patah (ruangan, selesai)
(10, 55, 19, 11,  '2026-03-20 16:00:00', 'Kerusakan Ringan',
 'Salah satu tiang jemuran di ruang jemur patah, jemuran sering jatuh.',
 'Selesai', '2026-03-22 11:00:00', 'Tiang jemuran sudah diganti yang baru.',
 'PLACEHOLDER_FOTO_LAPORAN', 'PLACEHOLDER_FOTO_MAINTENANCE'),

-- 9. Pintu kamar mandi wanita tidak bisa dikunci (ruangan, selesai)
(12, 47, 24, NULL, '2026-04-02 08:30:00', 'Kerusakan Ringan',
 'Kunci pintu kamar mandi wanita lantai 2 rusak, tidak bisa dikunci dari dalam.',
 'Selesai', '2026-04-03 09:00:00', 'Kunci sudah diganti, pintu bisa dikunci normal.',
 'PLACEHOLDER_FOTO_LAPORAN', 'PLACEHOLDER_FOTO_MAINTENANCE'),

-- 10. Atap musholla bocor saat hujan (selesai)
(13, 5,  6,  NULL, '2026-04-15 07:00:00', 'Kerusakan Darurat / Berat',
 'Atap musholla bocor parah saat hujan, air masuk ke dalam dan membahayakan jamaah.',
 'Selesai', '2026-04-20 17:00:00', 'Atap sudah diperbaiki dan dilapisi waterproofing.',
 'PLACEHOLDER_FOTO_LAPORAN', 'PLACEHOLDER_FOTO_MAINTENANCE'),

-- 11. Kursi balkon rusak (selesai)
(15, 51, 15, 57,  '2026-05-01 10:00:00', 'Kerusakan Ringan',
 'Kursi di balkon 1 kaki-kakinya patah, berbahaya jika duduk.',
 'Selesai', '2026-05-03 14:00:00', 'Kursi sudah diganti dengan yang baru.',
 'PLACEHOLDER_FOTO_LAPORAN', 'PLACEHOLDER_FOTO_MAINTENANCE'),

-- === STATUS: DIPROSES (11 data) ===

-- 12. Kran bocor di kamar mandi pria (diproses)
(16, 11, 7,  50,  '2026-05-10 09:00:00', 'Kerusakan Sedang',
 'Kran wastafel kamar mandi pria lantai 1 bocor dan tidak bisa dimatikan sepenuhnya, air terus mengalir.',
 'Diproses', NULL, NULL,
 'PLACEHOLDER_FOTO_LAPORAN', NULL),

-- 13. AC kamar tidak berfungsi (kamar, diproses)
(22, 39, NULL, 64, '2026-05-18 22:00:00', 'Kerusakan Sedang',
 'AC kamar 2E mati total, listrik hidup tapi unit tidak merespons sama sekali.',
 'Diproses', NULL, NULL,
 'PLACEHOLDER_FOTO_LAPORAN', NULL),

-- 14. Shower mampet (kamar mandi wanita, diproses)
(23, 47, 24, 116, '2026-05-25 07:30:00', 'Kerusakan Sedang',
 'Saluran shower kamar mandi wanita lantai 2 mampet, air menggenang sampai ke lutut saat mandi.',
 'Diproses', NULL, NULL,
 'PLACEHOLDER_FOTO_LAPORAN', NULL),

-- 15. Lampu pantry mati (pantry, diproses)
(24, 55, 26, NULL, '2026-06-01 19:00:00', 'Kerusakan Ringan',
 'Lampu di pantry lantai 2 mati, sangat gelap saat malam hari. Mahasiswa tidak bisa masak.',
 'Diproses', NULL, NULL,
 'PLACEHOLDER_FOTO_LAPORAN', NULL),

-- 16. Tembok kamar retak besar (kamar, diproses)
(25, 5,  NULL, NULL, '2026-06-08 14:00:00', 'Kerusakan Sedang',
 'Tembok kamar 2H retak cukup panjang di bagian dekat jendela, khawatir semakin parah.',
 'Diproses', NULL, NULL,
 'PLACEHOLDER_FOTO_LAPORAN', NULL),

-- 17. Pintu kamar 5E macet (kamar, diproses)
(32, 43, NULL, NULL, '2026-06-15 17:00:00', 'Kerusakan Sedang',
 'Pintu kamar 5E macet, susah dibuka dari luar. Sudah beberapa kali dipaksa sampai engsel longgar.',
 'Diproses', NULL, NULL,
 'PLACEHOLDER_FOTO_LAPORAN', NULL),

-- 18. Saluran air balkon tersumbat (balkon, diproses)
(33, 51, 27, NULL, '2026-06-20 16:00:00', 'Kerusakan Sedang',
 'Saluran pembuangan air di balkon 1 lantai 2 tersumbat, air hujan menggenang sampai meluber ke dalam.',
 'Diproses', NULL, NULL,
 'PLACEHOLDER_FOTO_LAPORAN', NULL),

-- 19. Kaca jendela kamar pecah (kamar, diproses)
(34, 11, NULL, NULL, '2026-06-25 11:00:00', 'Kerusakan Darurat / Berat',
 'Kaca jendela kamar 5G pecah, ada retakan besar yang bisa melukai penghuni. Mohon segera ditangani.',
 'Diproses', NULL, NULL,
 'PLACEHOLDER_FOTO_LAPORAN', NULL),

-- 20. Toilet pria lantai 5 macet (toilet, diproses)
(35, 47, 43, 132, '2026-07-01 08:00:00', 'Kerusakan Ringan',
 'WC di toilet pria lantai 5 tidak mau flush, sudah dicoba berkali-kali tidak ada air yang keluar.',
 'Diproses', NULL, NULL,
 'PLACEHOLDER_FOTO_LAPORAN', NULL),

-- 21. Kipas angin pantry rusak (pantry, diproses)
(36, 55, 31, NULL, '2026-07-05 13:00:00', 'Kerusakan Ringan',
 'Kipas angin di pantry lantai 3 tidak berputar, berbunyi keras saat dinyalakan.',
 'Diproses', NULL, NULL,
 'PLACEHOLDER_FOTO_LAPORAN', NULL),

-- 22. Lift (tangga darurat) lampunya mati (darurat, diproses)
(37, 5,  NULL, NULL, '2026-07-08 20:00:00', 'Kerusakan Darurat / Berat',
 'Lampu tangga darurat lantai 6 mati total, sangat gelap dan berbahaya jika ada darurat di malam hari.',
 'Diproses', NULL, NULL,
 'PLACEHOLDER_FOTO_LAPORAN', NULL),

-- === STATUS: DIAJUKAN (12 data) ===

-- 23. Kebersihan pantry kotor (diajukan)
(38, NULL, 39, NULL, '2026-06-28 10:00:00', 'Kerusakan Ringan',
 'Pantry lantai 4 sangat kotor, ada sisa makanan dan lemak menempel di kompor dan meja.',
 'Diajukan', NULL, NULL,
 'PLACEHOLDER_FOTO_LAPORAN', NULL),

-- 24. Shower kamar mandi pria tekanan lemah (diajukan)
(39, NULL, 7,  47,  '2026-07-01 07:00:00', 'Kerusakan Sedang',
 'Tekanan air shower di kamar mandi pria lantai 1 sangat lemah, hampir tidak ada air yang keluar.',
 'Diajukan', NULL, NULL,
 'PLACEHOLDER_FOTO_LAPORAN', NULL),

-- 25. Kasur kamar sobek (kamar, diajukan)
(40, NULL, NULL, 101, '2026-07-03 15:00:00', 'Kerusakan Ringan',
 'Kasur di kamar 7E sobek di bagian bawah, pegas sudah terasa menusuk saat tidur.',
 'Diajukan', NULL, NULL,
 'PLACEHOLDER_FOTO_LAPORAN', NULL),

-- 26. Laci kamar rusak engselnya (kamar, diajukan)
(41, NULL, NULL, 108, '2026-07-04 11:00:00', 'Kerusakan Ringan',
 'Engsel laci di kamar 7F patah, laci tidak bisa ditutup dengan benar.',
 'Diajukan', NULL, NULL,
 'PLACEHOLDER_FOTO_LAPORAN', NULL),

-- 27. Keran musholla bocor (ruangan, diajukan)
(1,  NULL, 25, NULL, '2026-07-05 09:30:00', 'Kerusakan Sedang',
 'Keran wudhu di musholla lantai 2 bocor pada sambungannya, air terus mengalir meski sudah dimatikan.',
 'Diajukan', NULL, NULL,
 'PLACEHOLDER_FOTO_LAPORAN', NULL),

-- 28. Balkon retak di lantai (diajukan)
(2,  NULL, 32, NULL, '2026-07-06 14:00:00', 'Kerusakan Sedang',
 'Ada retakan di lantai balkon 1 lantai 3, khawatir kondisinya semakin parah.',
 'Diajukan', NULL, NULL,
 'PLACEHOLDER_FOTO_LAPORAN', NULL),

-- 29. Pompa air gedung berbunyi aneh (darurat, diajukan)
(3,  NULL, NULL, NULL, '2026-07-07 06:30:00', 'Kerusakan Darurat / Berat',
 'Terdengar suara gemuruh dari ruang pompa air di basement sejak dini hari, takut pompa akan mati.',
 'Diajukan', NULL, NULL,
 'PLACEHOLDER_FOTO_LAPORAN', NULL),

-- 30. Lampu kamar berkedip-kedip (kamar, diajukan)
(4,  NULL, NULL, NULL, '2026-07-08 22:00:00', 'Kerusakan Ringan',
 'Lampu di kamar 3P berkedip-kedip terus saat malam, mengganggu dan membuat sakit mata saat belajar.',
 'Diajukan', NULL, NULL,
 'PLACEHOLDER_FOTO_LAPORAN', NULL),

-- 31. Bau tidak sedap di saluran kamar mandi (diajukan)
(7,  NULL, 8,  NULL, '2026-07-09 08:00:00', 'Kerusakan Sedang',
 'Ada bau menyengat seperti got di kamar mandi wanita lantai 1, kemungkinan saluran tersumbat.',
 'Diajukan', NULL, NULL,
 'PLACEHOLDER_FOTO_LAPORAN', NULL),

-- 32. Lemari kamar pintunya copot (kamar, diajukan)
(8,  NULL, NULL, 43, '2026-07-10 10:00:00', 'Kerusakan Ringan',
 'Engsel pintu lemari di kamar 4A copot, pintu tidak bisa ditutup.',
 'Diajukan', NULL, NULL,
 'PLACEHOLDER_FOTO_LAPORAN', NULL),

-- 33. Kebocoran pipa di ceiling lantai 3 (darurat, diajukan)
(9,  NULL, NULL, NULL, '2026-07-11 19:00:00', 'Kerusakan Darurat / Berat',
 'Ada tetes-tetes air dari langit-langit lorong lantai 3, kemungkinan pipa di atas bocor. Sudah ada genangan.',
 'Diajukan', NULL, NULL,
 'PLACEHOLDER_FOTO_LAPORAN', NULL),

-- 34. AC kamar 4F tidak merespons remote (kamar, diajukan)
(27, NULL, NULL, 79, '2026-07-12 21:30:00', 'Kerusakan Ringan',
 'Remote AC kamar 3F tidak bisa mengubah suhu, sudah ganti baterai tetap tidak merespons.',
 'Diajukan', NULL, NULL,
 'PLACEHOLDER_FOTO_LAPORAN', NULL),

-- === STATUS: DITOLAK (8 data) ===

-- 35. Laporan WiFi mati (ditolak - bukan ranah maintenance)
(10, 1,  NULL, NULL, '2026-01-20 14:00:00', 'Kerusakan Ringan',
 'WiFi di lantai 3 tidak bisa connect, sudah dari kemarin. Minta tolong diperbaiki.',
 'Ditolak', NULL, 'Laporan ditolak: Masalah WiFi bukan ranah maintenance fisik, silakan hubungi pihak ISP melalui pengurus.',
 'PLACEHOLDER_FOTO_LAPORAN', NULL),

-- 36. Laporan bau got (sudah tidak ada, ditolak)
(11, 1,  23, NULL, '2026-02-15 09:00:00', 'Kerusakan Sedang',
 'Ada bau got di sekitar toilet lantai 2, sangat mengganggu.',
 'Ditolak', NULL, 'Setelah dicek oleh petugas, bau sudah tidak terdeteksi. Kemungkinan sementara akibat cuaca panas. Laporan ditutup.',
 'PLACEHOLDER_FOTO_LAPORAN', NULL),

-- 37. Permintaan penambahan furnitur (ditolak - bukan perbaikan)
(13, 36, NULL, NULL, '2026-03-01 10:00:00', 'Kerusakan Ringan',
 'Mohon ditambahkan meja belajar di kamar, yang ada saat ini tidak cukup.',
 'Ditolak', NULL, 'Laporan ditolak: Permintaan penambahan fasilitas bukan termasuk laporan kerusakan. Silakan ajukan melalui pengurus asrama.',
 'PLACEHOLDER_FOTO_LAPORAN', NULL),

-- 38. Laporan AC kamar lain (ditolak - ID kamar salah)
(14, 36, NULL, 25, '2026-03-18 16:00:00', 'Kerusakan Sedang',
 'AC di kamar saya kurang dingin, minta diperiksa.',
 'Ditolak', NULL, 'Setelah pengecekan, AC berfungsi normal. Kondisi kurang dingin karena pintu/jendela sering dibuka. Bukan kerusakan.',
 'PLACEHOLDER_FOTO_LAPORAN', NULL),

-- 39. Laporan duplikat (ditolak)
(15, 14, 7,  9,   '2026-04-10 11:00:00', 'Kerusakan Sedang',
 'Wastafel kamar mandi pria masih bocor, laporan sebelumnya katanya sudah diperbaiki tapi masih bocor.',
 'Ditolak', NULL, 'Pengecekan ulang dilakukan, kondisi wastafel normal. Kemungkinan penghuni keliru dengan wastafel yang berbeda.',
 'PLACEHOLDER_FOTO_LAPORAN', NULL),

-- 40. Laporan kebisingan (ditolak - bukan ranah)
(16, 36, NULL, NULL, '2026-05-05 23:00:00', 'Kerusakan Ringan',
 'Penghuni kamar sebelah sangat berisik di malam hari, tolong ditegur.',
 'Ditolak', NULL, 'Laporan ditolak: Masalah kebisingan antar penghuni bukan ranah maintenance. Silakan laporkan ke pengurus asrama.',
 'PLACEHOLDER_FOTO_LAPORAN', NULL),

-- 41. Genset habis solar (ditolak - sudah teratasi mandiri)
(22, 14, NULL, NULL, '2026-05-22 20:00:00', 'Kerusakan Darurat / Berat',
 'Genset mati saat PLN padam, gedung gelap total.',
 'Ditolak', NULL, 'Laporan ditolak: Saat tim tiba, PLN sudah kembali menyala dan genset sudah normal. Tidak ada kerusakan.',
 'PLACEHOLDER_FOTO_LAPORAN', NULL),

-- 42. Kran kamar mandi sudah diperbaiki sendiri (ditolak)
(23, 14, 24, 119, '2026-06-10 10:00:00', 'Kerusakan Ringan',
 'Keran di kamar mandi wanita lantai 2 bocor kecil.',
 'Ditolak', NULL, 'Setelah dicek, keran dalam kondisi baik. Penghuni menyebutkan masalah sudah berhenti sendiri.',
 'PLACEHOLDER_FOTO_LAPORAN', NULL);

-- ============================================================
-- PAKET (40 data)
-- Kurir: J&T Express, JNE, Shopee Express, SiCepat, Anteraja, GoSend
-- ============================================================

INSERT INTO `paket`
    (`PetugasID`, `NamaPengirim`, `Kurir`, `JenisPaket`, `WaktuSampai`, `PenghuniID`)
VALUES

-- Paket 1-8 (J&T Express)
-- Keterangan kurir di kolom NamaPengirim sudah mencerminkan isi paket
(2,  'Ibu Sari Dewi — Pakaian',       'J&T Express',    'Paket',    '2026-01-10 09:30:00', 1),
(6,  'Toko Buku Gramedia — Buku',      'J&T Express',    'Paket',    '2026-01-18 11:00:00', 2),
(37, 'Ayah Hendra — Bekal Makanan',   'J&T Express',    'Paket',    '2026-02-05 10:00:00', 3),
(41, 'Shopee Store — Elektronik',      'J&T Express',    'Paket',    '2026-02-20 14:00:00', 4),
(45, 'Tokopedia — Kosmetik',           'J&T Express',    'Paket',    '2026-03-08 09:15:00', 7),
(49, 'Ibu Kartini — Pakaian',          'J&T Express',    'Paket',    '2026-04-02 10:30:00', 8),
(53, 'Kak Rizal — Makanan Kering',     'J&T Express',    'Paket',    '2026-05-14 08:45:00', 9),
(2,  'Lazada Store — Gadget',          'J&T Express',    'Paket',    '2026-06-22 13:00:00', 10),

-- Paket 9-16 (JNE)
(6,  'Mama Nurhaliza — Bekal Rumah',   'JNE',            'Paket',    '2026-01-25 11:30:00', 11),
(37, 'Toko Buku Online — Buku Kuliah', 'JNE',            'Dokumen',  '2026-02-10 09:00:00', 12),
(41, 'Bukalapak Store — Pakaian',      'JNE',            'Paket',    '2026-02-28 14:30:00', 13),
(45, 'Ortu Reza — Kiriman Orang Tua',  'JNE',            'Paket',    '2026-03-15 10:00:00', 14),
(49, 'Teman Kos Lama — Titipan',       'JNE',            'Paket',    '2026-04-10 12:00:00', 15),
(53, 'Blibli Official — Elektronik',   'JNE',            'Paket',    '2026-05-20 09:30:00', 16),
(2,  'Ibu Ani — Kosmetik',             'JNE',            'Paket',    '2026-06-05 11:00:00', 22),
(6,  'Tokopedia Seller — Pakaian',     'JNE',            'Paket',    '2026-07-01 14:00:00', 23),

-- Paket 17-23 (Shopee Express)
(37, 'Shopee Store — Buku Pelajaran',  'Shopee Express', 'Dokumen',  '2026-01-30 10:15:00', 24),
(41, 'Shopee Official — Kosmetik',     'Shopee Express', 'Paket',    '2026-03-05 13:30:00', 25),
(45, 'Kak Wulan — Baju Titipan',       'Shopee Express', 'Paket',    '2026-03-22 09:00:00', 26),
(49, 'Shopee Seller — Aksesoris HP',   'Shopee Express', 'Paket',    '2026-04-18 11:30:00', 27),
(53, 'Ayah Wirawan — Makanan Khas',    'Shopee Express', 'Paket',    '2026-05-28 08:30:00', 28),
(2,  'Shopee Store — Kebutuhan Kamar', 'Shopee Express', 'Paket',    '2026-06-15 15:00:00', 29),
(6,  'Teman SMA — Titipan Barang',     'Shopee Express', 'Paket',    '2026-07-05 10:00:00', 30),

-- Paket 24-30 (SiCepat)
(37, 'Blibli Store — Speaker BT',      'SiCepat',        'Paket',    '2026-02-15 14:00:00', 31),
(41, 'Ibu Dewi — Rendang dan Lauk',    'SiCepat',        'Paket',    '2026-03-10 09:30:00', 32),
(45, 'Tokopedia Seller — Sepatu',      'SiCepat',        'Paket',    '2026-04-05 11:00:00', 33),
(49, 'Shopee Official — Buku Teks',    'SiCepat',        'Dokumen',  '2026-05-12 10:30:00', 34),
(53, 'Ortu Fajar — Bekal Makanan',     'SiCepat',        'Paket',    '2026-06-10 13:00:00', 35),
(2,  'Kak Bagas — Skincare',           'SiCepat',        'Paket',    '2026-07-03 09:00:00', 36),
(6,  'Bukalapak Store — Peralatan',    'SiCepat',        'Paket',    '2026-07-08 14:30:00', 37),

-- Paket 31-36 (Anteraja)
(37, 'Ibu Sinta — Pakaian Lebaran',    'Anteraja',       'Paket',    '2026-02-25 10:00:00', 38),
(41, 'Tokopedia Official — Buku Ref.', 'Anteraja',       'Dokumen',  '2026-04-20 13:30:00', 39),
(45, 'Teman Kuliah — Makanan Kering',  'Anteraja',       'Paket',    '2026-05-08 09:15:00', 40),
(49, 'Ortu Marco — Keperluan Bulanan', 'Anteraja',       'Paket',    '2026-06-18 11:45:00', 41),
(53, 'Blibli Official — Powerbank',    'Anteraja',       'Paket',    '2026-07-07 10:00:00', 1),
(2,  'Ibu Rini — Perawatan Kulit',     'Anteraja',       'Paket',    '2026-07-10 15:30:00', 2),

-- Paket 37-40 (GoSend — lokal, lebih sedikit)
(6,  'Warung Bu Yati — Nasi Kotak',   'GoSend',         'Paket',    '2026-05-30 12:00:00', 3),
(37, 'Toko Alat Tulis — Stationery',  'GoSend',         'Paket',    '2026-06-28 13:30:00', 4),
(41, 'Teman Satu Kota — Titipan',     'GoSend',         'Paket',    '2026-07-09 11:00:00', 7),
(45, 'Laundry Express — Baju Bersih', 'GoSend',         'Paket',    '2026-07-11 09:30:00', 8);

-- ============================================================
-- PENGAMBILAN PAKET (25 data — untuk paket ID yang akan di-link)
-- Catatan: paket yang BELUM diambil = paket ID 8,16,22,23,30,31,37,38,39,40
--          (paket terbaru / belum ada pengambilan)
-- 25 paket pertama (ID 1–25 setelah insert) yang sudah diambil
-- Kita gunakan subquery agar tidak hardcode PaketID
-- ============================================================

-- Ambil PaketID terbaru yang baru saja diinsert
-- Kita asumsikan paket diinsert berurutan, ambil 30 paket pertama = sudah diambil
-- 10 paket terakhir = belum diambil

INSERT INTO `pengambilanpaket`
    (`PaketID`, `PenghuniID`, `PetugasID`, `FotoPengambilan`, `WaktuPengambilan`, `Status`, `Keterangan`)
SELECT
    p.PaketID,
    p.PenghuniID,
    CASE
        WHEN p.PetugasID IN (2,6,37,41,45,49,53) THEN p.PetugasID
        ELSE 2
    END AS PetugasID,
    'PLACEHOLDER_FOTO_PENGAMBILAN' AS FotoPengambilan,
    DATE_ADD(p.WaktuSampai, INTERVAL FLOOR(1 + RAND() * 5) DAY) AS WaktuPengambilan,
    'Sudah Diambil' AS Status,
    'Penghuni mengambil paket dengan menunjukkan KTM.' AS Keterangan
FROM paket p
INNER JOIN (
    SELECT PaketID FROM paket ORDER BY PaketID ASC LIMIT 30
) AS top30 ON top30.PaketID = p.PaketID
WHERE NOT EXISTS (
    SELECT 1 FROM pengambilanpaket pp WHERE pp.PaketID = p.PaketID
);

-- ============================================================
-- IN/OUT PENGHUNI (42 data)
-- Status: 'Masuk', 'Keluar', 'Pending'
-- ============================================================

INSERT INTO `inoutpenghuni`
    (`PenghuniID`, `PetugasID`, `WaktuKeluar`, `WaktuMasuk`, `Keperluan`, `Status`)
VALUES

-- === STATUS: MASUK (sudah kembali — 20 data) ===

-- 1. Pulang ke rumah weekend
(1,  2,  '2026-01-10 16:00:00', '2026-01-12 20:00:00', 'Pulang ke rumah',          'Masuk'),
-- 2. Keperluan akademik (bimbingan dosen)
(2,  6,  '2026-01-15 08:00:00', '2026-01-15 14:00:00', 'Keperluan akademik',       'Masuk'),
-- 3. Belanja kebutuhan bulanan
(3,  37, '2026-02-01 10:00:00', '2026-02-01 15:30:00', 'Belanja kebutuhan',        'Masuk'),
-- 4. Kunjungan keluarga (orang tua datang ke kota)
(4,  41, '2026-02-07 09:00:00', '2026-02-07 21:00:00', 'Kunjungan keluarga',       'Masuk'),
-- 5. Pulang ke rumah
(7,  45, '2026-02-14 15:00:00', '2026-02-17 19:00:00', 'Pulang ke rumah',          'Masuk'),
-- 6. Berobat ke klinik
(8,  49, '2026-02-22 09:00:00', '2026-02-22 13:00:00', 'Berobat',                  'Masuk'),
-- 7. Kegiatan KKN persiapan
(9,  53, '2026-03-01 07:00:00', '2026-03-01 17:00:00', 'Keperluan akademik',       'Masuk'),
-- 8. Jalan-jalan ke mall
(10, 2,  '2026-03-08 13:00:00', '2026-03-08 20:00:00', 'Jalan-jalan / olahraga',  'Masuk'),
-- 9. Pulang ke rumah lebaran
(11, 6,  '2026-03-28 14:00:00', '2026-04-05 18:00:00', 'Pulang ke rumah',          'Masuk'),
-- 10. Seminar kampus
(12, 37, '2026-04-08 07:30:00', '2026-04-08 16:00:00', 'Keperluan akademik',       'Masuk'),
-- 11. Belanja perlengkapan kamar
(13, 41, '2026-04-12 10:00:00', '2026-04-12 16:00:00', 'Belanja kebutuhan',        'Masuk'),
-- 12. Kunjungan ke rumah teman
(14, 45, '2026-04-18 14:00:00', '2026-04-18 22:30:00', 'Kunjungan keluarga',       'Masuk'),
-- 13. Olahraga futsal sore
(15, 49, '2026-05-02 15:00:00', '2026-05-02 20:00:00', 'Jalan-jalan / olahraga',  'Masuk'),
-- 14. Bimbingan skripsi
(16, 53, '2026-05-10 08:00:00', '2026-05-10 12:30:00', 'Keperluan akademik',       'Masuk'),
-- 15. Berobat ke puskesmas
(22, 2,  '2026-05-18 09:00:00', '2026-05-18 11:30:00', 'Berobat',                  'Masuk'),
-- 16. Kegiatan organisasi HMJ
(23, 6,  '2026-06-01 13:00:00', '2026-06-01 21:00:00', 'Kegiatan organisasi',      'Masuk'),
-- 17. Pulang ke rumah weekend
(24, 37, '2026-06-06 15:00:00', '2026-06-08 19:00:00', 'Pulang ke rumah',          'Masuk'),
-- 18. Belanja kebutuhan mandi dan makan
(25, 41, '2026-06-15 10:00:00', '2026-06-15 13:00:00', 'Belanja kebutuhan',        'Masuk'),
-- 19. Nonton film di bioskop
(26, 45, '2026-06-20 18:00:00', '2026-06-20 22:30:00', 'Jalan-jalan / olahraga',  'Masuk'),
-- 20. Praktikum lapangan
(27, 49, '2026-06-25 06:30:00', '2026-06-25 18:00:00', 'Keperluan akademik',       'Masuk'),

-- === STATUS: KELUAR (masih di luar — 11 data) ===

-- 21. Pulang ke rumah (belum kembali)
(28, 53, '2026-07-11 15:00:00', '2026-07-14 20:00:00', 'Pulang ke rumah',          'Keluar'),
-- 22. Sidang skripsi hari ini
(29, 2,  '2026-07-14 07:30:00', '2026-07-14 17:00:00', 'Keperluan akademik',       'Keluar'),
-- 23. Berobat ke RS
(30, 6,  '2026-07-13 08:00:00', '2026-07-13 14:00:00', 'Berobat',                  'Keluar'),
-- 24. Kegiatan KKN di luar kota
(31, 37, '2026-07-10 06:00:00', '2026-07-16 18:00:00', 'Keperluan akademik',       'Keluar'),
-- 25. Belanja
(32, 41, '2026-07-14 09:00:00', '2026-07-14 15:00:00', 'Belanja kebutuhan',        'Keluar'),
-- 26. Kunjungan keluarga
(33, 45, '2026-07-12 13:00:00', '2026-07-14 21:00:00', 'Kunjungan keluarga',       'Keluar'),
-- 27. Olahraga lari pagi (keluar pagi ini)
(34, 49, '2026-07-14 05:30:00', '2026-07-14 07:30:00', 'Jalan-jalan / olahraga',  'Keluar'),
-- 28. Kegiatan BEM kampus
(35, 53, '2026-07-14 08:00:00', '2026-07-14 20:00:00', 'Kegiatan organisasi',      'Keluar'),
-- 29. Pulang ke rumah (izin lama)
(36, 2,  '2026-07-08 14:00:00', '2026-07-15 19:00:00', 'Pulang ke rumah',          'Keluar'),
-- 30. Ujian sertifikasi
(37, 6,  '2026-07-14 07:00:00', '2026-07-14 16:00:00', 'Keperluan akademik',       'Keluar'),
-- 31. Belanja bahan skripsi (print, ATK)
(38, 37, '2026-07-14 10:30:00', '2026-07-14 14:00:00', 'Belanja kebutuhan',        'Keluar'),

-- === STATUS: PENDING (menunggu konfirmasi petugas — 11 data) ===

-- 32. Request pulang besok
(39, 2,  '2026-07-15 14:00:00', '2026-07-17 20:00:00', 'Pulang ke rumah',          'Pending'),
-- 33. Request izin besok akademik
(40, 2,  '2026-07-15 08:00:00', '2026-07-15 17:00:00', 'Keperluan akademik',       'Pending'),
-- 34. Request izin belanja
(41, 2,  '2026-07-15 10:00:00', '2026-07-15 15:00:00', 'Belanja kebutuhan',        'Pending'),
-- 35. Request kunjungan keluarga
(1,  2,  '2026-07-18 09:00:00', '2026-07-18 21:00:00', 'Kunjungan keluarga',       'Pending'),
-- 36. Request olahraga
(2,  2,  '2026-07-15 05:30:00', '2026-07-15 07:30:00', 'Jalan-jalan / olahraga',  'Pending'),
-- 37. Request berobat
(3,  2,  '2026-07-15 09:00:00', '2026-07-15 13:00:00', 'Berobat',                  'Pending'),
-- 38. Request kegiatan organisasi
(4,  2,  '2026-07-15 13:00:00', '2026-07-15 22:00:00', 'Kegiatan organisasi',      'Pending'),
-- 39. Request pulang akhir pekan
(7,  2,  '2026-07-18 15:00:00', '2026-07-20 20:00:00', 'Pulang ke rumah',          'Pending'),
-- 40. Request konsultasi dosen luar kota
(8,  2,  '2026-07-16 07:00:00', '2026-07-16 18:00:00', 'Keperluan akademik',       'Pending'),
-- 41. Request beli peralatan mandi
(9,  2,  '2026-07-15 11:00:00', '2026-07-15 13:30:00', 'Belanja kebutuhan',        'Pending'),
-- 42. Request temu keluarga
(10, 2,  '2026-07-19 10:00:00', '2026-07-19 22:00:00', 'Kunjungan keluarga',       'Pending');

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- SUMMARY:
--   maintenance      : 42 data
--     - Selesai      : 11
--     - Diproses     : 11
--     - Diajukan     : 12
--     - Ditolak      :  8
--   paket            : 40 data
--     - Sudah Diambil: 30 (via pengambilanpaket)
--     - Belum Diambil: 10 (paket terbaru)
--   inoutpenghuni    : 42 data
--     - Masuk        : 20
--     - Keluar       : 11
--     - Pending      : 11
-- ============================================================
