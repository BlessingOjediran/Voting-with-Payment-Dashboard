<?php
require_once 'config.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get contestant data
    $stmt = $pdo->query("
        SELECT 
            c.name,
            COUNT(v.id) as votes,
            COALESCE(SUM(v.amount), 0) as amount
        FROM contestants c
        LEFT JOIN votes v ON c.id = v.contestant_id
        GROUP BY c.id, c.name
        ORDER BY votes DESC
    ");
    $contestants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate totals
    $totalVotes = 0;
    $totalAmount = 0;
    foreach ($contestants as $contestant) {
        $totalVotes += $contestant['votes'];
        $totalAmount += $contestant['amount'];
    }

    // Get unique voters count
    $stmt = $pdo->query("SELECT COUNT(DISTINCT voter_id) as total_voters FROM votes");
    $totalVoters = $stmt->fetch(PDO::FETCH_ASSOC)['total_voters'];

    // Prepare response
    $response = [
        'contestants' => $contestants,
        'totalVotes' => $totalVotes,
        'totalAmount' => $totalAmount,
        'totalVoters' => $totalVoters
    ];

    header('Content-Type: application/json');
    echo json_encode($response);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 