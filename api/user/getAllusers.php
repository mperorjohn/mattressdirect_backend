<?php
session_start();
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
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(array(
        "status" => false, 
        "message" => "Method not allowed"));
    exit();
}

// Fetch all users
$sql = "SELECT * FROM users";
$stmt = $conn->prepare($sql);
$stmt->execute();

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Remove passwords from the results
foreach ($results as &$user) {
    unset($user['password']);
}

if (count($results) > 0) {
    // Store results in session
    $_SESSION['users'] = $results;

    echo json_encode(array(
        "status" => true, 
        "data" => $results), 200);
} else {
    echo json_encode(array(
        "status" => false,
        "message" => "No users found"), 404);
}

$conn = null;
?>