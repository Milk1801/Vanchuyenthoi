<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">Quản lý Lưu Bãi</h3>
    
    <div class="toolbar" style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 15px; align-items: center;">
      <div class="search-box" style="flex: 2; min-width: 250px;">
        <input type="text" v-model="searchQuery" placeholder="🔍 Tìm theo tên lô hàng..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #ccc;">
      </div>

      <div class="combobox-wrapper" style="flex: 1; min-width: 160px;">
        <input type="text" v-model="trangThaiSearchText" placeholder="📂 Trạng thái..." @focus="showTrangThaiDropdown = true" class="combobox-input-sm">
        <ul v-if="showTrangThaiDropdown" class="combobox-list">
          <li @click="selectSearchTrangThai('ALL')">📂 Tất cả trạng thái</li>
          <li v-for="st in filteredTrangThaiList" :key="st" @click="selectSearchTrangThai(st)">{{ st }}</li>
        </ul>
      </div>

      <div class="combobox-wrapper" style="flex: 1; min-width: 140px;">
        <input type="text" v-model="cuocVoSearchText" placeholder="💰 Cược vỏ..." @focus="showCuocVoDropdown = true" class="combobox-input-sm">
        <ul v-if="showCuocVoDropdown" class="combobox-list">
          <li @click="selectSearchCuocVo('ALL')">💰 Cược vỏ (Tất cả)</li>
          <li v-for="cv in filteredCuocVoList" :key="cv" @click="selectSearchCuocVo(cv)">{{ cv }}</li>
        </ul>
      </div>

      <div class="combobox-wrapper" style="flex: 1; min-width: 160px;">
        <input type="text" v-model="userSearchText" placeholder="👤 Người sửa..." @focus="showUserDropdown = true" class="combobox-input-sm">
        <ul v-if="showUserDropdown" class="combobox-list">
          <li @click="selectSearchUser('ALL')">👤 Người sửa (Tất cả)</li>
          <li v-for="user in filteredUsers" :key="user" @click="selectSearchUser(user)">{{ user }}</li>
        </ul>
      </div>

      <button @click="clearFilters" style="padding: 8px 15px; border: 1px solid #ccc; border-radius: 6px; background: #fff; cursor: pointer; transition: 0.2s;" title="Xóa lọc">🧹 Xóa lọc</button>

      <button class="btn btn-success" @click="router.push('/van-tai/luu-bai/add')" style="white-space: nowrap;">+ THÊM LƯU BÃI</button>
    </div>

    <div v-if="isLoading" style="text-align: center; padding: 20px; color: #3498db;">Đang tải dữ liệu...</div>

    <div v-else style="display: flex; gap: 20px; align-items: flex-start;">
      <!-- BÊN TRÁI: DANH SÁCH -->
      <div style="flex: 1; min-width: 0;">
        <!-- Kiểm soát phân trang -->
        <div v-if="listLuuBai.length > 0" class="pagination-controls" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 8px; border: 1px solid #e1e4e8;">
      <div style="display: flex; align-items: center; gap: 10px;">
        <span>Hiển thị</span>
        <select v-model="pageSize" style="padding: 5px 8px; border-radius: 5px; border: 1px solid #ccc;">
          <option v-for="size in pageSizes" :key="size" :value="size">{{ size }}</option>
        </select>
        <span>mục mỗi trang</span>
      </div>
      <div style="display: flex; align-items: center; gap: 10px;">
        <button @click="prevPage" :disabled="currentPage === 1" class="btn-pagination">◀ Trước</button>
        <span style="font-weight: bold;">Trang {{ currentPage }} / {{ totalPages }}</span>
        <button @click="nextPage" :disabled="currentPage === totalPages" class="btn-pagination">Sau ▶</button>
      </div>
    </div>
        

        <div class="table-card">
          <table>
            <thead>
              <tr>
                <th style="width: 50px; text-align: center;">STT</th>
                <th @click="sortBy('ten_lo_hang')" style="cursor: pointer; user-select: none;">Lô Hàng</th>
                <th @click="sortBy('ngay_bat_dau_luu_bai')" style="cursor: pointer; user-select: none;">Ngày Bắt Đầu</th>
                <th @click="sortBy('ngay_luu_bai_mien_phi')" style="cursor: pointer; user-select: none; text-align: center;">Ngày Miễn Phí</th>
                <th @click="sortBy('cuoc_vo')" style="cursor: pointer; user-select: none;">Cược Vỏ</th>
                <th @click="sortBy('trang_thai_luu_bai')" style="cursor: pointer; user-select: none;">Trạng Thái</th>
                <th>Người Sửa</th>
                <th style="text-align: center;">Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in paginatedData" :key="item.ma_luu_bai" 
                  :class="{ 'row-selected': (selectedItem?.ma_luu_bai === item.ma_luu_bai), 'row-even': (index % 2 !== 0), 'row-odd': (index % 2 === 0) }">
                <td style="text-align: center; color: #7f8c8d;">{{ (currentPage - 1) * pageSize + index + 1 }}</td>
                <td class="fw-bold" style="color: #2980b9;">{{ item.ten_lo_hang }}</td>
                <td>{{ item.ngay_bat_dau_luu_bai ? new Date(item.ngay_bat_dau_luu_bai).toLocaleString('vi-VN') : '---' }}</td>
                <td style="text-align: center;">{{ item.ngay_luu_bai_mien_phi }}</td>
                <td>{{ item.cuoc_vo }}</td>
                <td>
                  <span class="badge" :class="item.trang_thai_luu_bai === 'Đã trả vỏ' ? 'badge-active' : 'badge-warning'">
                    {{ item.trang_thai_luu_bai }}
                  </span>
                </td>
                <td>{{ item.ten_nguoi_sua || 'N/A' }}</td>
                <td style="text-align: center;">
                  <div style="display: grid; grid-template-columns: repeat(3, 35px); gap: 5px; justify-content: center; margin: 0 auto; width: fit-content;">
                    <button class="action-btn-no-mg text-success" @click="showShipmentInfo(item)" title="Xem thông tin lô hàng">📋</button>
                    <button class="action-btn-no-mg text-primary" @click="router.push('/van-tai/luu-bai/edit/' + item.ma_luu_bai)" title="Sửa">✏️</button>
                    <button class="action-btn-no-mg text-danger" @click="handleDelete(item.ma_luu_bai)" title="Xóa">🗑️</button>
                  </div>
                </td>
              </tr>
              <tr v-if="paginatedData.length === 0">
                <td colspan="8" style="text-align: center; padding: 20px; color: #7f8c8d;">Không tìm thấy dữ liệu nào!</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- BÊN PHẢI: SIDE PANEL -->
      <div v-if="viewType !== 'none'" class="side-panel">
        <div class="panel-header">
          <h4>{{ panelTitle }}</h4>
          <button @click="viewType = 'none'" class="close-panel">✖</button>
        </div>
        <div class="panel-body">
          <div v-if="isPanelLoading" style="text-align: center; padding: 20px;">Đang tải...</div>
          <div v-else-if="!selectedShipment" style="text-align: center; padding: 20px;">Không tìm thấy thông tin lô hàng</div>
          <div v-else>
            <div class="info-list">
              <div class="info-item"><strong>Mã lô hàng:</strong> <span>#{{ selectedShipment.ma_lo_hang }}</span></div>
              <div class="info-item"><strong>Tên lô hàng:</strong> <span>{{ selectedShipment.ten_lo_hang }}</span></div>
              <div class="info-item"><strong>Khách hàng:</strong> <span>{{ selectedShipment.ten_khach_hang || 'N/A' }}</span></div>
              <div class="info-item"><strong>Điều kiện (Incoterms):</strong> <span>{{ selectedShipment.dieu_kien_thuong_mai || 'N/A' }}</span></div>
              <div class="info-item"><strong>Trạng thái:</strong> <span class="badge badge-active">{{ selectedShipment.trang_thai_lo_hang }}</span></div>
              <div class="info-item"><strong>Booking liên kết:</strong> <span>{{ selectedShipment.so_booking || 'N/A' }}</span></div>
              <div class="info-item" style="flex-direction: column; align-items: flex-start; gap: 5px; border-bottom: none;">
                <strong>Nguồn gốc / Ghi chú:</strong>
                <div style="padding: 10px; background: #f9f9f9; border-radius: 4px; width: 100%; font-size: 13px; color: #555;">{{ selectedShipment.nguon_goc || '(Trống)' }}</div>
              </div>
              <button @click="router.push('/lo-hang/thong-tin-lo-hang/edit/' + selectedShipment.ma_lo_hang)" class="btn btn-success" style="width: 100%; margin-top: 15px; border-radius: 8px; font-weight: bold; padding: 12px;">
                📦 ĐI ĐẾN CHI TIẾT LÔ HÀNG
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const listLuuBai = ref([]);
const isLoading = ref(true);
const searchQuery = ref('');

