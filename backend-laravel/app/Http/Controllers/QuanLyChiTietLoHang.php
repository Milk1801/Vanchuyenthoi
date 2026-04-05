<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class QuanLyChiTietLoHang extends Controller
{
    public function index()
    {
        try {
            $data = DB::table('chi_tiet_lo_hang')
                ->leftJoin('hang_hoa', 'chi_tiet_lo_hang.ma_hang_hoa', '=', 'hang_hoa.ma_hang_hoa')
                ->leftJoin('don_vi_tinh', 'chi_tiet_lo_hang.ma_don_vi_tinh', '=', 'don_vi_tinh.ma_don_vi_tinh')
                ->leftJoin('tai_khoan', 'chi_tiet_lo_hang.nguoi_sua_cuoi', '=', 'tai_khoan.ma_tai_khoan')
                ->select('chi_tiet_lo_hang.ten_hang', 'chi_tiet_lo_hang.so_luong', 'chi_tiet_lo_hang.so_kien', 'chi_tiet_lo_hang.the_tich', 'chi_tiet_lo_hang.trong_luong', 'chi_tiet_lo_hang.gia_ca', 'hang_hoa.ten_hang_hoa', 'don_vi_tinh.ten_don_vi_tinh', 'tai_khoan.ten_tai_khoan')
                ->where('thoi_gian_xoa', '<', '2000-01-01')
                ->orderBy('ma_chi_tiet_lo_hang', 'desc')
                ->get();
            return response()->json(["success" => true, "data" => $data]);
        } catch (\Exception $e) {
            return response()->json(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
        }
    }

    public function save(Request $request)
    {
        $fields = [
            'ten_hang', 'so_luong', 'so_kien', 'the_tich', 'trong_luong', 
            'gia_ca', 'ma_hang_hoa', 'ma_lo_hang', 'ma_don_vi_tinh', 'nguoi_sua_cuoi'
        ];
        $data = $request->only($fields);
        $ma_chi_tiet = $request->input('ma_chi_tiet_lo_hang');

        try {
            if ($ma_chi_tiet) {
                DB::table('chi_tiet_lo_hang')->where('ma_chi_tiet_lo_hang', $ma_chi_tiet)->update($data);
                $message = "Cập nhật chi tiết hàng hóa thành công!";
            } else {
                DB::table('chi_tiet_lo_hang')->insert($data);
                $message = "Thêm chi tiết hàng hóa thành công!";
            }
            return response()->json(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => "Lỗi: " . $e->getMessage()]);
        }
    }

    public function delete(Request $request)
    {
        $ma_chi_tiet = $request->input('ma_chi_tiet_lo_hang');
        try {
            DB::table('chi_tiet_lo_hang')
                ->where('ma_chi_tiet_lo_hang', $ma_chi_tiet)
                ->update(['thoi_gian_xoa' => Carbon::now()]);
            return response()->json([
                'success' => true,
                'message' => "Đã xóa chi tiết lô hàng thành công!"
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => "Lỗi khi xóa: " . $e->getMessage()]);
        }
    }
}
