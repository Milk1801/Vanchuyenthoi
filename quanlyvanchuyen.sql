-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th6 04, 2026 lúc 09:37 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `quanlyvanchuyen`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `bien_ban_giao_nhan`
--

CREATE TABLE `bien_ban_giao_nhan` (
  `ma_bien_ban_giao_nhan` int(11) NOT NULL,
  `ngay_phat_hanh` datetime DEFAULT NULL,
  `ma_lo_hang` int(11) DEFAULT NULL,
  `ma_hang_van_tai` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `bien_ban_giao_nhan`
--

INSERT INTO `bien_ban_giao_nhan` (`ma_bien_ban_giao_nhan`, `ngay_phat_hanh`, `ma_lo_hang`, `ma_hang_van_tai`) VALUES
(1, '2026-04-13 15:21:00', 6, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `booking`
--

CREATE TABLE `booking` (
  `ma_booking` int(11) NOT NULL,
  `so_booking` varchar(30) NOT NULL,
  `ten_con_tau` varchar(100) DEFAULT NULL,
  `so_chuyen` varchar(30) DEFAULT NULL,
  `etd` datetime DEFAULT NULL,
  `eta` datetime DEFAULT NULL,
  `gio_cat_mang` datetime DEFAULT NULL,
  `ma_cang_di` int(11) DEFAULT NULL,
  `ma_cang_den` int(11) DEFAULT NULL,
  `ma_hang_tau` int(11) DEFAULT NULL,
  `nguoi_sua_cuoi` int(11) DEFAULT NULL,
  `thoi_gian_xoa` timestamp NOT NULL DEFAULT '1970-01-01 00:00:01'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `booking`
--

INSERT INTO `booking` (`ma_booking`, `so_booking`, `ten_con_tau`, `so_chuyen`, `etd`, `eta`, `gio_cat_mang`, `ma_cang_di`, `ma_cang_den`, `ma_hang_tau`, `nguoi_sua_cuoi`, `thoi_gian_xoa`) VALUES
(1, '123', '1', '1', '2026-04-23 23:13:00', '2026-04-15 23:13:00', '2026-04-05 13:12:00', 1, 4, 2, 2, '0000-00-00 00:00:00'),
(2, '124', 'V1', '124', '2026-04-16 22:19:00', '2026-04-10 22:19:00', '2026-04-10 22:18:00', 1, 4, 2, 2, '0000-00-00 00:00:00'),
(3, '125', 'V2', '125', '2026-04-10 22:20:00', '2026-04-10 22:20:00', '2026-04-10 22:20:00', 1, 4, 3, 2, '0000-00-00 00:00:00'),
(4, '126', '1v', '126', '2026-04-17 15:20:00', '2026-04-17 15:20:00', '2026-04-10 15:20:00', 1, 4, 2, 2, '0000-00-00 00:00:00'),
(5, '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, '2026-04-12 01:35:31'),
(6, '234', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 2, '2026-04-12 01:38:58'),
(7, '235', 'Tàu Hải Đông', '432', '2026-04-18 01:44:00', '2026-04-19 01:44:00', '2026-04-14 01:43:00', 1, 5, 2, 1, '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cang_bien`
--

CREATE TABLE `cang_bien` (
  `ma_cang` int(11) NOT NULL,
  `ten_cang` varchar(50) NOT NULL,
  `dia_chi` varchar(200) DEFAULT NULL,
  `ghi_chu` varchar(500) DEFAULT NULL,
  `thoi_gian_xoa` timestamp NOT NULL DEFAULT '1970-01-01 00:00:01'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `cang_bien`
--

INSERT INTO `cang_bien` (`ma_cang`, `ten_cang`, `dia_chi`, `ghi_chu`, `thoi_gian_xoa`) VALUES
(1, 'Hải Phòng 1', 'Hải Phòng 1', 'Quan trọng', '0000-00-00 00:00:00'),
(2, 'Hải Phòng 2', 'Hải Phòng 2', '2', '2026-04-04 21:32:50'),
(3, 'Hải Phòng 3', 'Hải Phòng 3', '3', '2026-04-05 02:58:23'),
(4, 'Hải Phòng 450', 'Hải Phòng 4', '1', '0000-00-00 00:00:00'),
(5, 'Hà Nội 10', 'Hà Nội 1', NULL, '0000-00-00 00:00:00'),
(6, 'Kho A', NULL, NULL, '1970-01-01 00:00:01');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chi_phi`
--

CREATE TABLE `chi_phi` (
  `ma_chi_phi` int(11) NOT NULL,
  `ten_chi_phi` varchar(50) DEFAULT NULL,
  `tong_tien` decimal(19,4) DEFAULT NULL,
  `trang_thai_thanh_toan` enum('Chưa thanh toán','Thanh toán một phần','Đã thanh toán') DEFAULT NULL,
  `ngay_thanh_toan` date DEFAULT NULL,
  `loai_giao_dich` enum('THU','CHI') DEFAULT NULL,
  `ma_lo_hang` int(11) DEFAULT NULL,
  `nguoi_sua_cuoi` int(11) DEFAULT NULL,
  `thoi_gian_xoa` timestamp NOT NULL DEFAULT '1970-01-01 00:00:01'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `chi_phi`
--

INSERT INTO `chi_phi` (`ma_chi_phi`, `ten_chi_phi`, `tong_tien`, `trang_thai_thanh_toan`, `ngay_thanh_toan`, `loai_giao_dich`, `ma_lo_hang`, `nguoi_sua_cuoi`, `thoi_gian_xoa`) VALUES
(1, 'Phí dịch vụ', 100000.0000, 'Thanh toán một phần', '2026-04-13', 'THU', 6, 1, '1970-01-01 00:00:01');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chi_tiet_lo_hang`
--

CREATE TABLE `chi_tiet_lo_hang` (
  `ma_chi_tiet_lo_hang` int(11) NOT NULL,
  `ten_hang` varchar(30) DEFAULT NULL,
  `so_luong` smallint(6) DEFAULT NULL,
  `so_kien` smallint(6) DEFAULT NULL,
  `the_tich` decimal(10,4) DEFAULT NULL,
  `trong_luong` decimal(10,4) DEFAULT NULL,
  `gia_ca` decimal(19,4) DEFAULT NULL,
  `ma_hang_hoa` int(11) DEFAULT NULL,
  `ma_lo_hang` int(11) DEFAULT NULL,
  `ma_don_vi_tinh` int(11) DEFAULT NULL,
  `nguoi_sua_cuoi` int(11) DEFAULT NULL,
  `thoi_gian_xoa` timestamp NOT NULL DEFAULT '1970-01-01 00:00:01'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `chi_tiet_lo_hang`
--

INSERT INTO `chi_tiet_lo_hang` (`ma_chi_tiet_lo_hang`, `ten_hang`, `so_luong`, `so_kien`, `the_tich`, `trong_luong`, `gia_ca`, `ma_hang_hoa`, `ma_lo_hang`, `ma_don_vi_tinh`, `nguoi_sua_cuoi`, `thoi_gian_xoa`) VALUES
(1, 'Bóng nhựa', 1, 1, 1.0000, 1.0000, 1.0000, 2, 1, 3, 1, '0000-00-00 00:00:00'),
(2, 'Thùng gỗ nâu', 1, 1, 1.0000, 1.0000, 3.0000, 3, 1, 2, 1, '0000-00-00 00:00:00'),
(3, 'Thùng đỏ', 1, 1, 1.0000, 1.0000, 1.0000, 3, NULL, 2, 2, '0000-00-00 00:00:00'),
(4, 'Thùng vàng', 1, 1, 1.0000, 1.0000, 0.0000, 3, 5, 2, 2, '0000-00-00 00:00:00'),
(5, 'Xe đồ chơi', 1, 1, 1.0000, 1.0000, 1.0000, 2, 6, 2, 1, '0000-00-00 00:00:00'),
(6, 'Mô hình', 12, 1, 1.0000, 1.0000, 2.0000, 2, 1, 2, 1, '0000-00-00 00:00:00'),
(7, 'Thùng xanh', 1, 1, 1.0000, 1.0000, 1.0000, 3, 1, 3, 1, '0000-00-00 00:00:00'),
(8, 'Thùng nâu', 1, 1, 1.0000, 1.0000, 1.0000, 3, 1, 3, 1, '0000-00-00 00:00:00'),
(9, 'Thùng bóng', 1, 1, 0.0001, 1.0000, 1.0000, 2, 1, 4, 1, '0000-00-00 00:00:00'),
(10, 'Thùng giấy bìa', 1, 1, 1.0000, 1.0000, 100000.0000, 3, 9, 4, 1, '1970-01-01 00:00:01'),
(11, 'Máy bay nhựa', 1, 1, 1.0000, 1.0000, 1.0000, 2, 3, 2, 1, '1970-01-01 00:00:01');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chi_tiet_quyen`
--

CREATE TABLE `chi_tiet_quyen` (
  `ma_chi_tiet_quyen` int(11) NOT NULL,
  `ma_tai_khoan` int(11) NOT NULL,
  `ma_quyen` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `chi_tiet_quyen`
--

INSERT INTO `chi_tiet_quyen` (`ma_chi_tiet_quyen`, `ma_tai_khoan`, `ma_quyen`) VALUES
(2, 2, 4),
(3, 1, 5),
(14, 3, 2),
(17, 4, 2);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chung_tu_so_hoa`
--

CREATE TABLE `chung_tu_so_hoa` (
  `ma_chung_tu` int(11) NOT NULL,
  `loai_chung_tu` enum('SC','INV','PKL','CO','BL','DO','Khác') DEFAULT NULL,
  `hinh_anh` varchar(255) DEFAULT NULL,
  `ma_lo_hang` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `chung_tu_so_hoa`
--

INSERT INTO `chung_tu_so_hoa` (`ma_chung_tu`, `loai_chung_tu`, `hinh_anh`, `ma_lo_hang`) VALUES
(1, 'SC', 'uploads/chungtu/1775811709_INV.pdf', 1),
(2, 'INV', 'uploads/chungtu/1775811722_hinh anh he thong khai nop phi.jpg', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `don_vi_tinh`
--

CREATE TABLE `don_vi_tinh` (
  `ma_don_vi_tinh` int(11) NOT NULL,
  `ten_don_vi_tinh` varchar(50) NOT NULL,
  `thoi_gian_xoa` timestamp NOT NULL DEFAULT '1970-01-01 00:00:01'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `don_vi_tinh`
--

INSERT INTO `don_vi_tinh` (`ma_don_vi_tinh`, `ten_don_vi_tinh`, `thoi_gian_xoa`) VALUES
(1, 'Hộp', '2026-04-04 21:48:44'),
(2, 'Cái', '0000-00-00 00:00:00'),
(3, 'Hộp', '0000-00-00 00:00:00'),
(4, 'Thùng', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hang_hoa`
--

CREATE TABLE `hang_hoa` (
  `ma_hang_hoa` int(11) NOT NULL,
  `ten_hang_hoa` varchar(50) NOT NULL,
  `hs_code` varchar(10) DEFAULT NULL,
  `thoi_gian_xoa` timestamp NOT NULL DEFAULT '1970-01-01 00:00:01'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `hang_hoa`
--

INSERT INTO `hang_hoa` (`ma_hang_hoa`, `ten_hang_hoa`, `hs_code`, `thoi_gian_xoa`) VALUES
(1, 'Súng', '123456', '2026-04-04 21:49:15'),
(2, 'Đồ chơi', '123456', '0000-00-00 00:00:00'),
(3, 'Thùng gỗ', '542123', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hang_tau`
--

CREATE TABLE `hang_tau` (
  `ma_hang_tau` int(11) NOT NULL,
  `ten_hang_tau` varchar(50) NOT NULL,
  `dia_chi` varchar(200) DEFAULT NULL,
  `so_dien_thoai` varchar(15) DEFAULT NULL,
  `so_fax` varchar(20) DEFAULT NULL,
  `ghi_chu` varchar(500) DEFAULT NULL,
  `nguoi_sua_cuoi` int(11) DEFAULT NULL,
  `thoi_gian_xoa` timestamp NOT NULL DEFAULT '1970-01-01 00:00:01'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `hang_tau`
--

INSERT INTO `hang_tau` (`ma_hang_tau`, `ten_hang_tau`, `dia_chi`, `so_dien_thoai`, `so_fax`, `ghi_chu`, `nguoi_sua_cuoi`, `thoi_gian_xoa`) VALUES
(1, 'Hãng 1', '123', '0123456789', '1000-1102', '1', 2, '2026-04-04 21:35:47'),
(2, 'Abc', '123', '0123456789', '1000-1111', '1', 2, '0000-00-00 00:00:00'),
(3, 'Cdf', 'Trung Quốc', '098712634', '4333-1111', NULL, 2, '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hang_van_tai`
--

CREATE TABLE `hang_van_tai` (
  `ma_hang_van_tai` int(11) NOT NULL,
  `ten_hang_van_tai` varchar(50) NOT NULL,
  `dia_chi` varchar(200) DEFAULT NULL,
  `so_dien_thoai` varchar(15) DEFAULT NULL,
  `tuyen_thuong_xuyen` text DEFAULT NULL,
  `cac_loai_xe` text DEFAULT NULL,
  `ghi_chu` varchar(500) DEFAULT NULL,
  `thoi_gian_xoa` timestamp NOT NULL DEFAULT '1970-01-01 00:00:01'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `hang_van_tai`
--

INSERT INTO `hang_van_tai` (`ma_hang_van_tai`, `ten_hang_van_tai`, `dia_chi`, `so_dien_thoai`, `tuyen_thuong_xuyen`, `cac_loai_xe`, `ghi_chu`, `thoi_gian_xoa`) VALUES
(1, 'Hãng xe lớn', NULL, NULL, 'Hà Nội - Hải Phòng', 'Xe tải', NULL, '1970-01-01 00:00:01'),
(2, 'adsd', NULL, NULL, 'adsád', 'ađá', NULL, '1970-01-01 00:00:01');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `khach_hang`
--

CREATE TABLE `khach_hang` (
  `ma_khach_hang` int(11) NOT NULL,
  `ten_khach_hang` varchar(50) NOT NULL,
  `dia_chi` varchar(200) DEFAULT NULL,
  `so_dien_thoai` varchar(15) DEFAULT NULL,
  `so_fax` varchar(20) DEFAULT NULL,
  `ghi_chu` varchar(500) DEFAULT NULL,
  `nguoi_sua_cuoi` int(11) DEFAULT NULL,
  `thoi_gian_xoa` timestamp NOT NULL DEFAULT '1970-01-01 00:00:01'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `khach_hang`
--

INSERT INTO `khach_hang` (`ma_khach_hang`, `ten_khach_hang`, `dia_chi`, `so_dien_thoai`, `so_fax`, `ghi_chu`, `nguoi_sua_cuoi`, `thoi_gian_xoa`) VALUES
(1, 'Công ty A', '123', '0123456789', '8000-1000', '1', 2, '0000-00-00 00:00:00'),
(2, 'Công ty B', '456', '0123456788', '1000-1111', '?', 2, '0000-00-00 00:00:00'),
(3, 'a', NULL, NULL, NULL, NULL, 2, '1970-01-01 00:00:01'),
(4, 'n', NULL, NULL, NULL, NULL, 2, '1970-01-01 00:00:01'),
(5, 'b', NULL, NULL, NULL, NULL, 2, '1970-01-01 00:00:01'),
(6, 'nnb', NULL, NULL, NULL, NULL, 2, '1970-01-01 00:00:01');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lenh_giao_hang`
--

CREATE TABLE `lenh_giao_hang` (
  `ma_lenh_giao_hang` int(11) NOT NULL,
  `ngay_phat_hanh` datetime DEFAULT NULL,
  `ma_lo_hang` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `lenh_giao_hang`
--

INSERT INTO `lenh_giao_hang` (`ma_lenh_giao_hang`, `ngay_phat_hanh`, `ma_lo_hang`) VALUES
(1, '2026-04-13 15:42:00', 6);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lo_hang`
--

CREATE TABLE `lo_hang` (
  `ma_lo_hang` int(11) NOT NULL,
  `ten_lo_hang` varchar(50) NOT NULL,
  `dieu_kien_thuong_mai` enum('FOB','CIF','EXW','DAP','DDP','CFR') DEFAULT NULL,
  `trang_thai_lo_hang` enum('Đang chờ xử lý','Đang vận chuyển','Đã thông quan','Hoàn tất','Hủy') DEFAULT NULL,
  `nguon_goc` varchar(100) DEFAULT NULL,
  `ma_booking` int(11) DEFAULT NULL,
  `ma_khach_hang` int(11) DEFAULT NULL,
  `nguoi_sua_cuoi` int(11) DEFAULT NULL,
  `thoi_gian_xoa` timestamp NOT NULL DEFAULT '1970-01-01 00:00:01'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `lo_hang`
--

INSERT INTO `lo_hang` (`ma_lo_hang`, `ten_lo_hang`, `dieu_kien_thuong_mai`, `trang_thai_lo_hang`, `nguon_goc`, `ma_booking`, `ma_khach_hang`, `nguoi_sua_cuoi`, `thoi_gian_xoa`) VALUES
(1, 'Lô hàng mới', 'FOB', 'Hoàn tất', NULL, 2, 1, 4, '0000-00-00 00:00:00'),
(2, 'Lô hàng thùng gỗ', 'CIF', 'Mới tạo', NULL, 1, 2, 2, '2026-04-10 06:58:40'),
(3, 'Lô hàng thùng gỗ 2', 'FOB', 'Hoàn tất', NULL, 4, 2, 1, '0000-00-00 00:00:00'),
(4, 'Thùng gỗ 2', 'FOB', 'Mới tạo', NULL, 1, 1, 2, '2026-04-10 07:23:13'),
(5, 'Thùng gỗ vàng', 'FOB', 'Đang chờ xử lý', NULL, 1, 2, 2, '2026-04-10 08:20:42'),
(6, 'Hàng nóng', 'FOB', 'Đang chờ xử lý', NULL, 7, 1, 1, '0000-00-00 00:00:00'),
(7, '2', 'FOB', 'Mới tạo', NULL, NULL, NULL, 1, '2026-04-12 14:41:04'),
(8, 'Lô hàng thùng nhựa', 'FOB', 'Đang chờ xử lý', NULL, NULL, NULL, 1, '2026-04-12 14:56:11'),
(9, 'Lô thùng giấy', 'FOB', 'Đang chờ xử lý', 'Việt Nam', 3, 2, 1, '1970-01-01 00:00:01');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `quyen`
--

CREATE TABLE `quyen` (
  `ma_quyen` int(11) NOT NULL,
  `ten_quyen` varchar(50) NOT NULL,
  `trang_thai` enum('Hoạt động','Tạm khóa') DEFAULT 'Hoạt động',
  `thoi_gian_xoa` timestamp NOT NULL DEFAULT '1970-01-01 00:00:01'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `quyen`
--

INSERT INTO `quyen` (`ma_quyen`, `ten_quyen`, `trang_thai`, `thoi_gian_xoa`) VALUES
(1, 'Chứng từ', 'Hoạt động', '1970-01-01 00:00:01'),
(2, 'Kế toán', 'Hoạt động', '1970-01-01 00:00:01'),
(3, 'Giao nhận', 'Hoạt động', '1970-01-01 00:00:01'),
(4, 'Khai báo hải quan', 'Hoạt động', '1970-01-01 00:00:01'),
(5, 'Admin', 'Hoạt động', '1970-01-01 00:00:01');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tai_khoan`
--

CREATE TABLE `tai_khoan` (
  `ma_tai_khoan` int(11) NOT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `trang_thai` enum('Hoạt động','Tạm khóa') DEFAULT 'Hoạt động',
  `email` varchar(100) NOT NULL,
  `ho_ten` varchar(50) NOT NULL,
  `ngay_sinh` date DEFAULT NULL,
  `thoi_gian_xoa` timestamp NOT NULL DEFAULT '1970-01-01 00:00:01'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `tai_khoan`
--

INSERT INTO `tai_khoan` (`ma_tai_khoan`, `mat_khau`, `trang_thai`, `email`, `ho_ten`, `ngay_sinh`, `thoi_gian_xoa`) VALUES
(1, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Hoạt động', 'duongminhle99@gmail.com', 'Lê Nhật Minh', '2004-01-18', '1970-01-01 00:00:01'),
(2, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Hoạt động', 'minh95261@st.vimaru.edu.vn', 'Lê Hoàng Linh', '2026-04-23', '1970-01-01 00:00:01'),
(3, '$2y$12$YRC5xtorKVk/ON7FWSRkDOXmld4xmVvAxs2mDIb9W/2h9aG3yTIUa', 'Hoạt động', 'longhainguyen@gmail.com', 'Nguyễn Hải Long', '2000-01-06', '1970-01-01 00:00:01'),
(4, '$2y$12$wr3TBmBsMl2nsEGq1NJGkOrzI4MZSE2KCGCrgfMFndCxa0BXspWfy', 'Hoạt động', 'a@gmail.com', 'Nhân viên mới', NULL, '1970-01-01 00:00:01');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thong_bao_hang_den`
--

CREATE TABLE `thong_bao_hang_den` (
  `ma_thong_bao_hang_den` int(11) NOT NULL,
  `ngay_phat_hanh` datetime DEFAULT NULL,
  `ma_lo_hang` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `thong_bao_hang_den`
--

INSERT INTO `thong_bao_hang_den` (`ma_thong_bao_hang_den`, `ngay_phat_hanh`, `ma_lo_hang`) VALUES
(1, '2026-04-13 15:14:00', 6);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thong_tin_luu_bai`
--

CREATE TABLE `thong_tin_luu_bai` (
  `ma_luu_bai` int(11) NOT NULL,
  `trang_thai_luu_bai` enum('Đang lưu bãi','Đã rút hàng','Đã trả vỏ') DEFAULT NULL,
  `ngay_bat_dau_luu_bai` datetime DEFAULT NULL,
  `ngay_luu_bai_mien_phi` smallint(6) DEFAULT NULL,
  `cuoc_vo` enum('Có','Không') DEFAULT 'Không',
  `ma_lo_hang` int(11) DEFAULT NULL,
  `nguoi_sua_cuoi` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `thong_tin_luu_bai`
--

INSERT INTO `thong_tin_luu_bai` (`ma_luu_bai`, `trang_thai_luu_bai`, `ngay_bat_dau_luu_bai`, `ngay_luu_bai_mien_phi`, `cuoc_vo`, `ma_lo_hang`, `nguoi_sua_cuoi`) VALUES
(1, 'Đang lưu bãi', '2026-05-21 15:46:00', 5, 'Không', 9, 4),
(2, 'Đã rút hàng', '2026-05-23 06:23:00', 0, 'Không', 6, 4),
(3, 'Đã trả vỏ', '2026-05-23 20:32:00', 0, 'Không', 3, 4),
(4, 'Đã rút hàng', '2026-05-19 08:32:00', 2, 'Không', 1, 4);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `to_khai_hai_quan`
--

CREATE TABLE `to_khai_hai_quan` (
  `ma_to_khai_hai_quan` int(11) NOT NULL,
  `ngay_thong_quan` datetime DEFAULT NULL,
  `phan_luong` enum('Luồng Xanh','Luồng Vàng','Luồng Đỏ') DEFAULT NULL,
  `ket_qua_thong_quan` enum('Đã thông quan','Chờ xử lý','Từ chối') DEFAULT NULL,
  `ma_lo_hang` int(11) DEFAULT NULL,
  `nguoi_sua_cuoi` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `to_khai_hai_quan`
--

INSERT INTO `to_khai_hai_quan` (`ma_to_khai_hai_quan`, `ngay_thong_quan`, `phan_luong`, `ket_qua_thong_quan`, `ma_lo_hang`, `nguoi_sua_cuoi`) VALUES
(1, '2026-04-14 13:54:00', 'Luồng Xanh', 'Đã thông quan', 1, 1),
(2, NULL, 'Luồng Vàng', 'Chờ xử lý', 6, 2);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `van_don`
--

CREATE TABLE `van_don` (
  `ma_van_don` int(11) NOT NULL,
  `loai_van_don` enum('Original B/L','Surrendered B/L','Seaway Bill') DEFAULT NULL,
  `ngay_phat_hanh` datetime DEFAULT NULL,
  `so_van_don_goc` varchar(30) DEFAULT NULL,
  `so_van_don` varchar(30) DEFAULT NULL,
  `so_cont` varchar(11) DEFAULT NULL,
  `so_chi` varchar(20) DEFAULT NULL,
  `phuong_thuc_dong_cont` enum('FCL','LCL') DEFAULT NULL,
  `dieu_kien_cuoc` enum('Freight Prepaid','Freight Collect') DEFAULT NULL,
  `ma_nguoi_gui_hang` int(11) DEFAULT NULL,
  `ma_nguoi_nhan_hang` int(11) DEFAULT NULL,
  `ma_ben_duoc_thong_bao` int(11) DEFAULT NULL,
  `ma_cang_di` int(11) DEFAULT NULL,
  `ma_cang_den` int(11) DEFAULT NULL,
  `ma_lo_hang` int(11) DEFAULT NULL,
  `nguoi_sua_cuoi` int(11) DEFAULT NULL,
  `thoi_gian_xoa` timestamp NOT NULL DEFAULT '1970-01-01 00:00:01'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `van_don`
--

INSERT INTO `van_don` (`ma_van_don`, `loai_van_don`, `ngay_phat_hanh`, `so_van_don_goc`, `so_van_don`, `so_cont`, `so_chi`, `phuong_thuc_dong_cont`, `dieu_kien_cuoc`, `ma_nguoi_gui_hang`, `ma_nguoi_nhan_hang`, `ma_ben_duoc_thong_bao`, `ma_cang_di`, `ma_cang_den`, `ma_lo_hang`, `nguoi_sua_cuoi`, `thoi_gian_xoa`) VALUES
(1, 'Original B/L', '2026-04-13 08:33:00', NULL, 'HPH20024', 'CONT123', 'S3456', 'FCL', 'Freight Prepaid', 1, 2, 2, 1, 4, 6, 1, '1970-01-01 00:00:01'),
(2, 'Original B/L', NULL, NULL, 'HBOU', NULL, NULL, 'FCL', 'Freight Prepaid', 1, 2, 2, NULL, NULL, 9, 1, '1970-01-01 00:00:01'),
(3, 'Original B/L', NULL, NULL, 'h', NULL, NULL, 'FCL', 'Freight Prepaid', 1, 2, 2, 1, 5, 9, 1, '1970-01-01 00:00:01'),
(4, 'Original B/L', '2026-05-14 15:12:00', 'hdai', 'đâ', 'HANH', 'GAUH123', 'FCL', 'Freight Prepaid', 1, 2, 2, 1, 4, 9, 1, '1970-01-01 00:00:01'),
(5, 'Original B/L', NULL, NULL, 'op', NULL, NULL, 'FCL', 'Freight Prepaid', NULL, NULL, NULL, NULL, NULL, 6, 2, '1970-01-01 00:00:01'),
(6, 'Original B/L', NULL, NULL, 'phph', NULL, NULL, 'FCL', 'Freight Prepaid', NULL, NULL, NULL, NULL, NULL, 6, 2, '1970-01-01 00:00:01'),
(7, 'Original B/L', NULL, NULL, 'ôiyi', NULL, NULL, 'FCL', 'Freight Prepaid', NULL, NULL, NULL, NULL, NULL, 6, 2, '1970-01-01 00:00:01'),
(8, 'Original B/L', NULL, NULL, 'uoiu', NULL, NULL, 'FCL', 'Freight Prepaid', NULL, NULL, NULL, NULL, NULL, 1, 2, '1970-01-01 00:00:01');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `bien_ban_giao_nhan`
--
ALTER TABLE `bien_ban_giao_nhan`
  ADD PRIMARY KEY (`ma_bien_ban_giao_nhan`),
  ADD KEY `ma_lo_hang` (`ma_lo_hang`),
  ADD KEY `ma_hang_van_tai` (`ma_hang_van_tai`);

--
-- Chỉ mục cho bảng `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`ma_booking`),
  ADD KEY `ma_cang_di` (`ma_cang_di`),
  ADD KEY `ma_cang_den` (`ma_cang_den`),
  ADD KEY `ma_hang_tau` (`ma_hang_tau`),
  ADD KEY `nguoi_sua_cuoi` (`nguoi_sua_cuoi`),
  ADD KEY `thoi_gian_xoa` (`thoi_gian_xoa`);

--
-- Chỉ mục cho bảng `cang_bien`
--
ALTER TABLE `cang_bien`
  ADD PRIMARY KEY (`ma_cang`),
  ADD KEY `thoi_gian_xoa` (`thoi_gian_xoa`);

--
-- Chỉ mục cho bảng `chi_phi`
--
ALTER TABLE `chi_phi`
  ADD PRIMARY KEY (`ma_chi_phi`),
  ADD KEY `ma_lo_hang` (`ma_lo_hang`),
  ADD KEY `nguoi_sua_cuoi` (`nguoi_sua_cuoi`),
  ADD KEY `thoi_gian_xoa` (`thoi_gian_xoa`);

--
-- Chỉ mục cho bảng `chi_tiet_lo_hang`
--
ALTER TABLE `chi_tiet_lo_hang`
  ADD PRIMARY KEY (`ma_chi_tiet_lo_hang`),
  ADD KEY `ma_hang_hoa` (`ma_hang_hoa`),
  ADD KEY `ma_lo_hang` (`ma_lo_hang`),
  ADD KEY `ma_don_vi_tinh` (`ma_don_vi_tinh`),
  ADD KEY `nguoi_sua_cuoi` (`nguoi_sua_cuoi`),
  ADD KEY `thoi_gian_xoa` (`thoi_gian_xoa`);

--
-- Chỉ mục cho bảng `chi_tiet_quyen`
--
ALTER TABLE `chi_tiet_quyen`
  ADD PRIMARY KEY (`ma_chi_tiet_quyen`),
  ADD KEY `ma_tai_khoan` (`ma_tai_khoan`),
  ADD KEY `ma_quyen` (`ma_quyen`);

--
-- Chỉ mục cho bảng `chung_tu_so_hoa`
--
ALTER TABLE `chung_tu_so_hoa`
  ADD PRIMARY KEY (`ma_chung_tu`),
  ADD KEY `ma_lo_hang` (`ma_lo_hang`);

--
-- Chỉ mục cho bảng `don_vi_tinh`
--
ALTER TABLE `don_vi_tinh`
  ADD PRIMARY KEY (`ma_don_vi_tinh`),
  ADD KEY `thoi_gian_xoa` (`thoi_gian_xoa`);

--
-- Chỉ mục cho bảng `hang_hoa`
--
ALTER TABLE `hang_hoa`
  ADD PRIMARY KEY (`ma_hang_hoa`),
  ADD UNIQUE KEY `ten_hang_hoa` (`ten_hang_hoa`,`thoi_gian_xoa`),
  ADD UNIQUE KEY `hs_code` (`hs_code`,`thoi_gian_xoa`),
  ADD KEY `thoi_gian_xoa` (`thoi_gian_xoa`);

--
-- Chỉ mục cho bảng `hang_tau`
--
ALTER TABLE `hang_tau`
  ADD PRIMARY KEY (`ma_hang_tau`),
  ADD UNIQUE KEY `so_dien_thoai` (`so_dien_thoai`,`thoi_gian_xoa`),
  ADD UNIQUE KEY `so_fax` (`so_fax`,`thoi_gian_xoa`),
  ADD KEY `nguoi_sua_cuoi` (`nguoi_sua_cuoi`),
  ADD KEY `thoi_gian_xoa` (`thoi_gian_xoa`);

--
-- Chỉ mục cho bảng `hang_van_tai`
--
ALTER TABLE `hang_van_tai`
  ADD PRIMARY KEY (`ma_hang_van_tai`),
  ADD UNIQUE KEY `so_dien_thoai` (`so_dien_thoai`,`thoi_gian_xoa`),
  ADD KEY `thoi_gian_xoa` (`thoi_gian_xoa`);

--
-- Chỉ mục cho bảng `khach_hang`
--
ALTER TABLE `khach_hang`
  ADD PRIMARY KEY (`ma_khach_hang`),
  ADD UNIQUE KEY `so_dien_thoai` (`so_dien_thoai`,`thoi_gian_xoa`),
  ADD UNIQUE KEY `so_fax` (`so_fax`,`thoi_gian_xoa`),
  ADD KEY `nguoi_sua_cuoi` (`nguoi_sua_cuoi`),
  ADD KEY `thoi_gian_xoa` (`thoi_gian_xoa`);

--
-- Chỉ mục cho bảng `lenh_giao_hang`
--
ALTER TABLE `lenh_giao_hang`
  ADD PRIMARY KEY (`ma_lenh_giao_hang`),
  ADD KEY `ma_lo_hang` (`ma_lo_hang`);

--
-- Chỉ mục cho bảng `lo_hang`
--
ALTER TABLE `lo_hang`
  ADD PRIMARY KEY (`ma_lo_hang`),
  ADD KEY `ten_lo_hang` (`ten_lo_hang`),
  ADD KEY `ma_booking` (`ma_booking`),
  ADD KEY `ma_khach_hang` (`ma_khach_hang`),
  ADD KEY `nguoi_sua_cuoi` (`nguoi_sua_cuoi`),
  ADD KEY `thoi_gian_xoa` (`thoi_gian_xoa`);

--
-- Chỉ mục cho bảng `quyen`
--
ALTER TABLE `quyen`
  ADD PRIMARY KEY (`ma_quyen`),
  ADD UNIQUE KEY `ten_quyen` (`ten_quyen`,`thoi_gian_xoa`),
  ADD KEY `thoi_gian_xoa` (`thoi_gian_xoa`);

--
-- Chỉ mục cho bảng `tai_khoan`
--
ALTER TABLE `tai_khoan`
  ADD PRIMARY KEY (`ma_tai_khoan`),
  ADD UNIQUE KEY `email` (`email`,`thoi_gian_xoa`),
  ADD KEY `thoi_gian_xoa` (`thoi_gian_xoa`);

--
-- Chỉ mục cho bảng `thong_bao_hang_den`
--
ALTER TABLE `thong_bao_hang_den`
  ADD PRIMARY KEY (`ma_thong_bao_hang_den`),
  ADD KEY `ma_lo_hang` (`ma_lo_hang`);

--
-- Chỉ mục cho bảng `thong_tin_luu_bai`
--
ALTER TABLE `thong_tin_luu_bai`
  ADD PRIMARY KEY (`ma_luu_bai`),
  ADD KEY `ma_lo_hang` (`ma_lo_hang`),
  ADD KEY `nguoi_sua_cuoi` (`nguoi_sua_cuoi`);

--
-- Chỉ mục cho bảng `to_khai_hai_quan`
--
ALTER TABLE `to_khai_hai_quan`
  ADD PRIMARY KEY (`ma_to_khai_hai_quan`),
  ADD KEY `ma_lo_hang` (`ma_lo_hang`),
  ADD KEY `nguoi_sua_cuoi` (`nguoi_sua_cuoi`);

--
-- Chỉ mục cho bảng `van_don`
--
ALTER TABLE `van_don`
  ADD PRIMARY KEY (`ma_van_don`),
  ADD UNIQUE KEY `so_van_don` (`so_van_don`,`thoi_gian_xoa`),
  ADD KEY `ma_nguoi_gui_hang` (`ma_nguoi_gui_hang`),
  ADD KEY `ma_nguoi_nhan_hang` (`ma_nguoi_nhan_hang`),
  ADD KEY `ma_ben_duoc_thong_bao` (`ma_ben_duoc_thong_bao`),
  ADD KEY `ma_cang_di` (`ma_cang_di`),
  ADD KEY `ma_cang_den` (`ma_cang_den`),
  ADD KEY `ma_lo_hang` (`ma_lo_hang`),
  ADD KEY `nguoi_sua_cuoi` (`nguoi_sua_cuoi`),
  ADD KEY `thoi_gian_xoa` (`thoi_gian_xoa`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `bien_ban_giao_nhan`
--
ALTER TABLE `bien_ban_giao_nhan`
  MODIFY `ma_bien_ban_giao_nhan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `booking`
--
ALTER TABLE `booking`
  MODIFY `ma_booking` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `cang_bien`
--
ALTER TABLE `cang_bien`
  MODIFY `ma_cang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `chi_phi`
--
ALTER TABLE `chi_phi`
  MODIFY `ma_chi_phi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `chi_tiet_lo_hang`
--
ALTER TABLE `chi_tiet_lo_hang`
  MODIFY `ma_chi_tiet_lo_hang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT cho bảng `chi_tiet_quyen`
--
ALTER TABLE `chi_tiet_quyen`
  MODIFY `ma_chi_tiet_quyen` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT cho bảng `chung_tu_so_hoa`
--
ALTER TABLE `chung_tu_so_hoa`
  MODIFY `ma_chung_tu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `don_vi_tinh`
--
ALTER TABLE `don_vi_tinh`
  MODIFY `ma_don_vi_tinh` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `hang_hoa`
--
ALTER TABLE `hang_hoa`
  MODIFY `ma_hang_hoa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `hang_tau`
--
ALTER TABLE `hang_tau`
  MODIFY `ma_hang_tau` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `hang_van_tai`
--
ALTER TABLE `hang_van_tai`
  MODIFY `ma_hang_van_tai` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `khach_hang`
--
ALTER TABLE `khach_hang`
  MODIFY `ma_khach_hang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `lenh_giao_hang`
--
ALTER TABLE `lenh_giao_hang`
  MODIFY `ma_lenh_giao_hang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `lo_hang`
--
ALTER TABLE `lo_hang`
  MODIFY `ma_lo_hang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `quyen`
--
ALTER TABLE `quyen`
  MODIFY `ma_quyen` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `tai_khoan`
--
ALTER TABLE `tai_khoan`
  MODIFY `ma_tai_khoan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `thong_bao_hang_den`
--
ALTER TABLE `thong_bao_hang_den`
  MODIFY `ma_thong_bao_hang_den` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `thong_tin_luu_bai`
--
ALTER TABLE `thong_tin_luu_bai`
  MODIFY `ma_luu_bai` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `to_khai_hai_quan`
--
ALTER TABLE `to_khai_hai_quan`
  MODIFY `ma_to_khai_hai_quan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `van_don`
--
ALTER TABLE `van_don`
  MODIFY `ma_van_don` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `bien_ban_giao_nhan`
--
ALTER TABLE `bien_ban_giao_nhan`
  ADD CONSTRAINT `bien_ban_giao_nhan_ibfk_1` FOREIGN KEY (`ma_lo_hang`) REFERENCES `lo_hang` (`ma_lo_hang`) ON DELETE CASCADE,
  ADD CONSTRAINT `bien_ban_giao_nhan_ibfk_2` FOREIGN KEY (`ma_hang_van_tai`) REFERENCES `hang_van_tai` (`ma_hang_van_tai`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`ma_cang_di`) REFERENCES `cang_bien` (`ma_cang`) ON DELETE SET NULL,
  ADD CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`ma_cang_den`) REFERENCES `cang_bien` (`ma_cang`) ON DELETE SET NULL,
  ADD CONSTRAINT `booking_ibfk_3` FOREIGN KEY (`ma_hang_tau`) REFERENCES `hang_tau` (`ma_hang_tau`) ON DELETE SET NULL,
  ADD CONSTRAINT `booking_ibfk_nguoi_sua_cuoi` FOREIGN KEY (`nguoi_sua_cuoi`) REFERENCES `tai_khoan` (`ma_tai_khoan`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `chi_phi`
--
ALTER TABLE `chi_phi`
  ADD CONSTRAINT `chi_phi_ibfk_1` FOREIGN KEY (`ma_lo_hang`) REFERENCES `lo_hang` (`ma_lo_hang`) ON DELETE CASCADE,
  ADD CONSTRAINT `chi_phi_ibfk_nguoi_sua_cuoi` FOREIGN KEY (`nguoi_sua_cuoi`) REFERENCES `tai_khoan` (`ma_tai_khoan`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `chi_tiet_lo_hang`
--
ALTER TABLE `chi_tiet_lo_hang`
  ADD CONSTRAINT `chi_tiet_lo_hang_ibfk_1` FOREIGN KEY (`ma_hang_hoa`) REFERENCES `hang_hoa` (`ma_hang_hoa`) ON DELETE SET NULL,
  ADD CONSTRAINT `chi_tiet_lo_hang_ibfk_2` FOREIGN KEY (`ma_lo_hang`) REFERENCES `lo_hang` (`ma_lo_hang`) ON DELETE CASCADE,
  ADD CONSTRAINT `chi_tiet_lo_hang_ibfk_3` FOREIGN KEY (`ma_don_vi_tinh`) REFERENCES `don_vi_tinh` (`ma_don_vi_tinh`) ON DELETE SET NULL,
  ADD CONSTRAINT `chi_tiet_lo_hang_ibfk_nguoi_sua_cuoi` FOREIGN KEY (`nguoi_sua_cuoi`) REFERENCES `tai_khoan` (`ma_tai_khoan`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `chi_tiet_quyen`
--
ALTER TABLE `chi_tiet_quyen`
  ADD CONSTRAINT `chi_tiet_quyen_ibfk_1` FOREIGN KEY (`ma_tai_khoan`) REFERENCES `tai_khoan` (`ma_tai_khoan`) ON DELETE CASCADE,
  ADD CONSTRAINT `chi_tiet_quyen_ibfk_2` FOREIGN KEY (`ma_quyen`) REFERENCES `quyen` (`ma_quyen`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `chung_tu_so_hoa`
--
ALTER TABLE `chung_tu_so_hoa`
  ADD CONSTRAINT `chung_tu_so_hoa_ibfk_1` FOREIGN KEY (`ma_lo_hang`) REFERENCES `lo_hang` (`ma_lo_hang`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `hang_tau`
--
ALTER TABLE `hang_tau`
  ADD CONSTRAINT `hang_tau_ibfk_nguoi_sua_cuoi` FOREIGN KEY (`nguoi_sua_cuoi`) REFERENCES `tai_khoan` (`ma_tai_khoan`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `khach_hang`
--
ALTER TABLE `khach_hang`
  ADD CONSTRAINT `khach_hang_ibfk_nguoi_sua_cuoi` FOREIGN KEY (`nguoi_sua_cuoi`) REFERENCES `tai_khoan` (`ma_tai_khoan`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `lenh_giao_hang`
--
ALTER TABLE `lenh_giao_hang`
  ADD CONSTRAINT `lenh_giao_hang_ibfk_1` FOREIGN KEY (`ma_lo_hang`) REFERENCES `lo_hang` (`ma_lo_hang`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `lo_hang`
--
ALTER TABLE `lo_hang`
  ADD CONSTRAINT `lo_hang_ibfk_2` FOREIGN KEY (`ma_booking`) REFERENCES `booking` (`ma_booking`) ON DELETE SET NULL,
  ADD CONSTRAINT `lo_hang_ibfk_3` FOREIGN KEY (`ma_khach_hang`) REFERENCES `khach_hang` (`ma_khach_hang`),
  ADD CONSTRAINT `lo_hang_ibfk_nguoi_sua_cuoi` FOREIGN KEY (`nguoi_sua_cuoi`) REFERENCES `tai_khoan` (`ma_tai_khoan`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `thong_bao_hang_den`
--
ALTER TABLE `thong_bao_hang_den`
  ADD CONSTRAINT `thong_bao_hang_den_ibfk_1` FOREIGN KEY (`ma_lo_hang`) REFERENCES `lo_hang` (`ma_lo_hang`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `thong_tin_luu_bai`
--
ALTER TABLE `thong_tin_luu_bai`
  ADD CONSTRAINT `thong_tin_luu_bai_ibfk_1` FOREIGN KEY (`ma_lo_hang`) REFERENCES `lo_hang` (`ma_lo_hang`) ON DELETE CASCADE,
  ADD CONSTRAINT `thong_tin_luu_bai_ibfk_nguoi_sua_cuoi` FOREIGN KEY (`nguoi_sua_cuoi`) REFERENCES `tai_khoan` (`ma_tai_khoan`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `to_khai_hai_quan`
--
ALTER TABLE `to_khai_hai_quan`
  ADD CONSTRAINT `to_khai_hai_quan_ibfk_1` FOREIGN KEY (`ma_lo_hang`) REFERENCES `lo_hang` (`ma_lo_hang`) ON DELETE CASCADE,
  ADD CONSTRAINT `to_khai_hai_quan_ibfk_nguoi_sua_cuoi` FOREIGN KEY (`nguoi_sua_cuoi`) REFERENCES `tai_khoan` (`ma_tai_khoan`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `van_don`
--
ALTER TABLE `van_don`
  ADD CONSTRAINT `van_don_ibfk_1` FOREIGN KEY (`ma_nguoi_gui_hang`) REFERENCES `khach_hang` (`ma_khach_hang`) ON DELETE SET NULL,
  ADD CONSTRAINT `van_don_ibfk_2` FOREIGN KEY (`ma_nguoi_nhan_hang`) REFERENCES `khach_hang` (`ma_khach_hang`) ON DELETE SET NULL,
  ADD CONSTRAINT `van_don_ibfk_3` FOREIGN KEY (`ma_ben_duoc_thong_bao`) REFERENCES `khach_hang` (`ma_khach_hang`) ON DELETE SET NULL,
  ADD CONSTRAINT `van_don_ibfk_4` FOREIGN KEY (`ma_cang_di`) REFERENCES `cang_bien` (`ma_cang`) ON DELETE SET NULL,
  ADD CONSTRAINT `van_don_ibfk_5` FOREIGN KEY (`ma_cang_den`) REFERENCES `cang_bien` (`ma_cang`) ON DELETE SET NULL,
  ADD CONSTRAINT `van_don_ibfk_6` FOREIGN KEY (`ma_lo_hang`) REFERENCES `lo_hang` (`ma_lo_hang`) ON DELETE CASCADE,
  ADD CONSTRAINT `van_don_ibfk_nguoi_sua_cuoi` FOREIGN KEY (`nguoi_sua_cuoi`) REFERENCES `tai_khoan` (`ma_tai_khoan`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


-- Lệnh alter để cập nhật enum cho cột `trang_thai_lo_hang` trong bảng `lo_hang`
-- -- Bước 1: Cập nhật dữ liệu cũ (nếu có)
-- UPDATE `lo_hang` 
-- SET `trang_thai_lo_hang` = 'Đang chờ xử lý' 
-- WHERE `trang_thai_lo_hang` = 'Mới tạo';

-- -- Bước 2: Chỉnh sửa cấu trúc cột
-- ALTER TABLE `lo_hang` 
-- MODIFY COLUMN `trang_thai_lo_hang` ENUM('Đang chờ xử lý', 'Đang vận chuyển', 'Đã thông quan', 'Hoàn tất', 'Hủy') DEFAULT NULL;
