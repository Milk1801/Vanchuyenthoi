<template>
  <div class="dashboard-wrapper">
    <aside class="sidebar">
      <div class="brand">
        <h3>SINCERE LOGISTICS</h3>
      </div>
      <nav class="menu">
        <router-link to="/home" active-class="active-menu">🏠 Trang chủ tổng quan</router-link>
        <router-link to="/tai-khoan" active-class="active-menu">👥 Quản lý Tài khoản</router-link>
        <router-link to="/danh-muc" active-class="active-menu">📚 Quản lý Danh mục</router-link>
        <div v-show="isDanhMucRoute" :class="['sub-menu-left', { 'sub-menu-left-open': isDanhMucRoute }]">
          <router-link to="/danh-muc/khach-hang" active-class="active-submenu">👤 Khách hàng</router-link>
          <router-link to="/danh-muc/hang-tau" active-class="active-submenu">🚢 Hãng tàu</router-link>
          <router-link to="/danh-muc/kho-cang" active-class="active-submenu">🏭 Kho cảng</router-link>
          <router-link to="/danh-muc/hang-hoa" active-class="active-submenu">📦 Hàng hóa</router-link>
          <router-link to="/danh-muc/hang-van-tai" active-class="active-submenu">🚚 Hãng vận tải</router-link>
          <router-link to="/danh-muc/don-vi-tinh" active-class="active-submenu">📏 Đơn vị tính</router-link>
        </div>
        <router-link to="/nghiep-vu/chi-phi" active-class="active-menu">📦 Nghiệp vụ Giao nhận & Thanh toán</router-link>
        
        <div v-show="isNghiepVuRoute" :class="['sub-menu-left', { 'sub-menu-left-open': isNghiepVuRoute }]">       
          <router-link to="/nghiep-vu/chi-phi" active-class="active-submenu">💰 Quản lý Chi phí</router-link>          
        </div>
      </nav>
      <div class="logout-box">
        <button @click="handleLogout" class="btn-logout">🚪 Đăng xuất</button>
      </div>
    </aside>

    <main class="main-container">
      <header class="header">
        <h2>Phân hệ Quản trị Hệ thống</h2>
        
        <router-link to="/ho-so" class="user-profile" style="text-decoration: none; color: inherit; cursor: pointer;">
          <div class="user-info">
            <span class="name">{{ userName }}</span>
            <span class="role">{{ userRole }}</span>
          </div>
          <div class="avatar">{{ userInitials }}</div>
        </router-link>
        </header>

      <div class="content-area">
        <router-view />
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';

const router = useRouter();
const route = useRoute();
const isDanhMucRoute = computed(() => route.path.startsWith('/danh-muc'));
const isNghiepVuRoute = computed(() => route.path.startsWith('/nghiep-vu'));
const userName = ref('Khách');
const userRole = ref('Chưa xác định');
const userInitials = ref('K');

// Chạy ngay khi giao diện load lên để lấy thông tin Đăng nhập
onMounted(() => {
  const userData = localStorage.getItem('sincere_user');
  if (userData) {
    const user = JSON.parse(userData);
    userName.value = user.ho_ten;
    userRole.value = user.chuc_vu;
    userInitials.value = user.ho_ten.charAt(0).toUpperCase(); // Lấy chữ cái đầu làm Avatar
  } else {
    // Nếu chưa đăng nhập mà cố tình vào, đá văng ra trang login
    router.push('/login');
  }
});

const handleLogout = () => {
  if (confirm('Bạn có chắc chắn muốn đăng xuất?')) {
    localStorage.removeItem('sincere_user');
    router.push('/login');
  }
};
</script>

<style scoped>
/* Khung bọc toàn bộ */
.dashboard-wrapper { display: flex; height: 100vh; background-color: #f4f7f6; font-family: 'Segoe UI', Tahoma, sans-serif; }

/* Sidebar */
.sidebar { width: 260px; background-color: #2c3e50; color: white; display: flex; flex-direction: column; flex-shrink: 0; }
.brand { padding: 20px; text-align: center; border-bottom: 1px solid #34495e; }
.brand h3 { color: #f1c40f; margin: 0; letter-spacing: 1px; font-size: 18px; }
  .menu { flex-grow: 1; padding-top: 20px; display: flex; flex-direction: column; }
  .menu a { color: #ecf0f1; text-decoration: none; padding: 15px 25px; font-size: 15px; border-left: 4px solid transparent; transition: 0.3s; }
  .menu a:hover { background-color: #34495e; }
  .active-menu { background-color: #34495e; border-left-color: #f1c40f !important; font-weight: bold; color: #f1c40f !important; }
  .sub-menu-left { display: flex; flex-direction: column; gap: 5px; padding-left: 20px; margin-top: 0; max-height: 0; opacity: 0; overflow: hidden; transition: max-height 0.3s ease, opacity 0.3s ease; }
  .sub-menu-left-open { max-height: 420px; opacity: 1; }
  .sub-menu-left a { color: #fff; background: #2c3e50; border: 1px solid #2c3e50; border-left: 4px solid transparent; border-radius: 5px; padding: 8px 15px; font-size: 13px; text-decoration: none; transition: 0.2s; display: flex; align-items: center; }
  .sub-menu-left a:hover { background: #34495e; color: #fff; border-left-color: transparent; }
  .sub-menu-left a::before { display: none; }
  .active-submenu { background: #34495e !important; color: #f1c40f !important; font-weight: 700; border-left-color: #f1c40f !important; }
  .active-submenu:hover { background: #34495e; color: #f1c40f !important; }
  .active-submenu::before { display: none; }
  .logout-box { padding: 20px; border-top: 1px solid #34495e; }
.btn-logout { width: 100%; padding: 10px; background: #e74c3c; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
.btn-logout:hover { background: #c0392b; }

/* Khu vực bên phải */
.main-container { flex-grow: 1; display: flex; flex-direction: column; overflow: hidden; }
.header { background: white; height: 70px; padding: 0 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); z-index: 10; }
.header h2 { margin: 0; font-size: 20px; color: #333; }
.user-profile { display: flex; align-items: center; gap: 15px; }
.user-info { display: flex; flex-direction: column; text-align: right; }
.user-info .name { font-weight: bold; color: #2c3e50; font-size: 15px; }
.user-info .role { font-size: 12px; color: #7f8c8d; }
.avatar { width: 40px; height: 40px; background: #3498db; color: white; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-size: 18px; font-weight: bold; }

/* Khu vực hiển thị nội dung các tab */
.content-area { padding: 30px; flex-grow: 1; overflow-y: auto; }
</style>