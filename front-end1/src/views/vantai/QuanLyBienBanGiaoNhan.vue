<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">Quản Lý Biên Bản Giao Nhận (BBGN)</h3>

    <div class="toolbar" style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 20px; align-items: flex-end; background: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #e9ecef;">
      
      <div class="search-box" style="flex: 1; min-width: 250px;">
        <input type="text" v-model="searchQuery" placeholder="🔍 Tìm: Tên lô, Booking, Vận đơn, Số Cont, Nhà xe..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #ccc; font-size: 14px; box-sizing: border-box;">
      </div>

      <div class="filter-box" style="min-width: 150px;">
        <select v-model="filterStatus" style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #ccc; font-size: 14px; cursor: pointer; background-color: #fff; box-sizing: border-box;">
          <option value="">📋 Tất cả</option>
          <option value="hoantat">✅ Đã hoàn tất</option>
          <option value="chuahoantat">⏳ Chưa xong</option>
        </select>
      </div>

      <div style="display: flex; align-items: center; gap: 5px;">
        <label style="font-size: 13px; font-weight: bold; color: #555;">Từ:</label>
        <input type="date" v-model="fromDate" style="padding: 8px 12px; border-radius: 6px; border: 1px solid #ccc; font-size: 14px;">
      </div>
      <div style="display: flex; align-items: center; gap: 5px;">
        <label style="font-size: 13px; font-weight: bold; color: #555;">Đến:</label>
        <input type="date" v-model="toDate" style="padding: 8px 12px; border-radius: 6px; border: 1px solid #ccc; font-size: 14px;">
      </div>

      <div style="display: flex; gap: 10px;">
        <button @click="clearFilters()" class="btn-clear" style="padding: 8px 15px; border-radius: 6px; cursor: pointer; border: 1px solid #ccc; background: #fff;" title="Xóa bộ lọc">❌</button>
        <button class="btn btn-success" @click="openModal()" style="border-radius: 6px; padding: 8px 20px; background: #2ecc71; color: white; border: none; cursor: pointer; font-weight: bold;">
          ➕ THÊM MỚI
        </button>
      </div>
    </div>

    <div class="pagination-bar" style="display: flex; justify-content: space-between; align-items: center; padding: 15px; background: #fff; border-radius: 8px; margin-bottom: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #dee2e6;">
      <div style="display: flex; align-items: center; gap: 10px;">
        <label style="color: #495057;">Hiển thị</label>
        <select v-model="itemsPerPage" style="padding: 5px 10px; border-radius: 4px; border: 1px solid #ccc; cursor: pointer;">
          <option :value="5">5</option>
          <option :value="10">10</option>
          <option :value="20">20</option>
          <option :value="50">50</option>
        </select>
        <label style="color: #495057;">mục</label>
      </div>
      <div style="display: flex; align-items: center; gap: 15px;">
        <button @click="prevPage" :disabled="currentPage === 1" class="btn-page">◀ Trước</button>
        <span style="font-weight: bold; color: #2c3e50;">Trang {{ currentPage }} / {{ totalPages || 1 }}</span>
        <button @click="nextPage" :disabled="currentPage === totalPages || totalPages === 0" class="btn-page">Sau ▶</button>
      </div>
    </div>

    <div class="table-container" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow-x: auto;">
      <table class="zebra-table" style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
          <tr>
            <th style="padding: 12px 15px; width: 100px;">Mã Phiếu</th>
            <th style="padding: 12px 15px;">Thuộc Lô Hàng</th>
            <th style="padding: 12px 15px; text-align: center;">Trạng Thái</th>
            <th style="padding: 12px 15px;">Số Vận Đơn</th>
            <th style="padding: 12px 15px;">Số Container</th>
            <th style="padding: 12px 15px;">Đơn Vị Vận Tải</th>
            <th style="padding: 12px 15px;">Ngày Lập</th>
            <th style="padding: 12px 15px; text-align: center; width: 150px;">Hành động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="isLoading"><td colspan="8" style="text-align: center; padding: 20px;">Đang tải dữ liệu...</td></tr>
          <tr v-else-if="paginatedList.length === 0"><td colspan="8" style="text-align: center; padding: 20px; color: #e74c3c;">Không tìm thấy dữ liệu nào phù hợp với bộ lọc!</td></tr>
          
          <tr v-for="item in paginatedList" :key="item.ma_phieu" class="table-row">
            <td style="padding: 12px 15px; font-weight: bold; color: #2980b9;">BB-{{ item.ma_phieu }}</td>
            <td style="padding: 12px 15px;">
              {{ item.ten_lo_hang || 'N/A' }}
              <button v-if="item.ten_lo_hang" @click="viewDetail('lo_hang', item)" class="btn-eye" title="Xem chi tiết Lô hàng">👁️</button>
            </td>

            <td style="padding: 12px 15px; text-align: center;">
              <span class="badge" :class="checkIsHoanTat(item) ? 'badge-success' : 'badge-warning'">
                {{ checkIsHoanTat(item) ? '✅ Hoàn tất' : '⏳ Chưa xong' }}
              </span>
            </td>

            <td style="padding: 12px 15px; font-weight: bold;">
              {{ item.so_van_don || '---' }}
              <button v-if="item.so_van_don" @click="viewDetail('van_don', item)" class="btn-eye" title="Xem chi tiết Vận đơn">👁️</button>
            </td>
            <td style="padding: 12px 15px;">
              {{ item.so_cont || 'N/A' }}
              <button v-if="item.so_cont" @click="viewDetail('container', item)" class="btn-eye" title="Xem chi tiết Container">👁️</button>
            </td>
            <td style="padding: 12px 15px; color: #e67e22; font-weight: bold;">
              {{ item.ten_hang_van_tai || 'Chưa điều xe' }}
              <button v-if="item.ten_hang_van_tai" @click="viewDetail('nha_xe', item)" class="btn-eye" title="Xem thông tin Nhà xe">👁️</button>
            </td>
            <td style="padding: 12px 15px;">{{ formatDateTime(item.ngay_phat_hanh) }}</td>
            <td style="padding: 12px 15px; text-align: center;">
              <button @click="downloadPDF(item.ma_phieu)" style="margin-right: 10px; cursor: pointer; border: none; background: none; font-size: 16px;" title="Xuất PDF">📄</button>
              <button @click="openModal(item)" style="margin-right: 10px; cursor: pointer; border: none; background: none; font-size: 16px;" title="Sửa">✏️</button>
              <button @click="handleDelete(item.ma_phieu)" style="cursor: pointer; border: none; background: none; font-size: 16px;" title="Xóa">🗑️</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="detailPanel.show" class="side-panel">
      <div class="panel-header">
        <h4>{{ detailPanel.title }}</h4>
        <button @click="detailPanel.show = false" class="close-btn">✖</button>
      </div>
      <div class="panel-body">
        
        <table v-if="detailPanel.type === 'lo_hang'" class="detail-table">
          <tbody>
            <tr><td>Số Booking:</td><td><strong>{{ detailPanel.data.so_booking }}</strong></td></tr>
            <tr><td>Tên Lô hàng:</td><td><strong>{{ detailPanel.data.ten_lo_hang }}</strong></td></tr>
            <tr><td>Khách hàng:</td><td><strong>{{ detailPanel.data.ten_khach_hang || '---' }}</strong></td></tr>
            <tr><td>ĐK Thương mại:</td><td><strong style="color: #8e44ad;">{{ detailPanel.data.dieu_kien_thuong_mai || '---' }}</strong></td></tr>
            <tr><td>Trạng thái:</td><td>
              <span class="badge" :class="checkIsHoanTat(detailPanel.data) ? 'badge-success' : 'badge-warning'">
                {{ checkIsHoanTat(detailPanel.data) ? 'Đã hoàn tất' : 'Đang xử lý' }}
              </span>
            </td></tr>
          </tbody>
        </table>

        <table v-if="detailPanel.type === 'van_don' || detailPanel.type === 'container'" class="detail-table">
          <tbody>
            <tr><td>Số House Bill:</td><td><strong style="color: #2980b9;">{{ detailPanel.data.so_van_don || '---' }}</strong></td></tr>
            <tr><td>Số Master Bill:</td><td><strong>{{ detailPanel.data.so_van_don_goc || '---' }}</strong></td></tr>
            <tr><td>Loại vận đơn:</td><td><strong>{{ detailPanel.data.loai_van_don || '---' }}</strong></td></tr>
            <tr><td>PT Đóng hàng:</td><td><strong>{{ detailPanel.data.phuong_thuc_dong_cont || '---' }}</strong></td></tr>
            <tr><td>Số Container:</td><td><strong>{{ detailPanel.data.so_cont || '---' }}</strong></td></tr>
            <tr><td>Số Chì (Seal):</td><td><strong>{{ detailPanel.data.so_chi || '---' }}</strong></td></tr>
          </tbody>
        </table>

        <table v-if="detailPanel.type === 'nha_xe'" class="detail-table">
          <tbody>
            <tr><td>Tên Nhà xe:</td><td><strong style="color: #e67e22;">{{ detailPanel.data.ten_hang_van_tai || '---' }}</strong></td></tr>
            <tr><td>Mã số hệ thống:</td><td><strong>NX-{{ detailPanel.data.ma_hang_van_tai }}</strong></td></tr>
            <tr><td colspan="2" style="font-style: italic; font-size: 12px;">(Bạn có thể xem chi tiết thông tin tài xế và biển số xe ở Menu Hãng Vận Tải)</td></tr>
          </tbody>
        </table>

      </div>
    </div>

    <div v-if="isModalOpen" class="modal-overlay">
      <div class="modal-content" style="max-width: 500px; padding: 20px; background: white; border-radius: 8px; width: 100%;">
        <h3 style="margin-top: 0;">{{ formData.ma_phieu ? 'Cập Nhật' : 'Thêm Mới' }} Biên Bản Giao Nhận</h3>
        <form @submit.prevent="saveData">
          
          <div style="margin-bottom: 15px; position: relative;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Thuộc Lô hàng *</label>
            <div v-if="isDropdownOpen" @click="isDropdownOpen = false" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9;"></div>
            <div style="position: relative; z-index: 10;">
              <input type="text" v-model="searchLoHangInput" @focus="isDropdownOpen = true" @input="isDropdownOpen = true" placeholder="🔍 Gõ số booking hoặc tên lô hàng..." required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
              <div v-if="isDropdownOpen" style="position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ccc; border-radius: 4px; max-height: 200px; overflow-y: auto; box-shadow: 0 4px 8px rgba(0,0,0,0.1); margin-top: 2px;">
                <div v-for="lo in filteredLoHangOptions" :key="lo.ma_lo_hang" @click="selectLoHang(lo)" style="padding: 10px; border-bottom: 1px solid #eee; cursor: pointer;">
                  <strong style="color: #2980b9;">[{{ lo.so_booking }}]</strong> - {{ lo.ten_lo_hang }}
                </div>
              </div>
            </div>
          </div>

          <div style="margin-bottom: 15px; position: relative;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Hãng Vận Tải (Nhà Xe) *</label>
            <div v-if="isDropdownHangVanTaiOpen" @click="isDropdownHangVanTaiOpen = false" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9;"></div>
            <div style="position: relative; z-index: 10;">
              <input type="text" v-model="searchHangVanTaiInput" @focus="isDropdownHangVanTaiOpen = true" @input="isDropdownHangVanTaiOpen = true" placeholder="🔍 Gõ tên nhà xe để tìm kiếm..." required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
              <div v-if="isDropdownHangVanTaiOpen" style="position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ccc; border-radius: 4px; max-height: 200px; overflow-y: auto; box-shadow: 0 4px 8px rgba(0,0,0,0.1); margin-top: 2px;">
                <div v-for="hang in filteredHangVanTaiOptions" :key="hang.ma_hang_van_tai" @click="selectHangVanTai(hang)" style="padding: 10px; border-bottom: 1px solid #eee; cursor: pointer;">
                  🚗 {{ hang.ten_hang_van_tai }}
                </div>
                <div v-if="filteredHangVanTaiOptions.length === 0" style="padding: 10px; color: #7f8c8d; text-align: center; font-style: italic;">
                  Không tìm thấy nhà xe nào!
                </div>
              </div>
            </div>
          </div>

          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Ngày lập *</label>
            <input type="datetime-local" v-model="formData.ngay_phat_hanh" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
          </div>

          <div style="text-align: right;">
            <button type="button" @click="isModalOpen = false" style="padding: 8px 15px; margin-right: 10px; cursor: pointer; border: 1px solid #ccc; background: #fff; border-radius: 4px;">Hủy</button>
            <button type="submit" :disabled="isSaving" style="padding: 8px 15px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer;">{{ isSaving ? 'Đang lưu...' : 'Lưu lại' }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';

const listBBGN = ref([]); 
const listLoHang = ref([]);
const listHangVanTai = ref([]); 
const isLoading = ref(true);
const isSaving = ref(false);
const isModalOpen = ref(false);

// BIẾN TÌM KIẾM VÀ LỌC (ĐÃ NÂNG CẤP)
const searchQuery = ref(''); 
const filterStatus = ref(''); 
const fromDate = ref(''); 
const toDate = ref('');   

// BIẾN PHÂN TRANG
const itemsPerPage = ref(10);
const currentPage = ref(1);

const detailPanel = ref({ show: false, type: '', title: '', data: {} });
const formData = ref({ ma_phieu: null, ma_lo_hang: null, ma_hang_van_tai: '', ngay_phat_hanh: '' });

// BIẾN CHO COMBOBOX
const searchLoHangInput = ref('');
const isDropdownOpen = ref(false);
const searchHangVanTaiInput = ref('');
const isDropdownHangVanTaiOpen = ref(false);

const clearFilters = () => {
  searchQuery.value = '';
  filterStatus.value = '';
  fromDate.value = '';
  toDate.value = '';
};

const formatDateTime = (dateStr) => {
  if (!dateStr) return 'N/A';
  return new Date(dateStr).toLocaleString('vi-VN');
};

const checkIsHoanTat = (item) => {
  let status = item.trang_thai_lo_hang; 
  if (status === undefined) {
    const lo = listLoHang.value.find(l => l.ma_lo_hang === item.ma_lo_hang);
    status = lo ? lo.trang_thai_lo_hang : null; 
  }
  const strStatus = String(status).trim().toLowerCase();
  if (strStatus.includes('hoàn tất') || strStatus === '1' || strStatus === 'true') {
    return true;
  }
  return false;
};

// --- LOGIC LỌC TỔNG HỢP (MỞ RỘNG TÌM THEO NHÀ XE VÀ NGÀY) ---
const filteredList = computed(() => {
  return listBBGN.value.filter(item => {
    // 1. Lọc theo chữ
    const search = searchQuery.value.toLowerCase();
    const matchSearch = !search || 
                       (item.ten_lo_hang && item.ten_lo_hang.toLowerCase().includes(search)) || 
                       (item.so_booking && item.so_booking.toLowerCase().includes(search)) ||
                       (item.so_van_don && item.so_van_don.toLowerCase().includes(search)) ||
                       (item.so_cont && item.so_cont.toLowerCase().includes(search)) ||
                       (item.ten_hang_van_tai && item.ten_hang_van_tai.toLowerCase().includes(search));

    // 2. Lọc theo trạng thái
    let matchStatus = true;
    const isHoanTat = checkIsHoanTat(item);
    if (filterStatus.value === 'hoantat') matchStatus = isHoanTat === true;
    else if (filterStatus.value === 'chuahoantat') matchStatus = isHoanTat === false;

    // 3. Lọc theo Khoảng Ngày
    let matchDate = true;
    if (fromDate.value || toDate.value) {
      const itemDate = new Date(item.ngay_phat_hanh).getTime();
      const start = fromDate.value ? new Date(fromDate.value).setHours(0, 0, 0, 0) : 0;
      const end = toDate.value ? new Date(toDate.value).setHours(23, 59, 59, 999) : Infinity;
      matchDate = itemDate >= start && itemDate <= end;
    }

    return matchSearch && matchStatus && matchDate;
  });
});

// --- LOGIC PHÂN TRANG ---
const totalPages = computed(() => Math.ceil(filteredList.value.length / itemsPerPage.value));

const paginatedList = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return filteredList.value.slice(start, end);
});

