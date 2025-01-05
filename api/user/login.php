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

// create a login api requesting username and password and return respective error and success messages
// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get posted data
    $data = json_decode(file_get_contents("php://input"));

    // Check if data is not empty
    $required_fields = array(
        'email', 'password'
    );

    $missing_fields = array();
    foreach ($required_fields as $field) {
        if (empty($data->$field)) {
            $missing_fields[] = $field;
        }
    }

    if (empty($missing_fields)) {
        // Set user property values
        $email = $data->email;
        $password = $data->password;

        // Check if user exists
        $sql = "SELECT id, first_name, last_name,permission, email, phone, company_name, company_phone, country, state, city, postal_code, address, created_at, password FROM users WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (password_verify($password, $user['password'])) {
                unset($user['password']); // Remove Password from the returned data
                echo json_encode(array(
                    "status" => true,
                    "message" => "Login successful",
                    "data" => $user
                ));
            } else {
                echo json_encode(array(
                    "status" => false,
                    "message" => "Invalid password"
                ));
            }
        } else {
            echo json_encode(array(
                "status" => false,
                "message" => "User not found"
            ));
        }
    } else {
        echo json_encode(array(
            "status" => false,
            "message" => "Missing fields: " . implode(', ', $missing_fields)
        ));
    }
} else {
    echo json_encode(array(
        "status" => false,
        "message" => "Method not allowed"
    ));
}
