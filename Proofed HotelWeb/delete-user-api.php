<?php
session_start();

include 'db.php';
header('Content-Type: application/json');

$userID = $_POST['user_id'] ?? 0;

if (empty($userID)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng chọn tài khoản để xóa.']);
    exit;
}


if ($userID == $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Bạn không thể tự xóa tài khoản Quản trị viên đang được sử dụng.']);
    exit;
}

try {
    $pdo->beginTransaction();
    $sqlUnlink = "UPDATE Customer SET UserID = NULL WHERE UserID = ?";
    $stmtUnlink = $pdo->prepare($sqlUnlink);
    $stmtUnlink->execute([$userID]);

    $sqlDelete = "DELETE FROM Users WHERE UserID = ?";
    $stmtDelete = $pdo->prepare($sqlDelete);
    $stmtDelete->execute([$userID]);

    $pdo->commit();

    if ($stmtDelete->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Xóa tài khoản thành công!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy tài khoản để xóa.']);
    }

} catch (Exception $e) {
    
    $pdo->rollBack();
    
    
    echo json_encode(['success' => false, 'message' => 'Lỗi CSDL khi xóa: ' . $e->getMessage()]);
}
?>