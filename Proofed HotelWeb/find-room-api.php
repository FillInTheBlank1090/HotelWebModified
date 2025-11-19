<?php
include 'db.php';
header('Content-Type: application/json');

$searchTerm = $_GET['searchTerm'] ?? '';

try {
    $sql = "SELECT * FROM Room 
            WHERE RoomName LIKE ? AND Status != 'Deleted'
            ORDER BY RoomName";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["%$searchTerm%"]);
    $rooms = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'rooms' => $rooms]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
}
?>