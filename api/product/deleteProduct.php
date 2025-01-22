<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");

include_once '../../config/database.php';

// Database connection
$databaseService = new DatabaseService();
$conn = $databaseService->getConnection(
    $_ENV['DB_HOST'],
    $_ENV['DB_DATABASE'],
    $_ENV['DB_USERNAME'],
    $_ENV['DB_PASSWORD']
);

// Check connection
if ($conn === null) {
    die(json_encode(array("status" => "error", "message" => "Connection failed")));
}

// Check if the request method is DELETE
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Get the id from query parameter
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        echo json_encode(array(
            "status" => false, 
            "message" => "Missing query parameter: id"
        ));
        exit;
    }

    $id = $_GET['id'];

    // Check if product still exists
    $sql = "SELECT * FROM products WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode(array(
            "status" => false,
            "message" => "Product not found"
        ));
        exit;
    }

    try {
        // Begin transaction
        $conn->beginTransaction();

        // Delete from product_sizes
        $sql2 = "DELETE FROM product_sizes WHERE product_id = :id";
        $stmt = $conn->prepare($sql2);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        // Delete from product_images
        $sql3 = "DELETE FROM product_images WHERE product_id = :id";
        $stmt = $conn->prepare($sql3);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        // Delete from products
        $sql = "DELETE FROM products WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        // Commit transaction
        $conn->commit();

        echo json_encode(array(
            "status" => true,
            "message" => "Product deleted successfully",
        ));
    } catch (Exception $e) {
        // Rollback transaction if something failed
        $conn->rollBack();
        error_log("Transaction failed: " . $e->getMessage());
        echo json_encode(array(
            "status" => false,
            "message" => "Failed to delete product: " . $e->getMessage()
        ));
    }
} else {
    echo json_encode(array(
        "status" => false,
        "message" => "Method not allowed"
    ));
}
?>