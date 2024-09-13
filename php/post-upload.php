<?php

header("Access-Control-Allow-Origin: *"); // Adjust as needed
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

include('db-connection.php'); // Adjust the path as necessary

// Define accepted image MIME types
$allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize input data
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : null;
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';

    // Ensure user_id and content are not empty
    if (!$user_id || empty($content)) {
        http_response_code(400); // Bad Request
        echo json_encode(["error" => "Invalid input data."]);
        exit();
    }

    // Insert the post into the `posts` table
    $stmt = $conn->prepare("INSERT INTO posts (user_id, content) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $content);

    if ($stmt->execute()) {
        // Get the last inserted post ID
        $post_id = $stmt->insert_id;

        // Handle image uploads if there are any
        if (isset($_FILES['images']) && count($_FILES['images']['name']) > 0) {
            $imageDir = 'uploads/images/'; // Ensure this directory exists and is writable
            $imageUrls = [];

            // Prepare the statement for inserting images outside the loop
            $imageStmt = $conn->prepare("INSERT INTO images (post_id, image_url) VALUES (?, ?)");

            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                // Check MIME type of the file
                $fileMimeType = mime_content_type($tmp_name);

                if (!in_array($fileMimeType, $allowedMimeTypes)) {
                    // Skip file if it is not a valid image type
                    continue;
                }

                $imageFileName = uniqid() . '_' . basename($_FILES['images']['name'][$key]);
                $targetFilePath = $imageDir . $imageFileName;

                // Move the uploaded file to the server directory
                if (move_uploaded_file($tmp_name, $targetFilePath)) {
                    // Insert the image into the `images` table
                    $imageStmt->bind_param("is", $post_id, $targetFilePath);
                    $imageStmt->execute();

                    // Add the URL to the array for response
                    $imageUrls[] = $targetFilePath;
                }
            }

            // Close the statement
            $imageStmt->close();
        }

        // Prepare a success response
        http_response_code(201); // Created
        echo json_encode([
            "message" => "Post created successfully.",
            "post_id" => $post_id,
            "image_urls" => $imageUrls ?? []
        ]);
        exit();
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(["error" => "Failed to create post."]);
        exit();
    }
}

// If the request method is not POST, respond with a 405 Method Not Allowed
http_response_code(405); // Method Not Allowed
echo json_encode(["error" => "Invalid request method."]);
exit();
?>
