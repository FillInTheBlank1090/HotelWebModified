<?php

// 1. Kết nối CSDL
include 'db.php';

// 2. Thông tin tài khoản admin
$username = 'admin';
$password = 'admin123';
$fullname = 'Quản trị viên';
$email = 'admin@khachsan.com';
$role = 'Admin';

try {
    // 3. Mã hóa mật khẩu
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    // 4. Chuẩn bị câu lệnh INSERT
    $sql = "INSERT INTO Users (Username, PasswordHash, FullName, Role, Email) 
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    
    // 5. Thực thi
    $stmt->execute([$username, $passwordHash, $fullname, $role, $email]);

    echo "<h1>Tài khoản admin đã được tạo thành công!</h1>";
    echo "<p>Username: $username</p>";
    echo "<p>Email: $email</p>";
    echo "<p>Mật khẩu: $password</p>";
    echo "<p>Bản mã hóa (đã lưu vào CSDL): $passwordHash</p>";
    echo "<a href='login.php'>Quay lại trang Đăng nhập</a>";

} catch (Exception $e) {
    echo "LỖI";
    // Kiểm tra lỗi trùng lặp tài khoản
    if ($e->getCode() == 23000) {
        echo "<p>Tài khoản admin đã tồn tại. Hãy đăng nhập.</p>";
    } else {
        echo "<p>Không thể tạo tài khoản: " . $e->getMessage() . "</p>";
    }
}
?>