<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">Quản lý thông tin Lô Hàng</h3>
    
    <!-- Thông báo Booking chưa sử dụng -->
    <div v-if="unusedBookingCount > 0" class="booking-alert-banner">
      <span class="icon">🔔</span>
      <span class="text">Hiện có <strong>{{ unusedBookingCount }}</strong> Booking Note chưa được liên kết (chưa có lô hàng nào chọn).</span>
    </div>

    <div class="toolbar" style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 15px;">
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

      <div class="combobox-wrapper" style="width: 180px;">
        <input type="text" v-model="hhSearchText" placeholder="📦 Loại hàng..." @focus="showHhDropdown = true" class="combobox-input-sm">
        <ul v-if="showHhDropdown" class="combobox-list">
          <li @click="selectFilter('hh', null, 'Tất cả Loại hàng')">Tất cả Loại hàng</li>
          <li v-for="h in filteredHhList" :key="h.ma_hang_hoa" @click="selectFilter('hh', h.ma_hang_hoa, h.ten_hang_hoa)">
            {{ h.ten_hang_hoa }}
          </li>
        </ul>
      </div>

      <select v-model="filterTrangThai" style="padding: 8px; border-radius: 4px; border: 1px solid #ccc; width: 180px;">
        <option value="ALL">- Tất cả Trạng thái -</option>
        <option v-for="st in listTrangThai" :key="st" :value="st">{{ st }}</option>
      </select>

      <div class="combobox-wrapper" style="width: 160px;">
        <input type="text" v-model="blSearchText" placeholder="📄 Số Vận Đơn..." @focus="showBlDropdown = true" class="combobox-input-sm">
        <ul v-if="showBlDropdown" class="combobox-list">
          <li @click="selectFilter('bl', null, '')">-- Tất cả Vận đơn --</li>
          <li v-for="val in filteredBlList" :key="val" @click="selectFilter('bl', val, val)">{{ val }}</li>
        </ul>
      </div>

      <div class="combobox-wrapper" style="width: 140px;">
        <input type="text" v-model="anSearchText" placeholder="📧 Mã giấy báo hàng đến..." @focus="showAnDropdown = true" class="combobox-input-sm">
        <ul v-if="showAnDropdown" class="combobox-list">
          <li @click="selectFilter('an', null, '')">-- Tất cả AN --</li>
          <li v-for="val in filteredAnList" :key="val" @click="selectFilter('an', val, val)">{{ val }}</li>
        </ul>
      </div>

      <div class="combobox-wrapper" style="width: 140px;">
        <input type="text" v-model="doSearchText" placeholder="🚚 Mã D/O..." @focus="showDoDropdown = true" class="combobox-input-sm">
        <ul v-if="showDoDropdown" class="combobox-list">
          <li @click="selectFilter('do', null, '')">-- Tất cả DO --</li>
          <li v-for="val in filteredDoList" :key="val" @click="selectFilter('do', val, val)">{{ val }}</li>
        </ul>
      </div>

      <div class="combobox-wrapper" style="width: 140px;">
        <input type="text" v-model="bbgnSearchText" placeholder="📝 Mã BBGN..." @focus="showBbgnDropdown = true" class="combobox-input-sm">
        <ul v-if="showBbgnDropdown" class="combobox-list">
          <li @click="selectFilter('bbgn', null, '')">-- Tất cả BBGN --</li>
          <li v-for="val in filteredBbgnList" :key="val" @click="selectFilter('bbgn', val, val)">{{ val }}</li>
        </ul>
      </div>

      <div class="combobox-wrapper" style="width: 140px;">
        <input type="text" v-model="tkSearchText" placeholder="📑 Mã Tờ Khai..." @focus="showTkDropdown = true" class="combobox-input-sm">
        <ul v-if="showTkDropdown" class="combobox-list">
          <li @click="selectFilter('tk', null, '')">-- Tất cả Tờ Khai --</li>
          <li v-for="val in filteredTkList" :key="val" @click="selectFilter('tk', val, val)">{{ val }}</li>
        </ul>
      </div>

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
        <ul v-if="showColumnDropdown" class="combobox-list" style="width: 220px; right: 0; left: auto; padding: 10px 0; display: flex; flex-direction: column;">
          <li v-for="col in columnDefinitions" :key="col.key" style="display: flex; align-items: center; gap: 10px; cursor: default; border-bottom: none; padding: 5px 15px;" @click.stop>
            <input type="checkbox" v-model="columnVisibility[col.key]" :id="'col-pop-' + col.key" style="cursor: pointer; width: 16px; height: 16px;">
            <label :for="'col-pop-' + col.key" style="cursor: pointer; flex: 1; font-size: 13px; color: #2c3e50;">{{ col.label }}</label>
          </li>
        </ul>
      </div>


      <button v-if="hasRole(1)" class="btn btn-success" @click="router.push('/lo-hang/thong-tin-lo-hang/add')">+ TẠO LÔ HÀNG MỚI</button>
    </div>

    <div style="width: 100%;">
      <div style="flex: 1; min-width: 0;">
        <div v-if="isLoading" style="text-align: center; padding: 20px; color: #3498db;">
          Đang tải dữ liệu Lô hàng...
        </div>

        <template v-if="!isLoading">
          <!-- Pagination Controls -->
          <div v-if="listLoHang.length > 0" class="pagination-controls" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 8px; border: 1px solid #e1e4e8;">
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

          <div class="table-card scrollable-table" style="margin-top: 15px;">
            <table>
              <thead>
                <tr>
                  <th style="width: 50px; text-align: center;">STT</th>
                  <th v-if="columnVisibility.ma_lo_hang" style="width: 80px;">Mã Lô</th>
                  <th v-if="columnVisibility.ten_lo_hang" style="width: 250px;">Tên Lô Hàng</th>
                  <th v-if="columnVisibility.ten_khach_hang" style="width: 200px;">Khách Hàng</th>
                  <th v-if="columnVisibility.dieu_kien_thuong_mai" style="width: 100px;">Điều kiện</th>
                  <th v-if="columnVisibility.so_booking" style="width: 150px;">Booking Note</th>
                  <th v-if="columnVisibility.so_van_don" style="width: 150px;">Số Vận Đơn</th>
                  <th v-if="columnVisibility.ma_thong_bao_hang_den" style="width: 130px;">Mã AN</th>
                  <th v-if="columnVisibility.ma_lenh_giao_hang" style="width: 120px;">Mã D/O</th>
                  <th v-if="columnVisibility.ma_bien_ban_giao_nhan" style="width: 120px;">Mã BBGN</th>
                  <th v-if="columnVisibility.ma_to_khai_hai_quan" style="width: 120px;">Mã Tờ Khai</th>
                  <th v-if="columnVisibility.nguon_goc" style="width: 150px;">Nguồn gốc</th>
                  <th v-if="columnVisibility.trang_thai_lo_hang" style="width: 160px;">Trạng thái</th>
                  <th v-if="columnVisibility.nguoi_sua_doi" style="width: 150px;">Người sửa cuối</th>
                  <th style="width: 180px; text-align: center;">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(lh, index) in paginatedLoHang" :key="lh.ma_lo_hang" :class="{ 'row-selected': (selectedItem === lh.ma_lo_hang) }" @click="selectedItem = lh.ma_lo_hang">
                  <td class="fw-bold" style="text-align: center;">{{ (currentPage - 1) * pageSize + index + 1 }}</td>
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
                  <td 
                    v-if="columnVisibility.so_booking" 
                    @click="lh.ma_booking && hasRole(3) && router.push('/lo-hang/booking/edit/' + lh.ma_booking)" 
                    :style="lh.ma_booking && hasRole(3) ? 'cursor: pointer;' : 'cursor: default;'"
                  >
                    <div style="display: flex; align-items: center; gap: 5px;">
                      <div class="custom-tooltip" v-if="lh.so_booking">
                        <span class="tooltip-trigger">{{ lh.so_booking }}</span>
                        <span v-if="lh.booking_tooltip" class="tooltip-text">{{ lh.booking_tooltip }}</span>
                      </div>
                      <span v-else>Chưa gắn</span>
                    </div>
                  </td>
                  <td 
                    v-if="columnVisibility.so_van_don" 
                    @click="handleVanDonClick(lh)" 
                    :style="hasRole(1) ? 'cursor: pointer;' : 'cursor: default;'"
                  >
                    <div class="custom-tooltip" v-if="lh.so_van_don">
                      <span class="tooltip-trigger">{{ lh.so_van_don }}</span>
                      <span v-if="lh.van_don_tooltip" class="tooltip-text">{{ lh.van_don_tooltip }}</span>
                    </div>
                    <span v-else style="color: #95a5a6; font-style: italic; font-size: 12px;">Tạo mới</span>
                  </td>
                  <td 
                    v-if="columnVisibility.ma_thong_bao_hang_den" 
                    @click="handleAnClick(lh)" 
                    :style="hasRole(1) ? 'cursor: pointer;' : 'cursor: default;'"
                  >
                    <div class="custom-tooltip" v-if="lh.ma_thong_bao_hang_den">
                      <span class="tooltip-trigger">{{ lh.ma_thong_bao_hang_den }}</span>
                      <span v-if="lh.an_tooltip" class="tooltip-text">{{ lh.an_tooltip }}</span>
                    </div>
                    <span v-else style="color: #95a5a6; font-style: italic; font-size: 12px;">Tạo mới</span>
                  </td>
                  <td 
                    v-if="columnVisibility.ma_lenh_giao_hang" 
                    @click="handleDoClick(lh)" 
                    :style="hasRole(1) ? 'cursor: pointer;' : 'cursor: default;'"
                  >
                    <div class="custom-tooltip" v-if="lh.ma_lenh_giao_hang">
                      <span class="tooltip-trigger">{{ lh.ma_lenh_giao_hang }}</span>
                      <span v-if="lh.do_tooltip" class="tooltip-text">{{ lh.do_tooltip }}</span>
                    </div>
                    <span v-else style="color: #95a5a6; font-style: italic; font-size: 12px;">Tạo mới</span>
                  </td>
                  <td 
                    v-if="columnVisibility.ma_bien_ban_giao_nhan" 
                    @click="handleBbgnClick(lh)" 
                    :style="hasRole(3) ? 'cursor: pointer;' : 'cursor: default;'"
                  >
                    <div class="custom-tooltip" v-if="lh.ma_bien_ban_giao_nhan">
                      <span class="tooltip-trigger">{{ lh.ma_bien_ban_giao_nhan }}</span>
                      <span v-if="lh.bbgn_tooltip" class="tooltip-text">{{ lh.bbgn_tooltip }}</span>
                    </div>
                    <span v-else style="color: #95a5a6; font-style: italic; font-size: 12px;">Tạo mới</span>
                  </td>
                  <td 
                    v-if="columnVisibility.ma_to_khai_hai_quan" 
                    @click="handleTkClick(lh)" 
                    :style="hasRole(4) ? 'cursor: pointer;' : 'cursor: default;'"
                  >
                    <div class="custom-tooltip" v-if="lh.ma_to_khai_hai_quan">
                      <span class="tooltip-trigger">{{ lh.ma_to_khai_hai_quan }}</span>
                      <span v-if="lh.to_khai_tooltip" class="tooltip-text">{{ lh.to_khai_tooltip }}</span>
                    </div>
                    <span v-else style="color: #95a5a6; font-style: italic; font-size: 12px;">Tạo mới</span>
                  </td>
                  <td v-if="columnVisibility.nguon_goc">{{ lh.nguon_goc || '---' }}</td>
                  <td v-if="columnVisibility.trang_thai_lo_hang">
                    <span class="badge" :class="statusClass(lh.trang_thai_lo_hang)" style="white-space: nowrap;">
                      {{ lh.trang_thai_lo_hang }}
                    </span>
                  </td>
                  <td v-if="columnVisibility.nguoi_sua_doi">{{ lh.nguoi_sua_doi || 'N/A' }}</td>
                  <td style="text-align: center;">
                    <div v-if="hasRole(1)" style="display: flex; gap: 8px; justify-content: center;">
                      <button class="action-btn text-primary" @click="router.push('/lo-hang/thong-tin-lo-hang/edit/' + lh.ma_lo_hang)" title="Sửa">✏️</button>
                      <button class="action-btn text-danger" @click="handleDelete(lh.ma_lo_hang)" title="Xóa">🗑️</button>
                      <button class="action-btn" @click="router.push(`/lo-hang/chung-tu/chi-tiet/${lh.ma_lo_hang}`)" title="Quản lý chứng từ cho lô hàng này">📂</button>
                    </div>
                    <!-- Mã quyền 2 hoặc các quyền khác không có số 1 chỉ có thể xem, không có nút thao tác -->
                    <span v-else style="color: #95a5a6; font-size: 12px; font-style: italic;">Chỉ xem</span>
                  </td>
                </tr>
                <tr v-if="paginatedLoHang.length === 0">
                  <td :colspan="visibleColumnCount" style="text-align: center; padding: 20px; color: #7f8c8d;">
                    Không tìm thấy lô hàng nào!
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </template>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, watch, reactive } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();
const listLoHang = ref([]);
const listKhachHang = ref([]);
const listBooking = ref([]);
const selectedItem = ref(null);
const isLoading = ref(true);
const searchQuery = ref('');
const searchItemQuery = ref(''); // Keep this for item search
const filterTrangThai = ref('ALL');
const filterKhachHang = ref(null);
const filterIncoterms = ref(null);
const filterUser = ref(null);
const filterHangHoa = ref(null);
const listHangHoa = ref([]);
const unusedBookingCount = ref(0);
let refreshInterval = null;
const filterSoVanDon = ref(null);
const filterAn = ref(null);
const filterDo = ref(null);
const filterBbgn = ref(null);
const filterTk = ref(null);

