<?php
include 'db.php';
header('Content-Type: application/json');

// Lấy năm từ request (mặc định là năm hiện tại)
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

try {
    // Truy vấn: Nhóm tổng tiền theo tháng trong năm đã chọn
    // CHỈ tính các hóa đơn đã được tạo (trong bảng Invoice)
    $sql = "SELECT 
                MONTH(InvoiceDate) as Month, 
                SUM(TotalAmount) as Total 
            FROM Invoice 
            WHERE YEAR(InvoiceDate) = ? 
            GROUP BY MONTH(InvoiceDate)
            ORDER BY Month ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$year]);
    $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // Trả về dạng [Tháng => Tổng]

    // Chuẩn bị dữ liệu đủ 12 tháng (tháng nào không có doanh thu thì bằng 0)
    $data = [];
    for ($i = 1; $i <= 12; $i++) {
        $data[] = [
            'month' => "Tháng $i",
            'revenue' => isset($results[$i]) ? (float)$results[$i] : 0
        ];
    }

    echo json_encode(['success' => true, 'data' => $data, 'year' => $year]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi CSDL: ' . $e->getMessage()]);
}
?>