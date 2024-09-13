<?php

header("Access-Control-Allow-Origin: *"); // Adjust as needed
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight requests for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Include database connection file (adjust the path as necessary)
include('db-connection.php');

// Check if the request method is POST and if the file and userId were uploaded
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar']) && isset($_POST['userId'])) {

    // Directory where the uploaded image will be saved
    $uploadDir = 'uploads/avatar/';
    
    // Get the uploaded file info
    $file = $_FILES['avatar'];
    $userId = $_POST['userId'];

    // Check if the file was uploaded without errors
    if ($file['error'] === UPLOAD_ERR_OK) {
        // Create a unique file name
        $fileName = uniqid() . '-' . basename($file['name']);
        $uploadFilePath = $uploadDir . $fileName;

        // Move the uploaded file to the destination directory
        if (move_uploaded_file($file['tmp_name'], $uploadFilePath)) {
            // File successfully uploaded, now update the user's avatar in the database
            $query = "UPDATE users SET avatar = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('si', $uploadFilePath, $userId); // Bind the new file name and userId
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                // Success response
                echo json_encode(['status' => 'success', 'message' => 'Avatar updated successfully.']);
            } else {
                // Error updating the database
                echo json_encode(['status' => 'error', 'message' => 'Failed to update avatar in the database.']);
            }

            $stmt->close();
        } else {
            // Error moving the uploaded file
            echo json_encode(['status' => 'error', 'message' => 'Failed to move uploaded file.']);
        }
    } else {
        // Error during file upload
        echo json_encode(['status' => 'error', 'message' => 'File upload error.']);
    }
} else {
    // Missing parameters
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}

?>
