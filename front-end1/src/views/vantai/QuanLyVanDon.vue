<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">Quản lý Vận đơn (Bill of Lading)</h3>
    
    <div class="toolbar" style="display: flex; gap: 15px; flex-wrap: wrap;">
      <div class="search-box" style="flex: 1; min-width: 250px;">
        <input 
          type="text" 
          v-model="searchQuery" 
          placeholder="Tìm theo tên vận đơn, mã..."
          style="width: 100%; padding: 8px 12px; border-radius: 4px; border: 1px solid #ccc;"
        >
      </div>

      <select v-model="filterLoaiVanDon" style="padding: 8px; border-radius: 4px; border: 1px solid #ccc; width: 200px;">
        <option value="ALL">- Tất cả Loại vận đơn -</option>
        <option v-for="type in listLoaiVanDon" :key="type" :value="type">{{ type }}</option>
      </select>

      <button 
        @click="fetchData(); fetchReferences();" 
        style="padding: 8px 15px; border: 1px solid #ccc; border-radius: 4px; background: #fff; cursor: pointer; transition: 0.2s;"
        title="Tải lại dữ liệu"
      >
        🔄 Làm mới
      </button>

      <button class="btn btn-success" @click="openModal()">+ TẠO VẬN ĐƠN MỚI</button>
    </div>

    <div v-if="isLoading" style="text-align: center; padding: 20px; color: #3498db;">
      Đang tải dữ liệu Vận đơn...
    </div>

    <div v-else class="table-card" style="margin-top: 15px;">
      <table>
        <thead>
          <tr>
            <th>Mã Vận Đơn</th>
            <th>Số Vận Đơn</th>
            <th>Loại Vận Đơn</th>
            <th>Lô hàng</th>
            <th>POL / POD</th>
            <th>Ngày PH</th>
            <th>Cont/Seal</th>
            <th style="text-align: center;">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="vd in filteredVanDon" :key="vd.ma_van_don">
            <td class="fw-bold">#{{ vd.ma_van_don }}</td>
            <td class="fw-bold" style="color: #2980b9;">{{ vd.so_van_don }}</td>
            <td><span class="badge badge-active">{{ vd.loai_van_don }}</span></td>
            <td>{{ vd.ten_lo_hang || '---' }}</td>
            <td><strong>{{ vd.ten_cang_di }}</strong> ➔ <strong>{{ vd.ten_cang_den }}</strong></td>
            <td>{{ formatDate(vd.ngay_phat_hanh) }}</td>
            <td>
              <span style="font-size: 11px;">
                C: {{ vd.so_cont || 'N/A' }}<br>S: {{ vd.so_chi || 'N/A' }}
              </span>
            </td>
            <td style="text-align: center;">
              <button class="action-btn text-primary" @click="openModal(vd)" title="Cập nhật">✏️</button>
              <button class="action-btn text-danger" @click="handleDelete(vd.ma_van_don)" title="Xóa">🗑️</button>
            </td>
          </tr>
          <tr v-if="filteredVanDon.length === 0">
            <td colspan="8" style="text-align: center; padding: 20px; color: #7f8c8d;">
              Không tìm thấy vận đơn nào!
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div v-if="isModalOpen" class="modal-overlay">
      <div class="modal-content" style="max-width: 700px;">
        <h3>{{ formData.ma_van_don ? 'Cập nhật Vận đơn' : 'Tạo Vận đơn mới' }}</h3>
        <form @submit.prevent="saveVanDon">
          <div style="display: flex; gap: 15px;">
            <div class="form-group" style="flex: 1;">
              <label>Số Vận Đơn *</label>
              <input v-model="formData.so_van_don" required placeholder="VD: HPH2024001">
            </div>
            <div class="form-group" style="flex: 1;">
              <label>Loại Vận Đơn</label>
              <select v-model="formData.loai_van_don">
                <option v-for="type in listLoaiVanDon" :key="type" :value="type">{{ type }}</option>
              </select>
            </div>
          </div>

          <div style="display: flex; gap: 15px;">
            <div class="form-group" style="flex: 1;">
              <label>Số Vận Đơn Gốc</label>
              <input v-model="formData.so_van_don_goc" placeholder="Nếu có">
            </div>
            <div class="form-group" style="flex: 1;">
              <label>Ngày Phát Hành</label>
              <input type="datetime-local" v-model="formData.ngay_phat_hanh">
            </div>
          </div>

          <div style="display: flex; gap: 15px;">
            <div class="form-group" style="flex: 1;">
              <label>Người Gửi Hàng</label>
              <select v-model="formData.ma_nguoi_gui_hang">
                <option :value="null">-- Chọn người gửi --</option>
                <option v-for="kh in listKhachHang" :key="kh.ma_khach_hang" :value="kh.ma_khach_hang">
                  {{ kh.ten_khach_hang }}
                </option>
              </select>
            </div>
            <div class="form-group" style="flex: 1;">
              <label>Người Nhận Hàng</label>
              <select v-model="formData.ma_nguoi_nhan_hang">
                <option :value="null">-- Chọn người nhận --</option>
                <option v-for="kh in listKhachHang" :key="kh.ma_khach_hang" :value="kh.ma_khach_hang">
                  {{ kh.ten_khach_hang }}
                </option>
              </select>
            </div>
          </div>

          <div style="display: flex; gap: 15px;">
            <div class="form-group" style="flex: 1;">
              <label>Cảng Đi (POL)</label>
              <select v-model="formData.ma_cang_di">
                <option :value="null">-- Chọn cảng --</option>
                <option v-for="c in listCangBien" :key="c.ma_cang" :value="c.ma_cang">
                  {{ c.ten_cang }}
                </option>
              </select>
            </div>
            <div class="form-group" style="flex: 1;">
              <label>Cảng Đến (POD)</label>
              <select v-model="formData.ma_cang_den">
                <option :value="null">-- Chọn cảng --</option>
                <option v-for="c in listCangBien" :key="c.ma_cang" :value="c.ma_cang">
                  {{ c.ten_cang }}
                </option>
              </select>
            </div>
          </div>

          <div style="display: flex; gap: 15px;">
            <div class="form-group" style="flex: 1;">
              <label>Lô Hàng Liên Kết *</label>
              <select v-model="formData.ma_lo_hang" required>
                <option :value="null">-- Chọn lô hàng --</option>
                <option v-for="lh in listLoHang" :key="lh.ma_lo_hang" :value="lh.ma_lo_hang">
                  {{ lh.ten_lo_hang }}
                </option>
              </select>
            </div>
            <div class="form-group" style="flex: 1;">
              <label>Phương Thức Đóng Cont</label>
              <select v-model="formData.phuong_thuc_dong_cont">
                <option value="FCL">FCL (Nguyên cont)</option>
                <option value="LCL">LCL (Hàng lẻ)</option>
              </select>
            </div>
          </div>

          <div style="display: flex; gap: 15px;">
            <div class="form-group" style="flex: 1;">
              <label>Số Container</label>
              <input v-model="formData.so_cont" placeholder="VD: TCNU1234567">
            </div>
            <div class="form-group" style="flex: 1;">
              <label>Số Chì (Seal)</label>
              <input v-model="formData.so_chi" placeholder="VD: S123456">
            </div>
          </div>

          <div class="form-group">
            <label>Điều Kiện Cước</label>
            <select v-model="formData.dieu_kien_cuoc">
              <option value="Freight Prepaid">Freight Prepaid (Cước trả trước)</option>
              <option value="Freight Collect">Freight Collect (Cước trả sau)</option>
            </select>
          </div>

          <div class="modal-actions">
            <button type="button" class="btn-cancel" @click="isModalOpen = false">Hủy</button>
            <button type="submit" class="btn-save" :disabled="isSaving">
              {{ isSaving ? 'Đang lưu...' : 'Lưu Vận Đơn' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';

const listVanDon = ref([]);
const listKhachHang = ref([]);
const listCangBien = ref([]);
const listLoHang = ref([]);
const isLoading = ref(true);
const isSaving = ref(false);
const isModalOpen = ref(false);
const searchQuery = ref('');
const filterLoaiVanDon = ref('ALL');

const listLoaiVanDon = ['Original B/L', 'Surrendered B/L', 'Seaway Bill'];

const formData = ref({
  ma_van_don: null, loai_van_don: 'Original B/L', ngay_phat_hanh: '',
  so_van_don_goc: '', so_van_don: '', so_cont: '', so_chi: '',
  phuong_thuc_dong_cont: 'FCL', dieu_kien_cuoc: 'Freight Prepaid',
  ma_nguoi_gui_hang: null, ma_nguoi_nhan_hang: null, ma_ben_duoc_thong_bao: null,
  ma_cang_di: null, ma_cang_den: null, ma_lo_hang: null, nguoi_sua_cuoi: null
});

const formatDate = (dateString) => {
  if (!dateString) return "---";
  return new Date(dateString).toLocaleDateString('vi-VN');
};

const formatForInput = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toISOString().slice(0, 16);
};

const filteredVanDon = computed(() => {
  return listVanDon.value.filter(vd => {
    const search = searchQuery.value.toLowerCase();
    const matchSearch = !search || 
      (vd.so_van_don && vd.so_van_don.toLowerCase().includes(search)) || 
      (vd.ma_van_don && vd.ma_van_don.toString().includes(search));
      
    const matchLoai = filterLoaiVanDon.value === 'ALL' || vd.loai_van_don === filterLoaiVanDon.value;
    return matchSearch && matchLoai;
  });
});

const fetchReferences = async () => {
  try {
    const res = await fetch('http://127.0.0.1:8000/api/van-don/references');
    const data = await res.json();
    if (data.success) {
      listKhachHang.value = data.khach_hang;
      listCangBien.value = data.cang_bien;
      listLoHang.value = data.lo_hang;
    }
  } catch (error) { console.error("Lỗi lấy dữ liệu tham chiếu"); }
};

const fetchData = async () => {
  isLoading.value = true;
  try {
    const res = await fetch('http://127.0.0.1:8000/api/van-don');
    const data = await res.json();
    if (data.success) listVanDon.value = data.data;
  } catch (error) { console.error("Lỗi lấy dữ liệu Vận đơn!"); }
  finally { isLoading.value = false; }
};

const openModal = async (vd = null) => {
  await fetchReferences();
  if (vd) {
    formData.value = { 
      ...vd,
      ngay_phat_hanh: formatForInput(vd.ngay_phat_hanh)
    };
  } else {
    formData.value = {
      ma_van_don: null, loai_van_don: 'Master B/L', ngay_phat_hanh: '',
      so_van_don_goc: '', so_van_don: '', so_cont: '', so_chi: '',
      phuong_thuc_dong_cont: 'FCL', dieu_kien_cuoc: 'Freight Prepaid',
      ma_nguoi_gui_hang: null, ma_nguoi_nhan_hang: null, ma_ben_duoc_thong_bao: null,
      ma_cang_di: null, ma_cang_den: null, ma_lo_hang: null, nguoi_sua_cuoi: null
    };
  }
  isModalOpen.value = true;
};

const saveVanDon = async () => {
  isSaving.value = true;
  const user = JSON.parse(localStorage.getItem('sincere_user'));
  formData.value.nguoi_sua_cuoi = user ? (user.id || user.ma_tai_khoan) : null;
  try {
    const res = await fetch('http://127.0.0.1:8000/api/van-don/save', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(formData.value)
    });
    const data = await res.json();
    if (data.success) { alert(data.message); isModalOpen.value = false; fetchData(); }
    else { alert("Lỗi: " + data.message); }
  } catch (e) { alert("Lỗi server!"); }
  finally { isSaving.value = false; }
};

const handleDelete = async (id) => {
  if (!confirm("Bạn có chắc muốn xóa lô hàng này?")) return;
  const user = JSON.parse(localStorage.getItem('sincere_user'));
  try {
    const res = await fetch('http://127.0.0.1:8000/api/van-don/delete', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ma_van_don: id, nguoi_sua_cuoi: user ? (user.id || user.ma_tai_khoan) : null })
    });
    const data = await res.json();
    if (data.success) { alert("Đã xóa vận đơn!"); fetchData(); }
    else { alert("Lỗi xóa: " + data.message); }
  } catch (e) { alert("Lỗi kết nối!"); }
};

onMounted(() => { fetchData(); fetchReferences(); });
</script>

<style scoped src="../../assets/quanlytaikhoan.css"></style>
<style scoped>
.badge-warning { background-color: #f39c12; color: white; }
</style>