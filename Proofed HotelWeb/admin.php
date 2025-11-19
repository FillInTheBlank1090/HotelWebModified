<?php 
$currentPage = 'admin';
session_start(); 
// --- BẢO VỆ TRANG ADMIN ---
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Admin') {
    $_SESSION['flash_message'] = 'Bạn phải đăng nhập với tư cách Quản trị viên để truy cập trang này.';
    header('Location: login.php');
    exit;
}
// --- KẾT THÚC BẢO VỆ ---

// --- LẤY DỮ LIỆU THỐNG KÊ TỔNG QUÁT (Giữ nguyên) ---
include 'db.php'; 
try {
    $stmt_rev = $pdo->query("SELECT SUM(TotalAmount) FROM Invoice");
    $totalRevenue = $stmt_rev->fetchColumn() ?: 0; 

    $stmt_guests = $pdo->query("SELECT COUNT(*) FROM Customer"); 
    $totalGuests = $stmt_guests->fetchColumn() ?: 0;

    $stmt_bookings = $pdo->query("SELECT COUNT(*) FROM Booking WHERE Status = 'Đang ở'");
    $totalBookings = $stmt_bookings->fetchColumn() ?: 0;
} catch (Exception $e) {
    $totalRevenue = 0; $totalGuests = 0; $totalBookings = 0;
}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta charset="utf-8" />
    <link rel="stylesheet" href="globals.css" />
    <link rel="stylesheet" href="styleguide.css" />
    <link rel="stylesheet" href="admin.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  </head>
  <body>
    <div class="page-container">
      <header class="navigation">
        <div class="nav-content-wrapper">
          <a href="mainmenu.php" class="nav-logo <?php if ($currentPage === 'mainmenu') echo 'active'; ?>">AA Hotel</a>
          <div class="nav-items">
            <a href="reserveroom.php" class="nav-link <?php if ($currentPage === 'reserveroom') echo 'active'; ?>">Đặt Phòng</a>
            <?php if (isset($_SESSION['user_id'])): 
                $role = $_SESSION['user_role'];
            ?>
                <span class="nav-link-welcome">Xin chào, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</span>
                <?php if ($role === 'Admin' || $role === 'Nhân viên'): ?>
                    <a href="staff.php" class="nav-link <?php if ($currentPage === 'staff') echo 'active'; ?>">Panel Nhân Viên</a>
                <?php endif; ?>
                <?php if ($role === 'Admin'): ?>
                    <a href="admin.php" class="nav-link <?php if ($currentPage === 'admin') echo 'active'; ?>">Panel Quản Trị</a>
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
        <h1 class="panel-title">Panel Quản Trị Viên</h1>
        <div class="panel">
          
          <section class="stats-section">
            <div class="stat-card">
              <div class="stat-title revenue">Tổng Doanh Thu</div>
              <div class="stat-value"><?php echo number_format($totalRevenue); ?> VNĐ</div>
            </div>
            <div class="stat-card">
              <div class="stat-title guests">Lượng Khách</div>
              <div class="stat-value"><?php echo number_format($totalGuests); ?></div>
            </div>
            <div class="stat-card">
              <div class="stat-title bookings">Lượng Đặt Trước</div>
              <div class="stat-value"><?php echo number_format($totalBookings); ?></div>
            </div>
          </section>

          <section class="management-section" style="margin-bottom: 40px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>Biểu Đồ Doanh Thu</h2>
                <div class="input-group" style="flex-direction: row; align-items: center; gap: 10px;">
                    <label for="chart-year">Chọn Năm:</label>
                    <select id="chart-year" style="height: 40px; padding: 0 10px; border-radius: 5px; border: 1px solid #ccc;">
                        </select>
                </div>
            </div>
            <div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #000;">
                <canvas id="revenueChart" style="max-height: 400px;"></canvas>
            </div>
          </section>

          <section class="management-section">
            <h2>Thêm Nhân Viên</h2>
            <div class="employee-form" id="employee-form">
              <div class="input-group"><label>Mã Nhân Viên (UserID)</label><input type="text" id="user-id-input" readonly placeholder="Tự động điền khi chọn"/></div>
              <div class="input-group"><label>Họ và Tên*</label><input type="text" id="user-fullname-input" placeholder="Nguyễn Văn A"/></div>
              <div class="input-group"><label>Email* (Dùng để đăng nhập)</label><input type="email" id="user-email-input" placeholder="NgVanA@gmail.com"/></div>
              <div class="input-group"><label>Tên đăng nhập (Username)*</label><input type="text" id="user-username-input" placeholder="nguyenvana"/></div>
              <div class="input-group"><label>Mật Khẩu*</label><input type="password" id="user-password-input" placeholder="Để trống nếu không muốn đổi"/></div>
              <div class="input-group">
                <label>Quyền Hạn*</label>
                <select id="user-role-select">
                    <option value="Customer">Khách hàng (Customer)</option>
                    <option value="Nhân viên">Nhân viên (Nhân viên)</option>
                    <option value="Admin">Quản trị (Admin)</option>
                </select>
              </div>
            </div>
            <div class="action-buttons">
              <button class="btn-primary" id="add-user-button">Thêm</button>
              <button class="btn-primary" id="edit-user-button">Chỉnh Sửa</button>
              <button class="btn-primary" id="delete-user-button">Xóa</button>
            </div>
          </section>

          <section class="management-section">
            <h2>Quản Lý Nhân Viên</h2>
            <div class="table-container">
              <table>
                <thead>
                  <tr>
                    <th>Mã NV (UserID)</th>
                    <th>Họ và Tên</th>
                    <th>Email</th>
                    <th>Quyền Hạn (Role)</th>
                  </tr>
                </thead>
                <tbody id="user-table-body">
                  </tbody>
              </table>
            </div>
            <div class="search-container">
                <input type="text" class="search-input" id="user-search-input" placeholder="Nhập tên hoặc email...">
                <button class="btn-primary" id="user-search-button">Tìm Kiếm</button>
            </div>
          </section>
        </div>
      </main>

      <footer class="footer">
        <div class="footer-content">
            <div class="footer-logo">Khách Sạn AA</div>
            <div class="footer-buttons-right" style="display: flex; gap: 16px;">
                 <a href="mainmenu.php"><button class="footer-button"><div class="footer-button-text">Về Lại Trang Chủ</div></button></a>
            </div>
        </div>
      </footer>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // --- 1. XỬ LÝ BIỂU ĐỒ (CODE MỚI) ---
        const chartYearSelect = document.getElementById('chart-year');
        const ctx = document.getElementById('revenueChart').getContext('2d');
        let myChart = null;

        // Khởi tạo Select Năm (Ví dụ: 5 năm gần đây)
        const currentYear = new Date().getFullYear();
        for (let i = currentYear; i >= currentYear - 4; i--) {
            const option = document.createElement('option');
            option.value = i;
            option.text = i;
            chartYearSelect.appendChild(option);
        }

        // Hàm vẽ biểu đồ
        function renderChart(labels, data, year) {
            if (myChart) myChart.destroy(); // Hủy biểu đồ cũ nếu có

            myChart = new Chart(ctx, {
                type: 'bar', // Loại biểu đồ: 'bar' (cột), 'line' (đường)
                data: {
                    labels: labels,
                    datasets: [{
                        label: `Doanh thu năm ${year} (VNĐ)`,
                        data: data,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)', // Màu cột
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString('vi-VN') + ' ₫';
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += context.parsed.y.toLocaleString('vi-VN') + ' ₫';
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Hàm lấy dữ liệu từ API
        function fetchRevenueStats(year) {
            fetch(`get-revenue-stats-api.php?year=${year}`)
                .then(response => response.json())
                .then(response => {
                    if (response.success) {
                        // Tách dữ liệu cho biểu đồ
                        const labels = response.data.map(item => item.month);
                        const revenues = response.data.map(item => item.revenue);
                        renderChart(labels, revenues, year);
                    } else {
                        console.error("Lỗi lấy dữ liệu biểu đồ:", response.message);
                    }
                })
                .catch(error => console.error("Lỗi kết nối:", error));
        }

        // Tải biểu đồ lần đầu
        fetchRevenueStats(currentYear);

        // Sự kiện khi đổi năm
        chartYearSelect.addEventListener('change', function() {
            fetchRevenueStats(this.value);
        });


        // --- 2. XỬ LÝ QUẢN LÝ NHÂN VIÊN (CODE CŨ GIỮ NGUYÊN) ---
        const userTableBody = document.getElementById('user-table-body');
        const userSearchInput = document.getElementById('user-search-input');
        const userSearchButton = document.getElementById('user-search-button');
        const userIdInput = document.getElementById('user-id-input');
        const userFullnameInput = document.getElementById('user-fullname-input');
        const userEmailInput = document.getElementById('user-email-input');
        const userUsernameInput = document.getElementById('user-username-input');
        const userPasswordInput = document.getElementById('user-password-input');
        const userRoleSelect = document.getElementById('user-role-select');
        const addButton = document.getElementById('add-user-button');
        const editButton = document.getElementById('edit-user-button');
        const deleteButton = document.getElementById('delete-user-button');

        fetchUsers();

        function populateUserTable(users) {
            userTableBody.innerHTML = '';
            if (users.length > 0) {
                users.forEach(user => {
                    const row = document.createElement('tr');
                    row.dataset.userId = user.UserID;
                    row.dataset.fullName = user.FullName;
                    row.dataset.email = user.Email;
                    row.dataset.role = user.Role;
                    row.dataset.username = user.Username;
                    row.style.cursor = 'pointer';
                    row.innerHTML = `
                        <td>${user.UserID}</td>
                        <td>${escapeHtml(user.FullName)}</td>
                        <td>${escapeHtml(user.Email)}</td>
                        <td>${escapeHtml(user.Role)}</td>
                    `;
                    userTableBody.appendChild(row);
                });
            } else {
                userTableBody.innerHTML = '<tr><td colspan="4" style="text-align: center;">Không tìm thấy nhân viên.</td></tr>';
            }
        }

        function fetchUsers() {
            fetch('get-users-api.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        populateUserTable(data.users);
                    } else {
                        alert('Lỗi tải danh sách: ' + data.message);
                    }
                })
                .catch(error => alert('Lỗi kết nối khi tải danh sách.'));
        }
        
        function clearUserFormFields() {
            userIdInput.value = '';
            userFullnameInput.value = '';
            userEmailInput.value = '';
            userUsernameInput.value = '';
            userPasswordInput.value = '';
            userRoleSelect.value = 'Customer';
        }

        userTableBody.addEventListener('click', function(event) {
            const row = event.target.closest('tr');
            if (!row || !row.dataset.userId) return;
            userIdInput.value = row.dataset.userId;
            userFullnameInput.value = row.dataset.fullName;
            userEmailInput.value = row.dataset.email;
            userRoleSelect.value = row.dataset.role;
            userUsernameInput.value = row.dataset.username || '';
            userPasswordInput.placeholder = "Để trống nếu không muốn đổi";
        });
        
        userSearchButton.addEventListener('click', function() {
            const searchTerm = userSearchInput.value.trim();
            fetch(`find-user-api.php?searchTerm=${encodeURIComponent(searchTerm)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        populateUserTable(data.users);
                    } else {
                        alert('Lỗi tìm kiếm: ' + data.message);
                    }
                })
                .catch(error => alert('Lỗi kết nối khi tìm kiếm.'));
        });

        addButton.addEventListener('click', function() {
            if (!confirm('Bạn có chắc muốn thêm tài khoản mới này?')) return;
            const formData = new FormData();
            formData.append('username', userUsernameInput.value);
            formData.append('fullname', userFullnameInput.value);
            formData.append('email', userEmailInput.value);
            formData.append('password', userPasswordInput.value);
            formData.append('role', userRoleSelect.value);

            fetch('create-user-api.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) { fetchUsers(); clearUserFormFields(); }
                })
                .catch(error => alert('Lỗi kết nối khi thêm.'));
        });

        editButton.addEventListener('click', function() {
            const userID = userIdInput.value;
            if (!userID) { alert('Vui lòng chọn một nhân viên từ danh sách để sửa.'); return; }
            if (!confirm('Bạn có chắc muốn cập nhật tài khoản này?')) return;
            const formData = new FormData();
            formData.append('user_id', userID);
            formData.append('username', userUsernameInput.value);
            formData.append('fullname', userFullnameInput.value);
            formData.append('email', userEmailInput.value);
            formData.append('password', userPasswordInput.value);
            formData.append('role', userRoleSelect.value);
            
            fetch('update-user-api.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) { fetchUsers(); clearUserFormFields(); }
                })
                .catch(error => alert('Lỗi kết nối khi sửa.'));
        });

        deleteButton.addEventListener('click', function() {
            const userID = userIdInput.value;
            if (!userID) { alert('Vui lòng chọn một nhân viên từ danh sách để xóa.'); return; }
            if (!confirm(`BẠN CÓ CHẮC CHẮN MUỐN XÓA TÀI KHOẢN (UserID: ${userID}) NÀY KHÔNG?`)) return;
            const formData = new FormData();
            formData.append('user_id', userID);

            fetch('delete-user-api.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) { fetchUsers(); clearUserFormFields(); }
                })
                .catch(error => alert('Lỗi kết nối khi xóa.'));
        });
        
        function escapeHtml(unsafe) {
            if (unsafe === null || unsafe === undefined) return '';
            return unsafe.toString()
                 .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
                 .replace(/"/g, "&quot;").replace(/'/g, "&#039;");
        }
    });
    </script>
  </body>
</html>