// State cho Combobox tìm kiếm
const khSearchText = ref('');
const showKhDropdown = ref(false);
const hhSearchText = ref('');
const showHhDropdown = ref(false);
const blSearchText = ref('');
const showBlDropdown = ref(false);
const anSearchText = ref('');
const showAnDropdown = ref(false);
const doSearchText = ref('');
const showDoDropdown = ref(false);
const bbgnSearchText = ref('');
const showBbgnDropdown = ref(false);
const tkSearchText = ref('');
const showTkDropdown = ref(false);
const showColumnDropdown = ref(false);

// Column visibility feature
const columnDefinitions = ref([
  { key: 'ma_lo_hang', label: 'Mã Lô' },
  { key: 'ten_lo_hang', label: 'Tên Lô Hàng' },
  { key: 'ten_khach_hang', label: 'Khách Hàng' },
  { key: 'dieu_kien_thuong_mai', label: 'Điều kiện' },
  { key: 'so_booking', label: 'Booking' },
  { key: 'so_van_don', label: 'Số Vận Đơn' },
  { key: 'ma_thong_bao_hang_den', label: 'Mã AN' },
  { key: 'ma_lenh_giao_hang', label: 'Mã D/O' },
  { key: 'ma_bien_ban_giao_nhan', label: 'Mã BBGN' },
  { key: 'ma_to_khai_hai_quan', label: 'Mã Tờ Khai' },
  { key: 'nguon_goc', label: 'Nguồn gốc' },
  { key: 'trang_thai_lo_hang', label: 'Trạng thái' },
  { key: 'nguoi_sua_doi', label: 'Người sửa cuối' },
]);

