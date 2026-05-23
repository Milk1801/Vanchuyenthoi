<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">Quản lý Tờ Khai Hải Quan</h3>
    
    <div class="toolbar" style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 15px;">
      <div style="display: flex; gap: 10px; width: 100%; flex-wrap: wrap;">
        <div class="search-box" style="flex: 1; min-width: 200px;">
          <input type="text" v-model="searchFilters.ma_to_khai" placeholder="🔍 Tìm theo mã tờ khai..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #ccc;">
        </div>
        <div class="search-box" style="flex: 1; min-width: 200px;">
          <input type="text" v-model="searchFilters.ten_lo_hang" placeholder="🔍 Tìm theo tên lô hàng..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #ccc;">
        </div>
      </div>

      <div class="combobox-wrapper" style="width: 180px;">
        <input type="text" v-model="phanLuongSearchText" placeholder="🌈 Phân luồng..." @focus="showPhanLuongDropdown = true" class="combobox-input-sm">
        <ul v-if="showPhanLuongDropdown" class="combobox-list">
          <li @click="selectSearchPhanLuong('')">🌈 Tất cả phân luồng</li>
          <li v-for="pl in filteredPhanLuongList" :key="pl" @click="selectSearchPhanLuong(pl)">{{ pl }}</li>
        </ul>
      </div>

      <div class="combobox-wrapper" style="width: 180px;">
        <input type="text" v-model="ketQuaSearchText" placeholder="✅ Kết quả..." @focus="showKetQuaDropdown = true" class="combobox-input-sm">
        <ul v-if="showKetQuaDropdown" class="combobox-list">
          <li @click="selectSearchKetQua('')">✅ Tất cả kết quả</li>
          <li v-for="kq in filteredKetQuaList" :key="kq" @click="selectSearchKetQua(kq)">{{ kq }}</li>
        </ul>
      </div>

      <div style="display: flex; align-items: center; gap: 8px; background: #fff; padding: 0 10px; border: 1px solid #ccc; border-radius: 6px;">
        <label style="font-size: 13px; color: #666; white-space: nowrap;">Ngày thông quan:</label>
        <input type="date" v-model="searchFilters.ngay_thong_quan" style="padding: 7px; border: none; outline: none;">
      </div>

      <div class="combobox-wrapper" style="width: 180px;">
        <input type="text" v-model="userSearchText" placeholder="👤 Người sửa..." @focus="showUserDropdown = true" class="combobox-input-sm">
        <ul v-if="showUserDropdown" class="combobox-list">
          <li @click="selectSearchUser('')">👤 Tất cả người sửa</li>
          <li v-for="name in filteredUserList" :key="name" @click="selectSearchUser(name)">{{ name }}</li>
        </ul>
      </div>

      <button @click="clearFilters" style="padding: 8px 15px; border: 1px solid #ccc; border-radius: 6px; background: #fff; cursor: pointer; transition: 0.2s;" title="Xóa lọc">🧹 Xóa lọc</button>
      <button v-if="canModify" class="btn btn-success" @click="router.push('/van-tai/to-khai-hai-quan/add')" style="border-radius: 6px;">+ TẠO TỜ KHAI MỚI</button>
    </div>

    <div v-if="isLoadingData" style="text-align: center; padding: 20px; color: #3498db;">Đang tải dữ liệu Tờ khai...</div>

    <div v-else>
      <!-- Ẩn hiện cột -->
      <div class="column-visibility-controls" style="margin-bottom: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px; border: 1px solid #ddd;">
        <h5 style="margin-top: 0; margin-bottom: 10px; color: #2c3e50;">Hiển thị cột dữ liệu:</h5>
        <div style="display: flex; flex-wrap: wrap; gap: 15px;">
          <div v-for="(col, key) in columnVisibility" :key="key" class="checkbox-item">
            <input type="checkbox" :id="'col-' + key" v-model="col.visible" style="margin-right: 5px;">
            <label :for="'col-' + key" style="font-size: 13px; color: #555;">{{ col.label }}</label>
          </div>
        </div>
      </div>

      <!-- BÊN TRÁI: DANH SÁCH -->
      <div style="flex: 1; min-width: 0;">
        <!-- Kiểm soát phân trang -->
        <div v-if="listToKhai.length > 0" class="pagination-controls" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 8px; border: 1px solid #e1e4e8;">
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

        <div class="table-card" style="overflow-x: auto; background: white; border-radius: 8px; border: 1px solid #ddd; width: 100%;">
          <table style="width: max-content; border-collapse: collapse;">
            <thead>
              <tr>
                <th class="sticky-col-left" style="width: 50px; text-align: center;">STT</th>
                <th v-if="columnVisibility.ma_to_khai.visible" style="width: 150px;">Mã Tờ Khai</th>
                <th v-if="columnVisibility.ten_lo_hang.visible" style="width: 250px;">Lô hàng</th>
                <th v-if="columnVisibility.ngay_thong_quan.visible" style="width: 180px;">Ngày thông quan</th>
                <th v-if="columnVisibility.phan_luong.visible" style="width: 130px;">Phân luồng</th>
                <th v-if="columnVisibility.ket_qua_thong_quan.visible" style="width: 180px;">Kết quả thông quan</th>
                <th v-if="columnVisibility.ten_nguoi_sua.visible" style="width: 150px;">Người sửa cuối</th>
                <th class="sticky-col-right" style="text-align: center; width: 100px;">Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(tk, index) in paginatedData" :key="tk.ma_to_khai_hai_quan" 
                  :class="{ 'row-even': (index % 2 !== 0), 'row-odd': (index % 2 === 0) }">
                <td class="sticky-col-left" style="text-align: center; color: #7f8c8d;">{{ (currentPage - 1) * pageSize + index + 1 }}</td>
                <td v-if="columnVisibility.ma_to_khai.visible">{{ tk.ma_to_khai_hai_quan }}</td>
                <td v-if="columnVisibility.ten_lo_hang.visible"
                    @mouseenter="handleMouseEnter($event, tk)" 
                    @mousemove="handleMouseMove"
                    @mouseleave="handleMouseLeave"
                    @click="goToLoHangEdit(tk.ma_lo_hang)"
                    style="cursor: pointer; color: #2980b9; font-weight: 500; text-decoration: underline;">
                  {{ tk.ten_lo_hang || '---' }}
                </td>
                <td v-if="columnVisibility.ngay_thong_quan.visible">{{ formatDateTime(tk.ngay_thong_quan) }}</td>
                <td v-if="columnVisibility.phan_luong.visible"><span class="badge" :style="getPhanLuongStyle(tk.phan_luong)">{{ tk.phan_luong || 'N/A' }}</span></td>
                <td v-if="columnVisibility.ket_qua_thong_quan.visible"><span class="badge" :style="getKetQuaStyle(tk.ket_qua_thong_quan)">{{ tk.ket_qua_thong_quan || 'N/A' }}</span></td>
                <td v-if="columnVisibility.ten_nguoi_sua.visible">{{ tk.ten_nguoi_sua || 'N/A' }}</td>
                <td class="sticky-col-right" style="text-align: center;">
                  <div style="display: flex; gap: 2px; justify-content: center;">
                    <button v-if="canModify" class="action-btn-no-mg text-primary" @click="router.push('/van-tai/to-khai-hai-quan/edit/' + tk.ma_to_khai_hai_quan)" title="Sửa">✏️</button>
                    <button v-if="canModify" class="action-btn-no-mg text-danger" @click="handleDelete(tk.ma_to_khai_hai_quan)" title="Xóa">🗑️</button>
                  </div>
                </td>
              </tr>
              <tr v-if="filteredData.length === 0">
                <td :colspan="visibleColumnsCount" style="text-align: center; padding: 20px; color: #7f8c8d;">Không tìm thấy tờ khai nào!</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Tooltip hiển thị thông tin lô hàng khi hover -->
    <div v-if="tooltipShipment" class="shipment-tooltip" :style="{ top: tooltipPos.y + 'px', left: tooltipPos.x + 'px' }">
      <div class="tooltip-header">📦 Thông tin lô hàng</div>
      <div class="tooltip-content">
        <div><strong>Mã:</strong> <span>#{{ tooltipShipment.ma_lo_hang }}</span></div>
        <div><strong>Tên:</strong> <span>{{ tooltipShipment.ten_lo_hang }}</span></div>
        <div><strong>Khách hàng:</strong> <span>{{ tooltipShipment.ten_khach_hang || 'N/A' }}</span></div>
        <div><strong>Incoterms:</strong> <span>{{ tooltipShipment.dieu_kien_thuong_mai || 'N/A' }}</span></div>
        <div><strong>Trạng thái:</strong> <span class="badge badge-active" style="font-size: 11px;">{{ tooltipShipment.trang_thai_lo_hang }}</span></div>
        <div><strong>Booking:</strong> <span>{{ tooltipShipment.so_booking || 'N/A' }}</span></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const listToKhai = ref([]);
