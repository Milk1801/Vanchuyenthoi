<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">Quản lý Lưu Bãi</h3>
    
    <div class="toolbar" style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 15px;">
      <div style="display: flex; gap: 10px; width: 100%; flex-wrap: wrap;">
        <div class="search-box" style="flex: 1; min-width: 200px;">
          <input type="text" v-model="searchQuery" placeholder="Tìm theo tên lô hàng hoặc mã lô hàng..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #ccc;">
        </div>
      </div>

      <div class="combobox-wrapper" style="width: 180px;">
        <Folder size="16" class="combobox-icon" />
        <input type="text" v-model="trangThaiSearchText" placeholder="Trạng thái..." @focus="showTrangThaiDropdown = true" class="combobox-input-sm">
        <ul v-if="showTrangThaiDropdown" class="combobox-list">
          <li @click="selectSearchTrangThai('ALL')">Tất cả trạng thái</li>
          <li v-for="st in filteredTrangThaiList" :key="st" @click="selectSearchTrangThai(st)">{{ st }}</li>
        </ul>
      </div>

      <div class="combobox-wrapper" style="width: 180px;">
        <Wallet size="16" class="combobox-icon" />
        <input type="text" v-model="cuocVoSearchText" placeholder="Cược vỏ..." @focus="showCuocVoDropdown = true" class="combobox-input-sm">
        <ul v-if="showCuocVoDropdown" class="combobox-list">
          <li @click="selectSearchCuocVo('ALL')">Tất cả cược vỏ</li>
          <li v-for="cv in filteredCuocVoList" :key="cv" @click="selectSearchCuocVo(cv)">{{ cv }}</li>
        </ul>
      </div>

      <div class="combobox-wrapper" style="width: 180px;">
        <User size="16" class="combobox-icon" />
        <input type="text" v-model="userSearchText" placeholder="Người sửa..." @focus="showUserDropdown = true" class="combobox-input-sm">
        <ul v-if="showUserDropdown" class="combobox-list">
          <li @click="selectSearchUser('ALL')">Tất cả người sửa</li>
          <li v-for="user in filteredUsers" :key="user" @click="selectSearchUser(user)">{{ user }}</li>
        </ul>
      </div>

      <div class="combobox-wrapper" style="width: auto;">
        <button @click="showColumnDropdown = !showColumnDropdown" style="padding: 8px 15px; border: 1px solid #ccc; border-radius: 6px; background: #fff; cursor: pointer; transition: 0.2s; height: 36px; font-size: 13px;" title="Tùy chỉnh hiển thị cột"><Settings size="12" /> Cột</button>
        <ul v-if="showColumnDropdown" class="combobox-list" style="width: 220px; right: 0; left: auto; padding: 10px 0; display: flex; flex-direction: column;">
          <li v-for="(col, key) in columnVisibility" :key="key" style="display: flex; align-items: center; gap: 10px; cursor: default; border-bottom: none; padding: 5px 15px;" @click.stop>
            <input type="checkbox" v-model="col.visible" :id="'col-pop-' + key" style="cursor: pointer; width: 16px; height: 16px;">
            <label :for="'col-pop-' + key" style="cursor: pointer; flex: 1; font-size: 13px; color: #2c3e50;">{{ col.label }}</label>
          </li>
        </ul>
      </div>

      <button @click="clearFilters" style="padding: 8px 15px; border: 1px solid #ccc; border-radius: 6px; background: #fff; cursor: pointer; transition: 0.2s;" title="Xóa lọc"><Eraser size="12" /> Xóa lọc</button>
      <button v-if="hasRole(4)" class="btn btn-success" @click="router.push('/van-tai/luu-bai/add')" style="border-radius: 6px;">+ THÊM LƯU BÃI</button>
    </div>

    <!-- Khung cảnh báo lưu bãi -->
    <div class="warning-cards" style="display: flex; gap: 20px; margin-bottom: 20px;">
      <div class="stat-card" style="flex: 1; padding: 15px; background: #fff; border-left: 5px solid #e74c3c; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); display: flex; flex-direction: column; gap: 5px;">
        <span style="font-size: 13px; font-weight: 500; color: #7f8c8d;">Lô hàng quá hạn lưu bãi</span>
        <span style="font-size: 24px; font-weight: bold; color: #e74c3c;">{{ warnings.overdue }}</span>
      </div>
      <div class="stat-card" style="flex: 1; padding: 15px; background: #fff; border-left: 5px solid #f39c12; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); display: flex; flex-direction: column; gap: 5px;">
        <span style="font-size: 13px; font-weight: 500; color: #7f8c8d;">Lô hàng sắp đến hạn (≤ 2 ngày)</span>
        <span style="font-size: 24px; font-weight: bold; color: #f39c12;">{{ warnings.nearing }}</span>
      </div>
    </div>

    <div v-if="isLoading" style="text-align: center; padding: 20px; color: #3498db;">Đang tải dữ liệu...</div>

    <div v-else>
      <!-- DANH SÁCH -->
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
        

        <div class="table-card" style="overflow-x: auto; background: white; border-radius: 8px; border: 1px solid #ddd; width: 100%;">
          <table style="width: max-content; border-collapse: collapse;">
            <thead>
              <tr>
                <th class="sticky-col-left" style="width: 50px; text-align: center;">STT</th>
                <th v-if="columnVisibility.ten_lo_hang.visible" style="width: 250px;">Lô hàng</th>
                <th v-if="columnVisibility.ngay_bat_dau_luu_bai.visible" style="width: 200px;">Ngày bắt đầu</th>
                <th v-if="columnVisibility.ngay_luu_bai_mien_phi.visible" style="width: 150px; text-align: center;">Ngày miễn phí</th>
                <th v-if="columnVisibility.cuoc_vo.visible" style="width: 120px;">Cược vỏ</th>
                <th v-if="columnVisibility.trang_thai_luu_bai.visible" style="width: 150px;">Trạng thái</th>
                <th v-if="columnVisibility.ten_nguoi_sua.visible" style="width: 150px;">Người sửa cuối</th>
                <th class="sticky-col-right" style="text-align: center; width: 120px;">Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in paginatedData" :key="item.ma_luu_bai"
                  :class="{ 
                    'row-even': (index % 2 !== 0), 
                    'row-odd': (index % 2 === 0),
                    'row-overdue': getWarningStatus(item) === 'overdue',
                    'row-nearing': getWarningStatus(item) === 'nearing'
                  }">
                <td class="sticky-col-left" style="text-align: center; color: #7f8c8d;">{{ (currentPage - 1) * pageSize + index + 1 }}</td>
                <td v-if="columnVisibility.ten_lo_hang.visible"
                    @mouseenter="handleMouseEnter($event, item)" 
                    @mousemove="handleMouseMove"
                    @mouseleave="handleMouseLeave"
                    @click="goToLoHangEdit(item.ma_lo_hang)"
                    style="cursor: pointer; color: #2980b9; font-weight: 500; text-decoration: underline;">
                  {{ item.ten_lo_hang }}
                </td>
                <td v-if="columnVisibility.ngay_bat_dau_luu_bai.visible">{{ item.ngay_bat_dau_luu_bai ? new Date(item.ngay_bat_dau_luu_bai).toLocaleString('vi-VN') : '---' }}</td>
                <td v-if="columnVisibility.ngay_luu_bai_mien_phi.visible" style="text-align: center;">{{ item.ngay_luu_bai_mien_phi }}</td>
                <td v-if="columnVisibility.cuoc_vo.visible">{{ item.cuoc_vo }}</td>
                <td v-if="columnVisibility.trang_thai_luu_bai.visible">
                  <span class="badge" :style="getStatusStyle(item.trang_thai_luu_bai)">
                    {{ item.trang_thai_luu_bai }}
                  </span>
                </td>
                <td v-if="columnVisibility.ten_nguoi_sua.visible">{{ item.ten_nguoi_sua || 'N/A' }}</td>
                <td class="sticky-col-right" style="text-align: center;">
                  <div style="display: flex; gap: 2px; justify-content: center;">
                    <button v-if="hasRole(4)" class="action-btn-no-mg text-primary" @click="router.push('/van-tai/luu-bai/edit/' + item.ma_luu_bai)" title="Sửa"><Pen size="16" /></button>
                    <button v-if="hasRole(4)" class="action-btn-no-mg text-danger" @click="handleDelete(item.ma_luu_bai)" title="Xóa"><Trash size="16" /></button>
                  </div>
                </td>
              </tr>
              <tr v-if="paginatedData.length === 0">
                <td :colspan="visibleColumnsCount" style="text-align: center; padding: 20px; color: #7f8c8d;">Không tìm thấy dữ liệu nào!</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Tooltip hiển thị thông tin lô hàng khi hover -->
    <div v-if="tooltipShipment" class="shipment-tooltip" :style="{ top: tooltipPos.y + 'px', left: tooltipPos.x + 'px' }">
      <div class="tooltip-header"><Package size="12" /> Thông tin lô hàng</div>
      <div class="tooltip-content">
        <div><strong>Mã:</strong> <span>#{{ tooltipShipment.ma_lo_hang }}</span></div>
        <div><strong>Tên:</strong> <span>{{ tooltipShipment.ten_lo_hang }}</span></div>
        <div><strong>Khách hàng:</strong> <span>{{ tooltipShipment.ten_khach_hang || 'N/A' }}</span></div>
        <div><strong>Incoterms:</strong> <span>{{ tooltipShipment.dieu_kien_thuong_mai || 'N/A' }}</span></div>
        <div><strong>Trạng thái:</strong> <span class="badge badge-active" style="font-size: 11px; background-color: #3498db; color: white; padding: 2px 6px; border-radius: 10px;">{{ tooltipShipment.trang_thai_lo_hang }}</span></div>
        <div><strong>Booking:</strong> <span>{{ tooltipShipment.so_booking || 'N/A' }}</span></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import { hasRole } from '../../assets/chucnang';