// Logic phân quyền
const currentUser = JSON.parse(localStorage.getItem('sincere_user') || '{}');
const hasRole = (roleIdOrArray) => {
  if (!currentUser.ds_quyen) return false;
  const roles = currentUser.ds_quyen.map(q => q.ma_quyen);
  if (roles.includes(5)) return true; // Mã quyền 5: Toàn quyền (Admin)
  
  const requiredRoles = Array.isArray(roleIdOrArray) ? roleIdOrArray : [roleIdOrArray];
  return requiredRoles.some(r => roles.includes(r));
};

const columnVisibility = reactive({});
columnDefinitions.value.forEach(col => {
  columnVisibility[col.key] = true;
});

// Computed lọc danh sách cho dropdown
const filteredKhList = computed(() => listKhachHang.value.filter(kh => kh.ten_khach_hang.toLowerCase().includes(khSearchText.value.toLowerCase())));
const filteredHhList = computed(() => listHangHoa.value.filter(h => h.ten_hang_hoa.toLowerCase().includes(hhSearchText.value.toLowerCase())));

const getUniqueList = (key, searchVal) => {
  const list = [...new Set(listLoHang.value.map(lh => lh[key]).filter(Boolean))].sort();
  return list.filter(item => String(item).toLowerCase().includes(searchVal.toLowerCase()));
};
const filteredBlList = computed(() => getUniqueList('so_van_don', blSearchText.value));
const filteredAnList = computed(() => getUniqueList('ma_thong_bao_hang_den', anSearchText.value));
const filteredDoList = computed(() => getUniqueList('ma_lenh_giao_hang', doSearchText.value));
const filteredBbgnList = computed(() => getUniqueList('ma_bien_ban_giao_nhan', bbgnSearchText.value));
const filteredTkList = computed(() => getUniqueList('ma_to_khai_hai_quan', tkSearchText.value));

