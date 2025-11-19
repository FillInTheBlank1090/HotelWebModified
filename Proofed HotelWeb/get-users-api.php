<?php
include 'db.php';
header('Content-Type: application/json');

try {
    $sql = "SELECT UserID, FullName, Email, Role, Username FROM Users ORDER BY FullName";
    $stmt = $pdo->query($sql);
    $users = $stmt->fetchAll();
    echo json_encode(['success' => true, 'users' => $users]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
}
?>