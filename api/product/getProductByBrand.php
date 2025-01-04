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

    $brand_name = $_GET['brand_name'] ?? '';
    $category = $_GET['category'] ?? '';


    if(empty($brand_name && $category)) {
        echo json_encode(array(
            "status" => false,
            "message" => "Brand name and category is required"
        ));
        exit();
    }

    // get all from products where brand_name = product_brand_name 
    $sql = "SELECT products.*, product_sizes.price AS product_price 
    FROM products 
    LEFT JOIN product_sizes 
    ON products.id = product_sizes.product_id 
    AND product_sizes.is_default = 1 
    AND product_sizes.is_available = 1
    WHERE products.product_brand_name = :brand_name 
    AND products.product_category = :category";


    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':brand_name', $brand_name);
    $stmt->bindParam(':category', $category);
    $stmt->execute();

    // Fetch products
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if(count($products) == 0) {
        echo json_encode(array(
            "status" => false,
            "message" => "No products found for this brand"
        ));
        exit();
    }else{
        echo json_encode(array(
            "status" => true,
            "message" => "Products found for this brand",
            'product_count' => count($products),
            "data" => $products
        ), 200);
        exit();
    }

} else {
    echo json_encode(array(
        "status" => false,
        "message" => "Method not allowed"
    ));
}
?>