const currentPage = ref(1);
const pageSize = ref(10);
const pageSizes = [10, 20, 50];

const listTrangThai = ['Mới tạo', 'Đang chờ xử lý', 'Đang vận chuyển', 'Đã thông quan', 'Hoàn tất', 'Hủy'];

const statusClass = (status) => {
  if (['Hoàn tất', 'Đang vận chuyển', 'Đã thông quan'].includes(status)) return 'badge-active';
  if (status === 'Hủy') return 'badge-locked';
  return 'badge-warning';
};

// Hàm format ngày giờ hiển thị
const formatDateTime = (dateString) => {
  if (!dateString || dateString.startsWith('1970') || dateString.startsWith('0000')) return "--";
  const d = new Date(dateString);
  return `${d.getHours().toString().padStart(2, '0')}:${d.getMinutes().toString().padStart(2, '0')} ${d.toLocaleDateString('vi-VN')}`;
};

const filteredLoHang = computed(() => {
  return listLoHang.value.filter(lh => {
    const search = searchQuery.value.toLowerCase();
    const matchSearch = !search || 
      (lh.ten_lo_hang && lh.ten_lo_hang.toLowerCase().includes(search)) || 
      (lh.ma_lo_hang && lh.ma_lo_hang.toString().includes(search)) ||
      (lh.nguon_goc && lh.nguon_goc.toLowerCase().includes(search)) ||
      (lh.so_van_don && lh.so_van_don.toLowerCase().includes(search)) ||
      (lh.ma_thong_bao_hang_den && lh.ma_thong_bao_hang_den.toString().includes(search)) ||
      (lh.ma_lenh_giao_hang && lh.ma_lenh_giao_hang.toString().includes(search)) ||
      (lh.ma_bien_ban_giao_nhan && lh.ma_bien_ban_giao_nhan.toString().includes(search)) ||
      (lh.ma_to_khai_hai_quan && lh.ma_to_khai_hai_quan.toString().includes(search));
      
    const itemSearch = searchItemQuery.value.toLowerCase();
    const matchItemSearch = !itemSearch || (lh.ds_ten_hang && lh.ds_ten_hang.toLowerCase().includes(itemSearch));

    const matchTrangThai = filterTrangThai.value === 'ALL' || lh.trang_thai_lo_hang === filterTrangThai.value;
    const matchKhachHang = !filterKhachHang.value || lh.ma_khach_hang === filterKhachHang.value;
    const matchIncoterms = !filterIncoterms.value || lh.dieu_kien_thuong_mai === filterIncoterms.value;
    const matchUser = !filterUser.value || (lh.nguoi_sua_cuoi === filterUser.value || lh.nguoi_sua_doi === filterUser.value);

    let matchHangHoa = true;
    if (filterHangHoa.value) {
      const ids = lh.ds_ma_hang_hoa ? String(lh.ds_ma_hang_hoa).split(',').map(Number) : [];
      matchHangHoa = ids.includes(Number(filterHangHoa.value));
    }

    const matchBl = !filterSoVanDon.value || lh.so_van_don === filterSoVanDon.value;
    const matchAn = !filterAn.value || String(lh.ma_thong_bao_hang_den) === String(filterAn.value);
    const matchDo = !filterDo.value || String(lh.ma_lenh_giao_hang) === String(filterDo.value);
    const matchBbgn = !filterBbgn.value || String(lh.ma_bien_ban_giao_nhan) === String(filterBbgn.value);
    const matchTk = !filterTk.value || String(lh.ma_to_khai_hai_quan) === String(filterTk.value);

    return matchSearch && matchItemSearch && matchTrangThai && matchKhachHang && 
           matchIncoterms && matchUser && matchHangHoa && matchBl && matchAn && matchDo && matchBbgn && matchTk;
  });
});

