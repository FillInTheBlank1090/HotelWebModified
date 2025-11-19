<?php 
$currentPage = 'changepass';
session_start(); 
?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta charset="utf-8" />
    <link rel="stylesheet" href="globals.css" />
    <link rel="stylesheet" href="styleguide.css" />
    <link rel="stylesheet" href="changepass.css" />
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
        <?php if ($role === 'Customer'): ?>
            <a href="changepass.php" class="nav-link <?php if ($currentPage === 'changepass') echo 'active'; ?>">Đổi Mật Khẩu</a>
        <?php endif; ?>
        <a href="logout.php" class="nav-button"><div class="nav-button-text">Đăng Xuất</div></a>
    <?php else: ?>
        <a href="register.php" class="nav-link <?php if ($currentPage === 'register') echo 'active'; ?>">Đăng Ký</a>
        <a href="login.php" class="nav-button"><div class="nav-button-text">Đăng Nhập</div></a>
    <?php endif; ?>
</div>
        </div>
      </header>
      
      <main class="main-content">
        <div class="form-container">
          <h1 class="form-title">Đổi Mật Khẩu</h1>
          <form class="form">
            
            <div class="input-group">
              <label for="email">Email Của Bạn*</label>
              <input class="field" type="email" id="email" name="email" placeholder="NguyenVanA@gmail.com" />
            </div>
            <div class="input-group">
              <label for="old_password">Mật Khẩu Cũ</label>
              <input class="field" type="password" id="old_password" name="old_password" placeholder="xxxxxx" />
            </div>
            <div class="input-group">
              <label for="new_password">Mật Khẩu Mới</label>
              <input class="field" type="password" id="new_password" name="new_password" placeholder="xxxxxx" />
            </div>
            <div class="input-group">
              <label for="confirm_password">Xác Nhận Mật Khẩu</label>
              <input class="field" type="password" id="confirm_password" name="confirm_password" placeholder="xxxxxx" />
            </div>
            <button class="submit-button" type="submit">Đổi Mật Khẩu</button>
          </form>
        </div>
      </main>

      <footer class="footer">
        </footer>
    </div>
    
    <script>
        const changePassForm = document.querySelector('.form');
        
        changePassForm.addEventListener('submit', function(event) {
            event.preventDefault(); 
            const formData = new FormData(changePassForm);
            
            fetch('changepassword-api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message); 
                
                if (data.success) {
                    window.location.href = 'login.php'; 
                }
            })
            .catch(error => {
                console.error('Lỗi nghiêm trọng:', error);
                alert('Đã xảy ra lỗi, không thể gửi yêu cầu.');
            });
        });
    </script>
  </body>
</html>