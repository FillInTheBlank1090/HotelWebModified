<?php
include 'db.php';
header('Content-Type: application/json');

$roomType = $_GET['room_type'] ?? '';
$checkIn = $_GET['checkin'] ?? '';
$checkOut = $_GET['checkout'] ?? '';

if (empty($roomType) || empty($checkIn) || empty($checkOut)) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin lọc phòng.']);
    exit;
}

try {
    // Chọn phòng có cùng Loại
    // loại trừ những phòng có lịch đặt trùng với khoảng thời gian khách chọn
    
    $sql = "SELECT * FROM Room r 
            WHERE r.RoomType = ? 
            AND r.Status != 'Deleted' -- Bỏ qua phòng đã xóa
            AND r.Status != 'Đang dọn' -- Bỏ qua phòng đang dọn/bảo trì
            AND NOT EXISTS (
                SELECT 1 FROM Booking b 
                WHERE b.RoomID = r.RoomID 
                AND b.Status != 'Đã hủy' 
                AND b.Status != 'Đã trả'
                -- Công thức kiểm tra trùng lịch: (StartA < EndB) và (EndA > StartB)
                AND (b.CheckInDate < ? AND b.CheckOutDate > ?)
            )
            ORDER BY r.RoomName";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$roomType, $checkOut, $checkIn]);
    $rooms = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'rooms' => $rooms]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
}
?>