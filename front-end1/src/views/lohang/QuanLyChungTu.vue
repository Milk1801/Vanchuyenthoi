<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">Quản lý Số hóa Chứng từ theo Lô hàng</h3>
    
    <div class="toolbar" style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 15px; align-items: flex-end;">
      <div class="search-box" style="flex: 1; min-width: 250px;">
        <input 
          type="text" 
          v-model="searchQuery" 
          placeholder="🔍 Tìm theo tên, mã, nguồn gốc..."
          style="width: 100%; padding: 8px 12px; border-radius: 4px; border: 1px solid #ccc;"
        >
      </div>

      <div class="search-box" style="flex: 1; min-width: 250px;">
        <input 
          type="text" 
          v-model="searchItemQuery" 
          placeholder="🔍 Tìm theo tên hàng hóa..."
          style="width: 100%; padding: 8px 12px; border-radius: 4px; border: 1px solid #ccc;"
        >
      </div>

      <div class="combobox-wrapper" style="width: 180px;">
        <input type="text" v-model="khSearchText" placeholder="👤 Khách hàng..." @focus="showKhDropdown = true" class="combobox-input-sm">
        <ul v-if="showKhDropdown" class="combobox-list">
          <li @click="selectFilter('kh', null, 'Tất cả Khách hàng')">Tất cả Khách hàng</li>
          <li v-for="kh in filteredKhList" :key="kh.ma_khach_hang" @click="selectFilter('kh', kh.ma_khach_hang, kh.ten_khach_hang)">
            {{ kh.ten_khach_hang }}
          </li>
        </ul>
      </div>

      <select v-model="filterIncoterms" style="padding: 8px; border-radius: 4px; border: 1px solid #ccc; width: 150px;">
        <option :value="null">🚚 Incoterms (Tất cả)</option>
        <option v-for="dk in ['FOB', 'CIF', 'EXW', 'DAP', 'DDP', 'CFR']" :key="dk" :value="dk">{{ dk }}</option>
      </select>

      <select v-model="filterTrangThai" style="padding: 8px; border-radius: 4px; border: 1px solid #ccc; width: 180px;">
        <option value="ALL">- Tất cả Trạng thái -</option>
        <option v-for="st in listTrangThai" :key="st" :value="st">{{ st }}</option>
      </select>

      <select v-model="filterDocType" style="padding: 8px; border-radius: 4px; border: 1px solid #ccc; width: 150px;">
        <option :value="null">📄 Loại C/T (Tất cả)</option>
        <option value="SC">Sales Contract (SC)</option>
        <option value="INV">Invoice (INV)</option>
        <option value="PKL">Packing List (PKL)</option>
        <option value="CO">C/O</option>
        <option value="BL">B/L</option>
        <option value="DO">D/O</option>
      </select>

      <select v-model="filterDocExistence" style="padding: 8px; border-radius: 4px; border: 1px solid #ccc; width: 130px;">
        <option value="ALL">🔍 Tình trạng</option>
        <option value="HAS">✅ Đã có</option>
        <option value="NOT_HAS">❌ Chưa có</option>
      </select>

      <select v-model="filterUser" style="padding: 8px; border-radius: 4px; border: 1px solid #ccc; width: 180px;">
        <option :value="null">👤 Người sửa (Tất cả)</option>
        <option v-for="user in uniqueUsers" :key="user" :value="user">{{ user }}</option>
      </select>

      <button 
        @click="resetFilters" 
        style="padding: 8px 15px; border: 1px solid #ccc; border-radius: 4px; background: #fff; cursor: pointer; transition: 0.2s;"
        title="Xóa lọc"
      >
        🧹 Xóa lọc
      </button>

      <div class="combobox-wrapper" style="width: auto;">
        <button @click="showColumnDropdown = !showColumnDropdown" style="padding: 8px 15px; border: 1px solid #ccc; border-radius: 4px; background: #fff; cursor: pointer; transition: 0.2s; height: 38px; font-size: 13px;" title="Tùy chỉnh hiển thị cột">⚙️ Cột</button>
        <ul v-if="showColumnDropdown" class="combobox-list column-dropdown-list" style="width: 220px; padding: 10px 0; display: flex; flex-direction: column;">
          <li v-for="col in columnDefinitions" :key="col.key" style="display: flex; align-items: center; gap: 10px; cursor: default; border-bottom: none; padding: 5px 15px;" @click.stop>
            <input type="checkbox" v-model="columnVisibility[col.key]" :id="'col-pop-' + col.key" style="cursor: pointer; width: 16px; height: 16px;">
            <label :for="'col-pop-' + col.key" style="cursor: pointer; flex: 1; font-size: 13px; color: #2c3e50;">{{ col.label }}</label>
          </li>
        </ul>
      </div>
    </div>

    <div v-if="isLoading" style="text-align: center; padding: 20px; color: #3498db;">
      <div class="loader-inline"></div> Đang tải dữ liệu Lô hàng...
    </div>

    <div v-else>
      <!-- Pagination Controls -->
      <div v-if="filteredLoHang.length > 0" class="pagination-controls" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 8px; border: 1px solid #e1e4e8;">
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

      <div class="table-card scrollable-table">
      <table>
        <thead>
          <tr @click="selectedItem = null">
            <th style="width: 50px; text-align: center;">STT</th>
            <th v-if="columnVisibility.ma_lo_hang" style="width: 100px;">Mã Lô</th>
            <th v-if="columnVisibility.ten_lo_hang" style="width: 250px;">Tên Lô Hàng</th>
            <th v-if="columnVisibility.ten_khach_hang" style="width: 200px;">Khách Hàng</th>
            <th v-if="columnVisibility.dieu_kien_thuong_mai" style="width: 100px;">Điều kiện</th>
            <th v-if="columnVisibility.so_booking" style="width: 150px;">Số Booking</th>
            <th v-if="columnVisibility.trang_thai_lo_hang" style="width: 150px;">Trạng thái</th>
            <th v-if="columnVisibility.nguoi_sua_doi" style="width: 150px;">Người sửa cuối</th>
            <th style="width: 180px; text-align: center;">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(lh, index) in paginatedLoHang" :key="lh.ma_lo_hang" :class="{ 'row-selected': (selectedItem === lh.ma_lo_hang) }" @click="selectedItem = lh.ma_lo_hang">
            <td style="text-align: center; color: #7f8c8d;">{{ (currentPage - 1) * pageSize + index + 1 }}</td>
            <td v-if="columnVisibility.ma_lo_hang">{{ lh.ma_lo_hang }}</td>
            <td v-if="columnVisibility.ten_lo_hang">
              <div class="custom-tooltip">
                <span class="tooltip-trigger" style="color: #2980b9;">{{ lh.ten_lo_hang }}</span>
                <span v-if="lh.ds_ten_hang" class="tooltip-text" style="max-width: 400px;">
                  <strong>📦 Danh sách hàng hóa:</strong><br>{{ lh.ds_ten_hang }}
                </span>
              </div>
            </td>
            <td v-if="columnVisibility.ten_khach_hang">{{ lh.ten_khach_hang || '---' }}</td>
            <td v-if="columnVisibility.dieu_kien_thuong_mai"><span class="badge" style="background-color: #9b59b6; color: white;">{{ lh.dieu_kien_thuong_mai }}</span></td>
            <td v-if="columnVisibility.so_booking" style="color: #2980b9; font-weight: bold;">{{ lh.so_booking || 'Chưa gắn' }}</td>
            <td v-if="columnVisibility.trang_thai_lo_hang">
              <span class="badge" :class="statusClass(lh.trang_thai_lo_hang)">
                {{ lh.trang_thai_lo_hang }}
              </span>
            </td>
            <td v-if="columnVisibility.nguoi_sua_doi">{{ lh.nguoi_sua_doi || 'N/A' }}</td>
            <td style="text-align: center;">
              <button v-if="hasRole(1)"
                class="btn-manage" 
                @click="router.push(`/lo-hang/chung-tu/chi-tiet/${lh.ma_lo_hang}`)"
                title="Quản lý chứng từ cho lô hàng này"
              >
                📂 Quản lý chứng từ
              </button>
              <span v-else style="color: #95a5a6; font-size: 12px; font-style: italic;">Chỉ xem</span>
            </td> 
          </tr>
          <tr v-if="paginatedLoHang.length === 0">
            <td :colspan="visibleColumnCount" style="text-align: center; padding: 30px; color: #95a5a6;">
              Không tìm thấy lô hàng nào!
            </td>
          </tr>
        </tbody>
      </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { hasRole } from '../../assets/chucnang';

