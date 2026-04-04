<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| 1. API ĐĂNG NHẬP
|--------------------------------------------------------------------------
*/
Route::post('/login', function (Request $request) {
    if (!$request->has('ma_tai_khoan') || !$request->has('mat_khau')) {
        return response()->json(["success" => false, "message" => "Vui lòng nhập đầy đủ Mã tài khoản và Mật khẩu!"]);
}

    $user = DB::table('tai_khoan')
        ->leftJoin('quyen', 'tai_khoan.ma_quyen', '=', 'quyen.ma_quyen')
        ->where('tai_khoan.ma_tai_khoan', $request->input('ma_tai_khoan'))
        ->where('tai_khoan.mat_khau', $request->input('mat_khau'))
        // ĐÃ SỬA CHỖ NÀY: Dùng < 2000-01-01 để né lỗi múi giờ của XAMPP
        ->where('tai_khoan.thoi_gian_xoa', '<', '2000-01-01') 
        ->select('tai_khoan.ma_tai_khoan', 'tai_khoan.ho_ten', 'tai_khoan.trang_thai', 'quyen.ten_quyen')
        ->first();

    if ($user) {
        if ($user->trang_thai === 'Tạm khóa') {
            return response()->json(["success" => false, "message" => "Tài khoản của bạn đã bị khóa!"]);
        }
        return response()->json([
            "success" => true, 
            "message" => "Đăng nhập thành công!",
            "user" => [
                "ma_tai_khoan" => $user->ma_tai_khoan,
                "ho_ten" => $user->ho_ten,
                "chuc_vu" => $user->ten_quyen
            ]
        ]);
    }
    
    return response()->json(["success" => false, "message" => "Mã tài khoản hoặc mật khẩu không chính xác!"]);
});

/*
|--------------------------------------------------------------------------
| 2. API QUẢN LÝ TÀI KHOẢN (get_account, save_account, delete_accounts)
|--------------------------------------------------------------------------
*/

