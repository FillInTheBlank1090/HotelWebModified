<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


include 'db.php';
header('Content-Type: application/json');



if (!isset($_SESSION['user_role']) || strcasecmp($_SESSION['user_role'], 'Admin') !== 0) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: Bạn không có quyền xóa phòng.']);
    exit;
}

$roomName = $_POST['room_name'] ?? '';

if (empty($roomName)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng chọn phòng để xóa.']);
    exit;
}

try {
    
    
    $sqlCheckActive = "SELECT COUNT(*) 
                       FROM Booking b
                       JOIN Room r ON b.RoomID = r.RoomID
                       WHERE r.RoomName = ? AND b.Status = 'Đang ở'";
                       
    $stmtCheck = $pdo->prepare($sqlCheckActive);
    $stmtCheck->execute([$roomName]);
    
    if ($stmtCheck->fetchColumn() > 0) {
        
        echo json_encode(['success' => false, 'message' => 'KHÔNG THỂ XÓA: Phòng này đang có khách ở (Status: Đang ở). Vui lòng trả phòng cho khách trước.']);
        exit;
    }

    
    $sql = "UPDATE Room SET Status = 'Deleted' WHERE RoomName = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$roomName]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Đã xóa phòng ' . $roomName . ' thành công!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy phòng hoặc phòng đã bị xóa trước đó.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
?>
