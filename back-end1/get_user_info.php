<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$host = "localhost"; $db_name = "quanlyvanchuyen"; $username = "root"; $password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $id = isset($_GET['id']) ? $_GET['id'] : die();

    // JOIN với bảng quyen để lấy ten_quyen cho xịn
    $query = "SELECT t.*, q.ten_quyen 
              FROM tai_khoan t 
              LEFT JOIN quyen q ON t.ma_quyen = q.ma_quyen 
              WHERE t.ma_tai_khoan = ?";
              
    $stmt = $conn->prepare($query);
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if($user) {
        echo json_encode(["success" => true, "data" => $user]);
    } else {
        echo json_encode(["success" => false, "message" => "Không tìm thấy user."]);
    }
} catch(PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>