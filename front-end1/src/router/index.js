import { createRouter, createWebHistory } from 'vue-router'
import Login from '../views/login.vue'
import DashboardLayout from '../views/DashboardLayout.vue'
import Home from '../views/Home.vue'
import QuanLyTaiKhoan from '../views/hethong/QuanLyTaiKhoan.vue'
import QuanLyQuyen from '../views/hethong/QuanLyQuyen.vue'
import QuanLyDanhMuc from '../views/QuanLyDanhMuc.vue'
import HoSoCaNhan from '../views/hethong/HoSoCaNhan.vue'
import KhachHang from '../views/danhmuc/KhachHang.vue'
import HangTau from '../views/danhmuc/HangTau.vue'
import KhoCang from '../views/danhmuc/KhoCang.vue'
import HangHoa from '../views/danhmuc/HangHoa.vue'
import HangVanTai from '../views/danhmuc/HangVanTai.vue'
import DonViTinh from '../views/danhmuc/DonViTinh.vue'
import QuanLyLoHang from '../views/QuanLyLoHang.vue'
import QuanLyThongTinLoHang from '../views/lohang/QuanLyThongTinLoHang.vue'
import QuanlyBooking from '../views/lohang/QuanLyBooking.vue'
import QuanLyChiPhivaThanhToan from '../views/QuanLyChiPhivaThanhToan.vue'
import QuanLyChiPhi from '../views/chiphivathanhtoan/QuanLyChiPhi.vue'
import QuanLyHeThong from '../views/QuanLyHeThong.vue' 
import QuanLyVanTai from '../views/QuanLyVanTai.vue' 
import BaoCaoThongKe from '../views/BaoCaoThongKe.vue'
import QuanLyVanDon from '../views/vantai/QuanLyVanDon.vue' 
import KhachHangForm from '../views/danhmuc/KhachHangForm.vue'
import KhoCangForm from '../views/danhmuc/KhoCangForm.vue'
import HangTauForm from '../views/danhmuc/HangTauForm.vue'
import HangVanTaiForm from '../views/danhmuc/HangVanTaiForm.vue'
import HangHoaForm from '../views/danhmuc/HangHoaForm.vue'
import DonViTinhForm from '../views/danhmuc/DonViTinhForm.vue'
import LoHangForm from '@/views/lohang/LoHangForm.vue'
import BookingForm from '@/views/lohang/BookingForm.vue'
import QuanLyChungTu from '@/views/lohang/QuanLyChungTu.vue'
import QuanLyLenhGiaoHang from '@/views/vantai/QuanLyLenhGiaoHang.vue'
import QuanLyVanDonForm from '../views/vantai/QuanLyVanDonForm.vue'
import QuanLyToKhaiHaiQuan from '@/views/vantai/QuanLyToKhaiHaiQuan.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: Login
    },
    {
      path: '/',
      component: DashboardLayout, // Khung giao diện chung
      children: [
        { path: '', redirect: '/home' }, // Mặc định vào / thì hất sang /home
        { path: 'home', name: 'home', component: Home }, // Trang chủ
        {
          path: 'he-thong',
          name: 'he-thong',
          component: QuanLyHeThong,
          redirect: '/he-thong/tai-khoan',
          children: [
            { path: 'tai-khoan', name: 'he-thong-tai-khoan', component: QuanLyTaiKhoan },
            { path: 'quyen', name: 'he-thong-quyen', component: QuanLyQuyen },
            { path: 'ho-so', name: 'he-thong-ho-so', component: HoSoCaNhan }
          ]
        },
        {
          path: 'danh-muc',
          name: 'danh-muc',
          component: QuanLyDanhMuc,
          redirect: '/danh-muc/khach-hang',
          children: [
            { path: 'khach-hang', name: 'danh-muc-khach-hang', component: KhachHang },
            { path: 'hang-tau', name: 'danh-muc-hang-tau', component: HangTau },
            { path: 'kho-cang', name: 'danh-muc-kho-cang', component: KhoCang },
            { path: 'hang-hoa', name: 'danh-muc-hang-hoa', component: HangHoa },
            { path: 'hang-van-tai', name: 'danh-muc-hang-van-tai', component: HangVanTai },
            { path: 'don-vi-tinh', name: 'danh-muc-don-vi-tinh', component: DonViTinh }
          ]
        },
        {
          path: '/danh-muc/khach-hang/add',
          name: 'danh-muc-khach-hang-add',
          component: KhachHangForm
        },
        {
          path: '/danh-muc/khach-hang/edit/:id',
          name: 'danh-muc-khach-hang-edit',
          component: KhachHangForm
        },
        {
          path: '/danh-muc/hang-tau/add',
          name: 'danh-muc-hang-tau-add',
          component: HangTauForm
        },
        {
          path: '/danh-muc/hang-tau/edit/:id',
          name: 'danh-muc-hang-tau-edit',
          component: HangTauForm
        },
        {
          path: '/danh-muc/hang-van-tai/add',
          name: 'danh-muc-hang-van-tai-add',
          component: HangVanTaiForm
        },
        {
          path: '/danh-muc/hang-van-tai/edit/:id',
          name: 'danh-muc-hang-van-tai-edit',
          component: HangVanTaiForm
        },
        {
          path: '/danh-muc/hang-hoa/add',
          name: 'danh-muc-hang-hoa-add',
          component: HangHoaForm
        },
        {
          path: '/danh-muc/hang-hoa/edit/:id',
          name: 'danh-muc-hang-hoa-edit',
          component: HangHoaForm
        },
        {
          path: '/danh-muc/kho-cang/add',
          name: 'danh-muc-kho-cang-add',
          component: KhoCangForm
        },
        {
          path: '/danh-muc/kho-cang/edit/:id',
          name: 'danh-muc-kho-cang-edit',
          component: KhoCangForm
        },
        {
          path: '/danh-muc/don-vi-tinh/add',
          name: 'danh-muc-don-vi-tinh-add',
          component: DonViTinhForm
        },
        {
          path: '/danh-muc/don-vi-tinh/edit/:id',
          name: 'danh-muc-don-vi-tinh-edit',
          component: DonViTinhForm
        },

        {
          path: 'lo-hang',
          name: 'lo-hang',
          component: QuanLyLoHang,
          redirect: '/lo-hang/thong-tin-lo-hang',
          children: [
            { path: 'thong-tin-lo-hang', name: 'lo-hang-thong-tin-lo-hang', component: QuanLyThongTinLoHang },
            { path: 'booking', name: 'lo-hang-booking', component: QuanlyBooking },
            { path: 'chung-tu', name: 'lo-hang-chung-tu', component: QuanLyChungTu } 
          ]
        },
        {
          path: '/lo-hang/thong-tin-lo-hang/add',
          name: 'lo-hang-add',
          component: LoHangForm
        },
        {
          path: '/lo-hang/thong-tin-lo-hang/edit/:id',
          name: 'lo-hang-edit',
          component: LoHangForm
        },
        {
          path: '/lo-hang/booking/add',
          name: 'lo-hang-booking-add',
          component: BookingForm
        },
        {
          path: '/lo-hang/booking/edit/:id',
          name: 'lo-hang-booking-edit',
          component: BookingForm
        },
        {
          path: '/danh-muc/don-vi-tinh/edit/:id',
          name: 'danh-muc-don-vi-tinh-edit',
          component: DonViTinhForm
        },
        
        {
          path: 'chi-phi-va-thanh-toan',
          name: 'chi-phi-va-thanh-toan',
          component: QuanLyChiPhivaThanhToan,
          redirect: '/chi-phi-va-thanh-toan/chi-phi',
          children: [
            { path: 'chi-phi', name: 'chi-phi', component: QuanLyChiPhi }
          ]
        },
        
        {
          path: 'van-tai',
          name: 'van-tai',
          component: QuanLyVanTai,
          redirect: '/van-tai/quan-ly-van-don',
          children: [
            { path: 'quan-ly-van-don', name: 'van-tai-quan-ly-van-don', component: QuanLyVanDon },
            { path: 'lenh-giao-hang', name: 'van-tai-lenh-giao-hang', component: QuanLyLenhGiaoHang },
            { path: 'to-khai-hai-quan', name: 'van-tai-to-khai-hai-quan', component: QuanLyToKhaiHaiQuan }
          ] 
        },
        {
          path: '/van-tai/Quan-ly-van-don/add',
          name: 'van-tai-quan-ly-van-don-add',
          component: QuanLyVanDonForm
        },
        {
          path: '/van-tai/Quan-ly-van-don/edit/:id',
          name: 'van-tai-quan-ly-van-don-edit',
          component: QuanLyVanDonForm
        },
        
        {
          path: 'bao-cao-thong-ke',
          name: 'bao-cao-thong-ke',
          component: BaoCaoThongKe
        }
      ]
    }
  ] 
})

export default router
