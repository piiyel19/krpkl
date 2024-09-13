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
    // Get POST data and validate it
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

    // Validate the data further if needed
    if ($post_id > 0 && $user_id > 0) {
        // Prepare the SQL query to update the post status
        $sql = "UPDATE posts SET status = 'deleted' WHERE id = ? AND user_id = ?";
        
        // Prepare the statement
        if ($stmt = $conn->prepare($sql)) {
            // Bind parameters (post_id, user_id)
            $stmt->bind_param('ii', $post_id, $user_id);
            
            // Execute the query
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo json_encode(['success' => true, 'message' => 'Post updated successfully']);
                } else {
                    // If no rows were updated, return an appropriate message
                    echo json_encode(['success' => false, 'message' => 'No post found or permission denied']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to execute query: ' . $stmt->error]);
            }
            
            // Close the statement
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare SQL statement: ' . $conn->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid input. Ensure post_id and user_id are greater than 0.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

// Close the database connection
$conn->close();

?>
