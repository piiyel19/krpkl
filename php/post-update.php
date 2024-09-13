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
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $new_content = isset($_POST['content']) ? trim($_POST['content']) : '';

    // Check if the required data is present
    if ($post_id > 0 && $user_id > 0 && !empty($new_content)) {
        // Prepare the SQL query to update the content
        $sql = "UPDATE posts SET content = ? WHERE id = ? AND user_id = ?";
        
        // Prepare the statement
        if ($stmt = $conn->prepare($sql)) {
            // Bind parameters (content, post_id, user_id)
            $stmt->bind_param('sii', $new_content, $post_id, $user_id);
            
            // Execute the query
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Post updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update post']);
            }
            
            // Close the statement
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare SQL statement']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

// Close the database connection
$conn->close();

?>
