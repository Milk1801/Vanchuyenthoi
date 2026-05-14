<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">Quản lý Vận đơn</h3>
    
    <div class="toolbar" style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 15px; background: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #ddd;">
      <!-- Row 1: Text search inputs -->
      <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; width: 100%;">
        <input type="text" v-model="searchSoVanDon" placeholder="🔍 Số Vận Đơn / Mã..." style="padding: 8px 12px; border-radius: 6px; border: 1px solid #ccc; font-size: 13px;">
        <input type="text" v-model="searchSoVanDonGoc" placeholder="🔍 Số Vận Đơn Gốc..." style="padding: 8px 12px; border-radius: 6px; border: 1px solid #ccc; font-size: 13px;">
        <input type="text" v-model="searchTenLoHang" placeholder="🔍 Tên Lô Hàng..." style="padding: 8px 12px; border-radius: 6px; border: 1px solid #ccc; font-size: 13px;">
        <input type="text" v-model="searchSoCont" placeholder="🔍 Số Container..." style="padding: 8px 12px; border-radius: 6px; border: 1px solid #ccc; font-size: 13px;">
        <input type="text" v-model="searchSoChi" placeholder="🔍 Số Chì..." style="padding: 8px 12px; border-radius: 6px; border: 1px solid #ccc; font-size: 13px;">
      </div>

      <!-- Row 2: Searchable Comboboxes -->
      <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; width: 100%;">
        <div class="combobox-wrapper">
          <input type="text" v-model="loaiVDSearchText" placeholder="📄 Loại B/L..." @focus="showLoaiVDDropdown = true" class="combobox-input-sm">
          <ul v-if="showLoaiVDDropdown" class="combobox-list">
            <li @click="selectSearchLoai('ALL')">-- Tất cả loại B/L --</li>
            <li v-for="t in filteredLoaiVDList" :key="t" @click="selectSearchLoai(t)">{{t}}</li>
          </ul>
        </div>
        <div class="combobox-wrapper">
          <input type="text" v-model="polSearchText" placeholder="⚓ Cảng Đi (POL)..." @focus="showPolDropdown = true" class="combobox-input-sm">
          <ul v-if="showPolDropdown" class="combobox-list">
            <li @click="selectSearchCang(null, 'pol')">-- Tất cả cảng --</li>
            <li v-for="c in filteredPol" :key="c.ma_cang" @click="selectSearchCang(c, 'pol')">{{c.ten_cang}}</li>
          </ul>
        </div>
        <div class="combobox-wrapper">
          <input type="text" v-model="podSearchText" placeholder="⚓ Cảng Đến (POD)..." @focus="showPodDropdown = true" class="combobox-input-sm">
          <ul v-if="showPodDropdown" class="combobox-list">
            <li @click="selectSearchCang(null, 'pod')">-- Tất cả cảng --</li>
            <li v-for="c in filteredPod" :key="c.ma_cang" @click="selectSearchCang(c, 'pod')">{{c.ten_cang}}</li>
          </ul>
        </div>
        <div class="combobox-wrapper">
          <input type="text" v-model="shipperSearchText" placeholder="👤 Người Gửi (Shipper)..." @focus="showShipperDropdown = true" class="combobox-input-sm">
          <ul v-if="showShipperDropdown" class="combobox-list">
            <li @click="selectSearchKh(null, 'shipper')">-- Tất cả --</li>
            <li v-for="kh in filteredShippers" :key="kh.ma_khach_hang" @click="selectSearchKh(kh, 'shipper')">{{kh.ten_khach_hang}}</li>
          </ul>
        </div>
        <div class="combobox-wrapper">
          <input type="text" v-model="consigneeSearchText" placeholder="👤 Người Nhận (Consignee)..." @focus="showConsigneeDropdown = true" class="combobox-input-sm">
          <ul v-if="showConsigneeDropdown" class="combobox-list">
            <li @click="selectSearchKh(null, 'consignee')">-- Tất cả --</li>
            <li v-for="kh in filteredConsignees" :key="kh.ma_khach_hang" @click="selectSearchKh(kh, 'consignee')">{{kh.ten_khach_hang}}</li>
          </ul>
        </div>
      </div>

      <!-- Row 3: Others and Actions -->
      <div style="display: flex; gap: 10px; width: 100%; align-items: center; flex-wrap: wrap;">
        <div class="combobox-wrapper" style="width: 200px;">
          <input type="text" v-model="notifySearchText" placeholder="👤 Bên Thông Báo..." @focus="showNotifyDropdown = true" class="combobox-input-sm">
          <ul v-if="showNotifyDropdown" class="combobox-list">
            <li @click="selectSearchKh(null, 'notify')">-- Tất cả --</li>
            <li v-for="kh in filteredNotify" :key="kh.ma_khach_hang" @click="selectSearchKh(kh, 'notify')">{{kh.ten_khach_hang}}</li>
          </ul>
        </div>
        <div style="display: flex; align-items: center; gap: 8px; background: #fff; padding: 0 10px; border: 1px solid #ccc; border-radius: 6px; height: 36px;">
          <label style="font-size: 13px; color: #666; white-space: nowrap;">Ngày PH:</label>
          <input type="date" v-model="filterNgayPH" style="padding: 0; border: none; outline: none; font-size: 13px;">
        </div>
        <div class="combobox-wrapper" style="width: 180px;">
          <input type="text" v-model="userSearchText" placeholder="👤 Người Sửa..." @focus="showUserDropdown = true" class="combobox-input-sm">
          <ul v-if="showUserDropdown" class="combobox-list">
            <li @click="selectSearchUser(null)">-- Tất cả --</li>
            <li v-for="u in filteredUsers" :key="u" @click="selectSearchUser(u)">{{u}}</li>
          </ul>
        </div>
        <div style="flex: 1;"></div>
        <button @click="resetFilters" style="padding: 8px 15px; border: 1px solid #ccc; border-radius: 6px; background: #fff; cursor: pointer; transition: 0.2s; height: 36px; font-size: 13px;">🧹 Xóa lọc</button>
        <button class="btn btn-success" @click="router.push('/van-tai/quan-ly-van-don/add')" style="border-radius: 6px; height: 36px; padding: 0 15px; font-size: 13px;">+ TẠO VẬN ĐƠN</button>
      </div>
    </div>

    <div v-if="isLoading" style="text-align: center; padding: 20px; color: #3498db;">
      Đang tải dữ liệu Vận đơn...
    </div>

    <div v-else style="display: flex; gap: 20px; align-items: flex-start;">
      <!-- BÊN TRÁI: DANH SÁCH VẬN ĐƠN -->
      <div style="flex: 1; min-width: 0;">
        <!-- Kiểm soát phân trang -->
        <div v-if="listVanDon.length > 0" class="pagination-controls" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 8px; border: 1px solid #e1e4e8;">
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

        <div class="table-card" style="overflow-x: auto; background: white; border-radius: 8px; border: 1px solid #ddd;">
          <table style="min-width: 2200px; width: 100%; border-collapse: collapse;">
            <thead>
              <tr>
                <th class="sticky-col-left" style="width: 50px; text-align: center;">STT</th>
                <th>Mã</th>
                <th>Số Vận Đơn</th>
                <th>Số Vận Đơn Gốc</th>
                <th>Loại B/L</th>
                <th>Lô hàng</th>
                <th>Cảng Đi (POL)</th>
                <th>Cảng Đến (POD)</th>
                <th>Người Gửi (Shipper)</th>
                <th>Người Nhận (Consignee)</th>
                <th>Bên Được Thông Báo</th>
                <th>Điều kiện cước</th>
                <th>Phương thức</th>
                <th>Số Container</th>
                <th>Số Chì (Seal)</th>
                <th>Ngày PH</th>
                <th>Người sửa</th>
                <th class="sticky-col-right" style="text-align: center;">Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(vd, index) in paginatedVanDon" :key="vd.ma_van_don" 
                  :class="{ 'row-selected': (selectedItem?.ma_van_don === vd.ma_van_don), 'row-even': (index % 2 !== 0), 'row-odd': (index % 2 === 0) }">
                <td class="sticky-col-left" style="text-align: center; color: #7f8c8d;">{{ (currentPage - 1) * pageSize + index + 1 }}</td>
                <td class="fw-bold">{{ vd.ma_van_don }}</td>
                <td class="fw-bold" style="color: #2980b9;">{{ vd.so_van_don }}</td>
                <td>{{ vd.so_van_don_goc || '---' }}</td>
                <td><span class="badge badge-active" style="white-space: nowrap; font-size: 11px;">{{ vd.loai_van_don }}</span></td>
                <td class="fw-bold">{{ vd.ten_lo_hang || '---' }}</td>
                <td><strong>{{ vd.ten_cang_di || '---' }}</strong></td>
                <td><strong>{{ vd.ten_cang_den || '---' }}</strong></td>
                <td style="font-size: 13px;">{{ vd.ten_nguoi_gui_hang || '---' }}</td>
                <td style="font-size: 13px;">{{ vd.ten_nguoi_nhan_hang || '---' }}</td>
                <td style="font-size: 13px;">{{ vd.ten_ben_duoc_thong_bao || '---' }}</td>
                <td style="text-align: center;">{{ vd.dieu_kien_cuoc }}</td>
                <td style="text-align: center;"><strong>{{ vd.phuong_thuc_dong_cont }}</strong></td>
                <td>{{ vd.so_cont || '---' }}</td>
                <td>{{ vd.so_chi || '---' }}</td>
                <td>{{ formatDate(vd.ngay_phat_hanh) }}</td>
                <td>{{ vd.nguoi_sua_doi || 'N/A' }}</td>
                <td class="sticky-col-right" style="text-align: center;">
                  <div style="display: grid; grid-template-columns: repeat(2, 35px); gap: 5px; justify-content: center; margin: 0 auto; width: fit-content;">
                    <button class="action-btn-no-mg text-success" @click="showShipmentInfo(vd)" title="Xem thông tin lô hàng">📋</button>
                    <button class="action-btn-no-mg text-warning" @click="handlePrintPDF(vd.ma_van_don)" title="Xuất PDF">🖨️</button>
                    <button class="action-btn-no-mg text-primary" @click="router.push('/van-tai/quan-ly-van-don/edit/' + vd.ma_van_don)" title="Sửa">✏️</button>
                    <button class="action-btn-no-mg text-danger" @click="handleDelete(vd.ma_van_don)" title="Xóa">🗑️</button>
                  </div>
                </td>
              </tr>
              <tr v-if="filteredVanDon.length === 0">
                <td colspan="11" style="text-align: center; padding: 20px; color: #7f8c8d;">
                  Không tìm thấy vận đơn nào!
                </td>
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
const listVanDon = ref([]);
const listCangBien = ref([]);
const listKhachHang = ref([]);
const isLoading = ref(true);
const searchSoVanDon = ref('');
const searchSoVanDonGoc = ref('');
const searchTenLoHang = ref('');
const searchSoCont = ref('');
const searchSoChi = ref('');
const filterLoaiVanDon = ref('ALL');
const filterCangDi = ref(null);
const filterCangDen = ref(null);
const filterShipper = ref(null);
const filterConsignee = ref(null);
const filterNotify = ref(null);
const filterNgayPH = ref('');
const filterUser = ref(null);

