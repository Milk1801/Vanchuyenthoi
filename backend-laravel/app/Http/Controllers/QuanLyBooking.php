<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class QuanLyBooking extends Controller
{
    public function index()
    {
        try {
            $data = DB::table('booking')
                ->leftJoin('cang_bien', 'booking.ma_cang_di', '=', 'cang_bien.ma_cang')
                ->leftJoin('cang_bien as cang_den', 'booking.ma_cang_den', '=', 'cang_den.ma_cang')
                ->leftJoin('tai_khoan', 'booking.nguoi_sua_cuoi', '=', 'tai_khoan.ma_tai_khoan')
                ->select('booking.ma_booking', 'booking.so_booking', 'booking.ten_con_tau', 'booking.so_chuyen', 'booking.etd', 'booking.eta', 'booking.gio_cat_mang', 'cang_bien.ten_cang as ten_cang_di', 'cang_den.ten_cang as ten_cang_den', 'tai_khoan.ten_tai_khoan')
                ->where('thoi_gian_xoa', '<', '2000-01-01')
                ->orderBy('ma_booking', 'desc')
                ->get();
            return response()->json(["success" => true, "data" => $data]);
        } catch (\Exception $e) {
            return response()->json(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
        }
    }

    public function save(Request $request)
    {
        $fields = [
            'so_booking', 'ten_con_tau', 'so_chuyen', 'etd', 'eta', 
            'gio_cat_mang', 'ma_cang_di', 'ma_cang_den', 'ma_hang_tau', 'nguoi_sua_cuoi'
        ];
        $data = $request->only($fields);
        $ma_booking = $request->input('ma_booking');

        try {
            if ($ma_booking) {
                DB::table('booking')->where('ma_booking', $ma_booking)->update($data);
                $message = "Cập nhật Booking thành công!";
            } else {
                DB::table('booking')->insert($data);
                $message = "Thêm Booking mới thành công!";
            }
            return response()->json(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => "Lỗi khi lưu: " . $e->getMessage()]);
        }
    }

    public function delete(Request $request)
    {
        $ma_booking = $request->input('ma_booking');
        try {
            DB::table('booking')
                ->where('ma_booking', $ma_booking)
                ->update(['thoi_gian_xoa' => Carbon::now()]);
            return response()->json([
                'success' => true,
                'message' => "Đã xóa Booking thành công!"
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => "Lỗi khi xóa: " . $e->getMessage()]);
        }
    }
}
