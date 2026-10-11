CREATE DATABASE IF NOT EXISTS perpustakaan_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE perpustakaan_db;

CREATE TABLE buku_tamu (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nama          VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL,
    pesan         TEXT         NOT NULL,
    tanggal_kirim DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_tanggal_kirim (tanggal_kirim)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;