const isLoadingData = ref(true);

// Phân trang
const currentPage = ref(1);
const pageSize = ref(10);
const pageSizes = [10, 20, 50];

// Kiểm tra quyền thao tác (Thêm/Sửa/Xóa): Chỉ cho phép mã quyền 4 hoặc 5
const canModify = computed(() => {
  try {
    const user = JSON.parse(localStorage.getItem('sincere_user'));
    if (!user) return false;
    // Kiểm tra mã quyền trong danh sách ds_quyen hoặc ds_ma_quyen (từ API login hoặc account list)
    const perms = user.ds_quyen ? user.ds_quyen.map(q => Number(q.ma_quyen)) : 
                 (user.ds_ma_quyen ? user.ds_ma_quyen.split(',').map(id => Number(id.trim())) : []);
    return perms.includes(4) || perms.includes(5);
  } catch (e) { return false; }
});

// Cấu hình ẩn hiện cột
const columnVisibility = ref({
  ma_to_khai: { label: 'Mã Tờ Khai', visible: true },
  ten_lo_hang: { label: 'Lô hàng', visible: true },
  ngay_thong_quan: { label: 'Ngày thông quan', visible: true },
  phan_luong: { label: 'Phân luồng', visible: true },
  ket_qua_thong_quan: { label: 'Kết quả thông quan', visible: true },
  ten_nguoi_sua: { label: 'Người sửa cuối', visible: true },
});

