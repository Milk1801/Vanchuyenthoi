<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class QuanLyQuyen extends Controller
{
    public function index()
    {
        try {
            $quyen = DB::table('quyen')
            ->select('ma_quyen', 'ten_quyen', 'trang_thai')
            ->where('thoi_gian_xoa', '<', '2000-01-01')
            ->orderBy('ma_quyen', 'asc')
            ->get();
            return response()->json(["success" => true, "data" => $quyen]);
        } catch (\Exception $e) {
            return response()->json(["success" => false, "message" => "Lỗi: " . $e->getMessage()]);
        }
    }

    public function save(Request $request)
    {
        $data = $request->only(['ten_quyen', 'trang_thai']);
        $ma_quyen = $request->input('ma_quyen');

        if ($ma_quyen) {
            // Cập nhật
            DB::table('quyen')->where('ma_quyen', $ma_quyen)->update($data);
            $message = "Cập nhật quyền thành công!";
        } else {
            // Thêm mới
            DB::table('quyen')->insert($data);
            $message = "Thêm quyền mới thành công!";
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    public function delete(Request $request)
    {
        $ma_quyen = $request->input('ma_quyen');
        
        // Thực hiện soft delete dựa trên cấu trúc thoi_gian_xoa của bạn
        DB::table('quyen')
            ->where('ma_quyen', $ma_quyen)
            ->update(['thoi_gian_xoa' => Carbon::now()]);

        return response()->json([
            'success' => true,
            'message' => "Đã xóa quyền thành công!"
        ]);
    }
}