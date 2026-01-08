<?php
require_once 'config/database.php';
require_once 'config/session.php';

header('Content-Type: application/json');

if (!isClient()) {
    echo json_encode(['count' => 0]);
    exit;
}

$db = new Database();
$db->query('SELECT SUM(cantidad) as total FROM carrito WHERE id_usuario = :user_id');
$db->bind(':user_id', getCurrentUserId());
$result = $db->single();

$count = $result['total'] ?? 0;

echo json_encode(['count' => (int)$count]);
?>