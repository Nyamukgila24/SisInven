<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'sisinven');
define('DB_USER', 'root');
define('DB_PASS', ''); 

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
            $pdo->exec("SET time_zone = '+07:00'");
        } catch (PDOException $e) {
            die('Koneksi database gagal. Periksa file config/database.php. Detail: ' . $e->getMessage());
        }
    }

    return $pdo;
}
