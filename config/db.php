<?php
class DB {
    private static $pdo = null;

    public static function connect() {
        if (self::$pdo === null) {
            $host = 'localhost';
            $dbname = 'hostel_db';
            $username = 'root';
            $password = ''; // your password

            try {
                self::$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}

// Create global $pdo variable
$pdo = DB::connect();
?>