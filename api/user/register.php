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

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get posted data
    $data = json_decode(file_get_contents("php://input"));

    // Check if data is not empty
    $required_fields = array(
        'first_name', 'last_name', 'email', 'phone' ,
         'password', 'country', 'state', 'city', 
        'postal_code', 'address'
    );

    $missing_fields = array();
    foreach ($required_fields as $field) {
        if (empty($data->$field)) {
            $missing_fields[] = $field;
        }
    }

    if (empty($missing_fields)) {
        // Set user property values
        $first_name = $data->first_name;
        $last_name = $data->last_name;
        $email = $data->email;
        $phone = $data->phone;
        $company_name = $data->company_name;
        $company_phone = $data->company_phone;
        $password = password_hash($data->password, PASSWORD_BCRYPT);
        $country = $data->country;
        $state = $data->state;
        $city = $data->city;
        $postal_code = $data->postal_code;
        $address = $data->address;
        $created_at = date('Y-m-d H:i:s');

        // Check if user exists
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            echo json_encode(array("status" => false, "message" => "User already exists"));
            exit();
        }

        // Create new user
        $sql = "INSERT INTO users (first_name, last_name, email, phone, company_name, company_phone, password, country, state, city, postal_code, address, created_at) VALUES (:first_name, :last_name, :email, :phone, :company_name, :company_phone, :password, :country, :state, :city, :postal_code, :address, :created_at)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':company_name', $company_name);
        $stmt->bindParam(':company_phone', $company_phone);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':country', $country);
        $stmt->bindParam(':state', $state);
        $stmt->bindParam(':city', $city);
        $stmt->bindParam(':postal_code', $postal_code);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':created_at', $created_at);

        if ($stmt->execute()) {
            echo json_encode(array(
                "status" => true, 
                "message" => "User created successfully"), 201);
        } else {
            echo json_encode(array(
                "status" => false, 
                "message" => "User could not be created"), 500);
        }
    } else {
        echo json_encode(array(
            "status" => false, 
            "message" => "Data is incomplete. Missing fields: " . implode(', ', $missing_fields)), 400);
    }
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(array(
        "status" => false,
        "message" => "Method not allowed"
    ));
    exit();
}
?>