const selectFilter = (type, val, text) => {
  if (type === 'kh') {
    filterKhachHang.value = val;
    khSearchText.value = text || '';
    showKhDropdown.value = false;
  } else if (type === 'hh') {
    filterHangHoa.value = val;
    hhSearchText.value = text || '';
    showHhDropdown.value = false;
  } else if (type === 'bl') {
    filterSoVanDon.value = val;
    blSearchText.value = text || '';
    showBlDropdown.value = false;
  } else if (type === 'an') {
    filterAn.value = val;
    anSearchText.value = text || '';
    showAnDropdown.value = false;
  } else if (type === 'do') {
    filterDo.value = val;
    doSearchText.value = text || '';
    showDoDropdown.value = false;
  } else if (type === 'bbgn') {
    filterBbgn.value = val;
    bbgnSearchText.value = text || '';
    showBbgnDropdown.value = false;
  } else if (type === 'tk') {
    filterTk.value = val;
    tkSearchText.value = text || '';
    showTkDropdown.value = false;
  }
};

const paginatedLoHang = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value;
  const end = start + pageSize.value;
  return filteredLoHang.value.slice(start, end);
});

const totalPages = computed(() => {
  return Math.ceil(filteredLoHang.value.length / pageSize.value) || 1;
});

const uniqueUsers = computed(() => {
  const users = listLoHang.value.map(lh => lh.nguoi_sua_cuoi || lh.nguoi_sua_doi).filter(Boolean);
  return [...new Set(users)];
});

