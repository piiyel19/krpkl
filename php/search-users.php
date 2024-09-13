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

    // Validate user_id
    if ($user_id <= 0) {
        echo json_encode(['error' => 'Invalid user ID']);
        exit();
    }

    // Prepare SQL queries
    $checkFollowingQuery = "
        SELECT following_id 
        FROM follows 
        WHERE follower_id = ?
    ";

    $checkFollowerQuery = "
        SELECT follower_id 
        FROM follows 
        WHERE following_id = ?
    ";

    // Prepare statement for checking following
    $stmt = mysqli_prepare($conn, $checkFollowingQuery);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $followingResult = mysqli_stmt_get_result($stmt);
    $following = [];
    while ($row = mysqli_fetch_assoc($followingResult)) {
        $following[] = $row['following_id'];
    }
    mysqli_stmt_close($stmt);

    // Prepare statement for checking followers
    $stmt = mysqli_prepare($conn, $checkFollowerQuery);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $followerResult = mysqli_stmt_get_result($stmt);
    $followers = [];
    while ($row = mysqli_fetch_assoc($followerResult)) {
        $followers[] = $row['follower_id'];
    }
    mysqli_stmt_close($stmt);

    // Prepare SQL query to get users with follower count
    $query = "
        SELECT 
            u.id, 
            u.name, 
            u.bio, 
            u.avatar, 
            COUNT(f.follower_id) AS followers_count
        FROM 
            users u
        LEFT JOIN 
            follows f ON u.id = f.following_id
        WHERE 
            u.id != ?
        GROUP BY 
            u.id, u.name, u.bio, u.avatar
    ";

    // Prepare statement
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // Check if query was successful
    if ($result) {
        $users = [];

        // Fetch all rows
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'bio' => $row['bio'],
                'avatar' => $row['avatar'],
                'followers_count' => intval($row['followers_count']),
                'is_following' => in_array($row['id'], $following),
                'is_followed' => in_array($row['id'], $followers)
            ];
        }

        // Return results as JSON
        echo json_encode([
            'success' => true,
            'users' => $users
        ]);
    } else {
        // Return error message if query failed
        echo json_encode(['success' => false, 'error' => 'Database query failed']);
    }

    // Free result set
    mysqli_free_result($result);
} else {
    // Return error if request method is not POST
    echo json_encode(['error' => 'Invalid request method']);
}

// Close database connection
mysqli_close($conn);

?>
