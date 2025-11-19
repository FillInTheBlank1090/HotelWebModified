<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db.php';


if (!isset($_SESSION['user_role']) || strcasecmp($_SESSION['user_role'], 'Admin') !== 0) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: Bạn không có quyền tạo phòng.']);
    exit;
}

$roomName = $_POST['room_name'] ?? '';
$roomType = $_POST['room_type'] ?? '';
$price = $_POST['price'] ?? '';

if (empty($roomName) || empty($roomType) || !is_numeric($price) || $price <= 0) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin.']);
    exit;
}

try {
    
    $sqlCheck = "SELECT RoomID, Status FROM Room WHERE RoomName = ?";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->execute([$roomName]);
    $existingRoom = $stmtCheck->fetch();

    if ($existingRoom) {
        
        if ($existingRoom['Status'] === 'Deleted') {
            
            $sqlUpdate = "UPDATE Room SET RoomType = ?, Price = ?, Status = 'Trống' WHERE RoomID = ?";
            $stmtUpdate = $pdo->prepare($sqlUpdate);
            $stmtUpdate->execute([$roomType, $price, $existingRoom['RoomID']]);
            
            echo json_encode(['success' => true, 'message' => 'Đã khôi phục phòng ' . $roomName . ' thành công!']);
            exit;
        } else {
            
            echo json_encode(['success' => false, 'message' => 'Số phòng "' . $roomName . '" đang hoạt động. Vui lòng chọn số khác.']);
            exit;
        }
    }
    
    
    $sqlInsert = "INSERT INTO Room (RoomName, RoomType, Price, Status) VALUES (?, ?, ?, 'Trống')";
    $stmtInsert = $pdo->prepare($sqlInsert);
    $stmtInsert->execute([$roomName, $roomType, $price]);

    echo json_encode(['success' => true, 'message' => 'Đã thêm phòng ' . $roomName . ' thành công!']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
}
?>