const resetFilters = () => {
  searchQuery.value = '';
  filterTrangThai.value = 'ALL';
  filterKhachHang.value = null;
  filterIncoterms.value = null;
  filterUser.value = null;
  filterHangHoa.value = null;
  filterSoVanDon.value = null;
  filterAn.value = null;
  filterDo.value = null;
  filterBbgn.value = null;
  filterTk.value = null;
  searchItemQuery.value = '';
  khSearchText.value = '';
  hhSearchText.value = '';
  blSearchText.value = anSearchText.value = doSearchText.value = bbgnSearchText.value = tkSearchText.value = '';
  currentPage.value = 1;
  fetchData();
  fetchReferences();
};

const prevPage = () => { if (currentPage.value > 1) currentPage.value--; };
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++; };

// Computed property for colspan in "No data" row
const visibleColumnCount = computed(() => {
  // Start with 2 for STT and Thao tác (which are always visible)
  let count = 2;
  for (const key in columnVisibility) {
    if (columnVisibility[key]) {
      count++;
    }
  }
  return count;
});

watch([searchQuery, searchItemQuery, filterTrangThai, filterKhachHang, filterIncoterms, filterUser, filterHangHoa, pageSize], () => {
  currentPage.value = 1;
});

const fetchReferences = async () => {
  try {
    // 1. Lấy danh mục khách hàng
    const resRef = await fetch(`${import.meta.env.VITE_API_URL}/lo-hang/references`);
    const dataRef = await resRef.json();
    if (dataRef.success) {
      listKhachHang.value = dataRef.khach_hang;
    }

    // 2. Lấy đầy đủ thông tin Booking (bao gồm cảng, hãng tàu, etd, eta...)
    const resBk = await fetch(`${import.meta.env.VITE_API_URL}/bookings`);
    const dataBk = await resBk.json();
    if (dataBk.success) {
      listBooking.value = dataBk.data;
    }

    // 3. Lấy danh mục hàng hóa (phục vụ bộ lọc)
    const resHH = await fetch(`${import.meta.env.VITE_API_URL}/chi-tiet-lo-hang/references`);
    const dataHH = await resHH.json();
    if (dataHH.success) listHangHoa.value = dataHH.hang_hoa;
  } catch (error) {
    console.error("Lỗi lấy dữ liệu tham chiếu");
  }
};

