<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\QuanLyDangNhap;
use App\Http\Controllers\QuanLyTaiKhoan;
use App\Http\Controllers\QuanLyKhachHang;
use App\Http\Controllers\QuanLyHangTau;
use App\Http\Controllers\QuanLyKhoCang;
use App\Http\Controllers\QuanLyHangHoa;
use App\Http\Controllers\QuanLyHangVanTai;
use App\Http\Controllers\QuanLyDonViTinh;
use App\Http\Controllers\QuanLyChiPhi;

/*
|--------------------------------------------------------------------------
| 1. API ĐĂNG NHẬP
|--------------------------------------------------------------------------
*/
Route::post('/login', [QuanLyDangNhap::class, 'login']);

/*
|--------------------------------------------------------------------------
| 2. API QUẢN LÝ TÀI KHOẢN
|--------------------------------------------------------------------------
*/
Route::get('/accounts', [QuanLyTaiKhoan::class, 'index']);
Route::post('/accounts/save', [QuanLyTaiKhoan::class, 'save']);
Route::post('/accounts/delete', [QuanLyTaiKhoan::class, 'delete']);

/*
|--------------------------------------------------------------------------
| 3. API HỒ SƠ CÁ NHÂN
|--------------------------------------------------------------------------
*/
Route::get('/user-info', [QuanLyDangNhap::class, 'userInfo']);
Route::post('/profile/update', [QuanLyDangNhap::class, 'updateProfile']);
Route::post('/profile/change-password', [QuanLyDangNhap::class, 'changePassword']);

/*
|--------------------------------------------------------------------------
| 4. API QUẢN LÝ DANH MỤC - KHÁCH HÀNG
|--------------------------------------------------------------------------
*/
Route::get('/khach-hang', [QuanLyKhachHang::class, 'index']);
Route::post('/khach-hang/save', [QuanLyKhachHang::class, 'save']);
Route::post('/khach-hang/delete', [QuanLyKhachHang::class, 'delete']);

/*
|--------------------------------------------------------------------------
| 5. API QUẢN LÝ DANH MỤC - HÃNG TÀU
|--------------------------------------------------------------------------
*/
Route::get('/hang-tau', [QuanLyHangTau::class, 'index']);
Route::post('/hang-tau/save', [QuanLyHangTau::class, 'save']);
Route::post('/hang-tau/delete', [QuanLyHangTau::class, 'delete']);

/*
|--------------------------------------------------------------------------
| 6. API QUẢN LÝ DANH MỤC - CẢNG BIỂN
|--------------------------------------------------------------------------
*/
Route::get('/cang-bien', [QuanLyKhoCang::class, 'index']);
Route::post('/cang-bien/save', [QuanLyKhoCang::class, 'save']);
Route::post('/cang-bien/delete', [QuanLyKhoCang::class, 'delete']);

/*
|--------------------------------------------------------------------------
| 7. API QUẢN LÝ DANH MỤC - HÀNG HÓA
|--------------------------------------------------------------------------
*/
Route::get('/hang-hoa', [QuanLyHangHoa::class, 'index']);
Route::post('/hang-hoa/save', [QuanLyHangHoa::class, 'save']);
Route::post('/hang-hoa/delete', [QuanLyHangHoa::class, 'delete']);

/*
|--------------------------------------------------------------------------
| 8. API QUẢN LÝ DANH MỤC - HÃNG VẬN TẢI
|--------------------------------------------------------------------------
*/
Route::get('/hang-van-tai', [QuanLyHangVanTai::class, 'index']);
Route::post('/hang-van-tai/save', [QuanLyHangVanTai::class, 'save']);
Route::post('/hang-van-tai/delete', [QuanLyHangVanTai::class, 'delete']);

/*
|--------------------------------------------------------------------------
| 9. API QUẢN LÝ DANH MỤC - ĐƠN VỊ TÍNH
|--------------------------------------------------------------------------
*/
Route::get('/don-vi-tinh', [QuanLyDonViTinh::class, 'index']);
Route::post('/don-vi-tinh/save', [QuanLyDonViTinh::class, 'save']);
Route::post('/don-vi-tinh/delete', [QuanLyDonViTinh::class, 'delete']);

/*
|--------------------------------------------------------------------------
| QUẢN LÝ CHI PHÍ (Thanh toán AR/AP)
|--------------------------------------------------------------------------
*/
Route::get('/costs', [QuanLyChiPhi::class, 'index']);
Route::post('/costs/save', [QuanLyChiPhi::class, 'save']);
