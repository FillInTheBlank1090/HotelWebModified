<?php
// Đính kèm file kết nối CSDL
include 'db.php';

// Bắt đầu một session để lưu trạng thái đăng nhập
session_start();

// Báo cho trình duyệt biết đây là file JSON
header('Content-Type: application/json');

// 1. Lấy dữ liệu từ form
$email = $_POST['email'] ?? ''; // Dùng ?? '' để tránh lỗi undefined index
$mat_khau = $_POST['password'] ?? '';

// 2. Kiểm tra dữ liệu
if (empty($email) || empty($mat_khau)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ email và mật khẩu.']);
    exit; // Dừng chương trình
}

try {
    // 3. Tìm người dùng bằng Email
    $sql = "SELECT UserID, PasswordHash, Role, FullName FROM Users WHERE Email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 4. Xác thực người dùng
    if ($user && password_verify($mat_khau, $user['PasswordHash'])) {
        // Mật khẩu chính xác!
        
        // 5. Lưu thông tin vào session
        $_SESSION['user_id'] = $user['UserID'];
        $_SESSION['user_role'] = $user['Role'];
        $_SESSION['user_name'] = $user['FullName'];
        

        // 6. Xác định trang chuyển hướng dựa trên vai trò (Role)
        $redirectUrl = 'mainmenu.php'; // Mặc định cho Customer
        
        if ($user['Role'] === 'Admin') {
            $redirectUrl = 'admin.php';
        } elseif ($user['Role'] === 'Nhân viên') {
            $redirectUrl = 'staff.php';
        }

        // 7. Gửi về thông báo thành công
        echo json_encode([
            'success' => true, 
            'message' => 'Đăng nhập thành công! Đang chuyển hướng...',
            'redirect' => $redirectUrl 
        ]);

    } else {
        // Sai email hoặc mật khẩu
        echo json_encode(['success' => false, 'message' => 'Sai thông tin đăng nhập.']);
    }

} catch (Exception $e) {
    // Báo lỗi máy chủ nếu có sự cố
    echo json_encode(['success' => false, 'message' => 'Lỗi máy chủ: ' . $e->getMessage()]);
}
?>