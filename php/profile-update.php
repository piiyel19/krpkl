<?php

header("Access-Control-Allow-Origin: *"); // Adjust as needed
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

include('db-connection.php'); // Adjust the path as necessary

// Check if the database connection is established
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// Check if required POST data is received
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get POST data
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';  // Ensure it's a string
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $bio = isset($_POST['bio']) ? trim($_POST['bio']) : '';

    // Check if the required data is valid
    if ($id > 0 && !empty($name) && !empty($bio)) {
        // Prepare the SQL query to update the user's name and bio
        $sql = "UPDATE users SET name = ?, bio = ? WHERE id = ?";
        
        // Prepare the statement
        if ($stmt = $conn->prepare($sql)) {
            // Bind parameters (name, bio, id)
            $stmt->bind_param('ssi', $name, $bio, $id);  // 's' for strings and 'i' for integer
            
            // Execute the query
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'User updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update user: ' . $stmt->error]);
            }
            
            // Close the statement
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare SQL statement: ' . $conn->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid input. Ensure name and bio are non-empty and id is valid.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

// Close the database connection
$conn->close();

?>
