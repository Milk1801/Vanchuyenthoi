<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class QuanLyChungTu extends Controller 
{
    public function index()
    {
        try {
            // 1. Lấy danh sách chứng từ (Join qua bảng lo_hang và booking)
            $chungTu = DB::table('chung_tu_so_hoa')
                ->leftJoin('lo_hang', 'chung_tu_so_hoa.ma_lo_hang', '=', 'lo_hang.ma_lo_hang')
                ->leftJoin('booking', 'lo_hang.ma_booking', '=', 'booking.ma_booking') // Kéo bảng booking vào
                ->select('chung_tu_so_hoa.*', 'booking.so_booking', 'lo_hang.ten_lo_hang')
                ->orderBy('chung_tu_so_hoa.ma_chung_tu', 'desc')
                ->get();
            
            // 2. Lấy danh sách Lô hàng (Cũng phải Join bảng booking để lấy số booking hiển thị ở Combobox)
            $loHang = DB::table('lo_hang')
                ->leftJoin('booking', 'lo_hang.ma_booking', '=', 'booking.ma_booking')
                ->leftJoin('khach_hang', 'lo_hang.ma_khach_hang', '=', 'khach_hang.ma_khach_hang')
                ->leftJoin('tai_khoan', 'lo_hang.nguoi_sua_cuoi', '=', 'tai_khoan.ma_tai_khoan')
                ->leftJoin(DB::raw('(SELECT ma_lo_hang, GROUP_CONCAT(ten_hang SEPARATOR ", ") as ds_ten_hang FROM chi_tiet_lo_hang GROUP BY ma_lo_hang) as ctlh'), 'lo_hang.ma_lo_hang', '=', 'ctlh.ma_lo_hang')
                ->leftJoin(DB::raw('(SELECT ma_lo_hang, GROUP_CONCAT(DISTINCT loai_chung_tu) as ds_loai_ct FROM chung_tu_so_hoa GROUP BY ma_lo_hang) as ct'), 'lo_hang.ma_lo_hang', '=', 'ct.ma_lo_hang')
                ->where('lo_hang.thoi_gian_xoa', '<=', '2000-01-01 00:00:00')
                ->select(
                    'lo_hang.ma_lo_hang', 
                    'lo_hang.ten_lo_hang', 
                    'lo_hang.ma_khach_hang', 
                    'lo_hang.dieu_kien_thuong_mai', 
                    'lo_hang.trang_thai_lo_hang', 
                    'lo_hang.nguoi_sua_cuoi',
                    'booking.so_booking', 
                    'khach_hang.ten_khach_hang', 
                    'tai_khoan.ho_ten as nguoi_sua_doi',
                    'ctlh.ds_ten_hang',
                    'ct.ds_loai_ct'
                )
                ->get();

            return response()->json(["success" => true, "data" => $chungTu, "lo_hang" => $loHang]);
        } catch (\Exception $e) {
            // Trả về lỗi để Frontend biết (nếu có)
            return response()->json(["success" => false, "message" => "Lỗi DB: " . $e->getMessage()]);
        }
    }

    public function save(Request $request)
    {
        try {
            $request->validate([
                'hinh_anh' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf|max:10240', // Tối đa 10MB
                'loai_chung_tu' => 'required',
                'ma_lo_hang' => 'required'
            ]);

            $ma_chung_tu = $request->input('ma_chung_tu');
            $data = [
                'loai_chung_tu' => $request->input('loai_chung_tu'),
                'ma_lo_hang' => $request->input('ma_lo_hang'),
            ];

            if ($request->hasFile('hinh_anh')) {
                if ($ma_chung_tu) {
                    $oldDoc = DB::table('chung_tu_so_hoa')->where('ma_chung_tu', $ma_chung_tu)->first();
                    if ($oldDoc && $oldDoc->hinh_anh && file_exists(public_path($oldDoc->hinh_anh))) {
                        unlink(public_path($oldDoc->hinh_anh));
                    }
                }

                $file = $request->file('hinh_anh');
                $filename = time() . '_' . $file->getClientOriginalName();
                
                $path = public_path('uploads/chungtu');
                if(!File::isDirectory($path)){
                    File::makeDirectory($path, 0777, true, true);
                }

                $file->move($path, $filename);
                $data['hinh_anh'] = 'uploads/chungtu/' . $filename; 
            }

            if ($ma_chung_tu) {
                DB::table('chung_tu_so_hoa')->where('ma_chung_tu', $ma_chung_tu)->update($data);
                $msg = "Đã cập nhật chứng từ!";
            } else {
                DB::table('chung_tu_so_hoa')->insert($data);
                $msg = "Đã tải lên chứng từ mới!";
            }

            return response()->json(['success' => true, 'message' => $msg]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
        }
    }

    public function delete(Request $request)
    {
        try {
            $ma_chung_tu = $request->input('ma_chung_tu');
            $oldDoc = DB::table('chung_tu_so_hoa')->where('ma_chung_tu', $ma_chung_tu)->first();
            
            if ($oldDoc && $oldDoc->hinh_anh && file_exists(public_path($oldDoc->hinh_anh))) {
                unlink(public_path($oldDoc->hinh_anh));
            }

            DB::table('chung_tu_so_hoa')->where('ma_chung_tu', $ma_chung_tu)->delete();
            return response()->json(['success' => true, 'message' => 'Đã xóa chứng từ thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi DB: ' . $e->getMessage()]);
        }
    }
    public function download($id)
{
    try {

        // Tìm chứng từ
        $doc = DB::table('chung_tu_so_hoa')
            ->where('ma_chung_tu', $id)
            ->first();

        // Không tồn tại
        if (!$doc) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy chứng từ'
            ], 404);
        }

        // Đường dẫn file thật
        $filePath = public_path($doc->hinh_anh);

        // Kiểm tra file tồn tại
        if (!file_exists($filePath)) {
            return response()->json([
                'success' => false,
                'message' => 'File không tồn tại'
            ], 404);
        }

        // Tên file tải về
        $fileName = basename($filePath);

        // Ép download
        return response()->download($filePath, $fileName);

    } catch (\Exception $e) {

        return response()->json([
            'success' => false,
            'message' => 'Lỗi tải file: ' . $e->getMessage()
        ], 500);

    }
}
}