<?php
require '../includes/config.php';

header('Content-Type: application/json');

if (!isset($_GET['order_id'])) {
    http_response_code(400);
    die(json_encode(['error' => 'Missing order ID']));
}

$order_id = (int)$_GET['order_id'];
$query = "SELECT status FROM orders WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

echo json_encode([
    'status' => $result['status'] ?? 'not_found',
    'last_updated' => time()
]);