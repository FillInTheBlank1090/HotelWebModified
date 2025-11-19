<?php
 
include 'db.php';
header('Content-Type: application/json');

$userID = $_POST['user_id'] ?? 0;
$username = $_POST['username'] ?? '';
$fullname = $_POST['fullname'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? ''; // Mật khẩu (có thể trống)
$role = $_POST['role'] ?? '';

if (empty($userID) || empty($username) || empty($fullname) || empty($email) || empty($role)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ các trường bắt buộc (*).']);
    exit;
}

try {
    // Kiểm tra trùng lặp (trừ chính user này)
    $sqlCheck = "SELECT UserID FROM Users WHERE (Email = ? OR Username = ?) AND UserID != ?";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->execute([$email, $username, $userID]);
    if ($stmtCheck->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email hoặc Tên đăng nhập đã bị trùng với tài khoản khác.']);
        exit;
    }

    if (!empty($password)) {
        //ập nhật CẢ MẬT KHẨU
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $sql = "UPDATE Users SET Username = ?, FullName = ?, Email = ?, Role = ?, PasswordHash = ? 
                WHERE UserID = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $fullname, $email, $role, $hashedPassword, $userID]);
    } else {
        //KHÔNG cập nhật mật khẩu
        $sql = "UPDATE Users SET Username = ?, FullName = ?, Email = ?, Role = ? 
                WHERE UserID = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $fullname, $email, $role, $userID]);
    }

    echo json_encode(['success' => true, 'message' => 'Cập nhật tài khoản ' . $fullname . ' thành công!']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
}
?>