const visibleColumnsCount = computed(() => {
  return Object.values(columnVisibility.value).filter(col => col.visible).length + 2;
});

const listAllLoHang = ref([]);
const tooltipShipment = ref(null);
const tooltipPos = ref({ x: 0, y: 0 });

const listPhanLuong = ['Luồng Xanh', 'Luồng Vàng', 'Luồng Đỏ'];
const listKetQua = ['Chờ xử lý', 'Đã thông quan', 'Từ chối'];

// State cho tìm kiếm combobox
const phanLuongSearchText = ref('');
const ketQuaSearchText = ref('');
const userSearchText = ref('');

const showPhanLuongDropdown = ref(false);
const showKetQuaDropdown = ref(false);
const showUserDropdown = ref(false);

const searchFilters = ref({
  ma_to_khai: '',
  ten_lo_hang: '',
  phan_luong: '',
  ket_qua_thong_quan: '',
  ngay_thong_quan: '',
  ten_nguoi_sua: ''
});

const uniqueNguoiSua = computed(() => {
  const names = listToKhai.value.map(item => item.ten_nguoi_sua).filter(Boolean);
  return [...new Set(names)];
});

const filteredPhanLuongList = computed(() => listPhanLuong.filter(pl => pl.toLowerCase().includes(phanLuongSearchText.value.toLowerCase())));
const filteredKetQuaList = computed(() => listKetQua.filter(kq => kq.toLowerCase().includes(ketQuaSearchText.value.toLowerCase())));
const filteredUserList = computed(() => uniqueNguoiSua.value.filter(u => u.toLowerCase().includes(userSearchText.value.toLowerCase())));

