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
| 4. API QUẢN LÝ DANH MỤC - KHÁCH HÀNG
|--------------------------------------------------------------------------
*/

// Lấy danh sách khách hàng (ĐÃ LOẠI BỎ XÓA MỀM)
Route::get('/khach-hang', function () {
    try {
        $khach_hang = DB::table('khach_hang')
            ->leftJoin('tai_khoan', 'khach_hang.nguoi_sua_cuoi', '=', 'tai_khoan.ma_tai_khoan')
            ->select(
                'khach_hang.ma_khach_hang', 
                'khach_hang.ten_khach_hang', 
                'khach_hang.dia_chi', 
                'khach_hang.so_dien_thoai', 
                'khach_hang.so_fax', 
                'khach_hang.ghi_chu', 
                'khach_hang.nguoi_sua_cuoi',
                'tai_khoan.ho_ten as nguoi_sua_doi'
            )
            // LOẠI BỎ NHỮNG KHÁCH HÀNG ĐÃ XÓA MỀM
            ->where('khach_hang.thoi_gian_xoa', '<', '2000-01-01')
            ->orderBy('khach_hang.ma_khach_hang', 'asc')
            ->get();
        
        return response()->json(["success" => true, "data" => $khach_hang]);
    } catch (\Exception $e) {
        return response()->json(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
    }
});

// Lưu/Cập nhật khách hàng
Route::post('/khach-hang/save', function (Request $request) {
    $ten_khach_hang = $request->input('ten_khach_hang');
    $ma_khach_hang = $request->input('ma_khach_hang');

    // Kiểm tra không được để trống tên khách hàng
    if (!$ten_khach_hang) {
        return response()->json(['success' => false, 'message' => 'Vui lòng nhập tên khách hàng.']);
    }

    try {
        // Chuẩn bị dữ liệu
        $dataToSave = [
            'ten_khach_hang' => $ten_khach_hang,
            'dia_chi' => $request->input('dia_chi'),
            'so_dien_thoai' => $request->input('so_dien_thoai'),
            'so_fax' => $request->input('so_fax'),
            'ghi_chu' => $request->input('ghi_chu'),
            'nguoi_sua_cuoi' => $request->input('nguoi_sua_cuoi')
        ];

        // Lưu vào Database
        if ($ma_khach_hang) {
            // Trường hợp: CẬP NHẬT KHÁCH HÀNG
            DB::table('khach_hang')->where('ma_khach_hang', $ma_khach_hang)->update($dataToSave);
            $message = "Đã cập nhật thông tin khách hàng thành công!";
        } else {
            // Trường hợp: THÊM MỚI KHÁCH HÀNG
            DB::table('khach_hang')->insert($dataToSave);
            $message = "Đã tạo khách hàng mới thành công!";
        }

        // Trả về thông báo thành công
        return response()->json(['success' => true, 'message' => $message]);
        
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
    }
});

// Xóa khách hàng (XÓA MỀM)
Route::post('/khach-hang/delete', function (Request $request) {
    $ma_khach_hang = $request->input('ma_khach_hang');
    
    if (empty($ma_khach_hang)) {
        return response()->json(['success' => false, 'message' => 'Thiếu mã khách hàng.']);
    }

    try {
        DB::table('khach_hang')
            ->where('ma_khach_hang', $ma_khach_hang)
            ->update([
                'thoi_gian_xoa' => now() 
            ]);
            
        return response()->json(['success' => true, 'message' => 'Đã xóa khách hàng thành công!']);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
    }
});

/*
|--------------------------------------------------------------------------
| 5. API QUẢN LÝ DANH MỤC - HÃNG TÀU
|--------------------------------------------------------------------------
*/
Route::get('/hang-tau', function () {
    try {
        $hang_tau = DB::table('hang_tau')
            ->leftJoin('tai_khoan', 'hang_tau.nguoi_sua_cuoi', '=', 'tai_khoan.ma_tai_khoan')
            ->select('hang_tau.ma_hang_tau', 'hang_tau.ten_hang_tau', 'hang_tau.dia_chi', 'hang_tau.so_dien_thoai', 'hang_tau.so_fax', 'hang_tau.ghi_chu', 'hang_tau.nguoi_sua_cuoi', 'tai_khoan.ho_ten as nguoi_sua_doi')
            ->where('hang_tau.thoi_gian_xoa', '<', '2000-01-01')
            ->orderBy('hang_tau.ma_hang_tau', 'asc')
            ->get();
        return response()->json(["success" => true, "data" => $hang_tau]);
    } catch (\Exception $e) {
        return response()->json(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
    }
});

Route::post('/hang-tau/save', function (Request $request) {
    $ten_hang_tau = $request->input('ten_hang_tau');
    $ma_hang_tau = $request->input('ma_hang_tau');

    if (!$ten_hang_tau) {
        return response()->json(['success' => false, 'message' => 'Vui lòng nhập tên hãng tàu.']);
    }

    try {
        $dataToSave = [
            'ten_hang_tau' => $ten_hang_tau,
            'dia_chi' => $request->input('dia_chi'),
            'so_dien_thoai' => $request->input('so_dien_thoai'),
            'so_fax' => $request->input('so_fax'),
            'ghi_chu' => $request->input('ghi_chu'),
            'nguoi_sua_cuoi' => $request->input('nguoi_sua_cuoi')
        ];

        if ($ma_hang_tau) {
            DB::table('hang_tau')->where('ma_hang_tau', $ma_hang_tau)->update($dataToSave);
            $message = "Đã cập nhật thông tin hãng tàu thành công!";
        } else {
            DB::table('hang_tau')->insert($dataToSave);
            $message = "Đã tạo hãng tàu mới thành công!";
        }

        return response()->json(['success' => true, 'message' => $message]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
    }
});

Route::post('/hang-tau/delete', function (Request $request) {
    $ma_hang_tau = $request->input('ma_hang_tau');
    
    if (empty($ma_hang_tau)) {
        return response()->json(['success' => false, 'message' => 'Thiếu mã hãng tàu.']);
    }

    try {
        DB::table('hang_tau')->where('ma_hang_tau', $ma_hang_tau)->update(['thoi_gian_xoa' => now()]);
        return response()->json(['success' => true, 'message' => 'Đã xóa hãng tàu thành công!']);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
    }
});

/*
|--------------------------------------------------------------------------
| 6. API QUẢN LÝ DANH MỤC - CẢNG BIỂN
|--------------------------------------------------------------------------
*/
Route::get('/cang-bien', function () {
    try {
        $cang_bien = DB::table('cang_bien')
            ->select('ma_cang', 'ten_cang', 'dia_chi', 'ghi_chu')
            ->where('thoi_gian_xoa', '<', '2000-01-01')
            ->orderBy('ma_cang', 'asc')
            ->get();
        return response()->json(["success" => true, "data" => $cang_bien]);
    } catch (\Exception $e) {
        return response()->json(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
    }
});

Route::post('/cang-bien/save', function (Request $request) {
    $ten_cang = $request->input('ten_cang');
    $ma_cang = $request->input('ma_cang');

    if (!$ten_cang) {
        return response()->json(['success' => false, 'message' => 'Vui lòng nhập tên cảng.']);
    }

    try {
        $dataToSave = [
            'ten_cang' => $ten_cang,
            'dia_chi' => $request->input('dia_chi'),
            'ghi_chu' => $request->input('ghi_chu')
        ];

        if ($ma_cang) {
            DB::table('cang_bien')->where('ma_cang', $ma_cang)->update($dataToSave);
            $message = "Đã cập nhật thông tin cảng thành công!";
        } else {
            DB::table('cang_bien')->insert($dataToSave);
            $message = "Đã tạo cảng mới thành công!";
        }

        return response()->json(['success' => true, 'message' => $message]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
    }
});

Route::post('/cang-bien/delete', function (Request $request) {
    $ma_cang = $request->input('ma_cang');
    
    if (empty($ma_cang)) {
        return response()->json(['success' => false, 'message' => 'Thiếu mã cảng.']);
    }

    try {
        DB::table('cang_bien')->where('ma_cang', $ma_cang)->update(['thoi_gian_xoa' => now()]);
        return response()->json(['success' => true, 'message' => 'Đã xóa cảng thành công!']);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
    }
});

/*
|--------------------------------------------------------------------------
| 7. API QUẢN LÝ DANH MỤC - HÀNG HÓA
|--------------------------------------------------------------------------
*/
Route::get('/hang-hoa', function () {
    try {
        $hang_hoa = DB::table('hang_hoa')
            ->select('ma_hang_hoa', 'ten_hang_hoa', 'hs_code')
            ->where('thoi_gian_xoa', '<', '2000-01-01')
            ->orderBy('ma_hang_hoa', 'asc')
            ->get();
        return response()->json(["success" => true, "data" => $hang_hoa]);
    } catch (\Exception $e) {
        return response()->json(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
    }
});

Route::post('/hang-hoa/save', function (Request $request) {
    $ten_hang_hoa = $request->input('ten_hang_hoa');
    $ma_hang_hoa = $request->input('ma_hang_hoa');

    if (!$ten_hang_hoa) {
        return response()->json(['success' => false, 'message' => 'Vui lòng nhập tên hàng hóa.']);
    }

    try {
        $dataToSave = [
            'ten_hang_hoa' => $ten_hang_hoa,
            'hs_code' => $request->input('hs_code')
        ];

        if ($ma_hang_hoa) {
            DB::table('hang_hoa')->where('ma_hang_hoa', $ma_hang_hoa)->update($dataToSave);
            $message = "Đã cập nhật thông tin hàng hóa thành công!";
        } else {
            DB::table('hang_hoa')->insert($dataToSave);
            $message = "Đã tạo hàng hóa mới thành công!";
        }

        return response()->json(['success' => true, 'message' => $message]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
    }
});

Route::post('/hang-hoa/delete', function (Request $request) {
    $ma_hang_hoa = $request->input('ma_hang_hoa');
    
    if (empty($ma_hang_hoa)) {
        return response()->json(['success' => false, 'message' => 'Thiếu mã hàng hóa.']);
    }

    try {
        DB::table('hang_hoa')->where('ma_hang_hoa', $ma_hang_hoa)->update(['thoi_gian_xoa' => now()]);
        return response()->json(['success' => true, 'message' => 'Đã xóa hàng hóa thành công!']);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
    }
});

/*
|--------------------------------------------------------------------------
| 8. API QUẢN LÝ DANH MỤC - HÃNG VẬN TẢI
|--------------------------------------------------------------------------
*/
Route::get('/hang-van-tai', function () {
    try {
        $hang_van_tai = DB::table('hang_van_tai')
            ->select('ma_hang_van_tai', 'ten_hang_van_tai', 'tuyen_thuong_xuyen', 'cac_loai_xe', 'ghi_chu')
            ->where('thoi_gian_xoa', '<', '2000-01-01')
            ->orderBy('ma_hang_van_tai', 'asc')
            ->get();
        return response()->json(["success" => true, "data" => $hang_van_tai]);
    } catch (\Exception $e) {
        return response()->json(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
    }
});

Route::post('/hang-van-tai/save', function (Request $request) {
    $ten_hang_van_tai = $request->input('ten_hang_van_tai');
    $ma_hang_van_tai = $request->input('ma_hang_van_tai');

    if (!$ten_hang_van_tai) {
        return response()->json(['success' => false, 'message' => 'Vui lòng nhập tên hãng vận tải.']);
    }

    try {
        $dataToSave = [
            'ten_hang_van_tai' => $ten_hang_van_tai,
            'tuyen_thuong_xuyen' => $request->input('tuyen_thuong_xuyen'),
            'cac_loai_xe' => $request->input('cac_loai_xe'),
            'ghi_chu' => $request->input('ghi_chu')
        ];

        if ($ma_hang_van_tai) {
            DB::table('hang_van_tai')->where('ma_hang_van_tai', $ma_hang_van_tai)->update($dataToSave);
            $message = "Đã cập nhật thông tin hãng vận tải thành công!";
        } else {
            DB::table('hang_van_tai')->insert($dataToSave);
            $message = "Đã tạo hãng vận tải mới thành công!";
        }

        return response()->json(['success' => true, 'message' => $message]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
    }
});

Route::post('/hang-van-tai/delete', function (Request $request) {
    $ma_hang_van_tai = $request->input('ma_hang_van_tai');
    
    if (empty($ma_hang_van_tai)) {
        return response()->json(['success' => false, 'message' => 'Thiếu mã hãng vận tải.']);
    }

    try {
        DB::table('hang_van_tai')->where('ma_hang_van_tai', $ma_hang_van_tai)->update(['thoi_gian_xoa' => now()]);
        return response()->json(['success' => true, 'message' => 'Đã xóa hãng vận tải thành công!']);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
    }
});

/*
|--------------------------------------------------------------------------
| 9. API QUẢN LÝ DANH MỤC - ĐƠN VỊ TÍNH
|--------------------------------------------------------------------------
*/
Route::get('/don-vi-tinh', function () {
    try {
        $don_vi_tinh = DB::table('don_vi_tinh')
            ->select('ma_don_vi_tinh', 'ten_don_vi_tinh')
            ->where('thoi_gian_xoa', '<', '2000-01-01')
            ->orderBy('ma_don_vi_tinh', 'asc')
            ->get();
        return response()->json(["success" => true, "data" => $don_vi_tinh]);
    } catch (\Exception $e) {
        return response()->json(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
    }
});

Route::post('/don-vi-tinh/save', function (Request $request) {
    $ten_don_vi_tinh = $request->input('ten_don_vi_tinh');
    $ma_don_vi_tinh = $request->input('ma_don_vi_tinh');

    if (!$ten_don_vi_tinh) {
        return response()->json(['success' => false, 'message' => 'Vui lòng nhập tên đơn vị tính.']);
    }

    try {
        $dataToSave = [
            'ten_don_vi_tinh' => $ten_don_vi_tinh
        ];

        if ($ma_don_vi_tinh) {
            DB::table('don_vi_tinh')->where('ma_don_vi_tinh', $ma_don_vi_tinh)->update($dataToSave);
            $message = "Đã cập nhật thông tin đơn vị tính thành công!";
        } else {
            DB::table('don_vi_tinh')->insert($dataToSave);
            $message = "Đã tạo đơn vị tính mới thành công!";
        }

        return response()->json(['success' => true, 'message' => $message]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
    }
});

Route::post('/don-vi-tinh/delete', function (Request $request) {
    $ma_don_vi_tinh = $request->input('ma_don_vi_tinh');
    
    if (empty($ma_don_vi_tinh)) {
        return response()->json(['success' => false, 'message' => 'Thiếu mã đơn vị tính.']);
    }

    try {
        DB::table('don_vi_tinh')->where('ma_don_vi_tinh', $ma_don_vi_tinh)->update(['thoi_gian_xoa' => now()]);
        return response()->json(['success' => true, 'message' => 'Đã xóa đơn vị tính thành công!']);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
    }
});
