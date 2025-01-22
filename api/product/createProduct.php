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

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Start transaction
        $conn->beginTransaction();

        // Check if required fields are set
        $product_name = $_POST['product_name'];
        $product_brand_name = $_POST['product_brand_name'];
        $product_description = $_POST['description'];
        $product_category = $_POST['product_category'];
        $product_available = $_POST['product_available'];

        // Sanitize product name: Replace spaces and unwanted characters
        $sanitized_product_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $product_name);

        // Define the base URL for images
        $base_url = "http://localhost/mattress/mattressdirect_backend/uploads/"; // The publicly accessible URL
        $upload_dir = '/var/www/html/mattress/mattressdirect_backend/uploads/';
        $product_image_url = '';
        $additional_images_urls = [];

        // Main Product Image
        if (isset($_FILES['product_image_one']) && $_FILES['product_image_one']['error'] == 0) {
            // Generate a unique name for the main product image
            $file_extension = pathinfo($_FILES['product_image_one']['name'], PATHINFO_EXTENSION);
            $new_file_name = $sanitized_product_name . "_main_" . date('Ymd_His') . "." . $file_extension;
            $relative_path = $new_file_name; // Relative path for the URL
            $product_image_url = $base_url . $relative_path; // Full URL for the database

            if (!move_uploaded_file($_FILES['product_image_one']['tmp_name'], $upload_dir . $relative_path)) {
                throw new Exception("Failed to move uploaded file for main product image");
            }
        } else {
            throw new Exception("No main product image uploaded or invalid file");
        }

        // Additional Product Images (Optional)
        for ($i = 2; $i <= 4; $i++) {
            $image_field = 'product_image_' . $i;  // product_image_2, product_image_3, product_image_4
            if (isset($_FILES[$image_field]) && $_FILES[$image_field]['error'] == 0) {
                // Validate the image (type and size)
                $allowed_extensions = ['jpg', 'jpeg', 'png'];
                $file_extension = pathinfo($_FILES[$image_field]['name'], PATHINFO_EXTENSION);
                $file_size = $_FILES[$image_field]['size'];

                if (!in_array($file_extension, $allowed_extensions)) {
                    throw new Exception("Only JPG, JPEG, PNG files are allowed for image $i");
                }

                if ($file_size > 2097152) {
                    throw new Exception("File size should not exceed 2MB for image $i");
                }

                // Generate a unique name for the additional product image
                $new_file_name = $sanitized_product_name . "_additional_" . $i . "_" . date('Ymd_His') . "." . $file_extension;
                $relative_path = $new_file_name; // Relative path for the URL
                $image_url = $base_url . $relative_path; // Full URL for the database

                // Move the file to the upload directory
                if (!move_uploaded_file($_FILES[$image_field]['tmp_name'], $upload_dir . $relative_path)) {
                    throw new Exception("Failed to move uploaded file for additional product image $i");
                }

                // Add the image URL to the list of additional images
                $additional_images_urls[] = $image_url;
            }
        }

        // Insert product details into the database
        $created_at = date('Y-m-d H:i:s');
        $sql = "INSERT INTO products (product_name, product_brand_name, product_description, product_category, product_image, is_available, created_at) 
                VALUES (:product_name, :product_brand_name, :product_description, :product_category, :product_image, :is_available, :created_at)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':product_name', $product_name);
        $stmt->bindParam(':product_brand_name', $product_brand_name);
        $stmt->bindParam(':product_description', $product_description);
        $stmt->bindParam(':product_category', $product_category);
        $stmt->bindParam(':product_image', $product_image_url);  // Store the readable URL
        $stmt->bindParam(':is_available', $product_available);
        $stmt->bindParam(':created_at', $created_at);

        if ($stmt->execute()) {
            // Save main product image in the database
            $product_id = $conn->lastInsertId();  // Get the last inserted product ID
            saveProductImage($product_id, $product_image_url, $conn);

            // Save additional images in the database
            foreach ($additional_images_urls as $image_url) {
                saveProductImage($product_id, $image_url, $conn);
            }

            // Commit transaction
            $conn->commit();

            echo json_encode(array(
                "status" => true,
                "message" => "Product created successfully"
            ));
        } else {
            // Rollback transaction if product creation fails
            $conn->rollBack();
            throw new Exception("Product not created, try again");
        }
    } else {
        throw new Exception("Invalid request method");
    }
} catch (Exception $e) {
    // Rollback transaction if any exception occurs
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(array(
        "status" => false,
        "message" => $e->getMessage()
    ));
}

function saveProductImage($product_id, $image_url, $conn) {
    try {
        // Prepare the insert query
        $sql = "INSERT INTO product_images (product_id, image_path) VALUES (:product_id, :image_url)";
        $stmt = $conn->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':image_url', $image_url);

        // Execute and check for errors
        if (!$stmt->execute()) {
            $errorInfo = $stmt->errorInfo(); // Get detailed error information
            throw new Exception("Error saving product image: " . implode(", ", $errorInfo));
        }
    } catch (Exception $e) {
        throw new Exception("Error saving product image: " . $e->getMessage());
    }
}