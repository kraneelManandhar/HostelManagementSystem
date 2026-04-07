<?php
/**
 * db.php
 * Secure Database Connection using PDO
 */

$host = 'localhost';
$db   = 'HostelManagement';
$user = 'root';
$pass = ''; // Default XAMPP password is empty
$charset = 'utf8mb4';

// The Data Source Name (DSN)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Configuration options for PDO
$options = [
    // Throw exceptions on SQL errors (Crucial for debugging)
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    // Return data as associative arrays (e.g., $row['full_name'])
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // Disable emulation of prepared statements for better security
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    /**
     * CRITICAL FIX: We pass $options as the 4th argument.
     * Without this, your ERRMODE_EXCEPTION and FETCH_ASSOC settings 
     * from above will be ignored.
     */
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // echo "Connected successfully!"; // Uncomment only for testing
} catch (\PDOException $e) {
    // If connection fails, stop the script and show the error
    // In a live site, you would log this to a file instead of echoing it
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

/**
 * BASEURL Constant
 * Helps with absolute paths for CSS/Images across different MVC folders
 */
define('BASE_URL', 'http://localhost/HOSTELMANAGEMENTSYSTEM/');
?>