const fetchData = async () => {
  isLoading.value = true;
  try {
    const res = await fetch(`${import.meta.env.VITE_API_URL}/lo-hang`);
    const data = await res.json();
    if (data.success) listLoHang.value = data.data;
  } catch (error) { console.error("Lỗi lấy dữ liệu Lô hàng!"); }
  finally { isLoading.value = false; }
};

const fetchUnusedBookingCount = async () => {
  try {
    const res = await fetch(`${import.meta.env.VITE_API_URL}/bookings/chua-dung`);
    const data = await res.json();
    if (data.success) unusedBookingCount.value = data.count;
  } catch (e) { console.error("Lỗi tải thông báo:", e); }
};

const handleDelete = async (id) => {
  if (!confirm("Bạn có chắc muốn xóa lô hàng này?")) return;
  const user = JSON.parse(localStorage.getItem('sincere_user'));
  try {
    const res = await fetch(`${import.meta.env.VITE_API_URL}/lo-hang/delete`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ma_lo_hang: id, nguoi_sua_cuoi: user ? (user.id || user.ma_tai_khoan) : null })
    });
    const data = await res.json();
    if (data.success) { alert("Đã xóa lô hàng!"); fetchData(); }
    else { alert("Lỗi: " + data.message); }
  } catch (e) { alert("Lỗi kết nối!"); }
};

const handleVanDonClick = (lh) => {
  if (hasRole(1)) {
    if (lh.ds_ma_van_don) {
      router.push('/van-tai/Quan-ly-van-don/edit/' + lh.ds_ma_van_don.split(',')[0].trim());
    } else {
      router.push({
        path: '/van-tai/Quan-ly-van-don/add',
        query: { auto_create_lo_hang: lh.ma_lo_hang }
      });
    }
  }
};

const handleAnClick = (lh) => {
  if (hasRole(1)) {
    if (!lh.ma_thong_bao_hang_den) { // Nếu ô trống -> Truyền tín hiệu mở Form
      router.push({
        path: '/van-tai/thong-bao-hang-den',
        query: { auto_create_lo_hang: lh.ma_lo_hang }
      });
    } else { // Đã có rồi thì sang xem danh sách bình thường
      router.push('/van-tai/thong-bao-hang-den');
    }
  }
};

const handleDoClick = (lh) => {
  if (hasRole(1)) {
    // CHỈ kích hoạt liên kết tạo mới nếu ô này đang trống (chưa có mã D/O)
    if (!lh.ma_lenh_giao_hang) {
      router.push({
        path: '/van-tai/lenh-giao-hang',
        query: { auto_create_lo_hang: lh.ma_lo_hang } // Đã sửa thành lh.ma_lo_hang
      });
    } else {
      // Nếu đã có mã D/O rồi thì click vào chỉ chuyển sang trang danh sách lệnh giao hàng bình thường
      router.push('/van-tai/lenh-giao-hang');
    }
  }
};

const handleBbgnClick = (lh) => {
  if (hasRole(3)) {
    if (!lh.ma_bien_ban_giao_nhan) {
      router.push({
        path: '/van-tai/bien-ban-giao-nhan',
        query: { auto_create_lo_hang: lh.ma_lo_hang }
      });
    } else {
      router.push('/van-tai/bien-ban-giao-nhan');
    }
  }
};

const handleTkClick = (lh) => {
  if (hasRole(4)) {
    if (lh.ma_to_khai_hai_quan) {
      router.push('/van-tai/to-khai-hai-quan/edit/' + lh.ma_to_khai_hai_quan.split(',')[0].trim());
    } else {
      router.push({
        path: '/van-tai/to-khai-hai-quan/add',
        query: { auto_create_lo_hang: lh.ma_lo_hang }
      });
    }
  }
};

