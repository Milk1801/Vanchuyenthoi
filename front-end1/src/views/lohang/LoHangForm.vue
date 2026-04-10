<template>
  <div class="lo-hang-form-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
      <h3 style="margin: 0; color: #2c3e50;">
        {{ formData.ma_lo_hang ? '📦 Cập nhật Lô Hàng #' + formData.ma_lo_hang : '📦 Tạo Lô Hàng mới' }}
      </h3>
      <button @click="handleCancel" class="btn-cancel" type="button">✖ Đóng</button>
    </div>

    <!-- Tab Navigation -->
    <div class="tabs">
      <button :class="{ active: activeTab === 'info' }" @click="activeTab = 'info'">📄 1. Thông tin chung</button>
      <button :class="{ active: activeTab === 'details' }" @click="activeTab = 'details'">📋 2. Chi tiết hàng hóa ({{ listDetails.length }})</button>
    </div>

    <div class="table-card" style="padding: 25px; min-height: 400px;">
      <!-- Tab 1: Thông tin Lô hàng -->
      <div v-show="activeTab === 'info'">
        <form @submit.prevent="handleSaveStep1">
          <div class="form-group">
            <label>Tên Lô Hàng *</label>
            <input v-model="formData.ten_lo_hang" required placeholder="VD: Lô hàng linh kiện điện tử Samsung tháng 4">
          </div>
          <div style="display: flex; gap: 20px;">
            <div class="form-group" style="flex: 1;">
              <label>Khách Hàng *</label>
              <select v-model="formData.ma_khach_hang" required>
                <option :value="null">-- Chọn khách hàng --</option>
                <option v-for="kh in listKhachHang" :key="kh.ma_khach_hang" :value="kh.ma_khach_hang">{{ kh.ten_khach_hang }}</option>
              </select>
            </div>
            <div class="form-group" style="flex: 1;">
              <label>Điều kiện thương mại (Incoterms)</label>
              <select v-model="formData.dieu_kien_thuong_mai">
                <option v-for="dk in ['FOB', 'CIF', 'EXW', 'DAP', 'DDP', 'CFR']" :key="dk" :value="dk">{{ dk }}</option>
              </select>
            </div>
          </div>
          <div style="display: flex; gap: 20px;">
            <div class="form-group" style="flex: 1;">
              <label>Booking liên kết</label>
              <select v-model="formData.ma_booking">
                <option :value="null">-- Không có booking --</option>
                <option v-for="bk in listBooking" :key="bk.ma_booking" :value="bk.ma_booking">{{ bk.so_booking }}</option>
              </select>
            </div>
            <div class="form-group" style="flex: 1;">
              <label>Trạng thái</label>
              <select v-model="formData.trang_thai_lo_hang">
                <option v-for="tt in ['Mới tạo', 'Đang chờ xử lý', 'Đang vận chuyển', 'Đã thông quan', 'Hoàn tất', 'Hủy']" :key="tt" :value="tt">{{ tt }}</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label>Nguồn gốc / Ghi chú</label>
            <textarea v-model="formData.nguon_goc" rows="3" style="width: 100%; border: 1px solid #ccc; border-radius: 4px; padding: 10px;"></textarea>
          </div>
          <div style="text-align: right; margin-top: 20px;">
            <button type="submit" class="btn-save">
              {{ isSaving ? 'Đang lưu...' : (formData.ma_lo_hang ? 'Cập nhật & Tiếp tục ➔' : 'Khởi tạo lô hàng & Tiếp tục ➔') }}
            </button>
          </div>
        </form>
      </div>

      <!-- Tab 2: Chi tiết hàng hóa (Packing List) -->
      <div v-show="activeTab === 'details'">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
          <h4 style="margin: 0; color: #34495e;">Danh sách hàng hóa trong lô</h4>
          <button type="button" class="btn-success" @click="addDetail">➕ Thêm chi tiết hàng</button>
        </div>
        
        <table class="detail-table" style="width: 100%; border-collapse: collapse;">
          <thead style="background: #f1f3f5;">
            <tr>
              <th>Tên hàng</th>
              <th>Loại hàng</th>
              <th>Số lượng</th>
              <th>Số kiện</th>
              <th>ĐVT</th>
              <th>Thể tích</th>
              <th>Trọng lượng</th>
              <th>Giá cả</th>
              <th>Người sửa cuối</th>
              <th style="text-align: center;">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in listDetails" :key="index">
              <td class="fw-bold">{{ item.ten_hang }}</td>
              <td>{{ item.ten_hang_hoa || getHangHoaName(item.ma_hang_hoa) }}</td>
              <td style="text-align: center;">{{ item.so_luong }}</td>
              <td style="text-align: center;">{{ item.so_kien }}</td>
              <td>{{ item.ten_don_vi_tinh || getDonViTinhName(item.ma_don_vi_tinh) }}</td>
              <td style="text-align: center;">{{ item.the_tich }}</td>
              <td style="text-align: center;">{{ item.trong_luong }}</td>
              <td style="text-align: right;">{{ item.gia_ca }}</td>
              <td style="color: #2980b9;">{{ item.nguoi_sua_cuoi }}</td>
              <td style="text-align: center;">
                <button @click="editDetail(index)" class="action-btn text-primary" title="Sửa">✏️</button>
                <button @click="deleteDetail(index)" class="action-btn text-danger" title="Xóa">🗑️</button>
              </td>
            </tr>
            <tr v-if="listDetails.length === 0">
              <td colspan="10" style="text-align: center; padding: 30px; color: #e74c3c; font-weight: bold;">
                ⚠️ Lô hàng chưa có chi tiết hàng hóa. Vui lòng thêm ít nhất 1 mục!
              </td>
            </tr>
          </tbody>
        </table>

        <div class="modal-actions" style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
          <button class="btn-cancel" @click="activeTab = 'info'" type="button" style="background: #95a5a6; color: white; border: none;">⬅ Quay lại</button>
          <button class="btn-save" @click="handleSaveAll" :disabled="isSaving" style="margin-left: 10px;">
            {{ isSaving ? 'Đang xử lý...' : 'HOÀN TẤT & ĐÓNG 💾' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modal thêm/sửa chi tiết hàng hóa -->
    <div v-if="isDetailModalOpen" class="modal-overlay">
      <div class="modal-content" style="max-width: 700px; width: 90%;">
        <h3 style="margin-top: 0; color: #2c3e50; border-bottom: 2px solid #eee; padding-bottom: 10px;">
          {{ detailFormData.ma_chi_tiet_lo_hang ? '✏️ Cập nhật hàng hóa' : '➕ Thêm hàng hóa mới' }}
        </h3>
        
        <form @submit.prevent="saveDetailItem">
          <div class="form-group">
            <label>Tên hàng / Mô tả chi tiết *</label>
            <input v-model="detailFormData.ten_hang" required placeholder="VD: Máy giặt Toshiba AW-DUK1150HV">
          </div>

          <div style="display: flex; gap: 15px;">
            <div class="form-group" style="flex: 1;">
              <label>Loại hàng hóa (Danh mục)</label>
              <select v-model="detailFormData.ma_hang_hoa">
                <option :value="null">-- Chọn loại hàng --</option>
                <option v-for="h in listHangHoa" :key="h.ma_hang_hoa" :value="h.ma_hang_hoa">{{ h.ten_hang_hoa }}</option>
              </select>
            </div>
            <div class="form-group" style="flex: 1;">
              <label>Đơn vị tính *</label>
              <select v-model="detailFormData.ma_don_vi_tinh" required>
                <option :value="null">-- Chọn ĐVT --</option>
                <option v-for="dvt in listDonViTinh" :key="dvt.ma_don_vi_tinh" :value="dvt.ma_don_vi_tinh">{{ dvt.ten_don_vi_tinh }}</option>
              </select>
            </div>
          </div>

          <div style="display: flex; gap: 15px;">
            <div class="form-group" style="flex: 1;">
              <label>Số lượng *</label>
              <input type="number" v-model="detailFormData.so_luong" required min="1">
            </div>
            <div class="form-group" style="flex: 1;">
              <label>Số kiện (Packages)</label>
              <input type="number" v-model="detailFormData.so_kien" min="0">
            </div>
            <div class="form-group" style="flex: 1;">
              <label>Giá cả (VNĐ/USD)</label>
              <input type="number" v-model="detailFormData.gia_ca" min="0" step="0.01">
            </div>
          </div>

          <div style="display: flex; gap: 15px;">
            <div class="form-group" style="flex: 1;">
              <label>Trọng lượng (kg) *</label>
              <input type="number" v-model="detailFormData.trong_luong" required min="0" step="0.01">
            </div>
            <div class="form-group" style="flex: 1;">
              <label>Thể tích (CBM)</label>
              <input type="number" v-model="detailFormData.the_tich" min="0" step="0.0001">
            </div>
          </div>

          <div class="modal-actions" style="margin-top: 20px; text-align: right;">
            <button type="button" class="btn-cancel" @click="isDetailModalOpen = false" style="margin-right: 10px;">Hủy bỏ</button>
            <button type="submit" class="btn-save" :disabled="isSavingDetail">
              {{ isSavingDetail ? 'Đang lưu...' : 'Lưu thông tin' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { useRouter, useRoute } from 'vue-router';

const router = useRouter();
const route = useRoute();
const activeTab = ref('info');
const isSaving = ref(false);
const isSavingDetail = ref(false);
const isDetailModalOpen = ref(false);
const editingIndex = ref(-1);

const listKhachHang = ref([]);
const listBooking = ref([]);
const listDetails = ref([]); // Đây là mảng đệm (buffer) để hiển thị và sửa local
const listDeletedDetails = ref([]); // Lưu ID của các chi tiết bị xóa để gửi lên server khi ấn Hoàn tất
const listHangHoa = ref([]);
const listDonViTinh = ref([]);

const formData = ref({
  ma_lo_hang: null, ten_lo_hang: '', dieu_kien_thuong_mai: 'FOB',
  trang_thai_lo_hang: 'Mới tạo', nguon_goc: '',
  ma_booking: null, ma_khach_hang: null, nguoi_sua_cuoi: null
});

const detailFormData = ref({
  ma_chi_tiet_lo_hang: null, ten_hang: '', so_luong: 1, so_kien: 0,
  the_tich: 0, trong_luong: 0, gia_ca: 0, ma_hang_hoa: null,
  ma_lo_hang: null, ma_don_vi_tinh: null, nguoi_sua_cuoi: null
});

// Ánh xạ tên từ ID để hiển thị bảng
const getHangHoaName = (id) => listHangHoa.value.find(x => x.ma_hang_hoa === id)?.ten_hang_hoa || '---';
const getDonViTinhName = (id) => listDonViTinh.value.find(x => x.ma_don_vi_tinh === id)?.ten_don_vi_tinh || '---';

const addDetail = () => {
  editingIndex.value = -1;
  detailFormData.value = {
    ma_chi_tiet_lo_hang: null, ten_hang: '', so_luong: 1, so_kien: 0,
    the_tich: 0, trong_luong: 0, gia_ca: 0, ma_hang_hoa: null,
    ma_lo_hang: formData.value.ma_lo_hang, ma_don_vi_tinh: null
  };
  isDetailModalOpen.value = true;
};

const editDetail = (index) => {
  editingIndex.value = index;
  detailFormData.value = { ...listDetails.value[index] };
  isDetailModalOpen.value = true;
};

// Chỉ lưu vào mảng local, chưa gửi API
const saveDetailItem = () => {
  if (editingIndex.value > -1) {
    listDetails.value[editingIndex.value] = { ...detailFormData.value };
  } else {
    listDetails.value.push({ ...detailFormData.value });
  }
  isDetailModalOpen.value = false;
};

const fetchDetailReferences = async () => {
  const res = await fetch('http://127.0.0.1:8000/api/chi-tiet-lo-hang/references');
  const data = await res.json();
  if (data.success) {
    listHangHoa.value = data.hang_hoa;
    listDonViTinh.value = data.don_vi_tinh;
  }
};

const deleteDetail = (index) => {
  const item = listDetails.value[index];
  if (item.ma_chi_tiet_lo_hang) {
    listDeletedDetails.value.push(item.ma_chi_tiet_lo_hang);
  }
  listDetails.value.splice(index, 1);
};

const fetchReferences = async () => {
  const id = route.params.id || '';
  const res = await fetch(`http://127.0.0.1:8000/api/lo-hang/references?ma_lo_hang=${id}`);
  const data = await res.json();
  if (data.success) {
    listKhachHang.value = data.khach_hang;
    listBooking.value = data.booking;
  }
};

const fetchDetails = async (ma_lo_hang) => {
  if (!ma_lo_hang) return;
  try {
    const resDetail = await fetch(`http://127.0.0.1:8000/api/chi-tiet-lo-hang?ma_lo_hang=${ma_lo_hang}`);
    const dataDetail = await resDetail.json();
    if (dataDetail.success) listDetails.value = dataDetail.data;
  } catch (error) { console.error(error); }
};

const fetchData = async (id) => {
  try {
    const res = await fetch(`http://127.0.0.1:8000/api/lo-hang`);
    const data = await res.json();
    if (data.success) {
      const found = data.data.find(x => String(x.ma_lo_hang) === String(id));
      if (found) {
        formData.value = { ...found };
        fetchDetails(found.ma_lo_hang);
      }
    }
  } catch (error) { console.error(error); }
};

const handleSaveStep1 = async () => {
  isSaving.value = true;
  const user = JSON.parse(localStorage.getItem('sincere_user'));
  formData.value.nguoi_sua_cuoi = user ? (user.id || user.ma_tai_khoan) : null;
  try {
    const res = await fetch('http://127.0.0.1:8000/api/lo-hang/save', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(formData.value)
    });
    const data = await res.json();
    if (data.success) {
      if (!formData.value.ma_lo_hang && data.ma_lo_hang) {
        formData.value.ma_lo_hang = data.ma_lo_hang;
      }
      activeTab.value = 'details';
    } else alert(data.message);
  } catch (e) { alert("Lỗi server!"); }
  finally { isSaving.value = false; }
};

const handleSaveAll = async () => {
  isSaving.value = true;
  const user = JSON.parse(localStorage.getItem('sincere_user'));
  const userId = user ? (user.id || user.ma_tai_khoan) : null;
  formData.value.nguoi_sua_cuoi = userId;

  try {
    // 1. Lưu thông tin Lô hàng (Tab 1) trước
    const resLH = await fetch('http://127.0.0.1:8000/api/lo-hang/save', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(formData.value)
    });
    const dataLH = await resLH.json();
    if (!dataLH.success) throw new Error(dataLH.message || "Không thể lưu thông tin lô hàng");

    // Lấy ID lô hàng: Ưu tiên mã có sẵn, nếu không lấy từ phản hồi server (trường hợp tạo mới)
    const maLoHang = formData.value.ma_lo_hang || dataLH.ma_lo_hang || dataLH.id;
    if (!maLoHang) throw new Error("Hệ thống không trả về mã lô hàng mới.");
    
    // Cập nhật lại vào formData để đồng bộ trạng thái
    formData.value.ma_lo_hang = maLoHang;

    // 2. Xóa các chi tiết hàng đã được đánh dấu xóa
    for (const ma_ct of listDeletedDetails.value) {
      await fetch('http://127.0.0.1:8000/api/chi-tiet-lo-hang/delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ma_chi_tiet_lo_hang: ma_ct, nguoi_sua_cuoi: userId }) // Truyền userId khi xóa
      });
    }

    // 3. Gán mã lô hàng vào từng chi tiết và lưu
    for (let i = 0; i < listDetails.value.length; i++) {
      const item = listDetails.value[i];
      // Đảm bảo ma_lo_hang được gán đúng từ lô hàng vừa lưu/sửa
      const detailPayload = {
        ...item,
        ma_lo_hang: maLoHang, 
        nguoi_sua_cuoi: userId 
      };
      
      const resCT = await fetch('http://127.0.0.1:8000/api/chi-tiet-lo-hang/save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(detailPayload)
      });
      const dataCT = await resCT.json();
      if (!dataCT.success) throw new Error(dataCT.message);

      // Nếu là chi tiết mới, cập nhật ma_chi_tiet_lo_hang từ phản hồi của server
      if (!item.ma_chi_tiet_lo_hang && dataCT.ma_chi_tiet_lo_hang) {
        listDetails.value[i].ma_chi_tiet_lo_hang = dataCT.ma_chi_tiet_lo_hang;
      }
    }

    router.push('/lo-hang/thong-tin-lo-hang');
  } catch (e) {
    alert("Lỗi khi lưu dữ liệu: " + e.message);
  } finally { isSaving.value = false; }
};

