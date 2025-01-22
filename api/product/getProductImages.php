<?php
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable direct display of errors
header("Content-Type: application/json");

include_once '../../config/database.php';

try {
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
        throw new Exception("Connection failed");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Check if product_id is set
        if (!isset($_GET['id'])) {
            throw new Exception("Product ID is required");
        }

        $product_id = $_GET['id']; // Corrected to match the check above

        // Fetch additional images from the database
        $sql = "SELECT image_path FROM product_images WHERE product_id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $product_id);
        $stmt->execute();

        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(array(
            "status" => true,
            "data" => $images
        ));
    } else {
        throw new Exception("Invalid request method");
    }
} catch (Exception $e) {
    echo json_encode(array(
        "status" => false,
        "message" => $e->getMessage()
    ));
}