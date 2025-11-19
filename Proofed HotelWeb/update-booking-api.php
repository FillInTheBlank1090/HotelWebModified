<?php
 
include 'db.php';
header('Content-Type: application/json');

$bookingID = $_POST['booking_id'] ?? 0;
$checkoutDate = $_POST['checkout_date'] ?? '';

if (empty($bookingID) || empty($checkoutDate)) {
    echo json_encode(['success' => false, 'message' => 'Thiếu ID đặt phòng hoặc Ngày trả phòng.']);
    exit;
}

// (Bạn có thể thêm validation để kiểm tra ngày trả >= ngày nhận ở đây)

try {
    $sql = "UPDATE Booking 
            SET CheckOutDate = ? 
            WHERE BookingID = ? AND Status = 'Đang ở'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$checkoutDate, $bookingID]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Cập nhật ngày trả phòng thành công!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn đặt phòng hoặc ngày không đổi.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
}
?>