const router = useRouter();
const listLoHang = ref([]);
const listKhachHang = ref([]);
const isLoading = ref(true);
const searchQuery = ref(''); 
const searchItemQuery = ref('');

const filterDocType = ref(null);
const filterDocExistence = ref('ALL');

const selectedItem = ref(null);

const currentPage = ref(1);
const pageSize = ref(10);
const pageSizes = [10, 20, 50];

// Filter states
const filterTrangThai = ref('ALL');
const filterKhachHang = ref(null);
const filterIncoterms = ref(null);
const filterUser = ref(null);

// Combobox search texts and dropdown visibility
const khSearchText = ref('');
const showKhDropdown = ref(false);
const showColumnDropdown = ref(false);

// Column visibility feature
const columnDefinitions = ref([
  { key: 'ma_lo_hang', label: 'Mã Lô' },
  { key: 'ten_lo_hang', label: 'Tên Lô Hàng' },
  { key: 'ten_khach_hang', label: 'Khách Hàng' },
  { key: 'dieu_kien_thuong_mai', label: 'Điều kiện' },
  { key: 'so_booking', label: 'Booking' },
  { key: 'trang_thai_lo_hang', label: 'Trạng thái' },
  { key: 'nguoi_sua_doi', label: 'Người sửa cuối' },
]);