// Combobox search texts
const trangThaiSearchText = ref('');
const cuocVoSearchText = ref('');
const userSearchText = ref('');

// Combobox dropdown visibility
const showTrangThaiDropdown = ref(false);
const showCuocVoDropdown = ref(false);
const showUserDropdown = ref(false);

// Actual filter values
const filterTrangThai = ref('ALL');
const filterCuocVo = ref('ALL');
const filterUser = ref('ALL');

const currentPage = ref(1);
const pageSize = ref(10);
const pageSizes = [10, 20, 50];
const sortConfig = ref({ key: null, direction: 'asc' });

// Side Panel state
const viewType = ref('none');
const panelTitle = ref('');
const selectedItem = ref(null);
const selectedShipment = ref(null);
const isPanelLoading = ref(false);

const uniqueUsers = computed(() => {
  const users = listLuuBai.value.map(item => item.ten_nguoi_sua).filter(Boolean);
  return [...new Set(users)];
});

const filteredTrangThaiList = computed(() => ['Đang lưu bãi', 'Đã rút hàng', 'Đã trả vỏ'].filter(t => t.toLowerCase().includes(trangThaiSearchText.value.toLowerCase())));
const filteredCuocVoList = computed(() => ['Có', 'Không'].filter(c => c.toLowerCase().includes(cuocVoSearchText.value.toLowerCase())));
const filteredUsers = computed(() => uniqueUsers.value.filter(u => u.toLowerCase().includes(userSearchText.value.toLowerCase())));

