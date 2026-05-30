<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">Quản lý Danh Mục Hãng Tàu</h3>
    
    <div class="toolbar" style="display:flex; flex-wrap:wrap; align-items:flex-end; gap:12px; justify-content:space-between;">
      <div class="search-group" style="display:flex; flex-wrap:wrap; gap:12px; flex:1; min-width:0;">
        <div class="search-box" style="flex:1 1 220px; min-width:200px;">
          <input type="text" v-model="searchFilters.ten_hang_tau" placeholder="Tìm theo tên hãng tàu...">
        </div>
        <div class="search-box" style="flex:1 1 220px; min-width:200px;">
          <input type="text" v-model="searchFilters.dia_chi" placeholder="Tìm theo địa chỉ...">
        </div>
        <div class="search-box" style="flex:1 1 220px; min-width:200px;">
          <input type="text" v-model="searchFilters.so_dien_thoai" placeholder="Tìm theo điện thoại...">
        </div>
        <div class="search-box" style="flex:1 1 220px; min-width:200px;">
          <input type="text" v-model="searchFilters.so_fax" placeholder="Tìm theo fax...">
        </div>
        <div class="combobox-wrapper" style="flex:1 1 220px; min-width:200px;">
          <input type="text" v-model="userSearchText" placeholder="👤 Người sửa cuối..." @focus="showUserDropdown = true" class="combobox-input-sm">
          <ul v-if="showUserDropdown" class="combobox-list">
            <li @click="selectSearchUser(null)">-- Tất cả người sửa --</li>
            <li v-for="acc in filteredAccountOptions" :key="acc.ma_tai_khoan" @click="selectSearchUser(acc)">{{ acc.ho_ten }}</li>
          </ul>
        </div>
        <div class="search-box" style="flex:0 0 auto; min-width:140px; display:flex; align-items:flex-end;">
          <button type="button" @click="clearFilters()" style="width:100%; background-color:#e74c3c; color:white; border:none; padding:8px 12px; border-radius:4px; cursor:pointer; font-weight:500;">Xóa bộ lọc</button>
        </div>
      </div>
      <button v-if="hasRole(1)" class="btn btn-success" @click="router.push('/danh-muc/hang-tau/add')">+ TẠO HÃNG TÀU MỚI</button>
    </div>

    <div v-if="isLoading" style="text-align: center; padding: 20px; color: #3498db;">
      Đang tải dữ liệu...
    </div>

    <div v-else class="table-card">
      <!-- Kiểm soát phân trang -->
      <div v-if="listData.length > 0" class="pagination-controls" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 8px; border: 1px solid #e1e4e8;">
        <div style="display: flex; align-items: center; gap: 10px;">
          <span>Hiển thị</span>
          <select v-model="pageSize" style="padding: 5px 8px; border-radius: 5px; border: 1px solid #ccc;">
            <option v-for="size in pageSizes" :key="size" :value="size">{{ size }}</option>
          </select>
          <span>mục</span>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
          <button @click="prevPage" :disabled="currentPage === 1" class="btn-pagination">◀ Trước</button>
          <span style="font-weight: bold;">Trang {{ currentPage }} / {{ totalPages }}</span>
          <button @click="nextPage" :disabled="currentPage === totalPages" class="btn-pagination">Sau ▶</button>
        </div>
      </div>

      <table>
        <thead>
          <tr>
            <th>Mã Hãng</th>
            <th>Tên Hãng Tàu</th>
            <th>Địa chỉ</th>
            <th>Điện thoại</th>
            <th>Fax</th>
            <th>Ghi chú</th>
            <th>Người sửa cuối</th>
            <th style="text-align: center;">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(ht, index) in paginatedHangTau" :key="ht.ma_hang_tau"
              :class="{ 'row-even': (index % 2 !== 0), 'row-odd': (index % 2 === 0) }">
            <td>{{ ht.ma_hang_tau }}</td>
            <td>{{ ht.ten_hang_tau }}</td>
            <td>{{ ht.dia_chi || 'Chưa cập nhật' }}</td>
            <td>{{ ht.so_dien_thoai || 'Chưa cập nhật' }}</td>
            <td>{{ ht.so_fax || 'Chưa cập nhật' }}</td>
            <td>{{ ht.ghi_chu || 'Không có' }}</td>
            <td>{{ ht.nguoi_sua_doi || 'N/A' }}</td>
            <td style="text-align: center;">
              <div v-if="hasRole(1)" style="display: flex; gap: 8px; justify-content: center;">
                <button class="action-btn text-primary" @click="router.push('/danh-muc/hang-tau/edit/' + ht.ma_hang_tau)" title="Sửa">✏️</button>
                <button class="action-btn text-danger" @click="handleDelete(ht.ma_hang_tau)" title="Xóa">🗑️</button>
              </div>
              <span v-else style="color: #95a5a6; font-size: 12px; font-style: italic;">Chỉ xem</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const listData = ref([]);
const accountOptions = ref([]);
const isLoading = ref(true);

// Logic phân quyền đồng nhất
const currentUser = JSON.parse(localStorage.getItem('sincere_user') || '{}');
const hasRole = (roleIdOrArray) => {
  if (!currentUser.ds_quyen) return false;
  const roles = currentUser.ds_quyen.map(q => q.ma_quyen);
  if (roles.includes(5)) return true; // Mã quyền 5: Toàn quyền (Super Admin)
  
  const requiredRoles = Array.isArray(roleIdOrArray) ? roleIdOrArray : [roleIdOrArray];
  return requiredRoles.some(r => roles.includes(r));
};

// Phân trang
const currentPage = ref(1);
const pageSize = ref(10);
const pageSizes = [10, 20, 50];

