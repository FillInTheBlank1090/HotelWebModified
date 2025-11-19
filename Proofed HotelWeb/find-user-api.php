<?php
include 'db.php';
header('Content-Type: application/json');
$searchTerm = $_GET['searchTerm'] ?? '';

try {
    // Tìm theo Tên HOẶC Email
    $sql = "SELECT UserID, FullName, Email, Role FROM Users 
            WHERE FullName LIKE ? OR Email LIKE ?
            ORDER BY FullName";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["%$searchTerm%", "%$searchTerm%"]);
    $users = $stmt->fetchAll();
    echo json_encode(['success' => true, 'users' => $users]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
}
?>