// State cho tìm kiếm combobox (tương tự QuanLyVanDonForm)
const polSearchText = ref('');
const podSearchText = ref('');
const shipperSearchText = ref('');
const consigneeSearchText = ref('');
const notifySearchText = ref('');
const userSearchText = ref('');
const loaiVDSearchText = ref('');

const showPolDropdown = ref(false);
const showPodDropdown = ref(false);
const showShipperDropdown = ref(false);
const showConsigneeDropdown = ref(false);
const showNotifyDropdown = ref(false);
const showUserDropdown = ref(false);
const showLoaiVDDropdown = ref(false);

// State cho Side Panel
const viewType = ref('none');
const panelTitle = ref('');
const selectedItem = ref(null);
const selectedShipment = ref(null);
const isPanelLoading = ref(false);

// Phân trang
const currentPage = ref(1);
const pageSize = ref(10);
const pageSizes = [10, 20, 50];

const listLoaiVanDon = ['Original B/L', 'Surrendered B/L', 'Seaway Bill'];

const formatDate = (dateString) => {
  if (!dateString) return "---";
  return new Date(dateString).toLocaleDateString('vi-VN');
};

const filteredPol = computed(() => listCangBien.value.filter(c => c.ten_cang.toLowerCase().includes(polSearchText.value.toLowerCase())));
const filteredPod = computed(() => listCangBien.value.filter(c => c.ten_cang.toLowerCase().includes(podSearchText.value.toLowerCase())));
const filteredShippers = computed(() => listKhachHang.value.filter(kh => kh.ten_khach_hang.toLowerCase().includes(shipperSearchText.value.toLowerCase())));
const filteredConsignees = computed(() => listKhachHang.value.filter(kh => kh.ten_khach_hang.toLowerCase().includes(consigneeSearchText.value.toLowerCase())));
const filteredNotify = computed(() => listKhachHang.value.filter(kh => kh.ten_khach_hang.toLowerCase().includes(notifySearchText.value.toLowerCase())));
const filteredUsers = computed(() => uniqueUsers.value.filter(u => u.toLowerCase().includes(userSearchText.value.toLowerCase())));
const filteredLoaiVDList = computed(() => listLoaiVanDon.filter(t => t.toLowerCase().includes(loaiVDSearchText.value.toLowerCase())));

