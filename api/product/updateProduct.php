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

// Check if the request method is PUT
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Get the id from query parameter
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        echo json_encode(array(
            "status" => false, 
            "message" => "Missing query parameter: id"
        ));
        exit;
    }

    $id = $_GET['id'];

    // Check if the product exists
    $checkSql = "SELECT COUNT(*) FROM products WHERE id = :id";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bindParam(':id', $id);
    $checkStmt->execute();
    $productExists = $checkStmt->fetchColumn();

    if (!$productExists) {
        echo json_encode(array(
            "status" => false, 
            "message" => "Product not found"
        ));
        exit;
    }

    // Get posted data
    $data = json_decode(file_get_contents("php://input"));

    // Validate product_price
    if (isset($data->product_price) && !is_numeric($data->product_price)) {
        echo json_encode(array(
            "status" => false, 
            "message" => "Invalid value for product_price"
        ));
        exit;
    }

    // Set product property values
    $fields = array(
        'product_name' => $data->product_name ?? null,
        'product_description' => $data->product_description ?? null,
        'product_price' => $data->product_price ?? null,
        'product_category' => $data->product_category ?? null,
        'product_image' => $data->product_image ?? null
    );

    // Build the SQL query dynamically
    $set_clause = [];
    foreach ($fields as $field => $value) {
        if ($value !== null) {
            $set_clause[] = "$field = :$field";
        }
    }

    if (empty($set_clause)) {
        echo json_encode(array(
            "status" => false, 
            "message" => "No fields to update"
        ));
        exit;
    }

    $sql = "UPDATE products SET " . implode(', ', $set_clause) . " WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id);
    foreach ($fields as $field => $value) {
        if ($value !== null) {
            $stmt->bindValue(":$field", $value);
        }
    }

    try {
        if ($stmt->execute()) {
            echo json_encode(array(
                "status" => true, 
                "message" => "Product updated successfully"
            ));
        } else {
            echo json_encode(array(
                "status" => false, 
                "message" => "Product not updated, try again"
            ));
        }
    } catch (PDOException $e) {
        echo json_encode(array(
            "status" => false, 
            "message" => $e->getMessage()
        ));
    }
} else {
    echo json_encode(array(
        "status" => false, 
        "message" => "Method not allowed"
    ));
}
?>