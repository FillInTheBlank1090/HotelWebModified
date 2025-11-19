<?php 
session_start(); 

// --- BƯỚC 1: THÊM CODE PHP ĐỂ LẤY GIÁ ---

// 1. Đặt giá mặc định (dự phòng nếu CSDL lỗi hoặc chưa có phòng)
$prices = [
    'Đơn' => 150000,
    'Đôi' => 250000,
    'VIP' => 400000
];

try {
    // 2. Kết nối CSDL
    include 'db.php';
    
    // 3. Lấy giá mới nhất cho mỗi loại phòng
    // Dùng GROUP BY để lấy giá đại diện (vì tính năng "cập nhật theo loại" của bạn làm cho chúng giống hệt nhau)
    $sql = "SELECT RoomType, MIN(Price) AS Price FROM Room WHERE RoomType IN ('Đơn', 'Đôi', 'VIP') GROUP BY RoomType";
    $stmt = $pdo->query($sql);
    
    // 4. Ghi đè giá mặc định bằng giá lấy từ CSDL
    while ($row = $stmt->fetch()) {
        if (isset($prices[$row['RoomType']])) {
            $prices[$row['RoomType']] = $row['Price'];
        }
    }

} catch (Exception $e) {
    // Nếu kết nối CSDL thất bại, $prices mặc định ở trên sẽ được sử dụng
    // Bạn có thể ghi log lỗi ở đây nếu muốn: error_log($e->getMessage());
}

// --- KẾT THÚC BƯỚC 1 ---

?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta charset="utf-8" />
    <link rel="stylesheet" href="globals.css" />
    <link rel="stylesheet" href="styleguide.css" />
    <link rel="stylesheet" href="resroom.css" />
  </head>
  <body>
    <div class="page-container">
      <header class="navigation">
        <div class="nav-content-wrapper">
          <a href="mainmenu.php" class="nav-logo">AA Hotel</a>
          <div class="nav-items">
    <a href="reserveroom.php" class="nav-link active">Đặt Phòng</a>

    <?php if (isset($_SESSION['user_id'])): 
        // Lấy vai trò để dễ kiểm tra
        $role = $_SESSION['user_role'];
    ?>
        <span class="nav-link-welcome">Xin chào, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</span>
        
        <?php 
        // Hiển thị "Panel Nhân Viên" nếu là Admin HOẶC Nhân viên
        if ($role === 'Admin' || $role === 'Nhân viên'): ?>
            <a href="staff.php" class="nav-link">Panel Nhân Viên</a>
        <?php endif; ?>

        <?php 
        // Chỉ hiển thị "Panel Quản Trị" nếu là Admin
        if ($role === 'Admin'): ?>
            <a href="admin.php" class="nav-link">Panel Quản Trị</a>
        <?php endif; ?>

        <a href="logout.php" class="nav-button"><div class="nav-button-text">Đăng Xuất</div></a>

    <?php else: ?>
        <a href="register.php" class="nav-link">Đăng Ký</a>
        <a href="login.php" class="nav-button"><div class="nav-button-text">Đăng Nhập</div></a>
    <?php endif; ?>
</div>
        </div>
      </header>
      
      <main class="main-content-full-width">
        <div class="hero-header">
            <div class="hero-dimmer"></div>
            <h1 class="hero-title">Đặt Phòng</h1>
        </div>

        <div class="room-list">
            <div class="room-item">
                <div class="room-image room-image-don"></div>
                <div class="room-info">
                    <h2 class="room-name">Phòng Đơn</h2>
                    <p class="room-description">1 Giường Lớn<br />Diện Tích: 35 m²</p>
                    <div class="room-actions">
                        <a href="booking.php" class="action-button primary">Đặt Phòng</a>
                        <div class="action-button secondary"><?php echo number_format($prices['Đơn']); ?> VNĐ/Ngày</div>
                    </div>
                </div>
            </div>

            <div class="room-item reverse">
                <div class="room-image room-image-doi"></div>
                <div class="room-info">
                    <h2 class="room-name">Phòng Đôi</h2>
                    <p class="room-description">2 Giường Lớn<br />Diện Tích: 85 m²<br />View Ra Biển</p>
                    <div class="room-actions">
                        <a href="booking.php" class="action-button primary">Đặt Phòng</a>
                        <div class="action-button secondary"><?php echo number_format($prices['Đôi']); ?> VNĐ/Ngày</div>
                    </div>
                </div>
            </div>

            <div class="room-item">
                <div class="room-image room-image-vip"></div>
                <div class="room-info">
                    <h2 class="room-name">Phòng VIP</h2>
                    <p class="room-description">1 Giường Lớn<br />Diện Tích: 55 m²<br />View ra Biển<br />Được Phục Vụ Các Bữa Ăn</p>
                    <div class="room-actions">
                        <a href="booking.php" class="action-button primary">Đặt Phòng</a>
                        <div class="action-button secondary"><?php echo number_format($prices['VIP']); ?> VNĐ/Ngày</div>
                    </div>
                </div>
            </div>
        </div>
      </main>

      <footer class="footer">
        <div class="footer-content">
            <div class="footer-logo">Khách Sạn AA</div>
            <a href="admin.php"><button class="footer-button"><div class="footer-button-text">Quản Trị Hệ Thống</div></button></a>
        </div>
      </footer>
    </div>
  </body>
</html>