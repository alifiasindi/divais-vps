-- FILE 1: Query SQL (Persiapan Database)
-- Buat tabel baru untuk menampung tap kartu sementara dari ESP32.
--
-- Catatan: Pastikan engine tabel konsisten (InnoDB) agar FK berfungsi.

CREATE TABLE IF NOT EXISTS temp_rfid (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_rfid VARCHAR(50) NOT NULL,
    waktu TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Index untuk performa polling (opsional tapi direkomendasikan)
CREATE INDEX IF NOT EXISTS idx_temp_rfid_waktu ON temp_rfid (waktu);
CREATE INDEX IF NOT EXISTS idx_temp_rfid_id_rfid ON temp_rfid (id_rfid);

