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
    // Set default category to 'mattress' if not provided
    $category = isset($_GET['category']) && !empty($_GET['category']) ? $_GET['category'] : 'mattress';

    // Query to select products by category
    $sql = "SELECT products.*, product_sizes.price AS product_price 
    FROM products 
    LEFT JOIN product_sizes 
    ON products.id = product_sizes.product_id 
    AND product_sizes.is_default = 1 
    AND product_sizes.is_available = 1
    WHERE products.product_category = :category";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':category', $category);
    $stmt->execute();

    // Fetch products by category
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return products in JSON format
    echo json_encode(array(
        "status" => true,
        "data" => $products
    ));
} else {
    echo json_encode(array(
        "status" => false,
        "message" => "Method not allowed"
    ));
}
?>