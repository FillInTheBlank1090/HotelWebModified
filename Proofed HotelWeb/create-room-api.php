<?php

include 'db.php';
header('Content-Type: application/json');


$roomName = $_POST['room_name'] ?? '';
$roomType = $_POST['room_type'] ?? '';
$price = $_POST['price'] ?? '';


if (empty($roomName) || empty($roomType) || !is_numeric($price) || $price <= 0) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ Số phòng, chọn Loại phòng và nhập Giá hợp lệ.']);
    exit;
}

try {
    
    
    $sqlCheck = "SELECT RoomID FROM Room WHERE RoomName = ?";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->execute([$roomName]);

    if ($stmtCheck->fetch()) {
        
        echo json_encode(['success' => false, 'message' => 'Số phòng "' . $roomName . '" đã tồn tại. Vui lòng chọn số khác.']);
        exit; 
    }
    

    
    $sqlInsert = "INSERT INTO Room (RoomName, RoomType, Price, Status) VALUES (?, ?, ?, 'Trống')";
    $stmtInsert = $pdo->prepare($sqlInsert);
    $stmtInsert->execute([$roomName, $roomType, $price]);

    
    echo json_encode(['success' => true, 'message' => 'Đã thêm phòng ' . $roomName . ' thành công!']);

} catch (Exception $e) {
    
    echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
}
?>