const filteredVanDon = computed(() => {
  return listVanDon.value.filter(vd => {
    const matchSoVD = !searchSoVanDon.value || (vd.so_van_don && vd.so_van_don.toLowerCase().includes(searchSoVanDon.value.toLowerCase()));
    const matchSoVDGoc = !searchSoVanDonGoc.value || (vd.so_van_don_goc && vd.so_van_don_goc.toLowerCase().includes(searchSoVanDonGoc.value.toLowerCase()));
    const matchTenLH = !searchTenLoHang.value || (vd.ten_lo_hang && vd.ten_lo_hang.toLowerCase().includes(searchTenLoHang.value.toLowerCase()));
    const matchMa = !searchSoVanDon.value || (vd.ma_van_don && vd.ma_van_don.toString().includes(searchSoVanDon.value));

    const matchLoai = filterLoaiVanDon.value === 'ALL' || vd.loai_van_don === filterLoaiVanDon.value;
    const matchCangDi = !filterCangDi.value || vd.ma_cang_di === filterCangDi.value;
    const matchCangDen = !filterCangDen.value || vd.ma_cang_den === filterCangDen.value;
    const matchShipper = !filterShipper.value || vd.ma_nguoi_gui_hang === filterShipper.value;
    const matchConsignee = !filterConsignee.value || vd.ma_nguoi_nhan_hang === filterConsignee.value;
    const matchNotify = !filterNotify.value || vd.ma_ben_duoc_thong_bao === filterNotify.value;
    const matchSoCont = !searchSoCont.value || (vd.so_cont && vd.so_cont.toLowerCase().includes(searchSoCont.value.toLowerCase()));
    const matchSoChi = !searchSoChi.value || (vd.so_chi && vd.so_chi.toLowerCase().includes(searchSoChi.value.toLowerCase()));
    const matchNgayPH = !filterNgayPH.value || (vd.ngay_phat_hanh && vd.ngay_phat_hanh.includes(filterNgayPH.value));
    const matchUser = !filterUser.value || vd.nguoi_sua_doi === filterUser.value;

    return (matchSoVD || matchMa) && matchSoVDGoc && matchTenLH && 
           matchLoai && matchCangDi && matchCangDen && 
           matchShipper && matchConsignee && matchNotify &&
           matchSoCont && matchSoChi && matchNgayPH && matchUser;
  });
});

