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

// create size
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $size = $data['size'] ?? '';
    $price = $data['price'] ?? '';
    $product_id = $data['product_id'] ?? '';
    $is_default = $data['is_default'] ?? 0;
    $is_available = $data['is_available'] ?? 1;

    if (empty($size) || empty($price) || empty($product_id)) {
        echo json_encode(array(
            "status" => false,
            "message" => "Size, price, and product_id are required"
        ));
        exit();
    }

    // check if there is any size with the product_id and has default as true
    $sql = "SELECT * FROM product_sizes WHERE product_id = :product_id AND is_default = 1";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->execute();
    $defaultSize = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($is_default == 1 && $defaultSize) {
        echo json_encode(array(
            "status" => false,
            "message" => "Default size already exists"
        ));
        exit();
    }

    $sql = "INSERT INTO product_sizes (size, price, product_id, is_default, is_available) VALUES (:size, :price, :product_id, :is_default, :is_available)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':size', $size);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->bindParam(':is_default', $is_default);
    $stmt->bindParam(':is_available', $is_available);
    $stmt->execute();

    echo json_encode(array(
        "status" => true,
        "message" => "Size created successfully"
    ));
} else {
    echo json_encode(array(
        "status" => false,
        "message" => "Method not allowed"
    ));
}
?>