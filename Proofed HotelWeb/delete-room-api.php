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

    $sql = "UPDATE Room SET Status = 'Deleted' 
            WHERE RoomName = ? AND Status != 'Đang thuê'";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$roomName]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Đã xóa phòng ' . $roomName . ' thành công!']);
    } else {

        echo json_encode(['success' => false, 'message' => 'Không thể xóa: Phòng đang có khách hoặc không tồn tại.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
?>