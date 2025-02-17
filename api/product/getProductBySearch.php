<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");

include_once '../../config/database.php';

// Database connection
global $conn; // Assuming $conn is defined in the included database.php

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
    WHERE products.product_name LIKE :search1 
    OR products.product_description LIKE :search2 
    OR products.product_category LIKE :search3
    OR products.product_brand_name LIKE :search4 LIMIT 20";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':search1', $search);
    $stmt->bindParam(':search2', $search);
    $stmt->bindParam(':search3', $search);
    $stmt->bindParam(':search4', $search);
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
