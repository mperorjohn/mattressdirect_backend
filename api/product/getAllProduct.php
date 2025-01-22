<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");

include_once '../../config/database.php';


// Check connection
if ($conn === null) {
    die(json_encode(array("status" => "error", "message" => "Connection failed")));
}

// Check if the request method is GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Query to select all products and get default price from product_sizes table and join here as product_price
    $sql = "SELECT products.*, 
                   CASE 
                       WHEN product_sizes.price IS NOT NULL THEN product_sizes.price 
                       ELSE 'No default price available' 
                   END as product_price 
            FROM products 
            LEFT JOIN product_sizes ON products.id = product_sizes.product_id 
            AND product_sizes.is_default = 1 AND product_sizes.is_available = 1 ORDER BY products.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    // Fetch all products
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