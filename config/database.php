<?php
/**
 * =====================================================================
 * KONFIGURASI KONEKSI DATABASE
 * =====================================================================
 * Ini SATU-SATUNYA file yang perlu diubah jika pengaturan database
 * di server/komputer Anda berbeda (misalnya password MySQL diubah,
 * atau nama database diganti).
 *
 * Jika ada error "Koneksi database gagal", cek 4 baris di bawah ini.
 * =====================================================================
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'sisinven');
define('DB_USER', 'root');
define('DB_PASS', '');      // Default XAMPP: password kosong

/**
 * Membuat dan mengembalikan koneksi PDO ke database.
 * Dipakai oleh semua halaman lewat: $pdo = getKoneksi();
 */
function getKoneksi() {
    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die('Koneksi database gagal. Periksa file config/database.php. Detail: ' . $e->getMessage());
        }
    }

    return $pdo;
}