import { Package, Pen, Trash, Eraser, Settings, User, Notebook, Folder, Wallet} from 'lucide-vue-next';

const router = useRouter();
const listLuuBai = ref([]);
const listAllLoHang = ref([]);
const isLoading = ref(true);
const searchQuery = ref('');

// Combobox tìm kiếm trạng thái, cược vỏ, người sửa
const trangThaiSearchText = ref('');
const cuocVoSearchText = ref('');
const userSearchText = ref('');

// Combobox trang thái, cược vỏ, người sửa hiển thị dropdown
const showTrangThaiDropdown = ref(false);
const showCuocVoDropdown = ref(false);
const showUserDropdown = ref(false);
const showColumnDropdown = ref(false);

// Các bộ lọc trạng thái, cược vỏ, người sửa
const filterTrangThai = ref('ALL');
const filterCuocVo = ref('ALL');
const filterUser = ref('ALL');

const currentPage = ref(1);
const pageSize = ref(10);
const pageSizes = [10, 20, 50];

// Định nghĩa các cột và trạng thái hiển thị của chúng
const columnVisibility = ref({
  ten_lo_hang: { label: 'Lô hàng', visible: true },
  ngay_bat_dau_luu_bai: { label: 'Ngày bắt đầu', visible: true },
  ngay_luu_bai_mien_phi: { label: 'Ngày miễn phí', visible: true },
  cuoc_vo: { label: 'Cược vỏ', visible: true },
  trang_thai_luu_bai: { label: 'Trạng thái', visible: true },
  ten_nguoi_sua: { label: 'Người sửa cuối', visible: true },
});

