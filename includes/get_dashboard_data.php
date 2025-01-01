<?php
require_once 'dbh.inc.php';
header('Content-Type: application/json');

try {
    $data = [
        'totalVotes' => 0,
        'totalAmount' => 0,
        'uniqueVoters' => 0,
        'contestantStats' => [],
        'timeline' => [],
        'recentVotes' => []
    ];

    // Get total votes and amount
    $result = $conn->query("SELECT 
        SUM(num_votes) as total_votes, 
        SUM(amount) as total_amount,
        COUNT(DISTINCT email) as unique_voters 
        FROM votes");
    $row = $result->fetch_assoc();
    $data['totalVotes'] = (int)$row['total_votes'];
    $data['totalAmount'] = (float)$row['total_amount'];
    $data['uniqueVoters'] = (int)$row['unique_voters'];

    // Get votes per contestant
    $result = $conn->query("SELECT 
        contestant_name, 
        SUM(num_votes) as total_votes 
        FROM votes 
        GROUP BY contestant_name 
        ORDER BY total_votes DESC");
    while ($row = $result->fetch_assoc()) {
        $data['contestantStats'][] = $row;
    }

    // Get votes timeline (last 7 days)
    $result = $conn->query("SELECT 
        DATE(created_at) as date, 
        SUM(num_votes) as votes 
        FROM votes 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY DATE(created_at) 
        ORDER BY date");
    while ($row = $result->fetch_assoc()) {
        $data['timeline'][] = $row;
    }

    // Get recent votes
    $result = $conn->query("SELECT 
        contestant_name, 
        num_votes, 
        amount, 
        created_at as vote_date 
        FROM votes 
        ORDER BY created_at DESC 
        LIMIT 10");
    while ($row = $result->fetch_assoc()) {
        $data['recentVotes'][] = $row;
    }

    echo json_encode($data);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?> 