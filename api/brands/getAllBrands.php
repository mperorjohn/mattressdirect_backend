<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");

include_once '../../config/database.php';

// Database connection
$databaseService = new DatabaseService();
$conn = $databaseService->getConnection(
    $_ENV['DB_HOST'],
    $_ENV['DB_NAME'],
    $_ENV['DB_USER'],
    $_ENV['DB_PASSWORD']
);

// Check connection
if ($conn === null) {
    die(json_encode(array("status" => "error", "message" => "Connection failed")));
}

// Check if the request method is GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Query to select the 12 most recently created products
    $sql = "SELECT * FROM brands LIMIT 12";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    // Fetch products
    $brands = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return products in JSON format
    echo json_encode(array(
        "status" => true,
        "data" => $brands
    ));
} else {
    echo json_encode(array(
        "status" => false,
        "message" => "Method not allowed"
    ));
}
?>