// Hàm kiểm tra trạng thái cảnh báo của từng dòng
const getWarningStatus = (item) => {
  if (item.trang_thai_luu_bai !== 'Đang lưu bãi') return 'none';
  
  const loHang = listAllLoHang.value.find(lh => lh.ma_lo_hang === item.ma_lo_hang);
  // Không cảnh báo nếu lô hàng đã Hoàn tất hoặc bị Hủy
  if (loHang && (loHang.trang_thai_lo_hang === 'Hủy' || loHang.trang_thai_lo_hang === 'Hoàn tất')) return 'none';

  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const start = item.ngay_bat_dau_luu_bai ? new Date(item.ngay_bat_dau_luu_bai) : null;
  if (!start) return 'none';
  
  start.setHours(0, 0, 0, 0);
  const diffDays = Math.floor((today - start) / (1000 * 60 * 60 * 24));
  const remainingDays = (item.ngay_luu_bai_mien_phi || 0) - diffDays;

  if (remainingDays < 0) return 'overdue';
  if (remainingDays <= 2) return 'nearing';
  return 'none';
};

const warnings = computed(() => {
  let overdueCount = 0;
  let nearingCount = 0;

  listLuuBai.value.forEach(item => {
    const status = getWarningStatus(item);
    if (status === 'overdue') {
      overdueCount++;
    } else if (status === 'nearing') {
      nearingCount++;
    }
  });

  return { overdue: overdueCount, nearing: nearingCount };
});

