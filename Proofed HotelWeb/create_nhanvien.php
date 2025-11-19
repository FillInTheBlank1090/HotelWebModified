<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'db.php';

$email = 'nhanvien@gmail.com';
$username = 'nhanvien';
$password = 'admin123';
$fullName = 'Nhân Viên A';
$role = 'Nhân viên';

try {
    // 1. Mã hóa mật khẩu
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    if (!$hashedPassword) {
        throw new Exception("Không thể hash mật khẩu.");
    }
    echo "Đã mã hóa mật khẩu";

    // 2. XÓA tài khoản cũ (nếu có)
    // xóa dựa trên Email VÀ Username
    $sqlDelete = "DELETE FROM Users WHERE Email = ? OR Username = ?";
    $stmtDelete = $pdo->prepare($sqlDelete);
    $stmtDelete->execute([$email, $username]);
    echo "Đã xóa " . $stmtDelete->rowCount() . " tài khoản cũ.<br>";

    // 3. THÊM tài khoản mới
    $sqlInsert = "INSERT INTO Users (Username, PasswordHash, FullName, Role, Email) 
                  VALUES (?, ?, ?, ?, ?)";
    $stmtInsert = $pdo->prepare($sqlInsert);
    $stmtInsert->execute([$username, $hashedPassword, $fullName, $role, $email]);

    echo "------------------------------------<br>";
    echo "TẠO THÀNH CÔNG.";
    echo "Đã tạo tài khoản nhân viên thành công.<br>";
    echo "<strong>Email (dùng để đăng nhập):</strong> " . $email . "<br>";
    echo "<strong>Mật khẩu:</strong> " . $password . "<br>";
    echo "<strong>Vai trò:</strong> " . $role . "<br>";

} catch (Exception $e) {
    echo "LỖI NGHIÊM TRỌNG";
    echo $e->getMessage();
}
?>