const filteredAndSortedData = computed(() => {
  let result = listLuuBai.value.filter(item => {
    const matchSearch = !searchQuery.value || 
                        item.ten_lo_hang.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                        String(item.ma_lo_hang).includes(searchQuery.value);
    const matchStatus = filterTrangThai.value === 'ALL' || item.trang_thai_luu_bai === filterTrangThai.value;
    const matchCuocVo = filterCuocVo.value === 'ALL' || item.cuoc_vo === filterCuocVo.value;
    const matchUser = filterUser.value === 'ALL' || item.ten_nguoi_sua === filterUser.value;
    return matchSearch && matchStatus && matchCuocVo && matchUser;
  });

  if (sortConfig.value.key) {
    const { key, direction } = sortConfig.value;
    result.sort((a, b) => {
      let vA = a[key] ?? 0;
      let vB = b[key] ?? 0;
      if (vA < vB) return direction === 'asc' ? -1 : 1;
      if (vA > vB) return direction === 'asc' ? 1 : -1;
      return 0;
    });
  }
  return result;
});

const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value;
  const end = start + pageSize.value;
  return filteredAndSortedData.value.slice(start, end);
});

const totalPages = computed(() => Math.ceil(filteredAndSortedData.value.length / pageSize.value));

const sortBy = (key) => {
  if (sortConfig.value.key === key) {
    sortConfig.value.direction = sortConfig.value.direction === 'asc' ? 'desc' : 'asc';
  } else {
    sortConfig.value.key = key;
    sortConfig.value.direction = 'asc';
  }
};

const getSortIcon = (key) => {
  if (sortConfig.value.key !== key) return '↕️';
  return sortConfig.value.direction === 'asc' ? '🔼' : '🔽';
};

const showShipmentInfo = async (item) => {
  selectedItem.value = item;
  viewType.value = 'shipment';
  panelTitle.value = '📦 Thông tin lô hàng: ' + (item.ten_lo_hang || item.ma_lo_hang);
  isPanelLoading.value = true;
  try {
    const res = await fetch(`${import.meta.env.VITE_API_URL}/lo-hang`);
    const data = await res.json();
    if (data.success) {
      selectedShipment.value = data.data.find(lh => lh.ma_lo_hang === item.ma_lo_hang);
    } else {
      selectedShipment.value = null;
    }
  } catch (error) {
    selectedShipment.value = null;
  } finally {
    isPanelLoading.value = false;
  }
};

const prevPage = () => { if (currentPage.value > 1) currentPage.value--; };
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++; };

const selectSearchTrangThai = (status) => {
  filterTrangThai.value = status;
  trangThaiSearchText.value = status === 'ALL' ? '' : status;
  showTrangThaiDropdown.value = false;
};

const selectSearchCuocVo = (value) => {
  filterCuocVo.value = value;
  cuocVoSearchText.value = value === 'ALL' ? '' : value;
  showCuocVoDropdown.value = false;
};

const selectSearchUser = (user) => {
  filterUser.value = user;
  userSearchText.value = user === 'ALL' ? '' : user;
  showUserDropdown.value = false;
};

const clearFilters = () => {
  searchQuery.value = '';
  filterTrangThai.value = 'ALL';
  filterCuocVo.value = 'ALL';
  filterUser.value = 'ALL';
  trangThaiSearchText.value = '';
  cuocVoSearchText.value = '';
  userSearchText.value = '';
  currentPage.value = 1;
  viewType.value = 'none';
};

