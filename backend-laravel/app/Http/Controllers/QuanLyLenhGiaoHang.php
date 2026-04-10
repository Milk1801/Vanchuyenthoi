<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuanLyLenhGiaoHang extends Controller 
{
    public function index()
    {
        try {
            // Lấy danh sách Thông Báo Hàng Đến (Kèm tên Lô hàng, Khách hàng, Tên tàu)
            $thongBaoHangDen = DB::table('thong_bao_hang_den')
                ->leftJoin('lo_hang', 'thong_bao_hang_den.ma_lo_hang', '=', 'lo_hang.ma_lo_hang')
                ->leftJoin('booking', 'lo_hang.ma_booking', '=', 'booking.ma_booking')
                ->leftJoin('khach_hang', 'lo_hang.ma_khach_hang', '=', 'khach_hang.ma_khach_hang')
                ->select(
                    'thong_bao_hang_den.ma_thong_bao_hang_den as ma_phieu', // Dùng bí danh ma_phieu cho dễ code Frontend
                    'thong_bao_hang_den.ngay_phat_hanh', 
                    'thong_bao_hang_den.ma_lo_hang', 
                    'lo_hang.ten_lo_hang',
                    'booking.so_booking',
                    'booking.ten_con_tau',
                    'khach_hang.ten_khach_hang'
                )
                ->orderBy('thong_bao_hang_den.ma_thong_bao_hang_den', 'desc')
                ->get();

            // Lấy danh sách Lệnh giao hàng (Kèm tên Lô hàng, Số Bill, Số Cont)
            $lenhGiaoHang = DB::table('lenh_giao_hang')
                ->leftJoin('lo_hang', 'lenh_giao_hang.ma_lo_hang', '=', 'lo_hang.ma_lo_hang')
                ->leftJoin('booking', 'lo_hang.ma_booking', '=', 'booking.ma_booking')
                ->leftJoin('van_don', 'lo_hang.ma_lo_hang', '=', 'van_don.ma_lo_hang')
                ->select(
                    'lenh_giao_hang.ma_lenh_giao_hang as ma_phieu', 
                    'lenh_giao_hang.ngay_phat_hanh', 
                    'lenh_giao_hang.ma_lo_hang', 
                    'booking.so_booking', 
                    'lo_hang.ten_lo_hang',
                    'van_don.so_van_don',
                    'van_don.so_cont'
                )
                ->orderBy('lenh_giao_hang.ma_lenh_giao_hang', 'desc')
                ->get();
            
            // Lấy danh sách Lô hàng cho Combobox
            $loHang = DB::table('lo_hang')
                ->leftJoin('booking', 'lo_hang.ma_booking', '=', 'booking.ma_booking')
                ->where('lo_hang.thoi_gian_xoa', '<=', '2000-01-01 00:00:00')
                ->select('lo_hang.ma_lo_hang', 'lo_hang.ten_lo_hang', 'booking.so_booking')
                ->get();

            return response()->json([
                "success" => true, 
                "data_an" => $thongBaoHangDen, 
                "data_do" => $lenhGiaoHang, 
                "lo_hang" => $loHang
            ]);
        } catch (\Exception $e) {
            return response()->json(["success" => false, "message" => "Lỗi DB: " . $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        try {
            $loai = $request->input('loai'); // 'AN' hoặc 'DO'
            $ma_phieu = $request->input('ma_phieu');
            
            $table = ($loai === 'AN') ? 'thong_bao_hang_den' : 'lenh_giao_hang';
            $pk = ($loai === 'AN') ? 'ma_thong_bao_hang_den' : 'ma_lenh_giao_hang';

            $data = [
                'ngay_phat_hanh' => $request->input('ngay_phat_hanh'),
                'ma_lo_hang' => $request->input('ma_lo_hang'),
            ];

            if ($ma_phieu) {
                // Cập nhật
                DB::table($table)->where($pk, $ma_phieu)->update($data);
                $msg = "Đã cập nhật thành công!";
            } else {
                // Thêm mới
                DB::table($table)->insert($data);
                $msg = "Đã thêm mới thành công!";
            }

            return response()->json(['success' => true, 'message' => $msg]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $loai = $request->input('loai');
            $id = $request->input('ma_phieu');
            
            $table = ($loai === 'AN') ? 'thong_bao_hang_den' : 'lenh_giao_hang';
            $pk = ($loai === 'AN') ? 'ma_thong_bao_hang_den' : 'ma_lenh_giao_hang';

            DB::table($table)->where($pk, $id)->delete();
            return response()->json(['success' => true, 'message' => 'Đã xóa dữ liệu thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
        }
    }
}