<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
// Kết nối DB tương tự file trên...
$data = json_decode(file_get_contents("php://input"));
if(!empty($data->id)) {
    // Lưu ý: Không nên xóa tài khoản admin chính mình (id=1) để tránh lỗi hệ thống
    $sql = "DELETE FROM tai_khoan WHERE ma_tai_khoan = :id AND ma_tai_khoan != 1";
    // ... thực thi stmt ...
}
?>