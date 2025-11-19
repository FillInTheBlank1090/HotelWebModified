<?php
// Đính kèm file kết nối CSDL
include 'db.php';
// Báo cho trình duyệt biết đây là file JSON
header('Content-Type: application/json');

// 1. Lấy dữ liệu từ form (name của các input trong register.html)
$fullname = $_POST['fullname'];
$password = $_POST['password'];
$phone = $_POST['phone'];
$idcard = $_POST['idcard'];
$email = $_POST['email'];

// 2. Kiểm tra dữ liệu
if (empty($fullname) || empty($password) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đủ Họ Tên, Mật Khẩu và Email.']);
    exit;
}

// 3. Bắt đầu một Transaction (để đảm bảo cả 2 bảng cùng được chèn)
try {
    $pdo->beginTransaction();

    // 4. Mã hóa mật khẩu
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // 5. Thêm vào bảng Users
    // Chúng ta dùng Email làm Username (vì nó là UNIQUE)
    $sqlUser = "INSERT INTO Users (Username, PasswordHash, FullName, Role, Email) 
                VALUES (?, ?, ?, 'Customer', ?)";
    $stmtUser = $pdo->prepare($sqlUser);
    $stmtUser->execute([$email, $passwordHash, $fullname, $email]);

    // 6. Lấy UserID vừa được tạo
    $lastUserID = $pdo->lastInsertId();

    // 7. Thêm vào bảng Customer
    $sqlCustomer = "INSERT INTO Customer (FullName, Phone, CCCD, UserID) 
                    VALUES (?, ?, ?, ?)";
    $stmtCustomer = $pdo->prepare($sqlCustomer);
    $stmtCustomer->execute([$fullname, $phone, $idcard, $lastUserID]);

    // 8. Nếu mọi thứ thành công, xác nhận transaction
    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Đăng ký thành công! Bạn có thể đăng nhập ngay bây giờ.']);

} catch (Exception $e) {
    // 9. Nếu có lỗi, hủy bỏ mọi thay đổi
    $pdo->rollBack();
    
    // Bắt lỗi nếu email/username đã tồn tại
    if ($e->getCode() == 23000) { // Mã lỗi 23000 là lỗi vi phạm UNIQUE
        echo json_encode(['success' => false, 'message' => 'Email, SĐT hoặc CCCD này đã được sử dụng.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ: ' . $e->getMessage()]);
    }
}
?>