const visibleColumnsCount = computed(() => {
  // Mặc định có STT và Thao tác (2) + các cột được chọn
  return Object.values(columnVisibility.value).filter(col => col.visible).length + 2;
});

const tooltipShipment = ref(null);
const tooltipPos = ref({ x: 0, y: 0 });

const uniqueUsers = computed(() => {
  const users = listLuuBai.value.map(item => item.ten_nguoi_sua).filter(Boolean);
  return [...new Set(users)];
});

const filteredTrangThaiList = computed(() => ['Đang lưu bãi', 'Đã rút hàng', 'Đã trả vỏ'].filter(t => t.toLowerCase().includes(trangThaiSearchText.value.toLowerCase())));
const filteredCuocVoList = computed(() => ['Có', 'Không'].filter(c => c.toLowerCase().includes(cuocVoSearchText.value.toLowerCase())));
const filteredUsers = computed(() => uniqueUsers.value.filter(u => u.toLowerCase().includes(userSearchText.value.toLowerCase())));

const filteredData = computed(() => {
  return listLuuBai.value.filter(item => {
    const matchSearch = !searchQuery.value || 
                        item.ten_lo_hang.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                        String(item.ma_lo_hang).includes(searchQuery.value);
    const matchStatus = filterTrangThai.value === 'ALL' || item.trang_thai_luu_bai === filterTrangThai.value;
    const matchCuocVo = filterCuocVo.value === 'ALL' || item.cuoc_vo === filterCuocVo.value;
    const matchUser = filterUser.value === 'ALL' || item.ten_nguoi_sua === filterUser.value;

    return matchSearch && matchStatus && matchCuocVo && matchUser;
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

const goToLoHangEdit = (ma_lo_hang) => {
  router.push('/lo-hang/thong-tin-lo-hang/edit/' + ma_lo_hang);
};

const updateTooltipPosition = (event) => {
  let x = event.clientX + 15;
  let y = event.clientY + 15;
  const tooltipHeight = 260; // Chiều cao ước tính của khung tooltip
  // Nếu vị trí hiển thị dự kiến vượt quá cạnh dưới màn hình
  if (event.clientY + tooltipHeight > window.innerHeight) {
    y = event.clientY - tooltipHeight - 10; // Hiển thị phía trên con trỏ chuột
  }
  tooltipPos.value = { x, y };
};

const handleMouseEnter = (event, item) => {
  const found = listAllLoHang.value.find(lh => lh.ma_lo_hang === item.ma_lo_hang);
  if (found) {
    tooltipShipment.value = found;
    updateTooltipPosition(event);
  }
};

const handleMouseMove = (event) => {
  updateTooltipPosition(event);
};

const handleMouseLeave = () => {
  tooltipShipment.value = null;
};

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
};

watch([searchQuery, filterTrangThai, filterCuocVo, filterUser, pageSize,
       trangThaiSearchText, cuocVoSearchText, userSearchText], () => {
  currentPage.value = 1;
});

const fetchData = async () => {
  isLoading.value = true;
  try {
    // Tải đồng thời danh sách lưu bãi và danh sách lô hàng để Tooltip có dữ liệu
    const [resLB, resLH] = await Promise.all([
      fetch(`${import.meta.env.VITE_API_URL}/luu-bai`),
      fetch(`${import.meta.env.VITE_API_URL}/lo-hang`)
    ]);
    const dataLB = await resLB.json();
    const dataLH = await resLH.json();

    if (dataLB.success) listLuuBai.value = dataLB.data;
    if (dataLH.success) listAllLoHang.value = dataLH.data;
  } catch (e) { console.error(e); }
  finally { 
    isLoading.value = false;
  }
};

const handleClickOutside = (e) => {
  if (!e.target.closest('.combobox-wrapper')) {
    showTrangThaiDropdown.value = showCuocVoDropdown.value = showUserDropdown.value = showColumnDropdown.value = false;
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

onMounted(() => {
  fetchData();
  window.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  window.removeEventListener('click', handleClickOutside);
});

const getStatusStyle = (status) => {
  if (status === 'Đã trả vỏ') return { backgroundColor: '#27ae60', color: 'white', whiteSpace: 'nowrap' };
  if (status === 'Đang lưu bãi') return { backgroundColor: '#f1c40f', color: 'white', whiteSpace: 'nowrap' };
  return { backgroundColor: '#f39c12', color: 'white', whiteSpace: 'nowrap' };
};
</script>

<style scoped src="../../assets/quanlytaikhoan.css"></style>
<style scoped>
.badge { padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: bold; }

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

/* Màu sắc cho hàng quá hạn/sắp quá hạn */
/* Quá hạn - Đỏ nhạt */
.row-overdue.row-odd td, .row-overdue.row-odd .sticky-col-left, .row-overdue.row-odd .sticky-col-right { background-color: #ffdcdc !important; }
.row-overdue.row-even td, .row-overdue.row-even .sticky-col-left, .row-overdue.row-even .sticky-col-right { background-color: #ffc1c1 !important; }

/* Sắp đến hạn - Vàng/Cam nhạt */
.row-nearing.row-odd td, .row-nearing.row-odd .sticky-col-left, .row-nearing.row-odd .sticky-col-right { background-color: #fff3cd !important; }
.row-nearing.row-even td, .row-nearing.row-even .sticky-col-left, .row-nearing.row-even .sticky-col-right { background-color: #ffe69c !important; }

/* Hiệu ứng hover để hàng nổi bật hơn khi di chuột qua */
.row-overdue:hover td, .row-overdue:hover .sticky-col-left, .row-overdue:hover .sticky-col-right { background-color: #fbcbcb !important; }
.row-nearing:hover td, .row-nearing:hover .sticky-col-left, .row-nearing:hover .sticky-col-right { background-color: #fbeaba !important; }

/* Tăng độ đậm cho chữ ở các hàng cảnh báo để dễ đọc trên nền màu */
.row-overdue td, .row-nearing td { color: #2c3e50; }

.table-card table th, .table-card table td { 
  white-space: nowrap; 
  padding: 12px 15px; 
}

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

/* Đảm bảo bảng hiển thị đẹp */
.table-card table th, .table-card table td { 
  white-space: nowrap; 
  padding: 12px 15px; 
}

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
  border-bottom: 1px solid #eee;
  padding-bottom: 5px;
  margin-bottom: 8px;
  font-weight: bold;
  color: #2980b9;
}
.tooltip-content div {
  margin-bottom: 4px;
  display: flex;
  justify-content: space-between;
  gap: 10px;
}
.tooltip-content strong {
  color: #7f8c8d;
  font-weight: normal;
  white-space: nowrap;
}
.tooltip-content span {
  text-align: right;
  font-weight: 600;
}
</style>