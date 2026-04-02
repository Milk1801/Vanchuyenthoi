<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Xử lý yêu cầu kiểm tra trước (Preflight) để tránh lỗi CORS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

$host = "localhost"; $db_name = "quanlyvanchuyen"; $username = "root"; $password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->ho_ten) && !empty($data->email)) {
        if (!empty($data->ma_tai_khoan)) {
            // --- TRƯỜNG HỢP: SỬA ---
            // Bổ sung ngay_sinh=:ngay_sinh vào câu lệnh UPDATE
            $sql = "UPDATE tai_khoan 
                    SET ho_ten=:ho_ten, 
                        email=:email, 
                        ngay_sinh=:ngay_sinh, 
                        ma_quyen=:ma_quyen, 
                        trang_thai=:trang_thai";
            
            if(!empty($data->mat_khau)) $sql .= ", mat_khau=:mat_khau"; 
            $sql .= " WHERE ma_tai_khoan=:ma_tai_khoan";
        } else {
            // --- TRƯỜNG HỢP: THÊM MỚI ---
            // Bổ sung ngay_sinh vào danh sách cột và giá trị
            $sql = "INSERT INTO tai_khoan (ho_ten, email, ngay_sinh, mat_khau, ma_quyen, trang_thai) 
                    VALUES (:ho_ten, :email, :ngay_sinh, :mat_khau, :ma_quyen, :trang_thai)";
        }

        $stmt = $conn->prepare($sql);
        
        // Bind các tham số chung
        $stmt->bindParam(":ho_ten", $data->ho_ten);
        $stmt->bindParam(":email", $data->email);
        $stmt->bindParam(":ngay_sinh", $data->ngay_sinh); // <--- MỚI THÊM DÒNG NÀY
        $stmt->bindParam(":ma_quyen", $data->ma_quyen);
        $stmt->bindParam(":trang_thai", $data->trang_thai);
        
        // Bind mã tài khoản nếu là Sửa
        if(!empty($data->ma_tai_khoan)) {
            $stmt->bindParam(":ma_tai_khoan", $data->ma_tai_khoan);
        }
        
        // Bind mật khẩu (Nếu thêm mới hoặc có nhập mật khẩu mới khi sửa)
        if(!empty($data->mat_khau) || empty($data->ma_tai_khoan)) {
            $stmt->bindParam(":mat_khau", $data->mat_khau);
        }

        if($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Lưu dữ liệu thành công!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Không thể thực thi câu lệnh SQL."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Vui lòng nhập đầy đủ Họ tên và Email."]);
    }
} catch(PDOException $e) { 
    echo json_encode(["success" => false, "message" => "Lỗi Database: " . $e->getMessage()]); 
}
?>