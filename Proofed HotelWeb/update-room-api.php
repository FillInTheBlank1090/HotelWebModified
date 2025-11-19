<?php

include 'db.php';
header('Content-Type: application/json');

// Get all possible data from the form
$roomName = $_POST['room_name'] ?? '';       // Room name to identify specific room
$roomType = $_POST['room_type'] ?? '';       // Room type (for either update type)
$status = $_POST['status'] ?? '';          // New status (for specific room update)
$price = $_POST['price'] ?? '';            // New price (for either update type)

// --- SCENARIO 1: Update SPECIFIC Room (if roomName is provided) ---
if (!empty($roomName)) {
    // Validate required fields for specific update
    if (empty($roomType) || empty($status) || !is_numeric($price) || $price < 0) {
        echo json_encode(['success' => false, 'message' => 'Để cập nhật phòng cụ thể, vui lòng điền/chọn đầy đủ Loại phòng, Trạng thái và Giá mới.']);
        exit;
    }
    
    // Optional: Validate Status and RoomType values if needed
    $validStatuses = ['Trống', 'Đang thuê', 'Đang dọn']; 
    if (!in_array($status, $validStatuses)) {
         echo json_encode(['success' => false, 'message' => 'Trạng thái phòng không hợp lệ.']);
         exit;
    }
    $validTypes = ['Đơn', 'Đôi', 'VIP']; 
    if (!in_array($roomType, $validTypes)) {
         echo json_encode(['success' => false, 'message' => 'Loại phòng không hợp lệ.']);
         exit;
    }

    try {
        // Prepare UPDATE statement for a specific room
        $sql = "UPDATE Room SET RoomType = ?, Status = ?, Price = ? WHERE RoomName = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$roomType, $status, $price, $roomName]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Đã cập nhật thông tin phòng ' . $roomName . ' thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy phòng ' . $roomName . ' hoặc thông tin không thay đổi.']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi CSDL khi cập nhật phòng cụ thể: ' . $e->getMessage()]);
    }

// --- SCENARIO 2: Update Price BY TYPE (if roomName is empty) ---
} elseif (empty($roomName)) { 
    // Validate required fields for type update
    if (empty($roomType) || !is_numeric($price) || $price < 0) {
        echo json_encode(['success' => false, 'message' => 'Để cập nhật giá theo loại, vui lòng chọn Loại Phòng và nhập Giá mới hợp lệ.']);
        exit;
    }
    
    // Optional: Validate RoomType
    $validTypes = ['Đơn', 'Đôi', 'VIP']; 
    if (!in_array($roomType, $validTypes)) {
         echo json_encode(['success' => false, 'message' => 'Loại phòng không hợp lệ.']);
         exit;
    }

    try {
        // Prepare UPDATE statement by RoomType
        $sql = "UPDATE Room SET Price = ? WHERE RoomType = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$price, $roomType]);
        
        $affectedRows = $stmt->rowCount();

        if ($affectedRows > 0) {
            echo json_encode(['success' => true, 'message' => 'Đã cập nhật giá cho ' . $affectedRows . ' phòng loại "' . $roomType . '" thành ' . number_format($price) . ' VNĐ!']);
        } else {
             echo json_encode(['success' => true, 'message' => 'Không có phòng nào loại "' . $roomType . '" cần cập nhật giá hoặc giá không thay đổi.']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi CSDL khi cập nhật giá theo loại: ' . $e->getMessage()]);
    }
    
} else {
     // This case should technically not be reached if validation is correct above
     echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
}
?>