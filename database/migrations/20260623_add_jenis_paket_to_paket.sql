ALTER TABLE paket
ADD COLUMN JenisPaket ENUM('Paket', 'Dokumen') NOT NULL DEFAULT 'Paket' AFTER Kurir;
