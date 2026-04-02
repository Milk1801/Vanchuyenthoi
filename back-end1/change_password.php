<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { exit; }

$host = "localhost"; $db_name = "quanlyvanchuyen"; $username = "root"; $password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->ma_tai_khoan) && !empty($data->current_pass) && !empty($data->new_pass)) {
        
        // 1. Kiểm tra mật khẩu cũ có đúng không
        $check_sql = "SELECT mat_khau FROM tai_khoan WHERE ma_tai_khoan = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->execute([$data->ma_tai_khoan]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || $user['mat_khau'] !== $data->current_pass) {
            echo json_encode(["success" => false, "message" => "Mật khẩu hiện tại không chính xác!"]);
            exit;
        }

        // 2. Nếu đúng thì tiến hành cập nhật mật khẩu mới
        $update_sql = "UPDATE tai_khoan SET mat_khau = :new_pass WHERE ma_tai_khoan = :id";
        $up_stmt = $conn->prepare($update_sql);
        $up_stmt->bindParam(":new_pass", $data->new_pass);
        $up_stmt->bindParam(":id", $data->ma_tai_khoan);

        if($up_stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Đổi mật khẩu thành công!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Lỗi thực thi SQL."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Vui lòng nhập đầy đủ thông tin."]);
    }
} catch(PDOException $e) {
    echo json_encode(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
}
?>