<template>
  <div class="dashboard-wrapper">
    <aside class="sidebar" :class="{ 'collapsed': isSidebarCollapsed }">
      <div class="brand" style="display: flex; align-items: center; justify-content: space-between; padding: 15px;">
        <h3 v-show="!isSidebarCollapsed">SINCERE LOGISTICS</h3>
        <button @click="toggleSidebar" class="btn-toggle-sidebar" :title="isSidebarCollapsed ? 'Mở rộng menu' : 'Thu nhỏ menu'">
          <ArrowBigRight v-if="isSidebarCollapsed" /><ArrowBigLeft v-else />
        </button>
      </div>
      <nav class="menu">
        <router-link to="/home" active-class="active-menu" title="Trang chủ">
          <Home /> <span v-show="!isSidebarCollapsed">Trang chủ tổng quan</span>
        </router-link>
        
        <router-link to="/he-thong" active-class="active-menu" title="Hệ thống">
          <Settings /> <span v-show="!isSidebarCollapsed">Quản lý hệ thống</span>
        </router-link>
        <div v-show="isHeThongRoute && !isSidebarCollapsed" :class="['sub-menu-left', { 'sub-menu-left-open': isHeThongRoute }]">
          <router-link to="/he-thong/tai-khoan" active-class="active-submenu"><Users />Quản lý người dùng và phân quyền</router-link>
          <router-link to="/he-thong/ho-so" active-class="active-submenu"><User />Quản lý hồ sơ cá nhân</router-link>
        </div>
        
        <router-link to="/danh-muc" active-class="active-menu" title="Danh mục">
          <Book /> <span v-show="!isSidebarCollapsed">Quản lý Danh mục</span>
        </router-link>
        <div v-show="isDanhMucRoute && !isSidebarCollapsed" :class="['sub-menu-left', { 'sub-menu-left-open': isDanhMucRoute }]">
          <router-link to="/danh-muc/khach-hang" active-class="active-submenu"><User />Quản lý danh mục khách hàng</router-link>
          <router-link to="/danh-muc/hang-tau" active-class="active-submenu"><Anchor />Quản lý danh mục hãng tàu</router-link>
          <router-link to="/danh-muc/kho-cang" active-class="active-submenu"><Warehouse />Quản lý danh mục kho cảng</router-link>
          <router-link to="/danh-muc/hang-hoa" active-class="active-submenu"><Package />Quản lý danh mục hàng hóa</router-link>
          <router-link to="/danh-muc/hang-van-tai" active-class="active-submenu"><Truck />Quản lý danh mục hãng vận tải</router-link>
          <router-link to="/danh-muc/don-vi-tinh" active-class="active-submenu"><Ruler />Quản lý đơn vị tính</router-link>
        </div>

        <router-link to="/lo-hang" :class="['menu-item', { 'active-menu': isLoHangRoute }]" title="Lô hàng">
          <Package /> <span v-show="!isSidebarCollapsed">Quản lý lô hàng</span>
        </router-link>
        <div v-show="isLoHangRoute && !isSidebarCollapsed" :class="['sub-menu-left', { 'sub-menu-left-open': isLoHangRoute }]">
          <router-link to="/lo-hang/booking" active-class="active-submenu"><Notebook /> Quản lý Booking Note</router-link>    
          <router-link to="/lo-hang/thong-tin-lo-hang" active-class="active-submenu"><Package /> Quản lý thông tin Lô hàng</router-link>
          <router-link to="/lo-hang/chung-tu" active-class="active-submenu"><Folder /> Số hoá và lưu trữ chứng từ</router-link>        
        </div>
        
        <router-link to="/van-tai" active-class="active-menu" title="Vận tải">
          <Truck /> <span v-show="!isSidebarCollapsed">Quản lý Vận tải</span>
        </router-link>
        <div v-show="isVanTaiRoute && !isSidebarCollapsed" :class="['sub-menu-left', { 'sub-menu-left-open': isVanTaiRoute }]">
          <router-link to="/van-tai/quan-ly-van-don" active-class="active-submenu"><Notebook /> Quản lý vận đơn</router-link>
          <router-link to="/van-tai/thong-bao-hang-den" active-class="active-submenu"><Megaphone /> Quản lý thông báo hàng đến</router-link>
          <router-link to="/van-tai/lenh-giao-hang" active-class="active-submenu"><Notebook /> Quản lý lệnh giao hàng</router-link>
          <router-link to="/van-tai/bien-ban-giao-nhan" active-class="active-submenu"><Notebook /> Quản lý biên bản giao nhận</router-link>
          <router-link to="/van-tai/to-khai-hai-quan" active-class="active-submenu"><Ship /> Quản lý tờ khai hải quan</router-link>
          <router-link to="/van-tai/luu-bai" active-class="active-submenu"><Warehouse /> Quản lý Lưu bãi</router-link>
        </div>
        
        <router-link to="/chi-phi-va-thanh-toan" active-class="active-menu" title="Chi phí">
          <Wallet /> <span v-show="!isSidebarCollapsed">Quản lý Chi phí</span>
        </router-link>
        <div v-show="isChiPhiRoute && !isSidebarCollapsed" :class="['sub-menu-left', { 'sub-menu-left-open': isChiPhiRoute }]">
          <router-link to="/chi-phi-va-thanh-toan/chi-phi" active-class="active-submenu"><Wallet /> Quản lý chi phí phát sinh</router-link> 
          <router-link to="/chi-phi-va-thanh-toan/trang-thai-thanh-toan" active-class="active-submenu"><Check /> Quản lý trạng thái thanh toán</router-link>
        </div>

        <router-link to="/bao-cao-thong-ke" active-class="active-menu" title="Báo cáo">
          <ChartColumn /> <span v-show="!isSidebarCollapsed">Báo cáo Thống kê</span>
        </router-link>
        <div v-show="isBaoCaoRoute && !isSidebarCollapsed" :class="['sub-menu-left', { 'sub-menu-left-open': isBaoCaoRoute }]">
          <router-link to="/bao-cao-thong-ke/van-chuyen" active-class="active-submenu"><ChartColumn /> Báo cáo vận chuyển</router-link>
          <router-link to="/bao-cao-thong-ke/san-luong" active-class="active-submenu"><ChartLine /> Báo cáo sản lượng vận chuyển</router-link>
          <router-link to="/bao-cao-thong-ke/booking" active-class="active-submenu"><Notebook /> Báo cáo booking</router-link>
          <router-link to="/bao-cao-thong-ke/chi-phi-ton-dong" active-class="active-submenu"><Wallet /> Báo cáo chi phí tồn đọng</router-link>
          <router-link to="/bao-cao-thong-ke/cuoc-vo" active-class="active-submenu"><Package /> Thống kê tình trạng cược vỏ</router-link>
          <router-link to="/bao-cao-thong-ke/canh-bao-luu-bai" active-class="active-submenu"><TriangleAlert /> Thống kê cảnh báo lưu bãi</router-link>
        </div>
      </nav>
      
      <div class="logout-box">
        <button @click="handleLogout" class="btn-logout" title="Đăng xuất">
          <span v-show="!isSidebarCollapsed">Đăng xuất</span>
        </button>
      </div>
    </aside>

    <main class="main-container">
      <header class="header">
        <h2></h2>
        <router-link to="/he-thong/ho-so" class="user-profile" style="text-decoration: none; color: inherit; cursor: pointer;">
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
import { ArrowBigLeft, ArrowBigRight, Home, Settings, Book, User, Users, Anchor, Warehouse, Package, Truck, Ruler, Notebook, Folder, Ship, Megaphone, Wallet, Check, ChartColumn, ChartLine, TriangleAlert } from 'lucide-vue-next';

