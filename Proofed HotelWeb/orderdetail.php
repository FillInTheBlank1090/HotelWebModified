<?php 
session_start(); 
// --- BẮT ĐẦU CODE PHP ĐỂ LẤY DỮ LIỆU ---

// Bật hiển thị lỗi để debug
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. Lấy Booking ID từ URL
$bookingID = $_GET['booking_id'] ?? 0;
if (empty($bookingID)) {
    die("Lỗi: Không tìm thấy mã đơn đặt phòng.");
}

try {
    // 2. Kết nối CSDL
    include 'db.php';

    // 3. Truy vấn SQL để lấy tất cả thông tin
    $sql = "SELECT 
                c.FullName, c.CCCD, c.Phone,
                u.Email,
                b.BookingID, b.CheckInDate, b.CheckOutDate,
                r.RoomName,
                b.TotalAmount AS RoomCostBase -- Lấy tiền phòng gốc từ Booking
            FROM Booking b
            JOIN Customer c ON b.CustomerID = c.CustomerID
            JOIN Room r ON b.RoomID = r.RoomID
            LEFT JOIN Users u ON c.UserID = u.UserID
            WHERE b.BookingID = ?";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$bookingID]);
    $invoice = $stmt->fetch();

    if (!$invoice) {
        die("Không tìm thấy thông tin hóa đơn cho mã: " . $bookingID);
    }

    // 4. Tính toán chi phí (dựa trên logic HTML tĩnh: +10% phí DV)
    $checkIn_dt = new DateTime($invoice['CheckInDate']);
    
    // Kiểm tra CheckOutDate có NULL không
    if (empty($invoice['CheckOutDate'])) {
        die("Lỗi: Đơn đặt phòng này chưa có ngày trả phòng. Không thể in hóa đơn.");
    }
    $checkOut_dt = new DateTime($invoice['CheckOutDate']);
    
    // Tính số đêm
    $interval = $checkIn_dt->diff($checkOut_dt);
    $numNights = $interval->days;
    if ($numNights <= 0) $numNights = 1; // Tối thiểu 1 đêm

    // Tính toán chi phí
    $roomCost = $invoice['RoomCostBase']; // Đây là tổng tiền phòng (ví dụ: 150k * 2 đêm = 300k)
    $serviceFee = $roomCost * 0.10; // 10% phí dịch vụ
    $totalCost = $roomCost + $serviceFee;

} catch (Exception $e) {
    die("Lỗi CSDL khi lấy hóa đơn: " . $e->getMessage());
}

// --- KẾT THÚC CODE PHP ---
?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta charset="utf-8" />
    <link rel="stylesheet" href="globals.css" />
    <link rel="stylesheet" href="styleguide.css" />
    <link rel="stylesheet" href="orderdetail.css" />
  </head>
  <body>
    <div class="page-container">
      <header class="navigation">
        <div class="nav-content-wrapper">
          <a href="mainmenu.php" class="nav-logo">AA Hotel</a>
          <div class="nav-items">
    <a href="reserveroom.php" class="nav-link">Đặt Phòng</a>
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

      <main class="main-content">
        <h1 class="page-title">Chi Tiết Hóa Đơn #<?php echo $invoice['BookingID']; ?></h1>
        <div class="order-container">
          
          <section class="order-section">
            <h2>Thông Tin Khách Hàng</h2>
            <div class="info-grid">
              <div class="info-item"><strong>Họ và Tên:</strong> <?php echo htmlspecialchars($invoice['FullName']); ?></div>
              <div class="info-item"><strong>CCCD:</strong> <?php echo htmlspecialchars($invoice['CCCD'] ?? 'N/A'); ?></div>
              <div class="info-item"><strong>Email:</strong> <?php echo htmlspecialchars($invoice['Email'] ?? 'N/A'); ?></div>
              <div class="info-item"><strong>Số Điện Thoại:</strong> <?php echo htmlspecialchars($invoice['Phone'] ?? 'N/A'); ?></div>
            </div>
          </section>

          <section class="order-section">
            <h2>Chi Tiết Đặt Phòng</h2>
            <div class="info-grid">
                <div class="info-item"><strong>Mã Đơn:</strong> #<?php echo $invoice['BookingID']; ?></div>
                <div class="info-item"><strong>Ngày Nhận Phòng:</strong> <?php echo $checkIn_dt->format('d/m/Y'); ?></div>
                <div class="info-item"><strong>Ngày Trả Phòng:</strong> <?php echo $checkOut_dt->format('d/m/Y'); ?></div>
                <div class="info-item"><strong>Số Đêm:</strong> <?php echo $numNights; ?></div>
            </div>
          </section>

          <section class="order-section">
            <h2>Chi Tiết Chi Phí</h2>
            <div class="cost-details">
                <div class="cost-item">
                    <span><?php echo htmlspecialchars($invoice['RoomName']); ?> x <?php echo $numNights; ?> đêm</span>
                    <span><?php echo number_format($roomCost); ?> VNĐ</span>
                </div>
                <div class="cost-item">
                    <span>Phí dịch vụ (10%)</span>
                    <span><?php echo number_format($serviceFee); ?> VNĐ</span>
                </div>
                <div class="cost-item total">
                    <span>Tổng Cộng</span>
                    <span><?php echo number_format($totalCost); ?> VNĐ</span>
                </div>
            </div>
          </section>

          <div class="action-buttons">
            <button class="btn-secondary" id="print-button">In Hóa Đơn</button>
            <button class="btn-primary" id="payment-button">Xác Nhận Thanh Toán</button>
          </div>
        </div>
      </main>

      <footer class="footer">
        </footer>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const printButton = document.getElementById('print-button');
            const paymentButton = document.getElementById('payment-button');
            const bookingID = <?php echo $invoice['BookingID']; ?>; // Lấy BookingID từ PHP

            // 1. Nút In Hóa Đơn
            printButton.addEventListener('click', function() {
                // Ẩn các nút trước khi in
                printButton.style.display = 'none';
                paymentButton.style.display = 'none';
                // Ẩn thanh navigation và footer
                document.querySelector('.navigation').style.display = 'none';
                document.querySelector('.footer').style.display = 'none';
                
                window.print(); // Mở hộp thoại in
                
                // Hiển thị lại các nút sau khi in
                printButton.style.display = 'inline-flex';
                paymentButton.style.display = 'inline-flex';
                document.querySelector('.navigation').style.display = 'block';
                document.querySelector('.footer').style.display = 'block';
            });

            // 2. Nút Xác Nhận Thanh Toán
            paymentButton.addEventListener('click', function() {
                if (!confirm('Bạn có chắc chắn muốn xác nhận thanh toán cho hóa đơn này?\nHành động này sẽ tạo một bản ghi hóa đơn chính thức.')) {
                    return;
                }
                
                paymentButton.disabled = true;
                paymentButton.textContent = 'Đang xử lý...';

                const formData = new FormData();
                formData.append('booking_id', bookingID);

                fetch('create-invoice-api.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        paymentButton.textContent = 'Đã Xác Nhận';
                        // Không vô hiệu hóa hẳn, để phòng trường hợp xem lại
                        // paymentButton.disabled = true; 
                    } else {
                         paymentButton.disabled = false;
                         paymentButton.textContent = 'Xác Nhận Thanh Toán';
                    }
                })
                .catch(error => {
                    console.error('Fetch Error:', error);
                    alert('Lỗi kết nối khi xác nhận thanh toán.');
                    paymentButton.disabled = false;
                    paymentButton.textContent = 'Xác Nhận Thanh Toán';
                });
            });
        });
    </script>
  </body>
</html>