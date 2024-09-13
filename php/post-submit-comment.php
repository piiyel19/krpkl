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
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';

    // Validate data
    if ($post_id > 0 && $user_id > 0 && !empty($content)) {
        // Prepare SQL query to insert comment
        $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $post_id, $user_id, $content);

        if ($stmt->execute()) {
            // Success
            $response = [
                'status' => 'success',
                'message' => 'Comment posted successfully.'
            ];
        } else {
            // Query error
            $response = [
                'status' => 'error',
                'message' => 'Failed to post comment.'
            ];
        }

        $stmt->close();
    } else {
        // Invalid input
        $response = [
            'status' => 'error',
            'message' => 'Invalid input data.'
        ];
    }

    // Return JSON response
    echo json_encode($response);
} else {
    // Invalid request method
    $response = [
        'status' => 'error',
        'message' => 'Invalid request method.'
    ];
    echo json_encode($response);
}

$conn->close();
?>
