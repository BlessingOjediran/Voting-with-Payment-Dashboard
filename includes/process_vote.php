<?php
require_once 'dbh.inc.php';

// Verify Paystack transaction
function verifyTransaction($reference) {
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($reference),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "accept: application/json",
            "authorization: Bearer sk_test_a81beccaa089822d25ca407e28c42942bd4293d0", // Replace with your Paystack secret key
            "cache-control: no-cache"
        ],
    ));
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    
    if ($err) {
        return false;
    }
    
    return json_decode($response, true);
}

// Get POST data
$reference = $_POST['reference'] ?? '';
$contestant_id = $_POST['contestant_id'] ?? '';
$num_votes = $_POST['votes'] ?? 0;

if (!$reference || !$contestant_id || !$num_votes) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

// Verify the transaction
$verification = verifyTransaction($reference);

if (!$verification || !$verification['status'] || $verification['data']['status'] !== 'success') {
    echo json_encode(['success' => false, 'message' => 'Payment verification failed']);
    exit();
}

try {
    // Insert vote record with additional fields
    $stmt = $conn->prepare("INSERT INTO votes (
        contestant_id, 
        contestant_name,
        email, 
        num_votes, 
        amount, 
        payment_reference
    ) VALUES (?, ?, ?, ?, ?, ?)");
    
    $email = $verification['data']['customer']['email'];
    $amount = $verification['data']['amount'] / 100; // Convert from kobo to naira
    $contestant_name = $_POST['contestant_name'] ?? '';
    
    $stmt->bind_param(
        "sssids", 
        $contestant_id, 
        $contestant_name,
        $email, 
        $num_votes, 
        $amount, 
        $reference
    );
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Vote recorded successfully']);
    } else {
        throw new Exception("Error recording vote");
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error recording vote']);
}

$stmt->close();
$conn->close();
?> 