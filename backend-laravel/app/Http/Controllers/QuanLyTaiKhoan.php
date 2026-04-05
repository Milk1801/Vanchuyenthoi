<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuanLyTaiKhoan extends Controller
{
    public function index()
    {
        try {
            $accounts = DB::table('tai_khoan')
                ->leftJoin('quyen', 'tai_khoan.ma_quyen', '=', 'quyen.ma_quyen')
                ->select('tai_khoan.ma_tai_khoan', 'tai_khoan.ho_ten', 'tai_khoan.email', 'tai_khoan.trang_thai', 'tai_khoan.ngay_sinh', 'quyen.ten_quyen')
                ->where('tai_khoan.thoi_gian_xoa', '<', '2000-01-01')
                ->orderBy('tai_khoan.ma_tai_khoan', 'asc')
                ->get();
            return response()->json(["success" => true, "data" => $accounts]);
        } catch (\Exception $e) {
            return response()->json(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
        }
    }

    public function save(Request $request)
    {
        $email = $request->input('email');
        $ma_tai_khoan = $request->input('ma_tai_khoan');

        if (!$request->input('ho_ten') || !$email) {
            return response()->json(['success' => false, 'message' => 'Vui lòng nhập đầy đủ Họ tên và Email.']);
        }

        try {
            $emailCheck = DB::table('tai_khoan')->where('email', $email)->where('thoi_gian_xoa', '<', '2000-01-01');
            if ($ma_tai_khoan) $emailCheck->where('ma_tai_khoan', '!=', $ma_tai_khoan);
            if ($emailCheck->exists()) return response()->json(['success' => false, 'message' => 'Email này đã tồn tại!']);

            $dataToSave = [
                'ho_ten' => $request->input('ho_ten'),
                'email' => $email,
                'ngay_sinh' => $request->input('ngay_sinh'),
                'ma_quyen' => $request->input('ma_quyen'),
                'trang_thai' => $request->input('trang_thai')
            ];

            if ($ma_tai_khoan) {
                if ($request->filled('mat_khau')) $dataToSave['mat_khau'] = $request->input('mat_khau');
                DB::table('tai_khoan')->where('ma_tai_khoan', $ma_tai_khoan)->update($dataToSave);
                $message = "Cập nhật thành công!";
            } else {
                $dataToSave['mat_khau'] = $request->input('mat_khau');
                DB::table('tai_khoan')->insert($dataToSave);
                $message = "Tạo mới thành công!";
            }
            return response()->json(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
        }
    }

    public function delete(Request $request)
    {
        $ma_tai_khoan = $request->input('ma_tai_khoan');
        if (empty($ma_tai_khoan)) return response()->json(['success' => false, 'message' => 'Thiếu mã tài khoản.']);
        if ($ma_tai_khoan == 1) return response()->json(['success' => false, 'message' => 'Không được phép xóa Admin!']);

        try {
            DB::table('tai_khoan')
                ->where('ma_tai_khoan', $ma_tai_khoan)
                ->update(['thoi_gian_xoa' => now()]);
            return response()->json(['success' => true, 'message' => 'Đã đưa vào thùng rác thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
        }
    }
}