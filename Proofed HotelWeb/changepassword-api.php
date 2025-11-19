<?php
session_start();
include 'db.php';
header('Content-Type: application/json');

// 1. Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: Bạn phải đăng nhập để đổi mật khẩu.']);
    exit;
}
$userID = $_SESSION['user_id'];

// 2. Lấy dữ liệu từ form
$email = $_POST['email'] ?? '';
$oldPass = $_POST['old_password'] ?? '';
$newPass = $_POST['new_password'] ?? '';
$confirmPass = $_POST['confirm_password'] ?? '';

// 3. Validate dữ liệu
if (empty($email) || empty($oldPass) || empty($newPass) || empty($confirmPass)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ tất cả các trường.']);
    exit;
}
if ($newPass !== $confirmPass) {
    echo json_encode(['success' => false, 'message' => 'Mật khẩu mới và xác nhận mật khẩu không khớp.']);
    exit;
}
if (strlen($newPass) < 6) { // Đặt độ dài tối thiểu nếu muốn
    echo json_encode(['success' => false, 'message' => 'Mật khẩu mới phải có ít nhất 6 ký tự.']);
    exit;
}

try {
    // 4. Lấy thông tin người dùng từ CSDL
    $sql = "SELECT Email, PasswordHash FROM Users WHERE UserID = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userID]);
    $user = $stmt->fetch();

    if (!$user) {
        throw new Exception('Không tìm thấy người dùng.');
    }

    // 5. Xác thực (Email phải khớp VÀ Mật khẩu cũ phải khớp)
    if ($user['Email'] !== $email) {
        echo json_encode(['success' => false, 'message' => 'Email không đúng với tài khoản của bạn.']);
        exit;
    }
    if (!password_verify($oldPass, $user['PasswordHash'])) {
        echo json_encode(['success' => false, 'message' => 'Mật khẩu cũ không chính xác.']);
        exit;
    }

    // 6. Mọi thứ chính xác -> Cập nhật mật khẩu mới
    $newPasswordHash = password_hash($newPass, PASSWORD_BCRYPT);
    $sqlUpdate = "UPDATE Users SET PasswordHash = ? WHERE UserID = ?";
    $stmtUpdate = $pdo->prepare($sqlUpdate);
    $stmtUpdate->execute([$newPasswordHash, $userID]);

    // 7. Hủy session (bắt buộc đăng nhập lại)
    session_destroy();

    echo json_encode([
        'success' => true, 
        'message' => 'Đổi mật khẩu thành công! Vui lòng đăng nhập lại.',
        'redirect' => 'login.php' // Trả về link để JS chuyển trang
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
}
?>