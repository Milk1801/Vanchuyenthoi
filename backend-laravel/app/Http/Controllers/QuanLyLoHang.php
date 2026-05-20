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

            ->select(
                'lo_hang.ma_lo_hang',
                'lo_hang.ten_lo_hang',
                'lo_hang.dieu_kien_thuong_mai',
                'lo_hang.trang_thai_lo_hang',
                'lo_hang.nguon_goc',
                'lo_hang.ma_booking',
                'lo_hang.ma_khach_hang',

                'booking.so_booking',
                'booking.ten_con_tau',
                'booking.so_chuyen',
                'booking.etd',
                'booking.eta',
                DB::raw("CONCAT('Tàu: ', IFNULL(booking.ten_con_tau, 'N/A'), '\nChuyến: ', IFNULL(booking.so_chuyen, 'N/A'), '\nETD: ', IFNULL(DATE_FORMAT(booking.etd, '%d/%m/%Y'), 'N/A'), '\nETA: ', IFNULL(DATE_FORMAT(booking.eta, '%d/%m/%Y'), 'N/A')) as booking_tooltip"),

                'khach_hang.ten_khach_hang',

                'tai_khoan.ho_ten as nguoi_sua_doi'
            )

            ->addSelect([

                // vận đơn
                'so_van_don' => DB::table('van_don')
                    ->selectRaw("GROUP_CONCAT(DISTINCT so_van_don SEPARATOR ', ')")
                    ->whereColumn('van_don.ma_lo_hang', 'lo_hang.ma_lo_hang'),
                'van_don_tooltip' => DB::table('van_don')
                    ->selectRaw("GROUP_CONCAT(DISTINCT CONCAT(so_van_don, ': Cont ', IFNULL(so_cont, 'N/A'), ' [', IFNULL(loai_van_don, 'N/A'), ']') SEPARATOR '\n')")
                    ->whereColumn('van_don.ma_lo_hang', 'lo_hang.ma_lo_hang'),

                // thông báo hàng đến
                'ma_thong_bao_hang_den' => DB::table('thong_bao_hang_den')
                    ->selectRaw("GROUP_CONCAT(DISTINCT ma_thong_bao_hang_den SEPARATOR ', ')")
                    ->whereColumn('thong_bao_hang_den.ma_lo_hang', 'lo_hang.ma_lo_hang'),
                'an_tooltip' => DB::table('thong_bao_hang_den')
                    ->selectRaw("GROUP_CONCAT(DISTINCT CONCAT(ma_thong_bao_hang_den, ' (Ngày: ', IFNULL(DATE_FORMAT(ngay_phat_hanh, '%d/%m/%Y'), 'N/A'), ')') SEPARATOR '\n')")
                    ->whereColumn('thong_bao_hang_den.ma_lo_hang', 'lo_hang.ma_lo_hang'),

                // lệnh giao hàng
                'ma_lenh_giao_hang' => DB::table('lenh_giao_hang')
                    ->selectRaw("GROUP_CONCAT(DISTINCT ma_lenh_giao_hang SEPARATOR ', ')")
                    ->whereColumn('lenh_giao_hang.ma_lo_hang', 'lo_hang.ma_lo_hang'),
                'do_tooltip' => DB::table('lenh_giao_hang')
                    ->selectRaw("GROUP_CONCAT(DISTINCT CONCAT(ma_lenh_giao_hang, ' (Ngày: ', IFNULL(DATE_FORMAT(ngay_phat_hanh, '%d/%m/%Y'), 'N/A'), ')') SEPARATOR '\n')")
                    ->whereColumn('lenh_giao_hang.ma_lo_hang', 'lo_hang.ma_lo_hang'),

                // biên bản giao nhận
                'ma_bien_ban_giao_nhan' => DB::table('bien_ban_giao_nhan')
                    ->selectRaw("GROUP_CONCAT(DISTINCT ma_bien_ban_giao_nhan SEPARATOR ', ')")
                    ->whereColumn('bien_ban_giao_nhan.ma_lo_hang', 'lo_hang.ma_lo_hang'),
                'bbgn_tooltip' => DB::table('bien_ban_giao_nhan')
                    ->leftJoin('hang_van_tai', 'bien_ban_giao_nhan.ma_hang_van_tai', '=', 'hang_van_tai.ma_hang_van_tai')
                    ->selectRaw("GROUP_CONCAT(DISTINCT CONCAT(ma_bien_ban_giao_nhan, ' (Nhà xe: ', IFNULL(ten_hang_van_tai, 'N/A'), ')') SEPARATOR '\n')")
                    ->whereColumn('bien_ban_giao_nhan.ma_lo_hang', 'lo_hang.ma_lo_hang'),

                // tờ khai hải quan
                'ma_to_khai_hai_quan' => DB::table('to_khai_hai_quan')
                    ->selectRaw("GROUP_CONCAT(DISTINCT ma_to_khai_hai_quan SEPARATOR ', ')")
                    ->whereColumn('to_khai_hai_quan.ma_lo_hang', 'lo_hang.ma_lo_hang'),
                'to_khai_tooltip' => DB::table('to_khai_hai_quan')
                    ->selectRaw("GROUP_CONCAT(DISTINCT CONCAT(ma_to_khai_hai_quan, ' [', IFNULL(phan_luong, 'N/A'), ' - ', IFNULL(ket_qua_thong_quan, 'N/A'), ']') SEPARATOR '\n')")
                    ->whereColumn('to_khai_hai_quan.ma_lo_hang', 'lo_hang.ma_lo_hang'),

                // count chi tiết lô hàng
                'has_items' => DB::table('chi_tiet_lo_hang')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('chi_tiet_lo_hang.ma_lo_hang', 'lo_hang.ma_lo_hang')
                    ->where('thoi_gian_xoa', '<', '2000-01-01'),

                // ds mã hàng
                'ds_ma_hang_hoa' => DB::table('chi_tiet_lo_hang')
                    ->selectRaw("GROUP_CONCAT(DISTINCT ma_hang_hoa SEPARATOR ', ')")
                    ->whereColumn('chi_tiet_lo_hang.ma_lo_hang', 'lo_hang.ma_lo_hang')
                    ->where('thoi_gian_xoa', '<', '2000-01-01'),

                // ds tên hàng
                'ds_ten_hang' => DB::table('chi_tiet_lo_hang')
                    ->selectRaw("GROUP_CONCAT(DISTINCT ten_hang SEPARATOR ', ')")
                    ->whereColumn('chi_tiet_lo_hang.ma_lo_hang', 'lo_hang.ma_lo_hang')
                    ->where('thoi_gian_xoa', '<', '2000-01-01'),

            ])

            ->where('lo_hang.thoi_gian_xoa', '<', '2000-01-01')

            ->orderByDesc('lo_hang.ma_lo_hang')

            ->get();

        return response()->json([
            "success" => true,
            "data" => $data
        ]);

    } catch (\Exception $e) {

        return response()->json([
            "success" => false,
            "message" => $e->getMessage()
        ]);
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

        if (empty($data['ten_lo_hang'])) {
            return response()->json(['success' => false, 'message' => 'Vui lòng nhập tên lô hàng.']);
        }

        try {
            if ($ma_lo_hang) {
                DB::table('lo_hang')->where('ma_lo_hang', $ma_lo_hang)->update($data);
                $message = "Cập nhật lô hàng thành công!";
                return response()->json(['success' => true, 'message' => $message, 'ma_lo_hang' => $ma_lo_hang]);
            } else {
                $newId = DB::table('lo_hang')->insertGetId($data);
                $message = "Tạo lô hàng mới thành công!";
                return response()->json(['success' => true, 'ma_lo_hang' => $newId, 'message' => $message]);
            }
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
    
    public function getReferences(Request $request)
    {
        try {
            $ma_lo_hang = $request->query('ma_lo_hang');

            $khachHang = DB::table('khach_hang')
                ->where('thoi_gian_xoa', '<', '2000-01-01')
                ->get(['ma_khach_hang', 'ten_khach_hang']);

            $booking = DB::table('booking')
                ->where('thoi_gian_xoa', '<', '2000-01-01')
                ->whereNotIn('ma_booking', function($q) use ($ma_lo_hang) {
                    $q->select('ma_booking')
                      ->from('lo_hang')
                      ->where('thoi_gian_xoa', '<', '2000-01-01')
                      ->whereNotNull('ma_booking');
                    
                    if ($ma_lo_hang) {
                        $q->where('ma_lo_hang', '!=', $ma_lo_hang);
                    }
                })
                ->get(['ma_booking', 'so_booking', 'ten_con_tau']);

            return response()->json([
                "success" => true,
                "khach_hang" => $khachHang,
                "booking" => $booking
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "Lỗi tải danh mục"
            ]);
        }
    }
}
