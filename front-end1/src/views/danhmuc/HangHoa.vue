<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">Quản lý Danh Mục Hàng Hóa</h3>
    
    <div class="toolbar" style="display:flex; flex-wrap:wrap; align-items:flex-end; gap:12px; justify-content:space-between;">
      <div class="search-group" style="display:flex; flex-wrap:wrap; gap:12px; flex:1; min-width:0;">
        <div class="search-box" style="flex:1 1 220px; min-width:200px;">
          <input type="text" v-model="searchFilters.ten_hang_hoa" placeholder="Tìm theo tên hàng hóa...">
        </div>
        <div class="search-box" style="flex:1 1 220px; min-width:200px;">
          <input type="text" v-model="searchFilters.hs_code" placeholder="Tìm theo HS Code...">
        </div>
        <div class="search-box" style="flex:0 0 auto; min-width:140px; display:flex; align-items:flex-end;">
          <button type="button" @click="clearFilters()" style="width:100%; background-color:#e74c3c; color:white; border:none; padding:8px 12px; border-radius:4px; cursor:pointer; font-weight:500;">Xóa bộ lọc</button>
        </div>
      </div>
      <button v-if="hasRole(4)" class="btn btn-success" @click="router.push('/danh-muc/hang-hoa/add')">+ TẠO HÀNG HÓA MỚI</button>
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
            <th>Mã Hàng</th>
            <th>Tên Hàng Hóa</th>
            <th>HS Code</th>
            <th style="text-align: center;">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(h, index) in paginatedHangHoa" :key="h.ma_hang_hoa"
              :class="{ 'row-even': (index % 2 !== 0), 'row-odd': (index % 2 === 0) }">
            <td>{{ h.ma_hang_hoa }}</td>
            <td>{{ h.ten_hang_hoa }}</td>
            <td>{{ h.hs_code || '-' }}</td>
            <td style="text-align: center;">
              <button v-if="hasRole(4)" class="action-btn text-primary" @click="router.push('/danh-muc/hang-hoa/edit/' + h.ma_hang_hoa)" title="Sửa">✏️</button>
              <button v-if="hasRole(4)" class="action-btn text-danger" @click="handleDelete(h.ma_hang_hoa)" title="Xóa">🗑️</button>
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
import { hasRole } from '../../assets/chucnang';
import { Pen, Trash } from 'lucide-vue-next';

const router = useRouter();
const listData = ref([]);
const isLoading = ref(true);


// Phân trang
const currentPage = ref(1);
const pageSize = ref(10);
const pageSizes = [10, 20, 50];

const searchFilters = ref({
  ten_hang_hoa: '',
  hs_code: ''
});

const filteredData = computed(() => {
  return listData.value.filter(item => {
    const tenMatch = !searchFilters.value.ten_hang_hoa || item.ten_hang_hoa.toLowerCase().includes(searchFilters.value.ten_hang_hoa.toLowerCase());
    const codeMatch = !searchFilters.value.hs_code || (item.hs_code && item.hs_code.includes(searchFilters.value.hs_code));

    return tenMatch && codeMatch;
  });
});

const paginatedHangHoa = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value;
  const end = start + pageSize.value;
  return filteredData.value.slice(start, end);
});

const totalPages = computed(() => Math.ceil(filteredData.value.length / pageSize.value) || 1);

const prevPage = () => { if (currentPage.value > 1) currentPage.value--; };
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++; };

const fetchData = async () => {
  isLoading.value = true;
  try {
    const response = await fetch(`${import.meta.env.VITE_API_URL}/hang-hoa`);
    const data = await response.json();
    if (data.success) {
      listData.value = data.data;
    }
  } catch (error) {
    alert("Không thể kết nối API lấy danh sách!");
  } finally {
    isLoading.value = false;
  }
};

const clearFilters = () => {
  searchFilters.value = {
    ten_hang_hoa: '',
    hs_code: '' 
  };
  currentPage.value = 1;
};

const handleDelete = async (ma_hang_hoa) => {
  if (confirm('Bạn có chắc chắn muốn xóa hàng hóa này không?')) {
    try {
      const response = await fetch(`${import.meta.env.VITE_API_URL}/hang-hoa/delete`, {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ ma_hang_hoa: ma_hang_hoa })
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
</style>
