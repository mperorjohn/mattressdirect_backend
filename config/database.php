<?php

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// used to get mysql database connection
class DatabaseService {
    private $conn;

    public function getConnection($db_host, $db_name, $db_user, $db_password) {
        if (!$db_host || !$db_name || !$db_user || !$db_password) {
            die(json_encode(array("status" => "error", "message" => "Database configuration variables are not set")));
        }

        try {
            $dsn = "mysql:host=$db_host;dbname=$db_name";
            if ($db_host === 'localhost') {
                $dsn .= ";unix_socket=/var/run/mysqld/mysqld.sock"; // Adjust the socket path if necessary
            }
            $options = array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            );
            $this->conn = new PDO($dsn, $db_user, $db_password, $options);
        } catch(PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
            exit;
        }

        return $this->conn;
    }
}

?>