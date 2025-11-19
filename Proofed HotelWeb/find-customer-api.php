<?php
include 'db.php';
header('Content-Type: application/json');

$cccd = $_GET['cccd'] ?? '';
$historyMode = $_GET['history'] ?? ''; 

if (empty($cccd)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập CCCD để tìm.']);
    exit;
}

try {
   
    $sql = "SELECT 
                c.CCCD, c.FullName, r.RoomName,
                b.CheckInDate, b.CheckOutDate, b.BookingID, b.Status
            FROM Customer c
            JOIN Booking b ON c.CustomerID = b.CustomerID
            JOIN Room r ON b.RoomID = r.RoomID
            WHERE c.CCCD LIKE ?";
            
    $params = ["%$cccd%"]; 

    
    if ($historyMode !== 'all') {
        $sql .= " AND b.Status = ?";
        $params[] = 'Đang ở'; 
    }
    
    $sql .= " ORDER BY b.CheckInDate DESC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params); 
    $customers = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'customers' => $customers]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
}
?>