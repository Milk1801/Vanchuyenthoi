<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuanLyChiPhi extends Controller
{
    public function index()
    {
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
    }

    public function save(Request $request)
    {
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
                $message = "Cập nhật chi phí thành công!";
            } else {
                DB::table('chi_phi')->insert($data);
                $message = "Lưu chi phí thành công!";
            }
            return response()->json(["success" => true, "message" => $message]);
        } catch (\Exception $e) {
            return response()->json(["success" => false, "message" => $e->getMessage()]);
        }
    }
}