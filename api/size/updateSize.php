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

// update size

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $size = $_POST['size'] ?? '';
    $price = $_POST['price'] ?? '';
    $product_id = $_POST['product_id'] ?? '';
    $is_default = $_POST['is_default'] ?? 0;
    $is_available = $_POST['is_available'] ?? 1;

    // check if product_id is empty and exist in products table
    $sql = "SELECT * FROM products WHERE id = :product_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode(array(
            "status" => false,
            "message" => "Product not found"
        ));
        exit();
    }

    if (empty($size) || empty($price) || empty($product_id)) {
        echo json_encode(array(
            "status" => false,
            "message" => "Size, price, and product_id are required"
        ));
        exit();
    }

    $sql = "UPDATE product_sizes SET size = :size, price = :price, product_id = :product_id, is_default = :is_default, is_available = :is_available WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':size', $size);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->bindParam(':is_default', $is_default);
    $stmt->bindParam(':is_available', $is_available);
    $stmt->bindParam(':id', $_POST['id']);
    $stmt->execute();

    echo json_encode(array(
        "status" => true,
        "message" => "Size updated successfully"
    ));
} else {
    echo json_encode(array(
        "status" => false,
        "message" => "Method not allowed"
    ));
}

