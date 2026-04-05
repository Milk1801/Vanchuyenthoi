<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class QuanLyLoHang extends Controller
{
    public function index()
    {
        try {
            $data = DB::table('lo_hang')
                ->leftJoin('booking', 'lo_hang.ma_booking', '=', 'booking.ma_booking')
                ->leftJoin('khach_hang', 'lo_hang.ma_khach_hang', '=', 'khach_hang.ma_khach_hang')
                ->leftJoin('tai_khoan', 'lo_hang.nguoi_sua_cuoi', '=', 'tai_khoan.ma_tai_khoan')
                ->select('lo_hang.ma_lo_hang', 'lo_hang.ten_lo_hang', 'lo_hang.dieu_kien_thuong_mai', 'lo_hang.trang_thai_lo_hang', 'lo_hang.nguon_goc', 'booking.so_booking', 'khach_hang.ten_khach_hang', 'tai_khoan.ten_tai_khoan')
                ->where('thoi_gian_xoa', '<', '2000-01-01')
                ->orderBy('ma_lo_hang', 'desc')
                ->get();
            return response()->json(["success" => true, "data" => $data]);
        } catch (\Exception $e) {
            return response()->json(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
        }
    }

    public function save(Request $request)
    {
        $fields = [
            'ten_lo_hang', 'dieu_kien_thuong_mai', 'trang_thai_lo_hang', 
            'nguon_goc', 'ma_booking', 'ma_khach_hang', 'nguoi_sua_cuoi'
        ];
        $data = $request->only($fields);
        $ma_lo_hang = $request->input('ma_lo_hang');

        try {
            if ($ma_lo_hang) {
                DB::table('lo_hang')->where('ma_lo_hang', $ma_lo_hang)->update($data);
                $message = "Cập nhật lô hàng thành công!";
            } else {
                DB::table('lo_hang')->insert($data);
                $message = "Tạo lô hàng mới thành công!";
            }
            return response()->json(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => "Lỗi: " . $e->getMessage()]);
        }
    }

    public function delete(Request $request)
    {
        $ma_lo_hang = $request->input('ma_lo_hang');
        try {
            DB::table('lo_hang')
                ->where('ma_lo_hang', $ma_lo_hang)
                ->update(['thoi_gian_xoa' => Carbon::now()]);
            return response()->json([
                'success' => true,
                'message' => "Đã xóa lô hàng thành công!"
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => "Lỗi khi xóa: " . $e->getMessage()]);
        }
    }
}
