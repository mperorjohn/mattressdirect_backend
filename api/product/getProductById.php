<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");

include_once '../../config/database.php';

// Database connection
$databaseService = new DatabaseService();
$conn = $databaseService->getConnection(
     $_ENV['DB_HOST'],
    $_ENV['DB_DATABASE'],  // Corrected from DB_NAME
    $_ENV['DB_USERNAME'],  // Corrected from DB_USER
    $_ENV['DB_PASSWORD']
);

// Check connection
if ($conn === null) {
    die(json_encode(array("status" => "error", "message" => "Connection failed")));
}

// Check if the request method is GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get the id from query parameter
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        echo json_encode(array(
            "status" => false, 
            "message" => "Missing query parameter: id"
        ));
        exit;
    }

    $id = $_GET['id'];

    // Query to select product by id
    $sql = "SELECT products.*, product_sizes.price AS product_price 
    FROM products 
    LEFT JOIN product_sizes 
    ON products.id = product_sizes.product_id 
    AND product_sizes.is_default = 1 
    AND product_sizes.is_available = 1
    WHERE products.id = :id";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    // Fetch product by id
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        // Return product in JSON format
        echo json_encode(array(
            "status" => true,
            "data" => $product
        ));
    } else {
        echo json_encode(array(
            "status" => false,
            "message" => "Product not found"
        ));
    }
} else {
    echo json_encode(array(
        "status" => false,
        "message" => "Method not allowed"
    ));
}
?>