onMounted(() => { 
  fetchData(); 
  fetchReferences();
  fetchUnusedBookingCount();

  // Thiết lập làm mới mỗi 15 giây
  refreshInterval = setInterval(fetchUnusedBookingCount, 15000);

  window.addEventListener('click', (e) => {
    if (!e.target.closest('.combobox-wrapper')) {
      showKhDropdown.value = showHhDropdown.value = showBlDropdown.value = showAnDropdown.value = showDoDropdown.value = showBbgnDropdown.value = showTkDropdown.value = showColumnDropdown.value = false;
    }
  });
});

onUnmounted(() => {
  if (refreshInterval) clearInterval(refreshInterval);
});
</script>

<style scoped src="../../assets/quanlytaikhoan.css"></style>
<style scoped>
.badge-warning { background-color: #f39c12; color: white; }
.btn-pagination {
  padding: 5px 12px; background: white; border: 1px solid #ddd; border-radius: 4px; cursor: pointer; transition: 0.2s;
}
.btn-pagination:hover:not(:disabled) { background: #f0f0f0; border-color: #3498db; color: #3498db; }
.btn-pagination:disabled { cursor: not-allowed; opacity: 0.5; }

.row-selected { background-color: #f0f7ff !important; }

.action-btn.text-success { color: #27ae60; }
.action-btn.text-warning { color: #f39c12; }
/* Zebra Striping cho bảng */
.table-card table tbody tr:nth-child(odd) {
  background-color: #ffffff;
}
.table-card table tbody tr:nth-child(even) {
  background-color: #f2f2f2;
}
.table-card table tbody tr:hover {
  background-color: #eef7ff;
}

/* Cố định cột STT và Thao tác tương tự QuanLyVanDon.vue */
.scrollable-table {
  overflow-x: auto;
  max-width: 100%;
}

.scrollable-table table {
  width: max-content;
  min-width: 100%;
  border-collapse: separate;
  border-spacing: 0;
}

.scrollable-table th:first-child,
.scrollable-table td:first-child {
  position: sticky;
  left: 0;
  z-index: 10;
  border-right: 2px solid #ddd;
}

.scrollable-table th:last-child,
.scrollable-table td:last-child {
  position: sticky;
  right: 0;
  z-index: 10;
  border-left: 2px solid #ddd;
}

/* Đảm bảo màu nền đặc để không lộ chữ bên dưới khi cuộn */
.scrollable-table th:first-child,
.scrollable-table th:last-child {
  background-color: #f8f9fa !important; /* Màu nền của header */
}

.table-card table tbody tr:nth-child(odd) td:first-child,
.table-card table tbody tr:nth-child(odd) td:last-child {
  background-color: #ffffff !important; /* Màu nền dòng lẻ */
}

.table-card table tbody tr:nth-child(even) td:first-child,
.table-card table tbody tr:nth-child(even) td:last-child {
  background-color: #f2f2f2 !important; /* Màu nền dòng chẵn */
}

/* Giữ màu hover cho cột cố định */
.table-card table tbody tr:hover td:first-child,
.table-card table tbody tr:hover td:last-child {
  background-color: #eef7ff !important;
}

/* Sửa lỗi tooltip bị che bởi cột sticky */
.scrollable-table td:hover {
  z-index: 1001; /* Phải cao hơn z-index: 10 của các ô sticky */
}

/* Style cho Combobox tìm kiếm */
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

/* Style cho Banner thông báo */
.booking-alert-banner {
  background: #fff4e5; border: 1px solid #ffcc80; border-radius: 8px;
  padding: 12px 20px; margin-bottom: 20px; display: flex; align-items: center;
  gap: 15px; animation: fadeInDown 0.5s ease;
}
.booking-alert-banner .icon { font-size: 20px; }
.booking-alert-banner .text { color: #856404; font-size: 14px; flex: 1; }
.booking-alert-banner .btn-go {
  background: #f39c12; color: white; border: none; padding: 6px 12px;
  border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: bold;
  transition: 0.2s;
}

.column-visibility-controls {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
}
.booking-alert-banner .btn-go:hover { background: #e67e22; }

@keyframes fadeInDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

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