<?php
class DB {
    public static function connect() {
        try {
            return new PDO("mysql:host=localhost;dbname=hostel", "root", "");
        } catch (PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    }
}
?>
<?php
class DB {
    public static function connect() {
        try {
            return new PDO("mysql:host=localhost;dbname=hostel", "root", "");
        } catch (PDOException $e) {
            die("Error: " . $e->getMessage());
        }
    }
}
?>