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


// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get posted data
    $data = json_decode(file_get_contents("php://input"));

    // Check if data is not empty
    $required_fields = array(
        'product_name', 'product_description', 'product_category', 'product_image','product_brand_name','is_available'
    );

    $missing_fields = array();
    foreach ($required_fields as $field) {
        if (empty($data->$field)) {
            $missing_fields[] = $field;
        }
    }

    if (empty($missing_fields)) {
        // Set product property values
        $product_name = strtolower($data->product_name);
        $product_brand_name = $data->product_brand_name;
        $product_description = $data->product_description;
        $product_category = $data->product_category;
        $product_image = $data->product_image;
        $is_available = $data->is_available;
        $created_at = date('Y-m-d H:i:s');

        

        // Insert product
        $sql = "INSERT INTO products (product_name, product_brand_name, product_description,  product_category, product_image, is_available,  created_at) 
                VALUES (:product_name, :product_brand_name, :product_description, :product_category, :product_image, :is_available, :created_at)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':product_name', $product_name);
        $stmt->bindParam(':product_brand_name', $product_brand_name);
        $stmt->bindParam(':product_description', $product_description);
        $stmt->bindParam(':product_category', $product_category);
        $stmt->bindParam(':product_image', $product_image);
        $stmt->bindParam(':is_available', $is_available);
        $stmt->bindParam(':created_at', $created_at);

        if ($stmt->execute()) {
            echo json_encode(array(
                "status" => true, 
                "message" => "Product created successfully"
            ));
        } else {
            echo json_encode(array(
                "status" => false, 
                "message" => "Product not created, try again"
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