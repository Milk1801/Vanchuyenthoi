import { createRouter, createWebHistory } from 'vue-router'
import Login from '../views/login.vue'
import DashboardLayout from '../views/DashboardLayout.vue'
import Home from '../views/Home.vue'
import QuanLyTaiKhoan from '../views/QuanLyTaiKhoan.vue'
import QuanLyQuyen from '../views/QuanLyQuyen.vue'
import QuanLyDanhMuc from '../views/QuanLyDanhMuc.vue'
import HoSoCaNhan from '../views/HoSoCaNhan.vue'
import KhachHang from '../views/danhmuc/KhachHang.vue'
import HangTau from '../views/danhmuc/HangTau.vue'
import KhoCang from '../views/danhmuc/KhoCang.vue'
import HangHoa from '../views/danhmuc/HangHoa.vue'
import HangVanTai from '../views/danhmuc/HangVanTai.vue'
import DonViTinh from '../views/danhmuc/DonViTinh.vue'

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
        { path: 'tai-khoan', name: 'tai-khoan', component: QuanLyTaiKhoan }, // Tab quản lý tài khoản
        { path: 'quyen', name: 'quyen', component: QuanLyQuyen }, // Tab quản lý quyền
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

        { path: 'ho-so', name: 'ho-so', component: HoSoCaNhan },
        
        //Đưa QuanLyChiPhi vào nằm trong mảng children của DashboardLayout
        {
          path: 'nghiep-vu/chi-phi',
          name: 'QuanLyChiPhi',
          component: () => import('../views/QuanLyChiPhi.vue') 
        },

        { path: 'ho-so', name: 'ho-so', component: HoSoCaNhan },
        
        {
          path: 'nghiep-vu/booking',
          name: 'QuanLyBooking',
          component: () => import('../views/QuanLyBooking.vue') 
}

      ]
    }
  ] 
})

export default router