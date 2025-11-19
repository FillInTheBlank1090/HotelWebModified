
I. Yêu cầu

* Cài đặt XAMPP (phiên bản hỗ trợ PHP 8.1+) và MySQL WorkBench

II. Cài đặt Mã nguồn

1.  Tải/copy toàn bộ dự án này về máy.
2.  Di chuyển thư mục dự án (ví dụ: `Proofed HotelWeb`) vào thư mục `htdocs` của XAMPP.
    Đường dẫn ví dụ: `C:\xampp\htdocs\Proofed HotelWeb`
3.  Khởi động module Apache và MySQL trong XAMPP Control Panel.

III. Cài đặt Cơ sở dữ liệu (Database)
1.  Tải và cài đặt:Tải MySQL Workbench từ trang chủ chính thức: `https://www.mysql.com/products/workbench/`
2.  Kết nối:
    * Mở Workbench.
    * Tại màn hình chính, nhấn dấu `+` để tạo kết nối mới.
    * Đặt tên kết nối (ví dụ: "XAMPP Local").
    * Tên người dùng (Username) là `root`.
    * Mật khẩu (Password): Nhấn `Store in Vault...` và nhập mật khẩu CSDL của bạn (ví dụ mật khẩu `123456` như đã ghi trong file db).
    * Nhấn `Test Connection`. Nếu thành công, nhấn `OK` để lưu.
    * Mở kết nối bạn vừa tạo.
3.  Chạy Querry (Tệp SQL):
    * Trong Workbench, nhấn vào menu File>*pen SQL Script...
    * Chọn tệp sql đính kèm
    * Toàn bộ nội dung tệp SQL sẽ hiện ra trong cửa sổ soạn thảo.
    * Execute toàn bộ script để tạo CSDL
    * Quá trình này sẽ tự động tạo CSDL `HotelManagement` và tất cả các bảng.
IV. Cấu hình kết nối (db.php)

1.  Mở tệp `db.php` trong thư mục gốc của dự án.
2.  Đảm bảo các thông tin trong đó khớp với CSDL của bạn

Ví dụ:

    *<?php
$host = '127.0.0.1'; 
$dbname = 'HotelManagement';
$username = 'root'; 
$password = '123456'; 
$charset = 'utf8mb4';
...
?>
Chạy 2 file create_nhanvien.php và create_admin.php bằng cách gọi chúng trên browser để khởi tạo 2 tài khoản đầu tiên được phân quyền staff và admin.