const paginatedVanDon = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value;
  const end = start + pageSize.value;
  return filteredVanDon.value.slice(start, end);
});

const totalPages = computed(() => Math.ceil(filteredVanDon.value.length / pageSize.value) || 1);

const uniqueUsers = computed(() => {
  const users = listVanDon.value.map(vd => vd.nguoi_sua_doi).filter(Boolean);
  return [...new Set(users)];
});

const resetFilters = () => {
  searchSoVanDon.value = '';
  searchSoVanDonGoc.value = '';
  searchTenLoHang.value = '';
  searchSoCont.value = '';
  searchSoChi.value = '';
  filterLoaiVanDon.value = 'ALL';
  filterCangDi.value = null;
  filterCangDen.value = null;
  filterShipper.value = null;
  filterConsignee.value = null;
  filterNotify.value = null;
  filterNgayPH.value = '';
  filterUser.value = null;
  polSearchText.value = '';
  podSearchText.value = '';
  shipperSearchText.value = '';
  consigneeSearchText.value = '';
  notifySearchText.value = '';
  userSearchText.value = '';
  loaiVDSearchText.value = '';
  currentPage.value = 1;
};

const selectSearchCang = (cang, target) => {
  if (target === 'pol') {
    filterCangDi.value = cang ? cang.ma_cang : null;
    polSearchText.value = cang ? cang.ten_cang : '';
    showPolDropdown.value = false;
  } else {
    filterCangDen.value = cang ? cang.ma_cang : null;
    podSearchText.value = cang ? cang.ten_cang : '';
    showPodDropdown.value = false;
  }
};

const selectSearchKh = (kh, target) => {
  if (target === 'shipper') {
    filterShipper.value = kh ? kh.ma_khach_hang : null;
    shipperSearchText.value = kh ? kh.ten_khach_hang : '';
    showShipperDropdown.value = false;
  } else if (target === 'consignee') {
    filterConsignee.value = kh ? kh.ma_khach_hang : null;
    consigneeSearchText.value = kh ? kh.ten_khach_hang : '';
    showConsigneeDropdown.value = false;
  } else {
    filterNotify.value = kh ? kh.ma_khach_hang : null;
    notifySearchText.value = kh ? kh.ten_khach_hang : '';
    showNotifyDropdown.value = false;
  }
};

