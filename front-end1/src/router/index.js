import { createRouter, createWebHistory } from 'vue-router'
import Login from '../views/login.vue'
import DashboardLayout from '../views/DashboardLayout.vue'
import Home from '../views/Home.vue'
import QuanLyTaiKhoan from '../views/QuanLyTaiKhoan.vue'
import HoSoCaNhan from '../views/HoSoCaNhan.vue'

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
        { path: 'ho-so', name: 'ho-so', component: HoSoCaNhan }   
      ]
    }
  ]
})

export default router