watch([searchQuery, filterTrangThai, filterCuocVo, filterUser, pageSize,
       trangThaiSearchText, cuocVoSearchText, userSearchText], () => {
  currentPage.value = 1;
});

const fetchData = async () => {
  isLoading.value = true;
  try {
    const res = await fetch(`${import.meta.env.VITE_API_URL}/luu-bai`);
    const data = await res.json();
    if (data.success) listLuuBai.value = data.data;
  } catch (e) { console.error(e); }
  finally { 
    isLoading.value = false;
    window.addEventListener('click', (e) => {
      if (!e.target.closest('.combobox-wrapper')) {
        showTrangThaiDropdown.value = showCuocVoDropdown.value = showUserDropdown.value = false;
      }
    });
  }
};

const handleDelete = async (id) => {
  if (!confirm("Xác nhận xóa thông tin này?")) return;
  try {
    const res = await fetch(`${import.meta.env.VITE_API_URL}/luu-bai/delete`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ma_luu_bai: id })
    });
    const data = await res.json();
    if (data.success) fetchData();
  } catch (e) { alert("Lỗi kết nối"); }
};

onMounted(fetchData);
</script>

<style scoped src="../../assets/quanlytaikhoan.css"></style>
<style scoped>
.badge { padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: bold; }
.badge-active { background-color: #27ae60; color: white; }
.badge-warning { background-color: #f39c12; color: white; }

.badge-warning { background-color: #f39c12; color: white; white-space: nowrap;}
.badge-active { background-color: #27ae60; color: white; white-space: nowrap;}

.side-panel {
  width: 380px; background: white; border-radius: 8px; border: 1px solid #ddd;
  box-shadow: -5px 0 15px rgba(0,0,0,0.05); position: sticky; top: 10px; min-height: 400px;
  display: flex; flex-direction: column; animation: slideIn 0.3s ease;
}
@keyframes slideIn { from { transform: translateX(20px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

.panel-header {
  padding: 15px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;
  background: #f8f9fa; border-radius: 8px 8px 0 0;
}
.panel-header h4 { margin: 0; color: #2c3e50; font-size: 15px; }
.close-panel { background: none; border: none; cursor: pointer; font-size: 16px; color: #95a5a6; }

.panel-body { padding: 15px; flex: 1; overflow-y: auto; }
.info-list { display: flex; flex-direction: column; gap: 15px; }
.info-item { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed #eee; padding-bottom: 8px; font-size: 14px; }
.info-item strong { color: #7f8c8d; font-weight: normal; }
.info-item span { color: #2c3e50; font-weight: 600; text-align: right; }

.row-selected { background-color: #f0f7ff !important; }
.action-btn-no-mg { background: none; border: none; cursor: pointer; font-size: 16px; transition: 0.2s; padding: 0; margin: 0; display: flex; align-items: center; justify-content: center; height: 35px; width: 35px; }
.action-btn-no-mg:hover { transform: scale(1.2); }
</style>
<style scoped>
/* CSS cho Combobox Tìm kiếm trong Toolbar */
.combobox-wrapper { position: relative; width: 100%; }
.combobox-input-sm {
  width: 100%; height: 36px; padding: 8px 12px; border: 1px solid #ccc;
  border-radius: 6px; box-sizing: border-box; background: #fff;
  transition: border-color 0.2s; font-size: 13px;
}
.combobox-input-sm:focus { border-color: #3498db; outline: none; }
.combobox-list {
  position: absolute; top: 100%; left: 0; right: 0; background: #fff;
  border: 1px solid #ddd; border-radius: 6px; margin: 2px 0 0 0; padding: 0;
  list-style: none; z-index: 1000; max-height: 200px; overflow-y: auto;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.combobox-list li {
  padding: 8px 12px; cursor: pointer; transition: background 0.2s;
  font-size: 13px; color: #2c3e50; border-bottom: 1px solid #f9f9f9;
  text-align: left;
}
.combobox-list li:hover { background: #f0f7ff; color: #2980b9; }

.btn-pagination {
  padding: 5px 12px; background: white; border: 1px solid #ddd; border-radius: 4px; cursor: pointer; transition: 0.2s;
}
.btn-pagination:hover:not(:disabled) { background: #f0f0f0; border-color: #3498db; color: #3498db; }
.btn-pagination:disabled { cursor: not-allowed; opacity: 0.5; }

.row-selected { background-color: #f0f7ff !important; }
.row-even { background-color: #f2f2f2 !important; }
.row-odd { background-color: #ffffff !important; }

/* Ensure table cells have consistent padding */
.table-card table th, .table-card table td { 
  white-space: nowrap; 
  padding: 12px 15px; 
}

</style>