const selectSearchUser = (user) => {
  filterUser.value = user;
  userSearchText.value = user || '';
  showUserDropdown.value = false;
};

const selectSearchLoai = (type) => {
  filterLoaiVanDon.value = type || 'ALL';
  loaiVDSearchText.value = type === 'ALL' ? '' : (type || '');
  showLoaiVDDropdown.value = false;
};

const prevPage = () => { if (currentPage.value > 1) currentPage.value--; };
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++; };

const showShipmentInfo = async (vd) => {
  selectedItem.value = vd;
  viewType.value = 'shipment';
  panelTitle.value = '📦 Thông tin lô hàng: ' + vd.ten_lo_hang;
  isPanelLoading.value = true;
  try {
    const res = await fetch(`${import.meta.env.VITE_API_URL}/lo-hang`);
    const data = await res.json();
    if (data.success) {
      selectedShipment.value = data.data.find(lh => lh.ma_lo_hang === vd.ma_lo_hang);
    } else {
      selectedShipment.value = null;
    }
  } catch (error) {
    selectedShipment.value = null;
  } finally {
    isPanelLoading.value = false;
  }
};

const handlePrintPDF = (id) => {
  window.open(`${import.meta.env.VITE_API_URL}/van-don/export-pdf/${id}`, '_blank');
};

const fetchData = async () => {
  isLoading.value = true;
  try {
    const res = await fetch(`${import.meta.env.VITE_API_URL}/van-don`);
    const data = await res.json();
    if (data.success) listVanDon.value = data.data;
  } catch (error) { console.error("Lỗi lấy dữ liệu Vận đơn!"); }
  finally { isLoading.value = false; }
};

const fetchReferences = async () => {
  try {
    const res = await fetch(`${import.meta.env.VITE_API_URL}/van-don/references`);
    const data = await res.json();
    if (data.success) {
      listCangBien.value = data.cang_bien;
      listKhachHang.value = data.khach_hang || [];
    }
  } catch (error) {
    console.error("Lỗi lấy danh mục!");
  }
};

const handleDelete = async (id) => {
  if (!confirm("Bạn có chắc muốn xóa vận đơn này?")) return;
  const user = JSON.parse(localStorage.getItem('sincere_user'));
  try {
    const res = await fetch(`${import.meta.env.VITE_API_URL}/van-don/delete`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ma_van_don: id, nguoi_sua_cuoi: user ? (user.id || user.ma_tai_khoan) : null })
    });
    const data = await res.json();
    if (data.success) { alert("Đã xóa vận đơn!"); fetchData(); }
    else { alert("Lỗi xóa: " + data.message); }
  } catch (e) { alert("Lỗi kết nối!"); }
};

watch([searchSoVanDon, searchSoVanDonGoc, searchTenLoHang, searchSoCont, searchSoChi, filterLoaiVanDon, filterCangDi, filterCangDen, filterShipper, filterConsignee, filterNotify, filterNgayPH, filterUser, pageSize], () => {
  currentPage.value = 1;
});

onMounted(() => { 
  fetchData(); 
  fetchReferences(); 
  window.addEventListener('click', (e) => {
    if (!e.target.closest('.combobox-wrapper')) {
      showPolDropdown.value = showPodDropdown.value = showShipperDropdown.value = showConsigneeDropdown.value = showNotifyDropdown.value = showUserDropdown.value = showLoaiVDDropdown.value = false;
    }
  });
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
.mini-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.mini-table th { text-align: left; padding: 8px; background: #f1f3f5; border-bottom: 2px solid #dee2e6; }
.mini-table td { padding: 8px; border-bottom: 1px solid #eee; }

.info-list { display: flex; flex-direction: column; gap: 15px; }
.info-item { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed #eee; padding-bottom: 8px; font-size: 14px; }
.info-item strong { color: #7f8c8d; font-weight: normal; }
.info-item span { color: #2c3e50; font-weight: 600; text-align: right; }

.row-selected { background-color: #f0f7ff !important; }
.action-btn.text-success { color: #27ae60; }

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

.table-card table th, .table-card table td { 
  white-space: nowrap; 
  padding: 12px 15px; 
}

.action-btn-no-mg { background: none; border: none; cursor: pointer; font-size: 16px; transition: 0.2s; padding: 0; margin: 0; display: flex; align-items: center; justify-content: center; height: 35px; width: 35px; }
.action-btn-no-mg:hover { transform: scale(1.2); }
.text-warning { color: #f39c12; }
</style>