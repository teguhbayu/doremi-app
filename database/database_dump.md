# Database Dump — `astrador_doremiapp`

> Generated on 2026-07-03 15:16:26 WIB

---

## Table of Contents

1. [inoutpenghuni](#inoutpenghuni)
2. [inventaris](#inventaris)
3. [kamar](#kamar)
4. [maintenance](#maintenance)
5. [paket](#paket)
6. [pengambilanpaket](#pengambilanpaket)
7. [penghuni](#penghuni)
8. [petugas](#petugas)
9. [ruangan](#ruangan)

---

## inoutpenghuni

**Rows:** 9

| InOutID | PenghuniID | PetugasID | WaktuKeluar | Keperluan | WaktuMasuk | Status |
| --- | --- | --- | --- | --- | --- | --- |
| 1 | 1 | 2 | 2026-06-11 14:20:50 | Beli Makan | 2026-06-11 14:21:01 | Masuk |
| 2 | 1 | 2 | 2026-06-11 21:24:33 | Fotocopy | 2026-06-11 21:24:42 | Masuk |
| 3 | 1 | 2 | 2026-06-11 21:42:46 | Beli Ciki | 2026-06-11 21:43:09 | Masuk |
| 4 | 1 | 2 | 2026-06-12 02:40:33 | LDKM | 2026-06-12 12:47:59 | Masuk |
| 5 | 3 | 2 | 2026-06-12 15:16:09 | Beli egg sando | 2026-06-23 08:22:58 | Masuk |
| 6 | 7 | 2 | 2026-06-19 10:00:20 | makan | 2026-06-19 10:01:46 | Masuk |
| 7 | 3 | 2 | 2026-06-23 13:05:02 | eek | 2026-06-23 13:05:05 | Masuk |
| 8 | 3 | _NULL_ | 2026-06-23 16:35:00 | eek | 2026-06-23 22:00:00 | Pending |
| 9 | 2 | _NULL_ | 2026-06-26 15:55:00 | Berak | 2026-06-26 19:53:00 | Pending |

---

## inventaris

**Rows:** 3

| InventarisID | RuanganID | KamarID | NamaBarang | Jumlah | Keterangan | UpdatedAt | IsDeleted |
| --- | --- | --- | --- | --- | --- | --- | --- |
| 1 | _NULL_ | 2 | Heri | 0 | Punya Fahri | 2026-06-26 13:24:19 | 0 |
| 2 | _NULL_ | 1 | Kursi | 2 |  | 2026-06-25 07:56:07 | 0 |
| 3 | 12 | _NULL_ | Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore | 1 | Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore … | 2026-06-25 10:17:01 | 0 |

---

## kamar

**Rows:** 7

| KamarID | NomorKamar | KapasitasPenghuni | UpdatedAt | IsDeleted | Lantai |
| --- | --- | --- | --- | --- | --- |
| 1 | 7H | 3 | 2026-06-12 13:29:25 | 0 | 7 |
| 2 | 7A | 3 | 2026-06-12 13:29:17 | 0 | 7 |
| 3 | 7C | 2 | 2026-06-12 14:13:38 | 0 | 7 |
| 4 | 3P | 4 | 2026-06-12 09:34:56 | 0 | 3 |
| 5 | 3W | 1 | 2026-06-19 10:21:29 | 0 | 3 |
| 7 | 3C | 4 | 2026-06-23 08:04:42 | 0 | 3 |
| 8 | 4A | 3 | 2026-06-23 08:12:06 | 0 | 4 |

---

## maintenance

**Rows:** 20

| MaintenanceID | PenghuniID | PetugasID | RuanganID | InventarisID | TanggalLapor | JenisLaporan | Deskripsi | StatusMaintenance | TanggalSelesai | Keterangan | FotoLaporan | FotoMaintenance | IsDeleted |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| 2 | 3 | 5 | 7 | _NULL_ | 2026-06-19 00:00:00 | Kerusakan Darurat / Berat | TOILET RUSAAAKK | Selesai | 2026-06-19 00:00:00 | Tai dah ngga belebran | data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAFwAAABNCAYAAAArZQNmAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjw… | data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABogAAAOtCAIAAAA95HBeAABhWmNhQlgAAGFaanVtYgAAAB5qdW1kYz… | 0 |
| 3 | 2 | 5 | 7 | _NULL_ | 2026-06-19 00:00:00 | Kerusakan Darurat / Berat | Tai beleberan | Selesai | 2026-06-19 00:00:00 | Tai masih beleberan | data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABogAAAOtCAIAAAA95HBeAABhWmNhQlgAAGFaanVtYgAAAB5qdW1kYz… | data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABogAAAOtCAIAAAA95HBeAABhWmNhQlgAAGFaanVtYgAAAB5qdW1kYz… | 0 |
| 4 | _NULL_ | 1 | 8 | _NULL_ | 2026-06-22 00:00:00 | Kerusakan Sedang | sdfghjkl | Selesai | 2026-06-23 00:00:00 | ass | data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAuUAAAPcCAYAAADmOMUvAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjw… | data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABogAAAOtCAIAAAA95HBeAABhWmNhQlgAAGFaanVtYgAAAB5qdW1kYz… | 0 |
| 5 | _NULL_ | 11 | 6 | _NULL_ | 2026-06-23 00:00:00 | Kerusakan Darurat / Berat | kabel rusak | Selesai | 2026-06-24 00:00:00 | 1234 |  | data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABogAAAOtCAIAAAA95HBeAABxn2NhQlgAAHGfanVtYgAAAB5qdW1kYz… | 0 |
| 6 | _NULL_ | 1 | 8 | _NULL_ | 2026-06-23 00:00:00 | Kerusakan Sedang | Tai beleberan | Diproses | _NULL_ | _NULL_ | data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABogAAAOtCAIAAAA95HBeAABxn2NhQlgAAHGfanVtYgAAAB5qdW1kYz… | _NULL_ | 0 |
| 7 | _NULL_ | 5 | 1 | _NULL_ | 2026-06-23 00:00:00 | Kerusakan Darurat / Berat | Tai Beleberan | Selesai | 2026-06-24 00:00:00 | 1234 | data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABogAAAOtCAIAAAA95HBeAABxn2NhQlgAAHGfanVtYgAAAB5qdW1kYz… | data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABogAAAOtCAIAAAA95HBeAABxn2NhQlgAAHGfanVtYgAAAB5qdW1kYz… | 0 |
| 8 | _NULL_ | 1 | 7 | _NULL_ | 2026-06-23 00:00:00 | Kerusakan Sedang | bbdhbhdbhbhbdhbdhbdhbdsddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddd… | Diajukan | _NULL_ | _NULL_ | data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABogAAAOtCAIAAAA95HBeAABxn2NhQlgAAHGfanVtYgAAAB5qdW1kYz… | _NULL_ | 0 |
| 9 | _NULL_ | 5 | 5 | _NULL_ | 2026-06-23 00:00:00 | Kerusakan Darurat / Berat | TAIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIII… | Selesai | 2026-06-24 00:00:00 | 1234 | data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABogAAAOtCAIAAAA95HBeAABxn2NhQlgAAHGfanVtYgAAAB5qdW1kYz… | data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABogAAAOtCAIAAAA95HBeAABxn2NhQlgAAHGfanVtYgAAAB5qdW1kYz… | 0 |
| 10 | _NULL_ | 1 | 1 | _NULL_ | 2026-06-23 00:00:00 | Kerusakan Sedang | Lorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesqu… | Diajukan | _NULL_ | _NULL_ | data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABogAAAOtCAIAAAA95HBeAABxn2NhQlgAAHGfanVtYgAAAB5qdW1kYz… | _NULL_ | 0 |
| 11 | _NULL_ | 1 | 1 | _NULL_ | 2026-06-23 00:00:00 | Kerusakan Ringan | Lorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesqu… | Diajukan | _NULL_ | _NULL_ | data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABogAAAOtCAIAAAA95HBeAABxn2NhQlgAAHGfanVtYgAAAB5qdW1kYz… | _NULL_ | 0 |
| 12 | _NULL_ | 1 | _NULL_ | 1 | 2026-06-23 00:00:00 | Kerusakan Ringan | Lorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesqu… | Diajukan | _NULL_ | _NULL_ | data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABogAAAOtCAIAAAA95HBeAABxn2NhQlgAAHGfanVtYgAAAB5qdW1kYz… | _NULL_ | 0 |
| 13 | _NULL_ | 1 | _NULL_ | 1 | 2026-06-23 00:00:00 | Kerusakan Ringan | Lorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesqu… | Diajukan | _NULL_ | _NULL_ | data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABogAAAOtCAIAAAA95HBeAABxn2NhQlgAAHGfanVtYgAAAB5qdW1kYz… | _NULL_ | 0 |
| 14 | _NULL_ | 5 | 6 | _NULL_ | 2026-06-23 00:00:00 | Kerusakan Darurat / Berat | Lorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesqu… | Selesai | 2026-06-23 00:00:00 | Halo wok | data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABogAAAOtCAIAAAA95HBeAABxn2NhQlgAAHGfanVtYgAAAB5qdW1kYz… | data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABogAAAOtCAIAAAA95HBeAABxn2NhQlgAAHGfanVtYgAAAB5qdW1kYz… | 0 |
| 15 | _NULL_ | 1 | 6 | _NULL_ | 2026-06-23 00:00:00 | Kerusakan Ringan | Lorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesqu… | Diajukan | _NULL_ | _NULL_ | data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABogAAAOtCAIAAAA95HBeAABxn2NhQlgAAHGfanVtYgAAAB5qdW1kYz… | _NULL_ | 0 |
| 16 | _NULL_ | 1 | 1 | _NULL_ | 2026-06-25 00:00:00 | Kerusakan Sedang | b | Diajukan | _NULL_ | _NULL_ |  | _NULL_ | 0 |
| 17 | _NULL_ | 5 | 12 | _NULL_ | 2026-06-25 00:00:00 | Kerusakan Darurat / Berat | Tai belerbarmn | Diproses | _NULL_ | _NULL_ | data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABogAAAOtCAIAAAA95HBeAABxn2NhQlgAAHGfanVtYgAAAB5qdW1kYz… | _NULL_ | 0 |
| 18 | _NULL_ | 5 | 1 | _NULL_ | 2026-07-03 00:00:00 | Kerusakan Darurat / Berat | jbwejbej | Diajukan | _NULL_ | _NULL_ | data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/4gHYSUNDX1BST0ZJTEUAAQEAAAHIAAAAAAQwAABtbnRyUkdCI… | _NULL_ | 0 |
| 19 | 2 | _NULL_ | 12 | _NULL_ | 2026-07-03 00:00:00 | Kerusakan Ringan | Lampu Mati/tidak nyala | Diajukan | _NULL_ | _NULL_ | data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/4gHYSUNDX1BST0ZJTEUAAQEAAAHIAAAAAAQwAABtbnRyUkdCI… | _NULL_ | 0 |
| 20 | 2 | _NULL_ | 2 | _NULL_ | 2026-07-03 00:00:00 | Kerusakan Sedang | Air mati total | Diajukan | _NULL_ | _NULL_ | data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/4gHYSUNDX1BST0ZJTEUAAQEAAAHIAAAAAAQwAABtbnRyUkdCI… | _NULL_ | 0 |
| 21 | 2 | _NULL_ | 11 | _NULL_ | 2026-07-03 00:00:00 | Kerusakan Darurat / Berat | Lantai terbelah | Diajukan | _NULL_ | _NULL_ | data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/4gHYSUNDX1BST0ZJTEUAAQEAAAHIAAAAAAQwAABtbnRyUkdCI… | _NULL_ | 0 |

---

## paket

**Rows:** 8

| PaketID | PetugasID | NamaPengirim | Kurir | JenisPaket | WaktuSampai | PenghuniID |
| --- | --- | --- | --- | --- | --- | --- |
| 1 | 2 | Tutut | JNE | Paket | 2026-06-17 02:15:00 | 2 |
| 2 | 2 | Tatat | SPX | Paket | 2026-06-17 02:17:00 | 3 |
| 3 | 2 | Egg Sando | Qadar | Paket | 2026-06-17 02:18:00 | 1 |
| 4 | 2 | PT. Fruit Sando | Qadar | Paket | 2026-06-19 09:18:00 | 7 |
| 5 | 2 | fghjk | hoho | Paket | 2026-06-19 10:05:00 | 2 |
| 6 | 2 | Lolok | lolaklolok | Paket | 2026-06-23 08:29:00 | 2 |
| 7 | 2 | FikSUn | Devnity | Dokumen | 2026-06-23 09:05:00 | 10 |
| 8 | 2 | SM | SMent | Paket | 2026-06-23 11:11:00 | 4 |

---

## pengambilanpaket

**Rows:** 4

| PengambilanPaketID | PaketID | PenghuniID | PetugasID | FotoPengambilan | WaktuPengambilan | Status | Keterangan |
| --- | --- | --- | --- | --- | --- | --- | --- |
| 1 | 2 | 3 | 2 | data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAuUAAAPcCAYAAADmOMUvAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjw… | 2026-06-17 03:48:27 | Sudah Diambil | paket tertukar |
| 2 | 3 | 1 | 2 | assets/uploads/paket/paket-3-20260617081613-9be90f66.png | 2026-06-19 09:21:00 | Sudah Diambil | Paket Bagus |
| 3 | 4 | 7 | 2 | data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAzkAAAK8CAYAAADMGHHGAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjw… | 2026-06-19 09:54:59 | Sudah Diambil | dfghjkl |
| 4 | 6 | 2 | 2 | data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAACAAAAAYACAYAAADimKhtAAC970lEQVR4nOzdC5Bd930f9v+5C0ASSE… | 2026-06-26 14:03:57 | Sudah Diambil | Skibidi Tolet |

---

## penghuni

**Rows:** 9

| PenghuniID | KamarID | NamaPenghuni | Nim | JenisKelamin | NoHP | Email | Password | Alamat | IsActive | UpdateAt | IsDeleted |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| 1 | 1 | Teguh Bayu Pratama | 0920250052 | L | 085156795782 | teguhbayupratama@gmail.com | $2y$12$CiFaIGHTrun9R2Y5vSuKUeZ.uCP2lVRz0My3jPauVAnVio3iTKNKO | Palu | 1 | 2026-06-11 14:17:21 | 0 |
| 2 | 2 | Rayyan Abdurrahman Qadar | 0920250049 | L | 081210822482 | unarayyan10@gmail.com | $2y$12$rZeDmEtwGpjmwQOMMNXvQu9QGfO9IDNqkec42WYDfu9zijxWAbvAy | Sebelah Musolah Surga | 1 | 2026-06-17 04:02:50 | 0 |
| 3 | 4 | Nadya Kamila | 0920250045 | P | 087825195922 | kamilanadya444@gmail.com | $2y$10$5C8Te2Nvn0gk6cbTUfxBOOEFkVtclh2Dj8fknfCOLDodh2sO9KNva | XIXIXIXIXIXI | 1 | 2026-06-12 09:35:13 | 0 |
| 4 | 2 | Loey Park | 0920220056 | L | 0920220056 | loeyp@mail.com | $2y$10$36hX4L31BFcqgqzWJX.9AOJMirgxIpr7VcGK0Y85XCqf4KwZAtlw6 | ejip | 1 | 2026-06-17 10:35:58 | 0 |
| 7 | 4 | Mingoo Kim | 0920220055 | P | 0983746253678 | mingoo@mail.com | $2y$10$bDltV2MJWPfDDVZlumP30evQ6OY9CCdGaKRV.tGDz5SkF1FhBMCJi | Jl. Gaharu | 1 | 2026-06-17 07:45:51 | 0 |
| 8 | 4 | Nodshey Yu | 0920250090 | P | 987654345678 | ho@mail.com | $2y$10$0NtE9IT5ktnw60GwTqHHyOGn7NgSf9.cwd8YFXFDnOxoHaBgsALGa | hhhh | 1 | 2026-06-23 13:17:54 | 0 |
| 9 | 4 | Yehs Park | 0920220052 | P | 098765456789 | ko@mail.com | $2y$10$v08UBxfw8t01GEzjQklu3etBZHTPKPqrTLFjR43ECOuLwr/rwUfhW | wertyuihgfdfghjk | 1 | 2026-06-23 13:18:14 | 0 |
| 10 | 5 | aleyey | 0920250029 | P | 085888267092 | gitaaleyda@gmail.com | $2y$10$cQ2rzl14UfASk/STEKthOeWK.NUmw5xaM08E4z/9aSHGgSSpF9oU2 | tangerang anti dajjal | 1 | 2026-06-23 07:29:24 | 0 |
| 11 | 8 | yaya | 0920250041 | P | 0957578657893 | yaya@mail.com | $2y$10$Twn.IKLZ0Q26vhFBe5oCTecA49DJoCqKO11AmQzZ.K3ByuW0yXbNG | Jl. Melati 2 | 1 | 2026-06-23 11:38:15 | 0 |

---

## petugas

**Rows:** 11

| PetugasID | NamaPetugas | Email | Password | Jabatan | NoHP | UpdatedAt | IsDeleted |
| --- | --- | --- | --- | --- | --- | --- | --- |
| 1 | Ganjar Pranowo | ganjar@mail.com | $2y$12$PO6v.sDCVxmEmVFubiB6N.mWVbioOObVEtivTZw2FafpgBu0SKTmm | PENGURUS | 086942069126 | 2026-06-09 10:53:34 | 0 |
| 2 | John Pork | john@mail.com | $2y$12$Q9evzz0VFtW0/cuCj6UnwO1NY4UjA1MqCycRIfCu/DjquLxgSR3Qe | SIGAP | 082134572356 | 2026-06-11 21:09:26 | 0 |
| 3 | Rayyan Abdurrahman Qadar | unarayyan10@gmail.com | $2y$12$vE1bg8cc1HNF3aTg6lQCtujymP9MVz7ETeEN8cTHLj2H8f.0UG5/y | PENGURUS | 081210822482 | 2026-06-12 13:16:26 | 1 |
| 4 | Al-Azhar | azhar@mail.com | $2y$10$keRlFX/hZNiPoDQ.Th4eYuw090uDd6Jdp2TMpAWyqYYGYFeMx2piu | SERVANDA | 08654689035 | 2026-06-12 13:52:21 | 0 |
| 5 | Rapip Piscok | apip@mail.com | $2y$12$wF1cGmnY0Eb/89DazoPYwu2igLukY.yF8Xz24LAK67kNT6PI0Np/G | MAINTENANCE | 086942069123 | 2026-06-12 14:03:36 | 0 |
| 6 | Anies Baswedan | anies@mail.com | $2y$12$1WRTlUOZ.s09zjnBwzgoFuZ9Cot8MACbhaQ7qyi9MaLl450bqetjS | SIGAP | 086735348459 | 2026-06-12 14:05:01 | 0 |
| 7 | wowo | wowo@mail.com | $2y$10$CEiPkNgvgcO8zhUjXheuKOPMu8rVESx/OziogQb4DRq/5VGASlJem | SIGAP | 098262773173789 | 2026-06-17 09:21:55 | 1 |
| 8 | prabski kuy | bunted88@mail.com | $2y$10$LxARY4zzqEFvgn7dEKpiLeuxWdb0oMT.6i70g0buFoxl/CAA/X5Zi | SIGAP | 087654356788 | 2026-06-17 10:12:39 | 1 |
| 9 | Prabski | prab@mail.com | $2y$10$2KAZyOy3JgiT6gD4J1CRtOpwJOxugYiVfyNrUQSGK2l4e55inmsi2 | SERVANDA | 098765434567 | 2026-06-19 14:23:59 | 0 |
| 10 | Megawati | meg@mail.com | $2y$10$5UUIrDpy3lnyd5JPmuG7E.J/L79SWhOEUVOysQDsPmXNITs5FUXRq | PENGURUS | 08764678905 | 2026-06-23 12:03:42 | 0 |
| 11 | Pol Pot | polpot@mail.com | $2y$10$lilLb5siKW//p9m6/h673OxIa0C1GTXpYWMvSP9kbRIQxI7QuMMBK | MAINTENANCE | 089122114114 | 2026-06-24 18:11:03 | 0 |

---

## ruangan

**Rows:** 12

| RuanganID | NamaRuangan | JenisRuangan | Keterangan | UpdatedAt | IsDeleted | Lantai |
| --- | --- | --- | --- | --- | --- | --- |
| 1 | Dapur Lantai 7 | Dapur | Dapur di Lantai 7 | 2026-06-09 02:52:58 | 1 | 7 |
| 2 | Dapur Lantai 7 | Dapur | Dapur di Lantai 7 | 2026-06-09 02:53:11 | 1 | 7 |
| 3 | Dapur Lantai 7 | Dapur | hbsdhbsd | 2026-06-09 02:53:46 | 1 | 7 |
| 4 | Dapur Lantai 7 | Skibidi | d | 2026-06-09 02:54:19 | 1 | 7 |
| 5 | Dapur MBG | Dapur | Terletak di Lantai 7 | 2026-06-09 03:03:37 | 1 | 7 |
| 6 | Musholla | Tempat Ibadah | Terletak di dekat lift | 2026-06-25 08:56:44 | 0 | 7 |
| 7 | Kamar Mandi Pria | Kamar Mandi | Terletak di samping musholla | 2026-06-25 08:56:33 | 0 | 7 |
| 8 | Kamar Mandi Wanita | Kamar Mandi | Terletak di depan lorong dispenser air | 2026-06-25 08:57:00 | 0 | 7 |
| 9 | rtyuicvbnm | rghjnmk | bhnjmkn | 2026-06-19 10:29:23 | 1 | 3 |
| 10 | Lapangan Basket | Lapangan Olahraga | Terletak di atas Aula | 2026-06-25 08:19:15 | 0 | 1 |
| 11 | Lapangan Badminton | Lapangan Olahraga | Terletak di area tengah | 2026-06-25 08:55:39 | 0 | 1 |
| 12 | Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore | Kamar Mandi | Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore … | 2026-06-25 10:13:32 | 0 | 2 |

---