const handleCancel = () => {
    if(confirm("Dữ liệu chưa lưu có thể bị mất. Bạn có muốn đóng không?")) {
        router.push('/lo-hang/thong-tin-lo-hang');
    }
}

onMounted(async () => {
  await fetchReferences();
  await fetchDetailReferences();
  const id = route.params.id;
  if (id) await fetchData(id);
});
</script>

<style scoped src="../../assets/quanlytaikhoan.css"></style>
<style scoped>
.tabs { display: flex; gap: 5px; margin-bottom: -1px; }
.tabs button {
  padding: 10px 20px; border: 1px solid #ddd; background: #f8f9fa;
  border-bottom: none; border-radius: 8px 8px 0 0; cursor: pointer;
  font-weight: bold; color: #7f8c8d; transition: 0.2s;
}
.tabs button.active { background: white; color: #3498db; border-top: 3px solid #3498db; }

.btn-cancel, .btn-save, .btn-success {
  height: 40px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0 20px;
  font-size: 14px;
  box-sizing: border-box;
}

.detail-table th, .detail-table td { padding: 12px; border: 1px solid #eee; text-align: left; }
.action-btn {
  background: none; border: none; cursor: pointer; font-size: 16px; margin: 0 5px; transition: 0.2s;
}
.action-btn:hover { transform: scale(1.2); }

.modal-overlay {
  position: fixed; top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000;
}
.modal-content {
  background: white; padding: 25px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}
.form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #34495e; }
.form-group input, .form-group select {
  width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box;
}
</style>