const columnVisibility = reactive({});
columnDefinitions.value.forEach(col => {
  columnVisibility[col.key] = true; // Mặc định hiển thị tất cả các cột
});

const visibleColumnCount = computed(() => {
  let count = 2; // Mặc định có STT và Thao tác
  columnDefinitions.value.forEach(col => { if (columnVisibility[col.key]) count++; });
  return count;
});

const listTrangThai = ['Đang chờ xử lý', 'Đang vận chuyển', 'Đã thông quan', 'Hoàn tất', 'Hủy'];

// Computed lọc danh sách cho dropdown
const filteredKhList = computed(() => listKhachHang.value.filter(kh => kh.ten_khach_hang.toLowerCase().includes(khSearchText.value.toLowerCase())));

const uniqueUsers = computed(() => {
  const users = listLoHang.value.map(lh => lh.nguoi_sua_cuoi || lh.nguoi_sua_doi).filter(Boolean);
  return [...new Set(users)];
});

const prevPage = () => { if (currentPage.value > 1) currentPage.value--; };
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++; };

const statusClass = (status) => {
  if (['Hoàn tất', 'Đang vận chuyển', 'Đã thông quan'].includes(status)) return 'badge-active';
  if (status === 'Hủy') return 'badge-locked';
  return 'badge-warning';
};

const filteredLoHang = computed(() => {
  const q = searchQuery.value.toLowerCase().trim();
  const itemQ = searchItemQuery.value.toLowerCase().trim();
  
  return listLoHang.value.filter(lh => {
    const matchSearch = !q || 
      (lh.ten_lo_hang && lh.ten_lo_hang.toLowerCase().includes(q)) ||
      (lh.ma_lo_hang && lh.ma_lo_hang.toString().includes(q)) ||
      (lh.so_booking && lh.so_booking.toLowerCase().includes(q)) ||
      (lh.ten_khach_hang && lh.ten_khach_hang.toLowerCase().includes(q));

    const matchItem = !itemQ || (lh.ds_ten_hang && lh.ds_ten_hang.toLowerCase().includes(itemQ));
    const matchTrangThai = filterTrangThai.value === 'ALL' || lh.trang_thai_lo_hang === filterTrangThai.value;
    const matchKhachHang = !filterKhachHang.value || lh.ma_khach_hang === filterKhachHang.value;
    const matchIncoterms = !filterIncoterms.value || lh.dieu_kien_thuong_mai === filterIncoterms.value;
    const matchUser = !filterUser.value || (lh.nguoi_sua_cuoi === filterUser.value || lh.nguoi_sua_doi === filterUser.value);

    const docs = lh.ds_loai_ct ? String(lh.ds_loai_ct).split(',') : [];
    const matchDoc = !filterDocType.value || filterDocExistence.value === 'ALL' || 
                    (filterDocExistence.value === 'HAS' ? docs.includes(filterDocType.value) : !docs.includes(filterDocType.value));

    return matchSearch && matchItem && matchTrangThai && matchKhachHang && matchIncoterms && matchUser && matchDoc;
  });
});

const selectFilter = (type, val, text) => {
  if (type === 'kh') {
    filterKhachHang.value = val;
    khSearchText.value = text || '';
    showKhDropdown.value = false;
  }
};

const resetFilters = () => {
  searchQuery.value = '';
  searchItemQuery.value = '';
  filterTrangThai.value = 'ALL';
  filterKhachHang.value = null;
  filterIncoterms.value = null;
  filterUser.value = null;
  filterDocType.value = null;
  filterDocExistence.value = 'ALL';
  khSearchText.value = '';
  currentPage.value = 1;
};

const totalPages = computed(() => {
  return Math.ceil(filteredLoHang.value.length / pageSize.value) || 1;
});

const paginatedLoHang = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value;
  const end = start + pageSize.value;
  return filteredLoHang.value.slice(start, end);
});

watch([searchQuery, searchItemQuery, filterTrangThai, filterKhachHang, filterIncoterms, filterUser, filterDocType, filterDocExistence, pageSize], () => {
  currentPage.value = 1;
});