const filteredData = computed(() => {
  return listToKhai.value.filter(item => {
    const matchMa = !searchFilters.value.ma_to_khai || 
      (item.ma_to_khai_hai_quan && String(item.ma_to_khai_hai_quan).includes(searchFilters.value.ma_to_khai));
    const matchLoHang = !searchFilters.value.ten_lo_hang || 
      (item.ten_lo_hang && item.ten_lo_hang.toLowerCase().includes(searchFilters.value.ten_lo_hang.toLowerCase()));
    const matchPhanLuong = !searchFilters.value.phan_luong || item.phan_luong === searchFilters.value.phan_luong;
    const matchKetQua = !searchFilters.value.ket_qua_thong_quan || item.ket_qua_thong_quan === searchFilters.value.ket_qua_thong_quan;
    
    const matchDate = !searchFilters.value.ngay_thong_quan || (item.ngay_thong_quan && item.ngay_thong_quan.includes(searchFilters.value.ngay_thong_quan));
    const matchNguoiSua = !searchFilters.value.ten_nguoi_sua || item.ten_nguoi_sua === searchFilters.value.ten_nguoi_sua;

    return matchMa && matchLoHang && matchPhanLuong && matchKetQua && matchDate && matchNguoiSua;
  });
});

const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value;
  const end = start + pageSize.value;
  return filteredData.value.slice(start, end);
});

const totalPages = computed(() => Math.ceil(filteredData.value.length / pageSize.value) || 1);
const prevPage = () => { if (currentPage.value > 1) currentPage.value--; };
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++; };

const fetchData = async () => {
  isLoadingData.value = true;
  try {
    const response = await fetch(`${import.meta.env.VITE_API_URL}/to-khai-hai-quan`);
    const data = await response.json();
    if (data.success) {
      listToKhai.value = data.data;
    }
  } catch (error) {
    console.error('Fetch error:', error);
    alert("Không thể kết nối API lấy danh sách!");
  } finally {
    isLoadingData.value = false;
  }
};

const fetchReferences = async () => {
  try {
    const res = await fetch(`${import.meta.env.VITE_API_URL}/lo-hang`);
    const data = await res.json();
    if (data.success) {
      listAllLoHang.value = data.data;
    }
  } catch (error) {
    console.error("Lỗi lấy danh sách lô hàng!");
  }
};

const selectSearchPhanLuong = (pl) => {
  searchFilters.value.phan_luong = pl;
  phanLuongSearchText.value = pl;
  showPhanLuongDropdown.value = false;
};

const selectSearchKetQua = (kq) => {
  searchFilters.value.ket_qua_thong_quan = kq;
  ketQuaSearchText.value = kq;
  showKetQuaDropdown.value = false;
};

const selectSearchUser = (user) => {
  searchFilters.value.ten_nguoi_sua = user;
  userSearchText.value = user;
  showUserDropdown.value = false;
};

const goToLoHangEdit = (ma_lo_hang) => {
  router.push('/lo-hang/thong-tin-lo-hang/edit/' + ma_lo_hang);
};

const handleMouseEnter = (event, tk) => {
  const found = listAllLoHang.value.find(lh => lh.ma_lo_hang === tk.ma_lo_hang);
  if (found) {
    tooltipShipment.value = found;
    tooltipPos.value = { x: event.clientX + 15, y: event.clientY + 15 };
  }
};

const handleMouseMove = (event) => {
  tooltipPos.value = { x: event.clientX + 15, y: event.clientY + 15 };
};

const handleMouseLeave = () => {
  tooltipShipment.value = null;
};

const clearFilters = () => {
  searchFilters.value = {
    ma_to_khai: '',
    ten_lo_hang: '',
    phan_luong: '',
    ket_qua_thong_quan: '',
    ngay_thong_quan: '',
    ten_nguoi_sua: ''
  };
  phanLuongSearchText.value = '';
  ketQuaSearchText.value = '';
  userSearchText.value = '';
  currentPage.value = 1;
};

watch([searchFilters, pageSize], () => {
  currentPage.value = 1;
}, { deep: true });

onMounted(() => {
  window.addEventListener('click', (e) => {
    if (!e.target.closest('.combobox-wrapper')) {
      showPhanLuongDropdown.value = showKetQuaDropdown.value = showUserDropdown.value = false;
    }
  });
});

const handleDelete = async (id) => {
  if (confirm('Bạn có chắc chắn muốn xóa tờ khai này không?')) {
    try {
      const response = await fetch(`${import.meta.env.VITE_API_URL}/to-khai-hai-quan/delete`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ma_to_khai_hai_quan: id })
      });
      const data = await response.json();
      if (data.success) { alert("Đã xóa thành công!"); fetchData(); }
      else { alert("Lỗi: " + data.message); }
    } catch (error) {
      alert("Lỗi khi xóa kết nối!");
    }
  }
};

