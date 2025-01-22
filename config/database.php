<?php

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

class DatabaseService {
    private $conn;

    public function getConnection($db_host, $db_name, $db_user, $db_password) {
        if (!$db_host || !$db_name || !$db_user || !$db_password) {
            die(json_encode(array("status" => "error", "message" => "Database configuration variables are not set")));
        }

        try {
            $dsn = "mysql:host=$db_host;dbname=$db_name";
            $options = array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            );
            $this->conn = new PDO($dsn, $db_user, $db_password, $options);
        } catch(PDOException $e) {
            error_log("Connection failed: " . $e->getMessage());
            die(json_encode(array("status" => "error", "message" => "Database connection failed: " . $e->getMessage())));
        }

        return $this->conn;
    }
}

$db_host = $_ENV['DB_HOST'] ?? null;
$db_name = $_ENV['DB_DATABASE'] ?? null;
$db_user = $_ENV['DB_USERNAME'] ?? null;
$db_password = $_ENV['DB_PASSWORD'] ?? null;

if (!$db_host || !$db_name || !$db_user || !$db_password) {
    die(json_encode(array("status" => "error", "message" => "One or more environment variables are not set")));
}

// Debugging: Log the environment variables to ensure they are loaded correctly
// error_log("DB_HOST: " . $db_host);
// error_log("DB_DATABASE: " . $db_name);
// error_log("DB_USERNAME: " . $db_user);
// error_log("DB_PASSWORD: " . $db_password);

$databaseService = new DatabaseService();
$conn = $databaseService->getConnection($db_host, $db_name, $db_user, $db_password);



// echo json_encode([
//     'DB_HOST' => $_ENV['DB_HOST'],
//     'DB_DATABASE' => $_ENV['DB_DATABASE'],
//     'DB_USERNAME' => $_ENV['DB_USERNAME'],
//     'DB_PASSWORD' => $_ENV['DB_PASSWORD']
// ]);
?>