// State cho tìm kiếm combobox
const userSearchText = ref('');
const showUserDropdown = ref(false);

const searchFilters = ref({
  ten_hang_tau: '',
  dia_chi: '',
  so_dien_thoai: '',
  so_fax: '',
  nguoi_sua_cuoi: ''
});

const filteredData = computed(() => {
  return listData.value.filter(item => {
    const tenMatch = !searchFilters.value.ten_hang_tau || item.ten_hang_tau.toLowerCase().includes(searchFilters.value.ten_hang_tau.toLowerCase());
    const diaChiMatch = !searchFilters.value.dia_chi || (item.dia_chi && item.dia_chi.toLowerCase().includes(searchFilters.value.dia_chi.toLowerCase()));
    const dienthoaiMatch = !searchFilters.value.so_dien_thoai || (item.so_dien_thoai && item.so_dien_thoai.includes(searchFilters.value.so_dien_thoai));
    const faxMatch = !searchFilters.value.so_fax || (item.so_fax && item.so_fax.includes(searchFilters.value.so_fax));
    const nguoiSuaMatch = !searchFilters.value.nguoi_sua_cuoi || String(item.nguoi_sua_cuoi) === String(searchFilters.value.nguoi_sua_cuoi);

    return tenMatch && diaChiMatch && dienthoaiMatch && faxMatch && nguoiSuaMatch;
  });
});

const paginatedHangTau = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value;
  const end = start + pageSize.value;
  return filteredData.value.slice(start, end);
});

const totalPages = computed(() => Math.ceil(filteredData.value.length / pageSize.value) || 1);

const filteredAccountOptions = computed(() => {
  return accountOptions.value.filter(acc => 
    acc.ho_ten.toLowerCase().includes(userSearchText.value.toLowerCase())
  );
});

const fetchData = async () => {
  isLoading.value = true;
  try {
    const response = await fetch(`${import.meta.env.VITE_API_URL}/hang-tau`);
    const data = await response.json();
    if (data.success) {
      listData.value = data.data;
    }
  } catch (error) {
    console.error('Fetch error:', error);
    alert("Không thể kết nối API lấy danh sách!");
  } finally {
    isLoading.value = false;
  }
};

const fetchAccounts = async () => {
  try {
    const response = await fetch(`${import.meta.env.VITE_API_URL}/accounts`);
    const data = await response.json();
    if (data.success) {
      accountOptions.value = data.data;
    }
  } catch (error) {
    console.error('Fetch accounts error:', error);
  }
};

const selectSearchUser = (acc) => {
  searchFilters.value.nguoi_sua_cuoi = acc ? acc.ma_tai_khoan : '';
  userSearchText.value = acc ? acc.ho_ten : '';
  showUserDropdown.value = false;
};

const prevPage = () => { if (currentPage.value > 1) currentPage.value--; };
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++; };

const clearFilters = () => {
  searchFilters.value = {
    ten_hang_tau: '',
    dia_chi: '',
    so_dien_thoai: '',
    so_fax: '',
    nguoi_sua_cuoi: ''
  };
  userSearchText.value = '';
  currentPage.value = 1;
};

const handleDelete = async (ma_hang_tau) => {
  if (confirm('Bạn có chắc chắn muốn xóa hãng tàu này không?')) {
    try {
      const response = await fetch(`${import.meta.env.VITE_API_URL}/hang-tau/delete`, {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ ma_hang_tau: ma_hang_tau })
      });
      const data = await response.json();
      if (data.success) {
        alert("Đã xóa thành công!");
        fetchData();
      } else {
        alert("Lỗi từ server: " + data.message);
      }
    } catch (error) {
      alert("Lỗi khi xóa kết nối!");
    }
  }
};

watch([searchFilters, pageSize], () => {
  currentPage.value = 1;
}, { deep: true });

onMounted(() => {
  fetchData();
  fetchAccounts();
  window.addEventListener('click', (e) => {
    if (!e.target.closest('.combobox-wrapper')) {
      showUserDropdown.value = false;
    }
  });
});
</script>

<style scoped src="../../assets/quanlytaikhoan.css"></style>
<style scoped>
.btn-pagination {
  padding: 5px 12px; background: white; border: 1px solid #ddd; border-radius: 4px; cursor: pointer; transition: 0.2s;
}
.btn-pagination:hover:not(:disabled) { background: #f0f0f0; border-color: #3498db; color: #3498db; }
.btn-pagination:disabled { cursor: not-allowed; opacity: 0.5; }

.row-even { background-color: #f2f2f2 !important; }
.row-odd { background-color: #ffffff !important; }

/* CSS cho Combobox Tìm kiếm */
.combobox-wrapper { position: relative; }
.combobox-input-sm {
  width: 100%; height: 38px; padding: 8px 12px; border: 1px solid #ccc;
  border-radius: 4px; box-sizing: border-box; background: #fff;
  transition: border-color 0.2s; font-size: 14px;
}
.combobox-input-sm:focus { border-color: #3498db; outline: none; }
.combobox-list {
  position: absolute; top: 100%; left: 0; right: 0; background: #fff;
  border: 1px solid #ddd; border-radius: 4px; margin: 2px 0 0 0; padding: 0;
  list-style: none; z-index: 1000; max-height: 200px; overflow-y: auto;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.combobox-list li {
  padding: 8px 12px; cursor: pointer; transition: background 0.2s;
  font-size: 13px; color: #2c3e50; border-bottom: 1px solid #f9f9f9;
  text-align: left;
}
.combobox-list li:hover { background: #f0f7ff; color: #2980b9; }
</style>
