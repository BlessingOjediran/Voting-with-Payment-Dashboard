<?php
require_once 'dbh.inc.php';

$query = "SELECT 
    c.id,
    c.name,
    COALESCE(SUM(v.num_votes), 0) as votes,
    COALESCE(SUM(v.amount), 0) as amount
FROM contestants c
LEFT JOIN votes v ON c.id = v.contestant_id
GROUP BY c.id, c.name
ORDER BY votes DESC";

$result = $conn->query($query);
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = [
        'name' => $row['name'],
        'votes' => (int)$row['votes'],
        'amount' => (float)$row['amount']
    ];
}

echo json_encode($data);
$conn->close();
?> 