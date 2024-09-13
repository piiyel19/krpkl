<?php 
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

	// Set an effectively unlimited timeout (very high value)
	ini_set('mysqli.connect_timeout', 0); // 0 means no timeout for mysqli connections
	ini_set('default_socket_timeout', 0); // 0 means no timeout for socket connections


	// Database connection parameters
	$servername = "localhost";
	$username = "root";
	$password = "root";
	$dbname = "pwa-social";

	// Create connection
	// $conn = new mysqli($servername, $username, $password, $dbname);

	$conn = mysqli_init();
	$conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 60);  // Increase to 60 seconds or more
	$conn->real_connect($servername, $username, $password, $dbname);

	// Check connection
	if ($conn->connect_error) {
		// $conn->query("SET SESSION wait_timeout = 500");

	    http_response_code(500); // Internal Server Error
	    echo json_encode(["error" => "Database connection failed."]);
	    exit();
	}
?>