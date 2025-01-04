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

// delete size
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';

    if (empty($id)) {
        echo json_encode(array(
            "status" => false,
            "message" => "ID is required"
        ));
        exit();
    }

    // check is size is not the default size befor deleting
    $sql = "SELECT * FROM product_sizes WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $size = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$size) {
        echo json_encode(array(
            "status" => false,
            "message" => "Size not found"
        ));
        exit();
    }

    if($size['is_default'] == 1) {
        echo json_encode(array(
            "status" => false,
            "message" => "Default size cannot be deleted"
        ));
        exit();
    }

    $sql = "DELETE FROM product_sizes WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    echo json_encode(array(
        "status" => true,
        "message" => "Size deleted successfully"
    ));
} else {
    echo json_encode(array(
        "status" => false,
        "message" => "Method not allowed"
    ));
}
