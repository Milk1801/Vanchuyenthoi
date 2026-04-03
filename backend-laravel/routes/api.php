<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| 1. API ĐĂNG NHẬP (login.php)
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

// Lấy danh sách tài khoản (get_account.php)
Route::get('/accounts', function () {
    try {
        $accounts = DB::table('tai_khoan')
            ->leftJoin('quyen', 'tai_khoan.ma_quyen', '=', 'quyen.ma_quyen')
            ->select('tai_khoan.ma_tai_khoan', 'tai_khoan.ho_ten', 'tai_khoan.email', 'tai_khoan.trang_thai', 'tai_khoan.ngay_sinh', 'quyen.ten_quyen')
            ->orderBy('tai_khoan.ma_tai_khoan', 'asc')
            ->get();
        
        return response()->json(["success" => true, "data" => $accounts]);
    } catch (\Exception $e) {
        return response()->json(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
    }
});

// Lưu/Cập nhật tài khoản (save_account.php)
Route::post('/accounts/save', function (Request $request) {
    if (!$request->input('ho_ten') || !$request->input('email')) {
        return response()->json(['success' => false, 'message' => 'Vui lòng nhập đầy đủ Họ tên và Email.']);
    }

    try {
        $dataToSave = [
            'ho_ten' => $request->input('ho_ten'),
            'email' => $request->input('email'),
            'ngay_sinh' => $request->input('ngay_sinh'),
            'ma_quyen' => $request->input('ma_quyen'),
            'trang_thai' => $request->input('trang_thai')
        ];

        if ($request->input('ma_tai_khoan')) {
            // Sửa tài khoản
            if ($request->filled('mat_khau')) {
                $dataToSave['mat_khau'] = $request->input('mat_khau');
            }
            DB::table('tai_khoan')->where('ma_tai_khoan', $request->input('ma_tai_khoan'))->update($dataToSave);
            $message = "Lưu dữ liệu thành công!";
        } else {
            // Thêm mới tài khoản
            $dataToSave['mat_khau'] = $request->input('mat_khau');
            DB::table('tai_khoan')->insert($dataToSave);
            $message = "Lưu dữ liệu thành công!";
        }

        return response()->json(['success' => true, 'message' => $message]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
    }
});

// Xóa tài khoản (delete_accounts.php)
Route::post('/accounts/delete', function (Request $request) {
    $ma_tai_khoan = $request->input('ma_tai_khoan');
    if (empty($ma_tai_khoan)) {
        return response()->json(['success' => false, 'message' => 'Thiếu mã tài khoản.']);
    }
    if ($ma_tai_khoan == 1) {
        return response()->json(['success' => false, 'message' => 'Không được phép xóa Admin hệ thống!']);
    }

    try {
        DB::table('tai_khoan')->where('ma_tai_khoan', $ma_tai_khoan)->delete();
        return response()->json(['success' => true, 'message' => 'Đã xóa tài khoản thành công!']);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
    }
});

/*
|--------------------------------------------------------------------------
| 3. API HỒ SƠ CÁ NHÂN (get_user_info, update_profile, change_password)
|--------------------------------------------------------------------------
*/

// Lấy thông tin 1 user (get_user_info.php) - Dùng chung cho trang Hồ sơ cá nhân
Route::get('/user-info', function (Request $request) {
    $ma_tai_khoan = $request->query('ma_tai_khoan'); // Lấy tham số ?ma_tai_khoan=... trên URL
    if (!$ma_tai_khoan) return response()->json(["success" => false, "message" => "Thiếu mã tài khoản"]);

    try {
        $user = DB::table('tai_khoan')
            ->leftJoin('quyen', 'tai_khoan.ma_quyen', '=', 'quyen.ma_quyen')
            ->select('tai_khoan.*', 'quyen.ten_quyen')
            ->where('tai_khoan.ma_tai_khoan', $ma_tai_khoan)
            ->first();

        if ($user) {
            return response()->json(["success" => true, "data" => $user]);
        }
        return response()->json(["success" => false, "message" => "Không tìm thấy user."]);
    } catch (\Exception $e) {
        return response()->json(["success" => false, "message" => $e->getMessage()]);
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