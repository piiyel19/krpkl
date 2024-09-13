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
    $content = isset($_POST['tick']) ? trim($_POST['tick']) : '';
}

if (isset($_POST['tick']) && isset($_POST['post_id']) && isset($_POST['user_id'])) {
    $tick = $_POST['tick'];
    $post_id = $_POST['post_id'];
    $user_id = $_POST['user_id'];

    if ($tick == 1) {
        // Check if the user already liked the post
        $check_stmt = $conn->prepare("SELECT * FROM likes WHERE user_id = ? AND post_id = ?");
        $check_stmt->bind_param("ii", $user_id, $post_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            // User already liked the post, so skip adding the like
            // echo json_encode(["status" => "error", "message" => "You have already liked this post"]);

            

        } else {
            // Insert like if tick = 1 and no existing like
            $stmt = $conn->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $user_id, $post_id);

            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "Like added successfully"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to add like"]);
            }
        }
    } elseif ($tick == 0) {
        // Delete like if tick = 0
        $stmt = $conn->prepare("DELETE FROM likes WHERE user_id = ? AND post_id = ?");
        $stmt->bind_param("ii", $user_id, $post_id);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Like removed successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to remove like"]);
        }
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
}
