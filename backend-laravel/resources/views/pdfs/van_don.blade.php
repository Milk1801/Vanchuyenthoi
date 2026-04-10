<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Vận Đơn - {{ $vanDon->so_van_don }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; line-height: 1.2; color: #000; margin: 0; padding: 0; }
        .bl-container { border: 1px solid #000; width: 100%; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        td { border: 1px solid #000; padding: 4px; vertical-align: top; }
        .label { font-size: 8px; font-weight: bold; text-transform: uppercase; display: block; margin-bottom: 2px; }
        .content { font-weight: bold; font-size: 10px; min-height: 40px; }
        .header-title { text-align: center; font-size: 16px; font-weight: bold; border: none !important; }
        .no-border { border: none !important; }
        .bg-gray { background-color: #f0f0f0; }
        .text-center { text-align: center; }
        .goods-table td { border-left: 1px solid #000; border-right: 1px solid #000; border-top: none; border-bottom: none; height: 250px; }
        .goods-header td { text-align: center; font-weight: bold; border-bottom: 1px solid #000; }
    </style>
</head>
<body>
    <div style="text-align: right; font-weight: bold; font-size: 14px; margin-bottom: 5px;">B/L No: {{ $vanDon->so_van_don }}</div>
    
    <div class="bl-container">
        <table>
            <tr>
                <td colspan="2" rowspan="2">
                    <span class="label">Shipper</span>
                    <div class="content">{{ $vanDon->ten_nguoi_gui_hang }}<br>{{ $vanDon->dia_chi_nguoi_gui }}</div>
                </td>
                <td colspan="2" class="header-title">BILL OF LADING</td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="label">Booking No.</span>
                    <div class="content">{{ $vanDon->ten_lo_hang }}</div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="label">Consignee</span>
                    <div class="content">{{ $vanDon->ten_nguoi_nhan_hang }}<br>{{ $vanDon->dia_chi_nguoi_nhan }}</div>
                </td>
                <td colspan="2">
                    <span class="label">Export References</span>
                    <div class="content">{{ $vanDon->so_van_don_goc }}</div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="label">Notify Party</span>
                    <div class="content">
                        @if($vanDon->ma_ben_duoc_thong_bao && $vanDon->ma_ben_duoc_thong_bao == $vanDon->ma_nguoi_nhan_hang)
                            SAME AS CONSIGNEE
                        @else
                            {{ $vanDon->ten_ben_duoc_thong_bao }}<br>{{ $vanDon->dia_chi_ben_thong_bao }}
                        @endif
                    </div>
                </td>
                <td colspan="2">
                    <span class="label">Forwarding Agent</span>
                    <div class="content">SINCERE LOGISTICS CO., LTD</div>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Pre-Carriage by</span>
                    <div class="content"></div>
                </td>
                <td>
                    <span class="label">Place of Receipt</span>
                    <div class="content">{{ $vanDon->ten_cang_di }}</div>
                </td>
                <td>
                    <span class="label">Ocean Vessel / Voy No.</span>
                    <div class="content"></div>
                </td>
                <td>
                    <span class="label">Port of Loading</span>
                    <div class="content">{{ $vanDon->ten_cang_di }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Port of Discharge</span>
                    <div class="content">{{ $vanDon->ten_cang_den }}</div>
                </td>
                <td>
                    <span class="label">Place of Delivery</span>
                    <div class="content">{{ $vanDon->ten_cang_den }}</div>
                </td>
                <td colspan="2">
                    <span class="label">Final Destination</span>
                    <div class="content">{{ $vanDon->ten_cang_den }}</div>
                </td>
            </tr>
        </table>

        <table class="goods-header">
            <tr>
                <td width="25%">Container No. / Seal No.</td>
                <td width="15%">No. of Pkgs</td>
                <td width="40%">Description of Goods</td>
                <td width="20%">Gross Weight / Measurement</td>
            </tr>
        </table>
        <table class="goods-table">
            <tr>
                <td width="25%">{{ $vanDon->so_cont }} / {{ $vanDon->so_chi }}</td>
                <td width="15%" class="text-center">
                    @foreach($chiTietHangHoa as $item)
                        {{ $item->so_luong }} {{ $item->ten_don_vi_tinh }}<br>
                    @endforeach
                </td>
                <td width="40%">
                    <strong>{{ $vanDon->phuong_thuc_dong_cont }}</strong><br>
                    @foreach($chiTietHangHoa as $item)
                        - {{ $item->ten_hang_hoa }}<br>
                    @endforeach
                </td>
                <td width="20%" class="text-center">
                    @foreach($chiTietHangHoa as $item)
                        {{ $item->trong_luong }} KGS / {{ $item->the_tich }} CBM<br>
                    @endforeach
                </td>
            </tr>
        </table>

        <table>
            <tr>
                <td colspan="2">
                    <span class="label">Freight & Charges</span>
                    <div class="content">{{ $vanDon->dieu_kien_cuoc }}</div>
                </td>
                <td>
                    <span class="label">Payable at</span>
                    <div class="content">
                        @if(str_contains(strtolower($vanDon->dieu_kien_cuoc), 'collect'))
                            DESTINATION
                        @else
                            ORIGIN
                        @endif
                    </div>
                </td>
                <td>
                    <span class="label">Total No. of Containers</span>
                    <div class="content">01 Container Only</div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="label">Place and Date of Issue</span>
                    <div class="content">HAIPHONG, {{ $vanDon->ngay_phat_hanh_formatted }}</div>
                </td>
                <td colspan="2" rowspan="2" style="text-align: center;">
                    <span class="label">Signed for the Carrier</span>
                    <br><br><br>
                    <div style="border-top: 1px dashed #000; display: inline-block; width: 80%;">SINCERE LOGISTICS</div>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Type of Movement</span>
                    <div class="content">{{ $vanDon->phuong_thuc_dong_cont }}</div>
                </td>
                <td>
                    <span class="label">B/L Type</span>
                    <div class="content">{{ $vanDon->loai_van_don }}</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