onMounted(async () => {
  await fetchData(); 
  await fetchReferences();
});

const formatDateTime = (str) => {
  if (!str) return '---';
  return new Date(str).toLocaleString('vi-VN');
};

const getPhanLuongStyle = (pl) => {
  if (pl === 'Luồng Xanh') return { backgroundColor: '#27ae60', color: 'white', whiteSpace: 'nowrap' };
  if (pl === 'Luồng Vàng') return { backgroundColor: '#f1c40f', color: 'white', whiteSpace: 'nowrap' };
  if (pl === 'Luồng Đỏ') return { backgroundColor: '#e74c3c', color: 'white', whiteSpace: 'nowrap' };
  return { backgroundColor: '#95a5a6', color: 'white', whiteSpace: 'nowrap' };
};

const getKetQuaStyle = (kq) => {
  if (kq === 'Đã thông quan') return { backgroundColor: '#27ae60', color: 'white', whiteSpace: 'nowrap' };
  if (kq === 'Chờ xử lý') return { backgroundColor: '#f39c12', color: 'white', whiteSpace: 'nowrap' };
  if (kq === 'Từ chối') return { backgroundColor: '#e74c3c', color: 'white', whiteSpace: 'nowrap' };
  return { backgroundColor: '#95a5a6', color: 'white', whiteSpace: 'nowrap' };
};
</script>

<style scoped src="../../assets/quanlytaikhoan.css"></style>
<style scoped>
.btn-pagination {
  padding: 5px 12px; background: white; border: 1px solid #ddd; border-radius: 4px; cursor: pointer; transition: 0.2s;
}
.btn-pagination:hover:not(:disabled) { background: #f0f0f0; border-color: #3498db; color: #3498db; }
.btn-pagination:disabled { cursor: not-allowed; opacity: 0.5; }

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

.badge-active { background-color: #27ae60; color: white; padding: 2px 8px; border-radius: 10px; font-size: 12px; }

.row-even { background-color: #f2f2f2 !important; }
.row-odd { background-color: #ffffff !important; }

/* CSS cho cột cố định */
.sticky-col-left {
  position: sticky;
  left: 0;
  z-index: 10;
  border-right: 1px solid #ddd !important;
}
.sticky-col-right {
  position: sticky;
  right: 0;
  z-index: 10;
  border-left: 1px solid #ddd !important;
}
tr.row-even .sticky-col-left, tr.row-even .sticky-col-right { background-color: #f2f2f2 !important; }
tr.row-odd .sticky-col-left, tr.row-odd .sticky-col-right { background-color: #ffffff !important; }
tr.row-selected .sticky-col-left, tr.row-selected .sticky-col-right { background-color: #f0f7ff !important; }
thead th.sticky-col-left, thead th.sticky-col-right { background-color: #f8f9fa !important; z-index: 11; }

.shipment-tooltip {
  position: fixed;
  z-index: 9999;
  background: white;
  border: 1px solid #3498db;
  border-radius: 8px;
  padding: 12px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.15);
  width: 300px;
  pointer-events: none;
  font-size: 13px;
  color: #2c3e50;
}
.tooltip-header {
  border-bottom: 1px solid #eee; padding-bottom: 5px; margin-bottom: 8px; font-weight: bold; color: #2980b9;
}
.tooltip-content div {
  margin-bottom: 4px; display: flex; justify-content: space-between; gap: 10px;
}
.tooltip-content strong { color: #7f8c8d; font-weight: normal; white-space: nowrap; }
.tooltip-content span { text-align: right; font-weight: 600; }

.table-card table th, .table-card table td { 
  white-space: nowrap; 
  padding: 12px 15px; 
}

/* CSS cho Combobox Tìm kiếm trong Toolbar */
.combobox-wrapper { position: relative; }
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

/* Đảm bảo bảng hiển thị đẹp */
.table-card table {
  border-collapse: collapse;
  width: 100%;
}
.table-card th, .table-card td {
  padding: 12px 15px;
  white-space: nowrap;
}
</style>