const prevPage = () => { if (currentPage.value > 1) currentPage.value--; };
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++; };

// Tự động Reset về trang 1
watch([searchQuery, itemsPerPage, filterStatus, fromDate, toDate], () => { currentPage.value = 1; });

// --- LỌC GỢI Ý COMBOBOX LÔ HÀNG ---
const filteredLoHangOptions = computed(() => {
  const loHangHopLe = listLoHang.value.filter(lo => {
    let status = lo.trang_thai_lo_hang || ''; 
    let strStatus = String(status).trim().toLowerCase(); 
    if (strStatus === 'hoàn tất' || strStatus === 'hủy') return false; 
    return true; 
  });
  if (!searchLoHangInput.value) return loHangHopLe;
  const term = searchLoHangInput.value.toLowerCase();
  return loHangHopLe.filter(lo => 
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

// --- LỌC GỢI Ý COMBOBOX HÃNG VẬN TẢI ---
const filteredHangVanTaiOptions = computed(() => {
  if (!searchHangVanTaiInput.value) return listHangVanTai.value;
  const term = searchHangVanTaiInput.value.toLowerCase();
  return listHangVanTai.value.filter(hang => 
    hang.ten_hang_van_tai && hang.ten_hang_van_tai.toLowerCase().includes(term)
  );
});

const selectHangVanTai = (hang) => {
  formData.value.ma_hang_van_tai = hang.ma_hang_van_tai;
  searchHangVanTaiInput.value = hang.ten_hang_van_tai;
  isDropdownHangVanTaiOpen.value = false;
};

watch(searchHangVanTaiInput, (newVal) => { if (newVal === '') formData.value.ma_hang_van_tai = ''; });


// --- CÁC HÀM XỬ LÝ CHÍNH ---
const viewDetail = (type, item) => {
  detailPanel.value.type = type; detailPanel.value.data = item; detailPanel.value.show = true;
  if (type === 'lo_hang') detailPanel.value.title = '📦 Chi tiết Lô hàng';
  if (type === 'van_don' || type === 'container') detailPanel.value.title = '📑 Chi tiết Vận đơn & Cont';
  if (type === 'nha_xe') detailPanel.value.title = '🚛 Thông tin Nhà Xe';
};

const fetchData = async () => {
  isLoading.value = true;
  try {
    const res = await fetch(`${import.meta.env.VITE_API_URL}/lenh-giao-hang`);
    const data = await res.json();
    if (data.success) {
      listBBGN.value = data.data_bbgn; 
      listLoHang.value = data.lo_hang;
      listHangVanTai.value = data.hang_van_tai;
    }
  } catch (error) { console.error("Lỗi!"); } finally { isLoading.value = false; }
};

const openModal = (item = null) => {
  if (item) {
    formData.value = { ma_phieu: item.ma_phieu, ma_lo_hang: item.ma_lo_hang, ma_hang_van_tai: item.ma_hang_van_tai || '', ngay_phat_hanh: item.ngay_phat_hanh ? new Date(item.ngay_phat_hanh).toISOString().slice(0, 16) : '' };
    
    const selectedLo = listLoHang.value.find(l => l.ma_lo_hang === item.ma_lo_hang);
    searchLoHangInput.value = selectedLo ? `[${selectedLo.so_booking}] - ${selectedLo.ten_lo_hang}` : '';
    
    const selectedHang = listHangVanTai.value.find(h => h.ma_hang_van_tai === item.ma_hang_van_tai);
    searchHangVanTaiInput.value = selectedHang ? selectedHang.ten_hang_van_tai : '';

  } else {
    formData.value = { ma_phieu: null, ma_lo_hang: null, ma_hang_van_tai: '', ngay_phat_hanh: '' }; 
    searchLoHangInput.value = '';
    searchHangVanTaiInput.value = ''; 
  }
  
  isDropdownOpen.value = false; 
  isDropdownHangVanTaiOpen.value = false; 
  isModalOpen.value = true;
};

const saveData = async () => {
  if (!formData.value.ma_lo_hang) { alert("Vui lòng chọn Lô hàng!"); return; }
  if (!formData.value.ma_hang_van_tai) { alert("Vui lòng chọn Hãng vận tải!"); return; }
  
  isSaving.value = true;
  try {
    const payload = { ...formData.value, loai: 'BBGN' }; 
    const res = await fetch(`${import.meta.env.VITE_API_URL}/lenh-giao-hang/save`, {
      method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
    });
    if ((await res.json()).success) { isModalOpen.value = false; fetchData(); }
  } catch (e) { alert("Lỗi!"); } finally { isSaving.value = false; }
};

const handleDelete = async (id) => {
  if (!confirm(`Hủy vĩnh viễn phiếu này?`)) return;
  try {
    const res = await fetch(`${import.meta.env.VITE_API_URL}/lenh-giao-hang/delete`, {
      method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ ma_phieu: id, loai: 'BBGN' })
    });
    if ((await res.json()).success) fetchData();
  } catch (e) { alert("Lỗi!"); }
};

const downloadPDF = async (id) => {
  try {
    const url = `${import.meta.env.VITE_API_URL}/lenh-giao-hang/export-pdf?id=${id}&loai=BBGN`;
    const response = await fetch(url);
    if (!response.ok) throw new Error('Network error');
    const blob = await response.blob();
    const downloadUrl = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = downloadUrl; a.download = `BBGN_${id}.pdf`;
    document.body.appendChild(a); a.click(); a.remove(); window.URL.revokeObjectURL(downloadUrl);
  } catch (error) { alert("Lỗi xuất PDF!"); }
};

onMounted(fetchData);
</script>

<style scoped>
/* CSS CHUNG */
.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.btn-eye { background: #f0f4f8; border: 1px solid #dcdde1; border-radius: 4px; cursor: pointer; padding: 4px 8px; font-size: 13px; margin-left: 8px; transition: all 0.2s; }
.btn-eye:hover { background: #3498db; border-color: #3498db; color: white; transform: scale(1.1); }
.side-panel { position: fixed; top: 90px; right: 20px; width: 380px; background: #fff; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); border: 1px solid #eee; z-index: 100; animation: slideIn 0.3s ease-out; }
.panel-header { display: flex; justify-content: space-between; align-items: center; padding: 15px; border-bottom: 1px solid #f1f2f6; background: #fafbfc; border-radius: 8px 8px 0 0; }
.panel-header h4 { margin: 0; color: #2c3e50; font-size: 16px; }
.close-btn { background: none; border: none; font-size: 18px; cursor: pointer; color: #7f8c8d; transition: 0.2s;}
.close-btn:hover { color: #e74c3c; transform: rotate(90deg); }
.panel-body { padding: 15px; }

/* Bảng chi tiết */
.detail-table { width: 100%; border-collapse: collapse; }
.detail-table tr { border-bottom: 1px solid #f1f2f6; }
.detail-table td { padding: 12px 0; color: #555; font-size: 14px; width: 40%; vertical-align: top;}
.detail-table strong { font-size: 14px; color: #2c3e50; display: table-cell; padding-left: 10px;}

@keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

/* CSS SO LE MÀU VÀ NÚT PHÂN TRANG */
.zebra-table .table-row { border-bottom: 1px solid #eee; transition: background-color 0.2s; }
.zebra-table .table-row:nth-child(even) { background-color: #f8f9fa; }
.zebra-table .table-row:hover { background-color: #e9ecef; }
.btn-page { background: white; border: 1px solid #ced4da; padding: 6px 12px; border-radius: 4px; cursor: pointer; color: #495057; font-size: 14px; transition: all 0.2s; }
.btn-page:hover:not(:disabled) { background: #f1f3f5; border-color: #adb5bd; }
.btn-page:disabled { opacity: 0.5; cursor: not-allowed; background: #f8f9fa; }

/* CSS BADGE (THẺ MÀU TRẠNG THÁI) */
.badge { padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; display: inline-block; white-space: nowrap; }
.badge-success { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
.badge-warning { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
</style>