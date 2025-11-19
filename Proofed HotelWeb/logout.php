<?php
// Luôn bắt đầu session ở đầu file
session_start();

// Xóa tất cả các biến trong session
session_unset();

// Hủy hoàn toàn session
session_destroy();

// Chuyển hướng người dùng về lại trang đăng nhập
header("Location: login.php");
exit;
?>