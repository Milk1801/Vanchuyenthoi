<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

$host = "localhost";
$db_name = "quanlyvanchuyen";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $exception) {
    echo json_encode(["success" => false, "message" => "Lỗi kết nối CSDL: " . $exception->getMessage()]);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

// Đổi từ email sang ma_tai_khoan
if(!empty($data->ma_tai_khoan) && !empty($data->mat_khau)) {
    $ma_tai_khoan = $data->ma_tai_khoan;
    $mat_khau = $data->mat_khau;

    // Sửa lại câu lệnh SQL WHERE t.ma_tai_khoan = :ma_tai_khoan
    $query = "SELECT t.ma_tai_khoan, t.ho_ten, t.trang_thai, q.ten_quyen 
              FROM tai_khoan t 
              LEFT JOIN quyen q ON t.ma_quyen = q.ma_quyen 
              WHERE t.ma_tai_khoan = :ma_tai_khoan AND t.mat_khau = :mat_khau LIMIT 1";
              
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":ma_tai_khoan", $ma_tai_khoan);
    $stmt->bindParam(":mat_khau", $mat_khau);
    $stmt->execute();

    if($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row['trang_thai'] === 'Tạm khóa') {
            echo json_encode(["success" => false, "message" => "Tài khoản của bạn đã bị khóa!"]);
        } else {
            echo json_encode([
                "success" => true, 
                "message" => "Đăng nhập thành công!",
                "user" => [
                    "id" => $row['ma_tai_khoan'],
                    "ho_ten" => $row['ho_ten'],
                    "chuc_vu" => $row['ten_quyen']
                ]
            ]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Mã tài khoản hoặc mật khẩu không chính xác!"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Vui lòng nhập đầy đủ Mã tài khoản và Mật khẩu!"]);
}
?>