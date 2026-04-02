<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

$host = "localhost";
$db_name = "quanlyvanchuyen";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Viết câu lệnh SQL lấy toàn bộ tài khoản (Nối với bảng quyen để lấy Tên quyền)
    $query = "SELECT t.ma_tai_khoan, t.ho_ten, t.email, t.trang_thai,t.ngay_sinh, q.ten_quyen 
              FROM tai_khoan t 
              LEFT JOIN quyen q ON t.ma_quyen = q.ma_quyen 
              ORDER BY t.ma_tai_khoan ASC";
              
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    // Lấy toàn bộ dữ liệu trả về dạng mảng (Array)
    $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Trả cục JSON về cho Vue.js
    echo json_encode([
        "success" => true,
        "data" => $accounts
    ]);

} catch(PDOException $exception) {
    echo json_encode(["success" => false, "message" => "Lỗi: " . $exception->getMessage()]);
}
?>