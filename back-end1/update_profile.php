<?php
// 1. Cho phép tất cả các nguồn truy cập (Fix lỗi CORS)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// 2. Phản hồi cho các yêu cầu kiểm tra trước (Preflight request) của trình duyệt
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

header("Content-Type: application/json; charset=UTF-8");

$host = "localhost"; $db_name = "quanlyvanchuyen"; $username = "root"; $password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->ma_tai_khoan)) {
        // Câu lệnh SQL chuẩn theo file SQL bạn gửi
        $query = "UPDATE tai_khoan 
                  SET ho_ten = :ho_ten, 
                      email = :email, 
                      ngay_sinh = :ngay_sinh 
                  WHERE ma_tai_khoan = :ma_tai_khoan";
        
        $stmt = $conn->prepare($query);

        $stmt->bindParam(":ho_ten", $data->ho_ten);
        $stmt->bindParam(":email", $data->email);
        $stmt->bindParam(":ngay_sinh", $data->ngay_sinh);
        $stmt->bindParam(":ma_tai_khoan", $data->ma_tai_khoan);

        if($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Cập nhật thành công!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Lỗi thực thi SQL."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Thiếu mã tài khoản!"]);
    }
} catch(PDOException $e) {
    // Trả về lỗi Database chi tiết để mình bắt bệnh
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Lỗi DB: " . $e->getMessage()]);
}
?>