<?php

header("Access-Control-Allow-Origin: *"); // Adjust as needed
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json; charset=UTF-8'); // Ensure the response is in JSON format

// Handle preflight requests for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// Include database connection file (adjust the path as necessary)
include('db-connection.php');

// Check if the request method is POST and if the userId was provided
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['userId'])) {
    $userId = $_POST['userId'];

    // Initialize the response array
    $response = [];

    // Fetch user details
    $query = "SELECT name, avatar, bio FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $userResult = $stmt->get_result();
    $userDetails = $userResult->fetch_assoc();

    if ($userDetails) {
        // Add user details to the response
        $response['user'] = $userDetails;

        // Fetch the total number of users the current user is following
        $queryFollowing = "SELECT count(*) as totalFollowing FROM follows WHERE follower_id = ?";
        $stmtFollowing = $conn->prepare($queryFollowing);
        $stmtFollowing->bind_param('i', $userId);
        $stmtFollowing->execute();
        $followingResult = $stmtFollowing->get_result();
        $followingCount = $followingResult->fetch_assoc()['totalFollowing'];

        // Fetch the total number of followers for the current user
        $queryFollowers = "SELECT count(*) as totalFollowers FROM follows WHERE following_id = ?";
        $stmtFollowers = $conn->prepare($queryFollowers);
        $stmtFollowers->bind_param('i', $userId);
        $stmtFollowers->execute();
        $followersResult = $stmtFollowers->get_result();
        $followersCount = $followersResult->fetch_assoc()['totalFollowers'];


        // Fetch the total number of followers for the current user
        $queryPost = "SELECT count(*) as totalPost FROM posts WHERE user_id = ? AND status='active'";
        $stmtPost = $conn->prepare($queryPost);
        $stmtPost->bind_param('i', $userId);
        $stmtPost->execute();
        $PostResult = $stmtPost->get_result();
        $PostCount = $PostResult->fetch_assoc()['totalPost'];

        // Add following and followers count to the response
        $response['following'] = $followingCount;
        $response['followers'] = $followersCount;
        $response['totalPost'] = $PostCount;

        // Return the response as JSON
        echo json_encode($response);
    } else {
        // User not found
        echo json_encode(['error' => 'User not found']);
    }
} else {
    // Invalid request
    echo json_encode(['error' => 'Invalid request']);
}

?>
