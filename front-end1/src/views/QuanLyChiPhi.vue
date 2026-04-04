<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">Quản lý Chi Phí & Thanh Toán (4.1)</h3>
    
    <div class="toolbar" style="display: flex; gap: 15px; flex-wrap: wrap;">
      <div class="search-box" style="flex: 1; min-width: 250px;">
        <input 
          type="text" 
          v-model="searchQuery" 
          placeholder="Tìm kiếm mã CP, tên chi phí, lô hàng..."
          style="width: 100%; padding: 8px 12px; border-radius: 4px; border: 1px solid #ccc;"
        >
      </div>

      <select v-model="filterLoai" style="padding: 8px; border-radius: 4px; border: 1px solid #ccc;">
        <option value="ALL">- Tất cả Giao dịch -</option>
        <option value="THU">Chỉ khoản Phải Thu (AR)</option>
        <option value="CHI">Chỉ khoản Phải Chi (AP)</option>
      </select>

      <select v-model="filterTrangThai" style="padding: 8px; border-radius: 4px; border: 1px solid #ccc;">
        <option value="ALL">- Tất cả Trạng thái -</option>
        <option value="Chưa thanh toán">Chưa thanh toán</option>
        <option value="Thanh toán một phần">Thanh toán một phần</option>
        <option value="Đã thanh toán">Đã thanh toán</option>
      </select>

      <button class="btn btn-success" @click="openModal()">+ TẠO KHOẢN PHÍ MỚI</button>
    </div>

    <div v-if="isLoading" style="text-align: center; padding: 20px; color: #3498db;">
      Đang tải dữ liệu chi phí...
    </div>

    <div v-else class="table-card" style="margin-top: 15px;">
      <table>
        <thead>
          <tr>
            <th>Mã CP</th>
            <th>Tên Chi Phí</th>
            <th>Loại</th>
            <th>Tổng Tiền</th>
            <th>Trạng thái</th>
            <th>Ngày TT</th>
            <th>Thuộc Lô hàng</th>
            <th style="text-align: center;">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="cp in filteredCosts" :key="cp.ma_chi_phi">
            <td class="fw-bold">#{{ cp.ma_chi_phi }}</td>
            <td class="fw-bold">{{ cp.ten_chi_phi }}</td>
            <td>
              <span :style="{ color: cp.loai_giao_dich === 'THU' ? '#27ae60' : '#e74c3c', fontWeight: 'bold' }">
                {{ cp.loai_giao_dich }}
              </span>
            </td>
            <td class="fw-bold" style="color: #2980b9;">{{ formatCurrency(cp.tong_tien) }}</td>
            <td>
              <span class="badge" :class="statusClass(cp.trang_thai_thanh_toan)">
                {{ cp.trang_thai_thanh_toan }}
              </span>
            </td>
            <td>{{ formatDate(cp.ngay_thanh_toan) }}</td>
            <td>{{ cp.ten_lo_hang || 'Chưa gắn' }}</td>
            <td style="text-align: center;">
              <button class="action-btn text-primary" @click="openModal(cp)" title="Cập nhật">✏️</button>
              <button class="action-btn text-danger" @click="handleDelete(cp.ma_chi_phi)" title="Xóa">🗑️</button>
            </td>
          </tr>
          <tr v-if="filteredCosts.length === 0">
            <td colspan="8" style="text-align: center; padding: 20px; color: #7f8c8d;">
              Không tìm thấy khoản chi phí nào phù hợp với bộ lọc!
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="isModalOpen" class="modal-overlay">
      <div class="modal-content">
        <h3>{{ formData.ma_chi_phi ? 'Cập nhật Chi Phí' : 'Khai báo Chi phí mới' }}</h3>
        <form @submit.prevent="saveCost">
          <div class="form-group">
            <label>Tên chi phí (VD: Phí THC, Phí lưu bãi...)</label>
            <input v-model="formData.ten_chi_phi" required placeholder="Nhập tên loại phí...">
          </div>
          
          <div style="display: flex; gap: 15px;">
            <div class="form-group" style="flex: 1;">
              <label>Loại Giao Dịch</label>
              <select v-model="formData.loai_giao_dich" required>
                <option value="THU">Phải Thu (Thu Khách Hàng)</option>
                <option value="CHI">Phải Chi (Trả Đối Tác)</option>
              </select>
            </div>

            <div class="form-group" style="flex: 1;">
              <label>Tổng tiền (VNĐ)</label>
              <input type="number" v-model="formData.tong_tien" required min="0">
            </div>
          </div>

          <div class="form-group">
            <label>Trạng thái thanh toán</label>
            <select v-model="formData.trang_thai_thanh_toan">
              <option value="Chưa thanh toán">Chưa thanh toán</option>
              <option value="Thanh toán một phần">Thanh toán một phần</option>
              <option value="Đã thanh toán">Đã thanh toán</option>
            </select>
          </div>

          <div class="form-group" v-if="formData.trang_thai_thanh_toan !== 'Chưa thanh toán'">
            <label>Ngày thanh toán</label>
            <input type="date" v-model="formData.ngay_thanh_toan" required>
          </div>

          <div class="form-group">
            <label>Mã Lô Hàng (Nếu có)</label>
            <input type="number" v-model="formData.ma_lo_hang" placeholder="Nhập ID lô hàng (nếu có)">
          </div>

          <div class="modal-actions">
            <button type="button" class="btn-cancel" @click="isModalOpen = false">Hủy</button>
            <button type="submit" class="btn-save" :disabled="isSaving">
              {{ isSaving ? 'Đang lưu...' : 'Lưu lại' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';

const listCosts = ref([]);
const isLoading = ref(true);
const isSaving = ref(false);
const isModalOpen = ref(false);

// BỘ TÌM KIẾM & LỌC
const searchQuery = ref('');
const filterLoai = ref('ALL');
const filterTrangThai = ref('ALL');

const formData = ref({
  ma_chi_phi: null,
  ten_chi_phi: '',
  tong_tien: 0,
  loai_giao_dich: 'THU',
  trang_thai_thanh_toan: 'Chưa thanh toán',
  ngay_thanh_toan: '',
  ma_lo_hang: null, 
  nguoi_sua_cuoi: null
});

// Hàm format tiền tệ (VD: 500000 -> 500.000 đ)
const formatCurrency = (value) => {
  if (!value) return '0 đ';
  return Number(value).toLocaleString('vi-VN') + ' đ';
};

const formatDate = (dateString) => {
  if (!dateString) return "-";
  return new Date(dateString).toLocaleDateString('vi-VN');
};

// Dùng chung CSS Badge giống hệt bên Quản lý Tài khoản
const statusClass = (status) => {
  if (status === 'Đã thanh toán') return 'badge-active'; 
  if (status === 'Thanh toán một phần') return 'badge-warning'; 
  return 'badge-locked'; // Chưa thanh toán
};

// XỬ LÝ LỌC NHIỀU ĐIỀU KIỆN KẾT HỢP
const filteredCosts = computed(() => {
  return listCosts.value.filter(cp => {
    const search = searchQuery.value.toLowerCase();
    
    // 1. Lọc theo thanh tìm kiếm (Khớp tên, mã phí, hoặc tên lô hàng)
    const matchSearch = !search || 
      cp.ten_chi_phi.toLowerCase().includes(search) || 
      cp.ma_chi_phi.toString().includes(search) || 
      (cp.ten_lo_hang && cp.ten_lo_hang.toLowerCase().includes(search));
      
    // 2. Lọc theo Dropdown Loại (Thu/Chi)
    const matchLoai = filterLoai.value === 'ALL' || cp.loai_giao_dich === filterLoai.value;
    
    // 3. Lọc theo Dropdown Trạng thái
    const matchTrangThai = filterTrangThai.value === 'ALL' || cp.trang_thai_thanh_toan === filterTrangThai.value;

    return matchSearch && matchLoai && matchTrangThai;
  });
});

const fetchCosts = async () => {
  isLoading.value = true;
  try {
    const res = await fetch('http://127.0.0.1:8000/api/costs');
    const data = await res.json();
    if (data.success) listCosts.value = data.data;
  } catch (error) {
    console.error("Lỗi lấy dữ liệu chi phí!");
  } finally {
    isLoading.value = false;
  }
};

const openModal = (cp = null) => {
  if (cp) {
    formData.value = { ...cp };
  } else {
    formData.value = {
      ma_chi_phi: null, ten_chi_phi: '', tong_tien: 0, loai_giao_dich: 'THU',
      trang_thai_thanh_toan: 'Chưa thanh toán', ngay_thanh_toan: '', ma_lo_hang: null
    };
  }
  isModalOpen.value = true;
};

const saveCost = async () => {
  isSaving.value = true;
  const user = JSON.parse(localStorage.getItem('sincere_user'));
  formData.value.nguoi_sua_cuoi = user ? (user.id || user.ma_tai_khoan) : null;

  try {
    const res = await fetch('http://127.0.0.1:8000/api/costs/save', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(formData.value)
    });
    const data = await res.json();
    if (data.success) {
      alert(data.message);
      isModalOpen.value = false;
      fetchCosts();
    } else {
      alert("Lỗi: " + data.message);
    }
  } catch (e) {
    alert("Lỗi kết nối Server!");
  } finally {
    isSaving.value = false;
  }
};

const handleDelete = async (id) => {
  if (!confirm("Bạn có chắc chắn muốn hủy khoản chi phí này không?")) return;
  const user = JSON.parse(localStorage.getItem('sincere_user'));
  
  try {
    const res = await fetch('http://127.0.0.1:8000/api/costs/delete', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ 
        ma_chi_phi: id,
        nguoi_sua_cuoi: user ? (user.id || user.ma_tai_khoan) : null 
      })
    });
    const data = await res.json();
    if (data.success) {
      alert("Đã xóa chi phí thành công!");
      fetchCosts();
    } else {
      alert("Lỗi: " + data.message);
    }
  } catch (e) {
    alert("Lỗi khi xóa kết nối!");
  }
};

onMounted(fetchCosts);
</script>

<style scoped src="../assets/quanlytaikhoan.css"></style>

<style scoped>
.badge-warning { background-color: #f39c12; color: white; }
</style>