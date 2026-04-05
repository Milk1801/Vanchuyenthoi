<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuanLyDangNhap extends Controller
{
    /**
     * API Đăng nhập
     */
    public function login(Request $request)
    {
        if (!$request->has('ma_tai_khoan') || !$request->has('mat_khau')) {
            return response()->json(["success" => false, "message" => "Vui lòng nhập đầy đủ Mã tài khoản và Mật khẩu!"]);
        }

        $user = DB::table('tai_khoan')
            ->leftJoin('quyen', 'tai_khoan.ma_quyen', '=', 'quyen.ma_quyen')
            ->where('tai_khoan.ma_tai_khoan', $request->input('ma_tai_khoan'))
            ->where('tai_khoan.mat_khau', $request->input('mat_khau'))
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
    }

    /**
     * Lấy thông tin cá nhân
     */
    public function userInfo(Request $request)
    {
        $ma_tai_khoan = $request->query('ma_tai_khoan');
        if (!$ma_tai_khoan) return response()->json(["success" => false, "message" => "Thiếu mã tài khoản"]);

        try {
            $user = DB::table('tai_khoan')
                ->leftJoin('quyen', 'tai_khoan.ma_quyen', '=', 'quyen.ma_quyen')
                ->where('tai_khoan.ma_tai_khoan', $ma_tai_khoan)
                ->where('tai_khoan.thoi_gian_xoa', '<', '2000-01-01') 
                ->select('tai_khoan.*', 'quyen.ten_quyen') 
                ->first();
                
            if ($user) return response()->json(["success" => true, "data" => $user]);
            return response()->json(["success" => false, "message" => "Không tìm thấy user."]);
        } catch (\Exception $e) {
            return response()->json(["success" => false, "message" => "Lỗi DB: " . $e->getMessage()], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        $ma_tai_khoan = $request->input('ma_tai_khoan');
        if (empty($ma_tai_khoan)) return response()->json(["success" => false, "message" => "Thiếu mã tài khoản!"]);

        try {
            DB::table('tai_khoan')->where('ma_tai_khoan', $ma_tai_khoan)->update([
                'ho_ten' => $request->input('ho_ten'),
                'email' => $request->input('email'),
                'ngay_sinh' => $request->input('ngay_sinh')
            ]);
            return response()->json(["success" => true, "message" => "Cập nhật thành công!"]);
        } catch (\Exception $e) {
            return response()->json(["success" => false, "message" => "Lỗi DB: " . $e->getMessage()], 500);
        }
    }

    public function changePassword(Request $request)
    {
        $ma_tai_khoan = $request->input('ma_tai_khoan');
        $current_pass = $request->input('current_pass');
        $new_pass = $request->input('new_pass');

        if (empty($ma_tai_khoan) || empty($current_pass) || empty($new_pass)) {
            return response()->json(["success" => false, "message" => "Vui lòng nhập đầy đủ thông tin."]);
        }

        try {
            $user = DB::table('tai_khoan')->where('ma_tai_khoan', $ma_tai_khoan)->first();
            if (!$user || $user->mat_khau !== $current_pass) {
                return response()->json(["success" => false, "message" => "Mật khẩu hiện tại không chính xác!"]);
            }

            DB::table('tai_khoan')->where('ma_tai_khoan', $ma_tai_khoan)->update(['mat_khau' => $new_pass]);
            return response()->json(["success" => true, "message" => "Đổi mật khẩu thành công!"]);
        } catch (\Exception $e) {
            return response()->json(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
        }
    }
}