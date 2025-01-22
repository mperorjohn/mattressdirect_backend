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

//check if the request method is GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Query to select all messages
    $sql = "SELECT * FROM contact_messages ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    // Fetch messages
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return messages in JSON format
    echo json_encode(array(
        "status" => true,
        "data" => $messages
    ));
} else {
    echo json_encode(array(
        "status" => false,
        "message" => "Method not allowed"
    ));
}
?>
