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
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $my_id = isset($_POST['my_id']) ? intval($_POST['my_id']) : 0;

    // Validate input
    if ($user_id <= 0 || !in_array($action, ['follow', 'unfollow'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid input']);
        exit();
    }

    // Check if the user is trying to follow/unfollow themselves
    if ($user_id == $my_id) {
        echo json_encode(['success' => false, 'error' => 'Cannot follow/unfollow yourself']);
        exit();
    }

    // Prepare SQL query for follow/unfollow action
    if ($action === 'follow') {
        $query = "INSERT INTO follows (follower_id, following_id) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $my_id, $user_id);
    } else { // action === 'unfollow'
        $query = "DELETE FROM follows WHERE follower_id = ? AND following_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $my_id, $user_id);
    }

    // Execute the query
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database operation failed']);
    }

    // Close the statement
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

// Close the database connection
mysqli_close($conn);

?>
