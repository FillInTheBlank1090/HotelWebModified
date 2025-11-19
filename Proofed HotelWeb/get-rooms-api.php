<?php
include 'db.php';
header('Content-Type: application/json');

try {
    $sql = "SELECT 
                r.RoomID, 
                r.RoomName, 
                r.RoomType, 
                r.Price,
                CASE 
                    WHEN EXISTS (
                        SELECT 1 FROM Booking b 
                        WHERE b.RoomID = r.RoomID 
                        AND b.Status = 'Đang ở'
                        AND NOW() >= b.CheckInDate 
                        AND NOW() <= b.CheckOutDate
                    ) THEN 'Đang thuê'
                    ELSE r.Status 
                END AS Status
            FROM Room r 
            WHERE r.Status != 'Deleted' 
            ORDER BY r.RoomName";
    
    $stmt = $pdo->query($sql);
    $rooms = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'rooms' => $rooms]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
}
?>