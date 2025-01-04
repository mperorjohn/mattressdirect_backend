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


// get size by product id 

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $product_id = $_GET['product_id'] ?? '';

    if (empty($product_id)) {
        echo json_encode(array(
            "status" => false,
            "message" => "Product ID is required"
        ));
        exit();
    }

    $sql = "SELECT * FROM product_sizes WHERE product_id = :product_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->execute();
    $sizes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(array(
        "status" => true,
        "data" => $sizes
    ));
} else {
    echo json_encode(array(
        "status" => false,
        "message" => "Method not allowed"
    ));
}