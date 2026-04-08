<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">Quản lý Lô Hàng</h3>
    
    <div class="toolbar" style="display: flex; gap: 15px; flex-wrap: wrap;">
      <div class="search-box" style="flex: 1; min-width: 250px;">
        <input 
          type="text" 
          v-model="searchQuery" 
          placeholder="Tìm theo tên lô hàng, mã..."
          style="width: 100%; padding: 8px 12px; border-radius: 4px; border: 1px solid #ccc;"
        >
      </div>

      <select v-model="filterTrangThai" style="padding: 8px; border-radius: 4px; border: 1px solid #ccc; width: 200px;">
        <option value="ALL">- Tất cả Trạng thái -</option>
        <option v-for="st in listTrangThai" :key="st" :value="st">{{ st }}</option>
      </select>

      <button 
        @click="fetchData(); fetchReferences();" 
        style="padding: 8px 15px; border: 1px solid #ccc; border-radius: 4px; background: #fff; cursor: pointer; transition: 0.2s;"
        title="Tải lại dữ liệu"
      >
        🔄 Làm mới
      </button>

      <button class="btn btn-success" @click="openModal()">+ TẠO LÔ HÀNG MỚI</button>
    </div>

    <div v-if="isLoading" style="text-align: center; padding: 20px; color: #3498db;">
      Đang tải dữ liệu Lô hàng...
    </div>

    <div v-else class="table-card" style="margin-top: 15px;">
      <table>
        <thead>
          <tr>
            <th>Mã Lô</th>
            <th>Tên Lô Hàng</th>
            <th>Khách Hàng</th>
            <th>Điều kiện (Incoterms)</th>
            <th>Booking</th>
            <th>Trạng thái</th>
            <th style="text-align: center;">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="lh in filteredLoHang" :key="lh.ma_lo_hang">
            <td class="fw-bold">#{{ lh.ma_lo_hang }}</td>
            <td class="fw-bold" style="color: #2980b9;">{{ lh.ten_lo_hang }}</td>
            <td>{{ lh.ten_khach_hang || '---' }}</td>
            <td><span class="badge" style="background-color: #9b59b6; color: white;">{{ lh.dieu_kien_thuong_mai }}</span></td>
            <td>{{ lh.so_booking || 'Chưa gắn' }}</td>
            <td>
              <span class="badge" :class="statusClass(lh.trang_thai_lo_hang)">
                {{ lh.trang_thai_lo_hang }}
              </span>
            </td>
            <td style="text-align: center;">
              <button class="action-btn text-primary" @click="openModal(lh)" title="Cập nhật">✏️</button>
              <button class="action-btn text-danger" @click="handleDelete(lh.ma_lo_hang)" title="Xóa">🗑️</button>
            </td>
          </tr>
          <tr v-if="filteredLoHang.length === 0">
            <td colspan="7" style="text-align: center; padding: 20px; color: #7f8c8d;">
              Không tìm thấy lô hàng nào!
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div v-if="isModalOpen" class="modal-overlay">
      <div class="modal-content" style="max-width: 700px;">
        <h3>{{ formData.ma_lo_hang ? 'Cập nhật Lô Hàng' : 'Tạo Lô Hàng mới' }}</h3>
        <form @submit.prevent="saveLoHang">
          <div class="form-group">
            <label>Tên Lô Hàng *</label>
            <input v-model="formData.ten_lo_hang" required placeholder="Nhập tên lô hàng (VD: Lô hàng linh kiện Samsung)...">
          </div>

          <div style="display: flex; gap: 15px;">
            <div class="form-group" style="flex: 1;">
              <label>Khách Hàng *</label>
              <select v-model="formData.ma_khach_hang" required>
                <option :value="null">-- Chọn khách hàng --</option>
                <option v-for="kh in listKhachHang" :key="kh.ma_khach_hang" :value="kh.ma_khach_hang">
                  {{ kh.ten_khach_hang }}
                </option>
              </select>
            </div>
            <div class="form-group" style="flex: 1;">
              <label>Điều kiện thương mại</label>
              <select v-model="formData.dieu_kien_thuong_mai">
                <option v-for="dk in listDieuKien" :key="dk" :value="dk">{{ dk }}</option>
              </select>
            </div>
          </div>

          <div style="display: flex; gap: 15px;">
            <div class="form-group" style="flex: 1;">
              <label>Booking liên kết</label>
              <select v-model="formData.ma_booking">
                <option :value="null">-- Không có booking --</option>
                <option v-for="bk in listBooking" :key="bk.ma_booking" :value="bk.ma_booking">
                  {{ bk.so_booking }} ({{ bk.ten_con_tau || 'N/A' }})
                </option>
              </select>
            </div>
            <div class="form-group" style="flex: 1;">
              <label>Trạng thái lô hàng</label>
              <select v-model="formData.trang_thai_lo_hang">
                <option v-for="tt in listTrangThai" :key="tt" :value="tt">{{ tt }}</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label>Nguồn gốc / Ghi chú</label>
            <input v-model="formData.nguon_goc" placeholder="VD: Nhập khẩu từ Thượng Hải">
          </div>

          <div class="modal-actions">
            <button type="button" class="btn-cancel" @click="isModalOpen = false">Hủy</button>
            <button type="submit" class="btn-save" :disabled="isSaving">
              {{ isSaving ? 'Đang lưu...' : 'Lưu Lô Hàng' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';

const listLoHang = ref([]);
const listKhachHang = ref([]);
const listBooking = ref([]);
const isLoading = ref(true);
const isSaving = ref(false);
const isModalOpen = ref(false);
const searchQuery = ref('');
const filterTrangThai = ref('ALL');

const listDieuKien = ['FOB', 'CIF', 'EXW', 'DAP', 'DDP', 'CFR'];
const listTrangThai = ['Mới tạo', 'Đang chờ xử lý', 'Đang vận chuyển', 'Đã thông quan', 'Hoàn tất', 'Hủy'];

const formData = ref({
  ma_lo_hang: null, ten_lo_hang: '', dieu_kien_thuong_mai: 'FOB',
  trang_thai_lo_hang: 'Mới tạo', nguon_goc: '',
  ma_booking: null, ma_khach_hang: null, nguoi_sua_cuoi: null
});

const statusClass = (status) => {
  if (['Hoàn tất', 'Đang vận chuyển', 'Đã thông quan'].includes(status)) return 'badge-active';
  if (status === 'Hủy') return 'badge-locked';
  return 'badge-warning';
};

const filteredLoHang = computed(() => {
  return listLoHang.value.filter(lh => {
    const search = searchQuery.value.toLowerCase();
    const matchSearch = !search || 
      (lh.ten_lo_hang && lh.ten_lo_hang.toLowerCase().includes(search)) || 
      (lh.ma_lo_hang && lh.ma_lo_hang.toString().includes(search));
      
    const matchTrangThai = filterTrangThai.value === 'ALL' || lh.trang_thai_lo_hang === filterTrangThai.value;
    return matchSearch && matchTrangThai;
  });
});

const fetchReferences = async () => {
  try {
    const res = await fetch('http://127.0.0.1:8000/api/lo-hang/references');
    const data = await res.json();

    if (data.success) {
      listKhachHang.value = data.khach_hang;
      listBooking.value = data.booking;
    }
  } catch (error) {
    console.error("Lỗi lấy dữ liệu tham chiếu");
  }
};

const fetchData = async () => {
  isLoading.value = true;
  try {
    const res = await fetch('http://127.0.0.1:8000/api/lo-hang');
    const data = await res.json();
    if (data.success) listLoHang.value = data.data;
  } catch (error) { console.error("Lỗi lấy dữ liệu Lô hàng!"); }
  finally { isLoading.value = false; }
};

const openModal = async (lh = null) => {
  await fetchReferences();
  if (lh) {
    formData.value = { ...lh };
  } else {
    formData.value = {
      ma_lo_hang: null, ten_lo_hang: '', dieu_kien_thuong_mai: 'FOB',
      trang_thai_lo_hang: 'Mới tạo', nguon_goc: '',
      ma_booking: null, ma_khach_hang: null, nguoi_sua_cuoi: null
    };
  }
  isModalOpen.value = true;
};

const saveLoHang = async () => {
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
      alert(data.message);
      isModalOpen.value = false;
      fetchData();
    } else { alert("Lỗi: " + data.message); }
  } catch (e) { alert("Lỗi server!"); }
  finally { isSaving.value = false; }
};

const handleDelete = async (id) => {
  if (!confirm("Bạn có chắc muốn xóa lô hàng này?")) return;
  const user = JSON.parse(localStorage.getItem('sincere_user'));
  try {
    const res = await fetch('http://127.0.0.1:8000/api/lo-hang/delete', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ma_lo_hang: id, nguoi_sua_cuoi: user ? (user.id || user.ma_tai_khoan) : null })
    });
    const data = await res.json();
    if (data.success) { alert("Đã xóa lô hàng!"); fetchData(); }
    else { alert("Lỗi: " + data.message); }
  } catch (e) { alert("Lỗi kết nối!"); }
};

onMounted(() => { fetchData(); fetchReferences(); });
</script>

<style scoped src="../../assets/quanlytaikhoan.css"></style>
<style scoped>
.badge-warning { background-color: #f39c12; color: white; }
</style>