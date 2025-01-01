<?php
header('Content-Type: application/json');

// Database connection
$conn = new mysqli('localhost', 'username', 'password', 'database_name');

if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

$contestant_name = $conn->real_escape_string($data['contestant_name']);
$votes = (int)$data['votes'];
$amount = (float)$data['amount'];

// Insert into database
$sql = "INSERT INTO votes (contestant_name, votes, amount, vote_date) 
        VALUES ('$contestant_name', $votes, $amount, NOW())";

if ($conn->query($sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => $conn->error]);
}

$conn->close();
?> 