<?php

header("Access-Control-Allow-Origin: *"); // Adjust as needed
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

include('db-connection.php'); // Adjust the path as necessary

// Check if required POST data is received
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get POST data
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

    if ($id > 0 && $post_id > 0 && $user_id > 0) {
        // Prepare update query
        $query = "UPDATE comments SET comment_delete = 1, updated_at = NOW() WHERE id = ? AND post_id = ? AND user_id = ?";
        
        if ($stmt = $conn->prepare($query)) {
            // Bind the parameters
            $stmt->bind_param('iii', $id, $post_id, $user_id);
            
            // Execute the query
            if ($stmt->execute()) {
                // Success response
                echo json_encode(['success' => true, 'message' => 'Comment updated successfully']);
            } else {
                // Error response if update failed
                echo json_encode(['success' => false, 'message' => 'Failed to update comment']);
            }

            // Close statement
            $stmt->close();
        } else {
            // Error response if query preparation failed
            echo json_encode(['success' => false, 'message' => 'Database query failed']);
        }
    } else {
        // Error response if invalid POST data
        echo json_encode(['success' => false, 'message' => 'Invalid data provided']);
    }
} else {
    // Error response if not a POST request
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

?>