const router = useRouter();
const route = useRoute();

const isHeThongRoute = computed(() => route.path.startsWith('/he-thong'));
const isDanhMucRoute = computed(() => route.path.startsWith('/danh-muc'));
const isLoHangRoute = computed(() => route.path.startsWith('/lo-hang'));
const isChiPhiRoute = computed(() => route.path.startsWith('/chi-phi-va-thanh-toan'));
const isVanTaiRoute = computed(() => route.path.startsWith('/van-tai'));
const isBaoCaoRoute = computed(() => route.path.startsWith('/bao-cao-thong-ke'));

const userName = ref('Khách');
const userRole = ref('Chưa xác định');
const userInitials = ref('K');

const isSidebarCollapsed = ref(false);
const toggleSidebar = () => {
  isSidebarCollapsed.value = !isSidebarCollapsed.value;
};

// Chạy ngay khi giao diện load lên để lấy thông tin Đăng nhập
onMounted(() => {
  const userData = localStorage.getItem('sincere_user');
  if (userData) {
    const user = JSON.parse(userData);
    userName.value = user.ho_ten;
    userRole.value = user.chuc_vu || 'Chưa phân quyền';
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
.dashboard-wrapper { display: flex; height: 120vh; background-color: #f4f7f6; font-family: 'Segoe UI', Tahoma, sans-serif; }

/* Sidebar */
.sidebar { width: 260px; background-color: #2c3e50; color: white; display: flex; flex-direction: column; flex-shrink: 0; transition: width 0.3s ease; overflow: hidden; }
.sidebar.collapsed { width: 75px; }
.brand { padding: 20px; text-align: center; border-bottom: 1px solid #34495e; min-height: 70px; display: flex; align-items: center; justify-content: center; }
.brand h3 { color: #f1c40f; margin: 0; letter-spacing: 1px; font-size: 18px; }

.btn-toggle-sidebar {
  background: #34495e;
  color: white;
  border: none;
  border-radius: 4px;
  padding: 5px 8px;
  cursor: pointer;
  font-size: 16px;
  transition: 0.2s;
}
.btn-toggle-sidebar:hover { background: #1abc9c; color: white; }

.menu { flex-grow: 1; padding-top: 20px; display: flex; flex-direction: column; }
.menu a { color: #ecf0f1; text-decoration: none; padding: 15px 25px; font-size: 15px; border-left: 4px solid transparent; transition: 0.3s; white-space: nowrap; display: flex; align-items: center; gap: 10px; }
.menu a:hover { background-color: #34495e; }
.active-menu { background-color: #34495e; border-left-color: #f1c40f !important; font-weight: bold; color: #f1c40f !important; }
/* Sub Menu */
.sub-menu-left { display: flex; flex-direction: column; gap: 5px; padding-left: 20px; margin-top: 0; max-height: 0; opacity: 0; overflow: hidden; transition: max-height 0.3s ease, opacity 0.3s ease; }
.sub-menu-left-open { max-height: 450px; opacity: 1; }
.sub-menu-left a { color: #fff; background: #2c3e50; border: 1px solid #2c3e50; border-left: 4px solid transparent; border-radius: 5px; padding: 8px 15px; font-size: 13px; text-decoration: none; transition: 0.2s; display: flex; align-items: flex-start; gap: 8px; white-space: normal; line-height: 1.4; }
.sub-menu-left a:hover { background: #34495e; color: #fff; border-left-color: transparent; }
.active-submenu { background: #34495e !important; color: #f1c40f !important; font-weight: 700; border-left-color: #f1c40f !important; }
.active-submenu:hover { background: #34495e; color: #f1c40f !important; }

/* Điều chỉnh khi sidebar thu nhỏ */
.sidebar.collapsed .menu a {
  padding-left: 25px;
}

/* Đăng xuất */
.logout-box { padding: 20px; border-top: 1px solid #34495e; }
.btn-logout { width: 100%; padding: 10px; background: #e74c3c; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; transition: 0.2s; }
.btn-logout:hover { background: #c0392b; }

/* Khu vực bên phải */
.main-container { flex-grow: 1; display: flex; flex-direction: column; overflow: hidden; }
.header { background: white; height: 70px; padding: 0 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); z-index: 10; }
.header h2 { margin: 0; font-size: 20px; color: #333; }
.user-profile { display: flex; align-items: center; gap: 15px; transition: 0.2s; }
.user-profile:hover { opacity: 0.8; }
.user-info { display: flex; flex-direction: column; text-align: right; }
.user-info .name { font-weight: bold; color: #2c3e50; font-size: 15px; }
.user-info .role { font-size: 12px; color: #7f8c8d; }
.avatar { width: 40px; height: 40px; background: #3498db; color: white; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-size: 18px; font-weight: bold; }

/* Khu vực hiển thị nội dung các tab */
.content-area { padding: 30px; flex-grow: 1; overflow-y: auto; }
</style>