// Lấy danh sách tài khoản (ĐÃ CẬP NHẬT XÓA MỀM)
Route::get('/accounts', function () {
    try {
        $accounts = DB::table('tai_khoan')
            ->leftJoin('quyen', 'tai_khoan.ma_quyen', '=', 'quyen.ma_quyen')
            ->select('tai_khoan.ma_tai_khoan', 'tai_khoan.ho_ten', 'tai_khoan.email', 'tai_khoan.trang_thai', 'tai_khoan.ngay_sinh', 'quyen.ten_quyen')
            // ĐỔI DÒNG NÀY: Lấy những người chưa xóa (Năm xóa < 2000)
            ->where('tai_khoan.thoi_gian_xoa', '<', '2000-01-01')
            ->orderBy('tai_khoan.ma_tai_khoan', 'asc')
            ->get();
        
        return response()->json(["success" => true, "data" => $accounts]);
    } catch (\Exception $e) {
        return response()->json(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
    }
});

// Lưu/Cập nhật tài khoản (Đã tích hợp Check trùng Email với Xóa mềm)
Route::post('/accounts/save', function (Request $request) {
    $email = $request->input('email');
    $ma_tai_khoan = $request->input('ma_tai_khoan');

    // 1. Kiểm tra không được để trống
    if (!$request->input('ho_ten') || !$email) {
        return response()->json(['success' => false, 'message' => 'Vui lòng nhập đầy đủ Họ tên và Email.']);
    }

    try {
        // 2. BỘ LỌC KIỂM TRA TRÙNG EMAIL 
        $emailCheck = DB::table('tai_khoan')
            ->where('email', $email)
            ->where('thoi_gian_xoa', '<', '2000-01-01'); // Chỉ check người chưa bị xóa mềm
            
        if ($ma_tai_khoan) {
            // Nếu là thao tác SỬA, phải bỏ qua chính cái tài khoản đang sửa
            // (Để tránh việc lưu nguyên email của mình lại bị báo là trùng)
            $emailCheck->where('ma_tai_khoan', '!=', $ma_tai_khoan);
        }
        
        if ($emailCheck->exists()) {
            return response()->json(['success' => false, 'message' => 'Email này đã tồn tại trong hệ thống. Vui lòng dùng email khác!']);
        }
        // ------------------------------------------------------------------

        // 3. Chuẩn bị dữ liệu chung
        $dataToSave = [
            'ho_ten' => $request->input('ho_ten'),
            'email' => $email,
            'ngay_sinh' => $request->input('ngay_sinh'),
            'ma_quyen' => $request->input('ma_quyen'),
            'trang_thai' => $request->input('trang_thai')
        ];

        // 4. Lưu vào Database
        if ($ma_tai_khoan) {
            // Trường hợp: SỬA TÀI KHOẢN
            if ($request->filled('mat_khau')) {
                $dataToSave['mat_khau'] = $request->input('mat_khau');
            }
            DB::table('tai_khoan')->where('ma_tai_khoan', $ma_tai_khoan)->update($dataToSave);
            $message = "Đã cập nhật thông tin tài khoản thành công!";
            
        } else {
            // Trường hợp: THÊM MỚI TÀI KHOẢN
            $dataToSave['mat_khau'] = $request->input('mat_khau');
            
            // Lưu ý: Không cần thêm 'thoi_gian_xoa' ở đây vì MySQL đã tự động set 
            DB::table('tai_khoan')->insert($dataToSave);
            $message = "Đã tạo tài khoản mới thành công!";
        }

        // 5. Trả về thông báo thành công
        return response()->json(['success' => true, 'message' => $message]);
        
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
    }
});

// Xóa tài khoản (ĐÃ CHUYỂN SANG XÓA MỀM)
Route::post('/accounts/delete', function (Request $request) {
    // Sửa lại thành chữ thường
    $ma_tai_khoan = $request->input('ma_tai_khoan');
    
    if (empty($ma_tai_khoan)) {
        return response()->json(['success' => false, 'message' => 'Thiếu mã tài khoản.']);
    }
    if ($ma_tai_khoan == 1) {
        return response()->json(['success' => false, 'message' => 'Không được phép xóa Admin hệ thống!']);
    }

    try {
        DB::table('tai_khoan')
            ->where('ma_tai_khoan', $ma_tai_khoan)
            ->update([
                'thoi_gian_xoa' => now() 
            ]);
            
        return response()->json(['success' => true, 'message' => 'Đã đưa tài khoản vào thùng rác thành công!']);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
    }
});

/*
|--------------------------------------------------------------------------
| 3. API HỒ SƠ CÁ NHÂN (get_user_info, update_profile, change_password)
|--------------------------------------------------------------------------
*/

// Lấy thông tin 1 user (Dùng chung cho trang Hồ sơ cá nhân)
Route::get('/user-info', function (Request $request) {
    $ma_tai_khoan = $request->query('ma_tai_khoan');
    if (!$ma_tai_khoan) return response()->json(["success" => false, "message" => "Thiếu mã tài khoản"]);

    try {
        $user = DB::table('tai_khoan')
            ->leftJoin('quyen', 'tai_khoan.ma_quyen', '=', 'quyen.ma_quyen')
            // ĐÃ SỬA: Bỏ cái check mat_khau đi, và lấy biến $ma_tai_khoan chuẩn
            ->where('tai_khoan.ma_tai_khoan', $ma_tai_khoan)
            ->where('tai_khoan.thoi_gian_xoa', '<', '2000-01-01') 
            // ĐÃ SỬA: Lấy tai_khoan.* để có đủ email, ngày sinh...
            ->select('tai_khoan.*', 'quyen.ten_quyen') 
            ->first();
            
        if ($user) {
            return response()->json(["success" => true, "data" => $user]);
        }
        return response()->json(["success" => false, "message" => "Không tìm thấy user."]);
    } catch (\Exception $e) {
        return response()->json(["success" => false, "message" => "Lỗi DB: " . $e->getMessage()], 500);
    }
});

// Cập nhật thông tin cá nhân (update_profile.php)
Route::post('/profile/update', function (Request $request) {
    $ma_tai_khoan = $request->input('ma_tai_khoan');
    if (empty($ma_tai_khoan)) {
        return response()->json(["success" => false, "message" => "Thiếu mã tài khoản!"]);
    }

    try {
        DB::table('tai_khoan')
            ->where('ma_tai_khoan', $ma_tai_khoan)
            ->update([
                'ho_ten' => $request->input('ho_ten'),
                'email' => $request->input('email'),
                'ngay_sinh' => $request->input('ngay_sinh')
            ]);

        return response()->json(["success" => true, "message" => "Cập nhật thành công!"]);
    } catch (\Exception $e) {
        return response()->json(["success" => false, "message" => "Lỗi DB: " . $e->getMessage()], 500);
    }
});

// Đổi mật khẩu (change_password.php)
Route::post('/profile/change-password', function (Request $request) {
    $ma_tai_khoan = $request->input('ma_tai_khoan');
    $current_pass = $request->input('current_pass');
    $new_pass = $request->input('new_pass');

    if (empty($ma_tai_khoan) || empty($current_pass) || empty($new_pass)) {
        return response()->json(["success" => false, "message" => "Vui lòng nhập đầy đủ thông tin."]);
    }

    try {
        // Kiểm tra mật khẩu cũ
        $user = DB::table('tai_khoan')->where('ma_tai_khoan', $ma_tai_khoan)->first();
        
        if (!$user || $user->mat_khau !== $current_pass) {
            return response()->json(["success" => false, "message" => "Mật khẩu hiện tại không chính xác!"]);
        }

        // Cập nhật mật khẩu mới
        DB::table('tai_khoan')
            ->where('ma_tai_khoan', $ma_tai_khoan)
            ->update(['mat_khau' => $new_pass]);

        return response()->json(["success" => true, "message" => "Đổi mật khẩu thành công!"]);
    } catch (\Exception $e) {
        return response()->json(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
    }
});

/*
|--------------------------------------------------------------------------
| QUẢN LÝ CHI PHÍ (Thanh toán AR/AP)
|--------------------------------------------------------------------------
*/

// Lấy danh sách chi phí và tên lô hàng tương ứng
Route::get('/costs', function () {
    try {
        $costs = DB::table('chi_phi')
            ->leftJoin('lo_hang', 'chi_phi.ma_lo_hang', '=', 'lo_hang.ma_lo_hang')
            ->select('chi_phi.*', 'lo_hang.ten_lo_hang')
            ->where('chi_phi.thoi_gian_xoa', '<', '2000-01-01')
            ->orderBy('chi_phi.ma_chi_phi', 'desc')
            ->get();
        return response()->json(["success" => true, "data" => $costs]);
    } catch (\Exception $e) {
        return response()->json(["success" => false, "message" => $e->getMessage()]);
    }
});

// Lưu hoặc cập nhật chi phí
Route::post('/costs/save', function (Request $request) {
    try {
        $data = [
            'ten_chi_phi' => $request->input('ten_chi_phi'),
            'tong_tien' => $request->input('tong_tien'),
            'loai_giao_dich' => $request->input('loai_giao_dich'),
            'trang_thai_thanh_toan' => $request->input('trang_thai_thanh_toan'),
            'ngay_thanh_toan' => $request->input('ngay_thanh_toan'),
            'ma_lo_hang' => $request->input('ma_lo_hang'),
            'nguoi_sua_cuoi' => $request->input('nguoi_sua_cuoi')
        ];

        if ($request->has('ma_chi_phi') && $request->input('ma_chi_phi')) {
            DB::table('chi_phi')->where('ma_chi_phi', $request->input('ma_chi_phi'))->update($data);
        } else {
            DB::table('chi_phi')->insert($data);
        }
        return response()->json(["success" => true, "message" => "Lưu chi phí thành công!"]);
    } catch (\Exception $e) {
        return response()->json(["success" => false, "message" => $e->getMessage()]);
    }
});

