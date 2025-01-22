<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");

require_once '../../config/database.php';

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
    // Validate if a file was uploaded
    if (isset($_FILES['brand_image']) && $_FILES['brand_image']['error'] === UPLOAD_ERR_OK) {
        // Sanitize input
        $brand_name = filter_var($_POST['brand_name'], FILTER_SANITIZE_STRING);
        $brand_image = $_FILES['brand_image'];

        // Validate file extension and MIME type
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
        $file_extension = strtolower(pathinfo($brand_image['name'], PATHINFO_EXTENSION));
        $file_mime = mime_content_type($brand_image['tmp_name']);
        $allowed_mime_types = array('image/jpeg', 'image/png', 'image/gif');

        if (!in_array($file_extension, $allowed_extensions) || !in_array($file_mime, $allowed_mime_types)) {
            echo json_encode(array(
                "status" => false,
                "message" => "Invalid file. Only JPG, JPEG, PNG, and GIF images are allowed."
            ));
            exit();
        }

        // Define the upload directory and base URL
        $upload_dir = '/var/www/html/mattress/mattressdirect_backend/uploads/';
        $base_url = "http://localhost/mattress/mattressdirect_backend/uploads/";

        // Create the upload directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Generate a unique name for the uploaded file
        $unique_name = uniqid() . '-' . preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', basename($brand_image['name']));
        $image_path = $upload_dir . $unique_name;
        $image_url = $base_url . $unique_name; // Construct the full image URL

        // Move the uploaded file to the upload directory
        if (move_uploaded_file($brand_image['tmp_name'], $image_path)) {
            // Insert the brand details into the database
            $sql = "INSERT INTO brands (brand_name, brand_image) VALUES (:brand_name, :brand_image)";
            $stmt = $conn->prepare($sql);

            // Bind parameters
            $stmt->bindParam(':brand_name', $brand_name);
            $stmt->bindParam(':brand_image', $image_url);

            // Execute the query
            if ($stmt->execute()) {
                echo json_encode(array(
                    "status" => true,
                    "message" => "Brand created successfully",
                    "data" => array(
                        "brand_name" => $brand_name,
                        "brand_image_url" => $image_url
                    )
                ));
            } else {
                echo json_encode(array(
                    "status" => false,
                    "message" => "Failed to create brand"
                ));
            }
        } else {
            echo json_encode(array(
                "status" => false,
                "message" => "Failed to upload image"
            ));
        }
    } else {
        echo json_encode(array(
            "status" => false,
            "message" => "No image uploaded or upload error"
        ));
    }
} else {
    echo json_encode(array(
        "status" => false,
        "message" => "Method not allowed"
    ));
}
?>
