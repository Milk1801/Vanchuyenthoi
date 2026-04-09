<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuanLyLenhGiaoHang extends Controller 
{
    public function index()
    {
        try {
            // Lấy danh sách Lệnh giao hàng kèm thông tin Lô hàng và Booking
            $lenhGiaoHang = DB::table('lenh_giao_hang')
                ->leftJoin('lo_hang', 'lenh_giao_hang.ma_lo_hang', '=', 'lo_hang.ma_lo_hang')
                ->leftJoin('booking', 'lo_hang.ma_booking', '=', 'booking.ma_booking')
                ->select(
                    'lenh_giao_hang.ma_lenh_giao_hang', 
                    'lenh_giao_hang.ngay_phat_hanh', 
                    'lenh_giao_hang.ma_lo_hang', 
                    'booking.so_booking', 
                    'lo_hang.ten_lo_hang'
                )
                ->orderBy('lenh_giao_hang.ma_lenh_giao_hang', 'desc')
                ->get();
            
            // Lấy danh sách Lô hàng cho Combobox
            $loHang = DB::table('lo_hang')
                ->leftJoin('booking', 'lo_hang.ma_booking', '=', 'booking.ma_booking')
                ->where('lo_hang.thoi_gian_xoa', '<=', '2000-01-01 00:00:00')
                ->select('lo_hang.ma_lo_hang', 'lo_hang.ten_lo_hang', 'booking.so_booking')
                ->get();

            return response()->json(["success" => true, "data" => $lenhGiaoHang, "lo_hang" => $loHang]);
        } catch (\Exception $e) {
            return response()->json(["success" => false, "message" => "Lỗi DB: " . $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = [
                'ngay_phat_hanh' => $request->input('ngay_phat_hanh'),
                'ma_lo_hang' => $request->input('ma_lo_hang'),
            ];

            if ($request->has('ma_lenh_giao_hang') && $request->input('ma_lenh_giao_hang')) {
                // Cập nhật
                DB::table('lenh_giao_hang')
                    ->where('ma_lenh_giao_hang', $request->input('ma_lenh_giao_hang'))
                    ->update($data);
                $msg = "Đã cập nhật Lệnh giao hàng!";
            } else {
                // Thêm mới
                DB::table('lenh_giao_hang')->insert($data);
                $msg = "Đã thêm Lệnh giao hàng mới!";
            }

            return response()->json(['success' => true, 'message' => $msg]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $id = $request->input('ma_lenh_giao_hang');
            DB::table('lenh_giao_hang')->where('ma_lenh_giao_hang', $id)->delete();
            return response()->json(['success' => true, 'message' => 'Đã xóa lệnh giao hàng!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
        }
    }
}