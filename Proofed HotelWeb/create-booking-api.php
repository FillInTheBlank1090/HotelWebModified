<?php
session_start();

include 'db.php';
header('Content-Type: application/json');


if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Bạn phải đăng nhập để đặt phòng.']);
    exit;
}



$roomID = $_POST['room_id'] ?? 0;
$checkinDate = $_POST['checkin_date'] ?? '';
$checkoutDate = $_POST['checkout_date'] ?? '';


$loggedInUserID = $_SESSION['user_id'];
$loggedInUserRole = $_SESSION['user_role'];


$walkinFullname = $_POST['walkin_fullname'] ?? '';
$walkinCccd = $_POST['walkin_cccd'] ?? '';
$walkinPhone = $_POST['walkin_phone'] ?? '';


if (empty($roomID) || empty($checkinDate) || empty($checkoutDate)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng chọn phòng và ngày nhận/trả phòng hợp lệ.']);
    exit;
}


try {
    $checkin_dt = new DateTime($checkinDate);
    $checkout_dt = new DateTime($checkoutDate);
    $today_dt = new DateTime(date('Y-m-d')); 

    if ($checkin_dt < $today_dt) {
        throw new Exception('Ngày nhận phòng không thể ở trong quá khứ.');
    }
    if ($checkout_dt <= $checkin_dt) {
        throw new Exception('Ngày trả phòng phải sau ngày nhận phòng ít nhất 1 ngày.');
    }

    $interval = $checkin_dt->diff($checkout_dt);
    $numDays = $interval->days;
    
    if ($numDays <= 0) {
         throw new Exception('Số ngày ở không hợp lệ.');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}


try {
    $pdo->beginTransaction();

    
    $customerID = null; 

    
    if (($loggedInUserRole === 'Admin' || $loggedInUserRole === 'Nhân viên') && !empty($walkinFullname)) 
    {
        if (empty($walkinCccd)) {
             throw new Exception('Nhân viên: Vui lòng nhập CCCD của khách.');
        }
        
        $sqlCheckCust = "SELECT CustomerID FROM Customer WHERE CCCD = ?";
        $stmtCheckCust = $pdo->prepare($sqlCheckCust);
        $stmtCheckCust->execute([$walkinCccd]);
        $existingCustomer = $stmtCheckCust->fetch();

        if ($existingCustomer) {
            $customerID = $existingCustomer['CustomerID'];
        } else {
            $sqlNewCust = "INSERT INTO Customer (FullName, Phone, CCCD, UserID) VALUES (?, ?, ?, NULL)";
            $stmtNewCust = $pdo->prepare($sqlNewCust);
            $stmtNewCust->execute([$walkinFullname, $walkinPhone, $walkinCccd]);
            $customerID = $pdo->lastInsertId(); 
        }
    } 
    
    else 
    {
        
        $sqlCustomer = "SELECT CustomerID FROM Customer WHERE UserID = ?";
        $stmtCustomer = $pdo->prepare($sqlCustomer);
        $stmtCustomer->execute([$loggedInUserID]);
        $customer = $stmtCustomer->fetch();
        
        if ($customer) {
            $customerID = $customer['CustomerID'];
        } elseif ($loggedInUserRole === 'Customer') {
            throw new Exception('Tài khoản của bạn chưa có hồ sơ khách hàng.');
        } else {
            throw new Exception('Vui lòng điền thông tin khách vãng lai.');
        }
    }
    
    if (empty($customerID)) {
        throw new Exception('Không xác định được khách hàng.');
    }
    
    
    
    $sqlCheckConflict = "SELECT COUNT(*) FROM Booking 
                         WHERE RoomID = ? 
                         AND Status != 'Đã hủy' AND Status != 'Đã trả'
                         AND (CheckInDate < ? AND CheckOutDate > ?)
                         FOR UPDATE"; 
                 
    $stmtCheck = $pdo->prepare($sqlCheckConflict);
    
    $stmtCheck->execute([$roomID, $checkoutDate, $checkinDate]);
    
    if ($stmtCheck->fetchColumn() > 0) {
        throw new Exception('Phòng này đã có người đặt trong khoảng thời gian bạn chọn. Vui lòng chọn ngày khác hoặc phòng khác.');
    }

    
    
    $priceSql = "SELECT Price FROM Room WHERE RoomID = ?";
    $stmtPrice = $pdo->prepare($priceSql);
    $stmtPrice->execute([$roomID]);
    $price = $stmtPrice->fetchColumn();
    
    $totalAmount = $price * $numDays;

    
    $sqlInsert = "INSERT INTO Booking (RoomID, CustomerID, CheckInDate, CheckOutDate, TotalAmount, Status) 
                  VALUES (?, ?, ?, ?, ?, 'Đang ở')";
                  
    $stmtInsert = $pdo->prepare($sqlInsert);
    $stmtInsert->execute([$roomID, $customerID, $checkinDate, $checkoutDate, $totalAmount]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Đặt phòng thành công!']);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}