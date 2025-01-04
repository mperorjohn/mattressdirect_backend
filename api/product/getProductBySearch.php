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
    // Get the search term from query parameter
    if (!isset($_GET['search']) || empty($_GET['search'])) {
        echo json_encode(array(
            "status" => false, 
            "message" => "Missing query parameter: search"
        ));
        exit;
    }

    $search = '%' . $_GET['search'] . '%';

    // Query to search products by any field
    $sql = "SELECT products.*, product_sizes.price AS product_price 
    FROM products 
    LEFT JOIN product_sizes 
    ON products.id = product_sizes.product_id 
    AND product_sizes.is_default = 1 
    AND product_sizes.is_available = 1
    WHERE products.product_name LIKE :search 
    OR products.product_description LIKE :search 
    OR products.product_category LIKE :search
    OR products.product_brand_name LIKE :search LIMIT 20" 
    ;

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':search', $search);
    $stmt->execute();

    // Fetch products matching the search term
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($products) {
        // Return products in JSON format
        echo json_encode(array(
            "status" => true,
            "message" => count($products) . " products found",
            "data" => $products
        ));
    } else {
        echo json_encode(array(
            "status" => false,
            "message" => "No products found"
        ));
    }
} else {
    echo json_encode(array(
        "status" => false,
        "message" => "Method not allowed"
    ));
}
?>