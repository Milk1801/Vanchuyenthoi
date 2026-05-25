<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">Quản Lý Chi Phí phát sinh</h3>

    <div class="dashboard-cards">
      <div class="card card-thu">
        <div class="card-title">💰 Tổng Thu Dự Kiến</div>
        <div class="card-value">{{ formatCurrency(dashboardStats.tong_thu) }}</div>
      </div>
      <div class="card card-chi">
        <div class="card-title">💸 Tổng Chi Dự Kiến</div>
        <div class="card-value">{{ formatCurrency(dashboardStats.tong_chi) }}</div>
      </div>
      <div class="card card-ton">
        <div class="card-title">⚠️ Đang Tồn Đọng</div>
        <div class="card-value">{{ formatCurrency(dashboardStats.ton_dong) }}</div>
      </div>
    </div>

    <div style="display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 2px solid #ccc;">
      <button @click="currentTab = 'THU'" :style="tabStyle(currentTab === 'THU')">
        📥 KHOẢN THU (Khách hàng)
      </button>
      <button @click="currentTab = 'CHI'" :style="tabStyle(currentTab === 'CHI')">
        📤 KHOẢN CHI (Hãng tàu, Cảng...)
      </button>
    </div>

    <div class="toolbar" style="display: flex; gap: 15px; flex-wrap: nowrap; overflow-x: auto; margin-bottom: 20px; align-items: center; background: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #e9ecef;">
      <div class="search-box" style="flex: 1; min-width: 200px;">
        <input type="text" v-model="searchQuery" placeholder="🔍 Tìm theo Tên chi phí, Lô hàng..." style="width: 100%; padding: 0 12px; border-radius: 6px; border: 1px solid #ccc; height: 40px; box-sizing: border-box;">
      </div>
      
      <div class="filter-box" style="min-width: 160px; flex-shrink: 0;">
        <select v-model="filterStatus" style="width: 100%; padding: 0 12px; border-radius: 6px; border: 1px solid #ccc; background: white; height: 40px; box-sizing: border-box;">
          <option value="">Tất cả trạng thái</option>
          <option value="Chưa thanh toán">🔴 Chưa thanh toán</option>
          <option value="Thanh toán một phần">🟡 Thanh toán một phần</option>
          <option value="Đã thanh toán">🟢 Đã thanh toán</option>
        </select>
      </div>

      <div style="display: flex; align-items: center; gap: 5px; flex-shrink: 0; white-space: nowrap;">
        <label style="font-size: 13px; font-weight: bold; color: #555;">Từ:</label>
        <input type="date" v-model="fromDate" style="padding: 0 12px; border-radius: 6px; border: 1px solid #ccc; font-size: 14px; height: 40px; box-sizing: border-box;">
      </div>
      <div style="display: flex; align-items: center; gap: 5px; flex-shrink: 0; white-space: nowrap;">
        <label style="font-size: 13px; font-weight: bold; color: #555;">Đến:</label>
        <input type="date" v-model="toDate" style="padding: 0 12px; border-radius: 6px; border: 1px solid #ccc; font-size: 14px; height: 40px; box-sizing: border-box;">
      </div>

      <div style="display: flex; flex-shrink: 0;">
        <button v-if="hasRole([2, 5])" class="btn-success" @click="openModal()" style="border-radius: 6px; padding: 0 20px; background: #2ecc71; color: white; border: none; cursor: pointer; font-weight: bold; height: 40px; white-space: nowrap;">
          ➕ THÊM CHI PHÍ
        </button>
      </div>
    </div>

    <div class="table-container" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow-x: auto;">
      <table class="zebra-table" style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
          <tr>
            <th style="padding: 12px 15px;">Mã CP</th>
            <th style="padding: 12px 15px;">Tên Chi Phí</th>
            <th style="padding: 12px 15px;">Thuộc Lô Hàng</th>
            <th style="padding: 12px 15px; text-align: right;">Tổng Tiền</th>
            <th style="padding: 12px 15px; text-align: center;">Trạng Thái</th>
            <th style="padding: 12px 15px;">Ngày TT</th>
            <th style="padding: 12px 15px;">Người cập nhật</th>
            <th style="padding: 12px 15px; text-align: center;">Hành động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="isLoading"><td colspan="8" style="text-align: center; padding: 20px;">Đang tải dữ liệu...</td></tr>
          <tr v-else-if="filteredList.length === 0"><td colspan="8" style="text-align: center; padding: 20px;">Không có dữ liệu!</td></tr>
          
          <tr v-for="item in filteredList" :key="item.ma_chi_phi" class="table-row">
            <td style="padding: 12px 15px; font-weight: bold; color: #7f8c8d;">CP-{{ item.ma_chi_phi }}</td>
            <td style="padding: 12px 15px; font-weight: bold;">{{ item.ten_chi_phi }}</td>
            
            <td style="padding: 12px 15px;">
              <span v-if="item.ten_lo_hang" class="hover-trigger" @mousemove="showTooltip($event, item)" @mouseleave="hideTooltip">
                {{ item.ten_lo_hang }}
              </span>
              <span v-else>N/A</span>
            </td>
            
            <td style="padding: 12px 15px; text-align: right; font-weight: bold; color: #2c3e50;">
              {{ formatCurrency(item.tong_tien) }}
            </td>
            
            <td style="padding: 12px 15px; text-align: center;">
              <span :class="'badge ' + getStatusClass(item.trang_thai_thanh_toan)">
                {{ item.trang_thai_thanh_toan || 'Chưa xác định' }}
              </span>
            </td>
            
            <td style="padding: 12px 15px; font-weight: bold;">
                {{ item.ngay_thanh_toan ? formatDate(item.ngay_thanh_toan) : '---' }}
            </td>

            <td style="padding: 12px 15px; color: #7f8c8d; font-size: 13px;">
              👤 {{ item.nguoi_cap_nhat || '---' }}
            </td>
            
            <td style="padding: 12px 15px; text-align: center; white-space: nowrap;">
              <template v-if="hasRole([2, 5])">
                <button @click="openModal(item)" style="margin-right: 10px; cursor: pointer; border: none; background: none; font-size: 16px;" title="Sửa">✏️</button>
                <button @click="handleDelete(item.ma_chi_phi)" style="cursor: pointer; border: none; background: none; font-size: 16px;" title="Xóa">🗑️</button>
              </template>
              <span v-else style="color: #95a5a6; font-size: 13px; font-style: italic; background: #f8f9fa; padding: 5px 10px; border-radius: 4px; border: 1px solid #eee;">
                Chỉ xem
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="hoverPanel.show" class="hover-tooltip" :style="{ top: hoverPanel.y + 'px', left: hoverPanel.x + 'px' }">
      <div class="tooltip-header">
        <h4>📦 Thông tin Lô hàng</h4>
      </div>
      <div class="tooltip-body">
        <table class="detail-table">
          <tbody>
            <tr><td>Tên Lô hàng:</td><td><strong>{{ hoverPanel.data.ten_lo_hang }}</strong></td></tr>
            <tr><td>Số Booking:</td><td><strong>{{ hoverPanel.data.so_booking || '---' }}</strong></td></tr>
            <tr><td>Khách hàng:</td><td><strong>{{ hoverPanel.data.ten_khach_hang || '---' }}</strong></td></tr>
            <tr><td>Điều kiện TM:</td><td><strong style="color: #8e44ad;">{{ hoverPanel.data.dieu_kien_thuong_mai || '---' }}</strong></td></tr>
            <tr><td>Trạng thái lô:</td><td>
              <strong :style="{ color: hoverPanel.data.trang_thai_lo_hang === 'Hoàn tất' ? '#27ae60' : '#f39c12' }">
                {{ hoverPanel.data.trang_thai_lo_hang || '---' }}
              </strong>
            </td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="isModalOpen" class="modal-overlay">
      <div class="modal-content" style="max-width: 500px; padding: 20px; background: white; border-radius: 8px; width: 100%;">
        <h3 style="margin-top: 0;">{{ formData.ma_chi_phi ? 'Cập Nhật' : 'Ghi Nhận' }} Chi Phí Phát Sinh</h3>
        
        <form @submit.prevent="saveData">
          <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Loại giao dịch *</label>
            <div style="display: flex; gap: 20px;">
              <label><input type="radio" v-model="formData.loai_giao_dich" value="THU" required> Khoản Thu</label>
              <label><input type="radio" v-model="formData.loai_giao_dich" value="CHI" required> Khoản Chi</label>
            </div>
          </div>

          <div style="margin-bottom: 15px; position: relative;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Thuộc Lô hàng *</label>
            <div v-if="isDropdownOpen" @click="isDropdownOpen = false" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9;"></div>
            <div style="position: relative; z-index: 10;">
              <input type="text" v-model="searchLoHangInput" @focus="isDropdownOpen = true" @input="isDropdownOpen = true" placeholder="🔍 Gõ số booking hoặc tên lô hàng..." required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
              
              <div v-if="isDropdownOpen" style="position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ccc; border-radius: 4px; max-height: 200px; overflow-y: auto; box-shadow: 0 4px 8px rgba(0,0,0,0.1); margin-top: 2px;">
                <div v-if="filteredLoHangOptions.length === 0" style="padding: 10px; text-align: center; color: #999;">Không tìm thấy kết quả</div>
                <div v-for="lo in filteredLoHangOptions" :key="lo.ma_lo_hang" @click="selectLoHang(lo)" style="padding: 10px; border-bottom: 1px solid #eee; cursor: pointer;">
                  <strong style="color: #2980b9;">[{{ lo.so_booking }}]</strong> - {{ lo.ten_lo_hang }}
                </div>
              </div>
            </div>
          </div>

          <div style="display: flex; gap: 15px; margin-bottom: 25px;">
            <div style="flex: 1;">
              <label style="display: block; margin-bottom: 5px; font-weight: bold;">Tên chi phí *</label>
              <input type="text" v-model="formData.ten_chi_phi" required placeholder="VD: Phí THC, Lưu bãi..." style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            </div>
            <div style="flex: 1;">
              <label style="display: block; margin-bottom: 5px; font-weight: bold;">Tổng tiền (VNĐ) *</label>
              <input type="number" v-model="formData.tong_tien" required min="0" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            </div>
          </div>

          <div style="text-align: right;">
            <button type="button" @click="isModalOpen = false" style="padding: 8px 15px; margin-right: 10px; cursor: pointer; border: 1px solid #ccc; background: #fff; border-radius: 4px;">Hủy</button>
            <button type="submit" :disabled="isSaving" style="padding: 8px 15px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer;">
              {{ isSaving ? 'Đang lưu...' : 'Lưu lại' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';

// --- LOGIC PHÂN QUYỀN (Mã quyền: 2 = Kế toán, 5 = Admin) ---
const currentUser = JSON.parse(localStorage.getItem('sincere_user') || '{}');

const hasRole = (roleIdOrArray) => {
  if (!currentUser.ds_quyen && !currentUser.ds_ma_quyen) return false;
  
  let roles = [];
  if (currentUser.ds_quyen) {
      roles = currentUser.ds_quyen.map(q => Number(q.ma_quyen));
  } else if (currentUser.ds_ma_quyen) {
      roles = currentUser.ds_ma_quyen.split(',').map(id => Number(id.trim()));
  }

  if (roles.includes(5)) return true; // Admin có toàn quyền
  
  const requiredRoles = Array.isArray(roleIdOrArray) ? roleIdOrArray : [roleIdOrArray];
  return requiredRoles.some(r => roles.includes(r));
};

const currentTab = ref('THU'); 
const listChiPhi = ref([]);
const listLoHang = ref([]);
const isLoading = ref(true);
const isSaving = ref(false);
const isModalOpen = ref(false);

const searchQuery = ref(''); 
const filterStatus = ref('');
const fromDate = ref(''); 
const toDate = ref('');   

const dashboardStats = ref({ tong_thu: 0, tong_chi: 0, ton_dong: 0 });

// --- BIẾN CHO HOVER TOOLTIP ---
const hoverPanel = ref({
  show: false,
  data: {},
  x: 0,
  y: 0
});

const searchLoHangInput = ref('');
const isDropdownOpen = ref(false);

const formData = ref({
  ma_chi_phi: null,
  ma_lo_hang: null,
  ten_chi_phi: '',
  tong_tien: 0,
  trang_thai_thanh_toan: 'Chưa thanh toán', 
  ngay_thanh_toan: null,
  loai_giao_dich: 'THU'
});

// Format Tiền tệ & Ngày
const formatCurrency = (val) => {
  if (!val) return '0 ₫';
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val);
};

const formatDate = (dateStr) => {
  if (!dateStr) return '---';
  return new Date(dateStr).toLocaleDateString('vi-VN');
};

const tabStyle = (isActive) => ({
  padding: '10px 20px', border: 'none', background: 'transparent', fontWeight: 'bold', fontSize: '16px', cursor: 'pointer',
  borderBottom: isActive ? '3px solid #3498db' : '3px solid transparent',
  color: isActive ? '#3498db' : '#7f8c8d'
});

const getStatusClass = (status) => {
  if (status === 'Đã thanh toán') return 'badge-success';
  if (status === 'Thanh toán một phần') return 'badge-warning';
  return 'badge-danger';
};

// --- HÀM XỬ LÝ HOVER TOOLTIP ---
const showTooltip = (event, item) => {
  // Vì item trong danh sách Chi Phí không chứa sẵn trạng thái lô hàng (trang_thai_lo_hang)
  // Ta sẽ tìm nó trong listLoHang
  const foundLoHang = listLoHang.value.find(l => l.ma_lo_hang === item.ma_lo_hang);
  hoverPanel.value.data = foundLoHang ? { ...item, ...foundLoHang } : item;
  
  let x = event.clientX + 15;
  let y = event.clientY + 15;
  const tooltipWidth = 380; 
  const tooltipHeight = 220; 

  if (x + tooltipWidth > window.innerWidth) x = event.clientX - tooltipWidth - 10;
  if (y + tooltipHeight > window.innerHeight) y = event.clientY - tooltipHeight - 10;

  hoverPanel.value.x = x;
  hoverPanel.value.y = y;
  hoverPanel.value.show = true;
};

const hideTooltip = () => { hoverPanel.value.show = false; };

// Logic lọc dữ liệu
const filteredList = computed(() => {
  return listChiPhi.value.filter(item => {
    const matchTab = item.loai_giao_dich === currentTab.value;
    
    const search = searchQuery.value.toLowerCase();
    const matchSearch = !search || 
      (item.ten_chi_phi && item.ten_chi_phi.toLowerCase().includes(search)) || 
      (item.ten_lo_hang && item.ten_lo_hang.toLowerCase().includes(search));

    const matchStatus = !filterStatus.value || item.trang_thai_thanh_toan === filterStatus.value;

    let matchDate = true;
    if (fromDate.value || toDate.value) {
      if (!item.ngay_thanh_toan) {
        matchDate = false; 
      } else {
        const itemDate = new Date(item.ngay_thanh_toan).getTime();
        const start = fromDate.value ? new Date(fromDate.value).setHours(0, 0, 0, 0) : 0;
        const end = toDate.value ? new Date(toDate.value).setHours(23, 59, 59, 999) : Infinity;
        matchDate = itemDate >= start && itemDate <= end;
      }
    }

    return matchTab && matchSearch && matchStatus && matchDate;
  });
});

const filteredLoHangOptions = computed(() => {
  if (!searchLoHangInput.value) return listLoHang.value;
  const term = searchLoHangInput.value.toLowerCase();
  return listLoHang.value.filter(lo => 
    (lo.ten_lo_hang && lo.ten_lo_hang.toLowerCase().includes(term)) || 
    (lo.so_booking && lo.so_booking.toLowerCase().includes(term))
  );
});

const selectLoHang = (lo) => {
  formData.value.ma_lo_hang = lo.ma_lo_hang; 
  searchLoHangInput.value = `[${lo.so_booking}] - ${lo.ten_lo_hang}`; 
  isDropdownOpen.value = false; 
};

watch(searchLoHangInput, (newVal) => { if (newVal === '') formData.value.ma_lo_hang = null; });

const fetchData = async () => {
  isLoading.value = true;
  try {
    const res = await fetch(`${import.meta.env.VITE_API_URL}/chi-phi`);
    const data = await res.json();
    if (data.success) {
      listChiPhi.value = data.data;
      listLoHang.value = data.lo_hang;
      dashboardStats.value = data.thong_ke;
    }
  } catch (error) { console.error("Lỗi lấy dữ liệu!"); }
  finally { isLoading.value = false; }
};

const openModal = (item = null) => {
  if (item) {
    formData.value = { ...item };
    const selectedLo = listLoHang.value.find(l => l.ma_lo_hang === item.ma_lo_hang);
    searchLoHangInput.value = selectedLo ? `[${selectedLo.so_booking}] - ${selectedLo.ten_lo_hang}` : '';
  } else {
    formData.value = { ma_chi_phi: null, ma_lo_hang: null, ten_chi_phi: '', tong_tien: 0, trang_thai_thanh_toan: 'Chưa thanh toán', ngay_thanh_toan: null, loai_giao_dich: currentTab.value };
    searchLoHangInput.value = '';
  }
  isDropdownOpen.value = false;
  isModalOpen.value = true;
};

const saveData = async () => {
  if (!formData.value.ma_lo_hang) { alert("Vui lòng chọn Lô hàng!"); return; }
  
  if (formData.value.trang_thai_thanh_toan === 'Chưa thanh toán') {
      formData.value.ngay_thanh_toan = null;
  }

  const userId = currentUser ? (currentUser.id || currentUser.ma_tai_khoan) : null;

  isSaving.value = true;
  try {
    const payload = { ...formData.value, nguoi_sua_cuoi: userId }; 
    
    const res = await fetch(`${import.meta.env.VITE_API_URL}/chi-phi/save`, {
      method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (data.success) { isModalOpen.value = false; fetchData(); } else { alert(data.message); }
  } catch (e) { alert("Lỗi Server!"); }
  finally { isSaving.value = false; }
};

const handleDelete = async (id) => {
  if (!confirm(`Bạn có chắc muốn xóa khoản chi phí này?`)) return;
  try {
    const res = await fetch(`${import.meta.env.VITE_API_URL}/chi-phi/delete`, {
      method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ ma_chi_phi: id })
    });
    if ((await res.json()).success) fetchData();
  } catch (e) { alert("Lỗi!"); }
};

onMounted(fetchData);
</script>

<style scoped>
/* CSS CHO DASHBOARD CARDS */
.dashboard-cards { display: flex; gap: 20px; margin-bottom: 25px; }
.card { flex: 1; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); color: white; }
.card-title { font-size: 14px; font-weight: bold; text-transform: uppercase; margin-bottom: 10px; opacity: 0.9; }
.card-value { font-size: 24px; font-weight: bold; }
.card-thu { background: linear-gradient(135deg, #2ecc71, #27ae60); }
.card-chi { background: linear-gradient(135deg, #e74c3c, #c0392b); }
.card-ton { background: linear-gradient(135deg, #f1c40f, #f39c12); }

/* BADGES TRẠNG THÁI */
.badge { padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; display: inline-block; white-space: nowrap;}
.badge-success { background: #d4efdf; color: #27ae60; }
.badge-danger { background: #fadbd8; color: #c0392b; }
.badge-warning { background: #fcf3cf; color: #d35400; }

/* CSS HIỆU ỨNG HOVER CHỮ */
.hover-trigger { cursor: pointer; color: #2980b9; border-bottom: 1px dashed #2980b9; transition: all 0.2s; padding-bottom: 1px; }
.hover-trigger:hover { color: #e74c3c; border-bottom-color: #e74c3c; }

/* CSS BẢNG TOOLTIP NỔI BẬT */
.hover-tooltip { position: fixed; background: #fff; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.25); border: 1px solid #dcdde1; z-index: 9999; width: 380px; pointer-events: none; }
.tooltip-header { background: #f8f9fa; padding: 12px 15px; border-bottom: 1px solid #eee; border-radius: 8px 8px 0 0; }
.tooltip-header h4 { margin: 0; font-size: 15px; color: #2c3e50; }
.tooltip-body { padding: 15px; }

/* CSS Bảng chi tiết */
.detail-table { width: 100%; border-collapse: collapse; }
.detail-table tr { border-bottom: 1px dashed #ecf0f1; }
.detail-table td { padding: 10px 0; color: #7f8c8d; font-size: 13px; width: 40%;}
.detail-table strong { font-size: 13px; color: #2c3e50; padding-left: 10px;}

/* CSS Modal */
.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }

/* CSS SO LE MÀU BẢNG CHÍNH */
.zebra-table .table-row { border-bottom: 1px solid #eee; transition: background-color 0.2s; }
.zebra-table .table-row:nth-child(even) { background-color: #f8f9fa; }
.zebra-table .table-row:hover { background-color: #e9ecef; }
</style>