const fetchReferences = async () => {
  try {
    const resRef = await fetch(`${import.meta.env.VITE_API_URL}/lo-hang/references`);
    const dataRef = await resRef.json();
    if (dataRef.success) {
      listKhachHang.value = dataRef.khach_hang;
    }
  } catch (error) {
    console.error("Lỗi lấy dữ liệu tham chiếu");
  }
  window.addEventListener('click', (e) => {
    if (!e.target.closest('.combobox-wrapper')) { 
      showKhDropdown.value = false; 
      showColumnDropdown.value = false;
    }
  });
};

const fetchData = async () => {
  isLoading.value = true;
  try {
    const res = await fetch(`${import.meta.env.VITE_API_URL}/chung-tu`);
    const data = await res.json();
    if (data.success) listLoHang.value = data.lo_hang;
  } catch (error) {
    console.error("Lỗi lấy dữ liệu Lô hàng!");
  } finally { isLoading.value = false; }
}; 

onMounted(fetchData);
onMounted(fetchReferences);
</script>

<style scoped src="../../assets/quanlytaikhoan.css"></style>
<style scoped>
.btn-manage {
  background: #3498db;
  color: white;
  border: none;
  padding: 8px 15px;
  border-radius: 4px;
  cursor: pointer;
  font-weight: bold;
  transition: 0.3s;
  display: inline-flex;
  align-items: center;
  gap: 5px;
}
.btn-manage:hover {
  background: #2980b9;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(52, 152, 219, 0.3);
}

.column-visibility-controls {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
}


.row-selected { background-color: #f0f7ff !important; }

/* Tooltip tự định nghĩa không dùng thư viện */
.custom-tooltip {
  position: relative;
  display: inline-block;
}
.tooltip-trigger {
  cursor: help;
  color: #2980b9;
  border-bottom: 1px dashed #2980b9;
  font-weight: 500;
}
.custom-tooltip .tooltip-text {
  visibility: hidden;
  width: 320px;
  background-color: #fff;
  color: #2c3e50;
  text-align: left;
  border-radius: 6px;
  padding: 12px;
  position: absolute;
  z-index: 9999;
  bottom: 100%; /* Sát mép trên của chữ */
  margin-bottom: 10px;
  left: 50%;
  transform: translateX(-50%);
  opacity: 0;
  transition: opacity 0.3s;
  font-size: 12px;
  line-height: 1.5;
  white-space: pre-wrap; /* Giữ định dạng xuống dòng từ backend */
  box-shadow: 0 10px 30px rgba(0,0,0,0.3);
  pointer-events: none;
  border: 1px solid #455a64;
}

.badge-warning { background-color: #f39c12; color: white; }

.btn-pagination {
  padding: 5px 12px; background: white; border: 1px solid #ddd; border-radius: 4px; cursor: pointer;
}
.btn-pagination:hover:not(:disabled) { background: #f0f0f0; border-color: #3498db; color: #3498db; }
.btn-pagination:disabled { opacity: 0.5; cursor: not-allowed; }

.scrollable-table {
  overflow-x: auto;
  max-width: 100%;
  border: 1px solid #e1e4e8;
  border-radius: 8px;
}

.loader-inline {
  border: 2px solid #f3f3f3;
  border-top: 2px solid #3498db;
  border-radius: 50%;
  width: 14px;
  height: 14px;
  animation: spin 1s linear infinite;
  display: inline-block;
}
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

/* Xử lý cho 2 dòng đầu tiên của bảng: Hiển thị tooltip xuống dưới để không bị mép bảng/header che mất */
tbody tr:nth-child(-n+3) .custom-tooltip .tooltip-text {
  bottom: auto;
  top: 100%;
  margin-top: 10px;
  margin-bottom: 0;
}
/* Đảo ngược mũi tên cho trường hợp tooltip hiển thị phía dưới */
tbody tr:nth-child(-n+2) .custom-tooltip .tooltip-text::after {
  top: auto;
  bottom: 100%;
  border-color: transparent transparent #2c3e50 transparent;
}

.column-dropdown-list { right: 0; left: auto; }

.custom-tooltip:hover {
  z-index: 9999;
}
.custom-tooltip:hover .tooltip-text {
  visibility: visible;
  opacity: 1;
  pointer-events: auto;
}
/* Tạo mũi tên cho tooltip */
.custom-tooltip .tooltip-text::after {
  content: "";
  position: absolute;
  top: 100%;
  left: 50%;
  margin-left: -5px;
  border-width: 5px;
  border-style: solid;
  border-color: #2c3e50 transparent transparent transparent;
}
</style>