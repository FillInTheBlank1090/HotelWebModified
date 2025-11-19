<?php
include 'db.php';
header('Content-Type: application/json');


$historyMode = $_GET['history'] ?? ''; 

try {
   
    $sql = "SELECT 
                c.CCCD, c.FullName, r.RoomName,
                b.CheckInDate, b.CheckOutDate, b.BookingID, b.Status 
            FROM Customer c
            JOIN Booking b ON c.CustomerID = b.CustomerID
            JOIN Room r ON b.RoomID = r.RoomID";
    
    
    if ($historyMode !== 'all') {
        $sql .= " WHERE b.Status = 'Đang ở'";
    }
    
    $sql .= " ORDER BY b.CheckInDate DESC"; 
            
    $stmt = $pdo->query($sql);
    $customers = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'customers' => $customers]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
}
?>