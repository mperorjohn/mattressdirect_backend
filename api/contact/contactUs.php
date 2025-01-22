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

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get posted data
    $required_fields = array('first_name', 'last_name', 'email', 'message');
    $missing_fields = array();

    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $missing_fields[] = $field;
        }
    }

    if (empty($missing_fields)) {
        // Set contact property values
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email = $_POST['email'];
        $message = $_POST['message'];
        $created_at = date('Y-m-d H:i:s');

        // Insert contact
        $sql = "INSERT INTO contact_messages (first_name, last_name, email, message, created_at) 
                VALUES (:first_name, :last_name, :email, :message, :created_at)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':message', $message);
        $stmt->bindParam(':created_at', $created_at);

        if ($stmt->execute()) {
            echo json_encode(array(
                "status" => true, 
                "message" => "Message sent successfully"
            ));
        } else {
            echo json_encode(array(
                "status" => false, 
                "message" => "Message not sent, try again"
            ));
        }
    } else {
        echo json_encode(array(
            "status" => false,
            "message" => "Missing fields: " . implode(', ', $missing_fields),
            "submitted" => array_diff($required_fields, $missing_fields)
        ));
    }
} else {
    echo json_encode(array("status" => "error", "message" => "Method not allowed"));
}
?>
