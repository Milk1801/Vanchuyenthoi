<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">Quản lý Tờ Khai Hải Quan</h3>
    
    <div class="toolbar" style="display:flex; flex-wrap:wrap; align-items:flex-end; gap:12px; justify-content:space-between;">
      <div class="search-group" style="display:flex; flex-wrap:wrap; gap:12px; flex:1; min-width:0;">
        <div class="search-box" style="flex:1 1 220px; min-width:200px;">
          <input type="text" v-model="searchFilters.ten_lo_hang" placeholder="Tìm theo tên lô hàng...">
        </div>
        <div class="search-box" style="flex:1 1 220px; min-width:200px;">
          <select v-model="searchFilters.phan_luong">
            <option value="">-- Tất cả phân luồng --</option>
            <option v-for="pl in listPhanLuong" :key="pl" :value="pl">{{ pl }}</option>
          </select>
        </div>
        <div class="search-box" style="flex:1 1 220px; min-width:200px;">
          <select v-model="searchFilters.ket_qua_thong_quan">
            <option value="">-- Tất cả kết quả --</option>
            <option v-for="kq in listKetQua" :key="kq" :value="kq">{{ kq }}</option>
          </select>
        </div>

        <div class="search-box" style="flex:0 0 auto; min-width:140px; display:flex; align-items:flex-end;">
          <button type="button" @click="clearFilters()" style="width:100%; background-color:#e74c3c; color:white; border:none; padding:8px 12px; border-radius:4px; cursor:pointer; font-weight:500;">Xóa lọc</button>
        </div>
      </div>
      <button class="btn btn-success" @click="openModal()">+ TẠO TỜ KHAI MỚI</button>
    </div>

    <div v-if="isLoadingData" style="text-align: center; padding: 20px; color: #3498db;">
      Đang tải dữ liệu...
    </div>

    <div v-else class="table-card">
      <table>
        <thead>
          <tr>
            <th>Mã Tờ Khai</th>
            <th>Thuộc Lô Hàng</th>
            <th>Ngày Thông Quan</th>
            <th>Phân Luồng</th>
            <th>Kết Quả</th>
            <th>Người sửa cuối</th>
            <th style="text-align: center;">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="tk in filteredData" :key="tk.ma_to_khai_hai_quan">
            <td class="fw-bold">{{ tk.ma_to_khai_hai_quan }}</td>
            <td class="fw-bold" style="color: #2980b9;">{{ tk.ten_lo_hang || '---' }}</td>
            <td>{{ formatDateTime(tk.ngay_thong_quan) }}</td>
            <td>
              <span class="badge" :style="getPhanLuongStyle(tk.phan_luong)">
                {{ tk.phan_luong || 'N/A' }}
              </span>
            </td>
            <td>
              <span class="badge" :style="getKetQuaStyle(tk.ket_qua_thong_quan)">
                {{ tk.ket_qua_thong_quan || 'N/A' }}
              </span>
            </td>
            <td>{{ tk.ten_nguoi_sua || 'N/A' }}</td>
            <td style="text-align: center;">
              <button class="action-btn text-primary" @click="openModal(tk)" title="Sửa">✏️</button>
              <button class="action-btn text-danger" @click="handleDelete(tk.ma_to_khai_hai_quan)" title="Xóa">🗑️</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Thêm/Sửa Tờ Khai -->
    <div v-if="isModalOpen" class="modal-overlay">
      <div class="modal-content" style="max-width: 500px;">
        <h3>{{ formData.ma_to_khai_hai_quan ? 'Cập Nhật Tờ Khai' : 'Thêm Tờ Khai Mới' }}</h3>
        <form @submit.prevent="saveToKhai">
          <div class="form-group">
            <label>Lô Hàng Liên Kết *</label>
            <select v-model="formData.ma_lo_hang" required>
              <option :value="null">-- Chọn lô hàng --</option>
              <option v-for="lh in listLoHang" :key="lh.ma_lo_hang" :value="lh.ma_lo_hang">
                {{ lh.ten_lo_hang }}
              </option>
            </select>
          </div>

          <div class="form-group">
            <label>Ngày Thông Quan</label>
            <input type="datetime-local" v-model="formData.ngay_thong_quan">
          </div>

          <div style="display: flex; gap: 15px;">
            <div class="form-group" style="flex: 1;">
              <label>Phân Luồng</label>
              <select v-model="formData.phan_luong">
                <option v-for="pl in listPhanLuong" :key="pl" :value="pl">{{ pl }}</option>
              </select>
            </div>
            <div class="form-group" style="flex: 1;">
              <label>Kết Quả</label>
              <select v-model="formData.ket_qua_thong_quan">
                <option v-for="kq in listKetQua" :key="kq" :value="kq">{{ kq }}</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label>Ghi chú (Tùy chọn)</label>
            <textarea v-model="formData.ghi_chu" rows="2" style="width:100%; border:1px solid #ddd; border-radius:6px; padding:10px;"></textarea>
          </div>

          <div class="modal-actions">
            <button type="button" class="btn-cancel" @click="isModalOpen = false">Hủy</button>
            <button type="submit" class="btn-save" :disabled="isSaving">
              {{ isSaving ? 'Đang lưu...' : 'Lưu Tờ Khai' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const listToKhai = ref([]);
const listLoHang = ref([]);
const isLoadingData = ref(true);
const isSaving = ref(false);
const isModalOpen = ref(false);

const listPhanLuong = ['Luồng Xanh', 'Luồng Vàng', 'Luồng Đỏ'];
const listKetQua = ['Chờ xử lý', 'Đã thông quan', 'Từ chối'];

const searchFilters = ref({
  ten_lo_hang: '',
  phan_luong: '',
  ket_qua_thong_quan: ''
});

const formData = ref({
  ma_to_khai_hai_quan: null,
  ngay_thong_quan: '',
  phan_luong: 'Luồng Xanh',
  ket_qua_thong_quan: 'Chờ xử lý',
  ma_lo_hang: null,
  nguoi_sua_cuoi: null
});

const filteredData = computed(() => {
  return listToKhai.value.filter(item => {
    const matchLoHang = !searchFilters.value.ten_lo_hang || 
      (item.ten_lo_hang && item.ten_lo_hang.toLowerCase().includes(searchFilters.value.ten_lo_hang.toLowerCase()));
    const matchPhanLuong = !searchFilters.value.phan_luong || item.phan_luong === searchFilters.value.phan_luong;
    const matchKetQua = !searchFilters.value.ket_qua_thong_quan || item.ket_qua_thong_quan === searchFilters.value.ket_qua_thong_quan;

    return matchLoHang && matchPhanLuong && matchKetQua;
  });
});

const fetchData = async () => {
  isLoadingData.value = true;
  try {
    const response = await fetch('http://127.0.0.1:8000/api/to-khai-hai-quan');
    const data = await response.json();
    if (data.success) {
      listToKhai.value = data.data;
    }
    
    // Đồng thời lấy tham chiếu lô hàng
    await fetchReferences();
  } catch (error) {
    console.error('Fetch error:', error);
    alert("Không thể kết nối API lấy danh sách!");
  } finally {
    isLoadingData.value = false;
  }
};

const fetchReferences = async () => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/to-khai-hai-quan/references');
    const data = await response.json();
    if (data.success) listLoHang.value = data.lo_hang;
  } catch (error) {
    console.error('Fetch refs error:', error);
  }
};

const openModal = (item = null) => {
  if (item) {
    formData.value = { 
      ...item,
      ngay_thong_quan: item.ngay_thong_quan ? new Date(item.ngay_thong_quan).toISOString().slice(0, 16) : ''
    };
  } else {
    formData.value = {
      ma_to_khai_hai_quan: null,
      ngay_thong_quan: '',
      phan_luong: 'Luồng Xanh',
      ket_qua_thong_quan: 'Chờ xử lý',
      ma_lo_hang: null,
      nguoi_sua_cuoi: null
    };
  }
  isModalOpen.value = true;
};

const saveToKhai = async () => {
  isSaving.value = true;
  const user = JSON.parse(localStorage.getItem('sincere_user'));
  formData.value.nguoi_sua_cuoi = user ? (user.id || user.ma_tai_khoan) : null;
  
  try {
    const res = await fetch('http://127.0.0.1:8000/api/to-khai-hai-quan/save', {
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
  } catch (e) { alert("Lỗi kết nối máy chủ!"); }
  finally { isSaving.value = false; }
};

const clearFilters = () => {
  searchFilters.value = {
    ten_lo_hang: '',
    phan_luong: '',
    ket_qua_thong_quan: ''
  };
};

const handleDelete = async (id) => {
  if (confirm('Bạn có chắc chắn muốn xóa tờ khai này không?')) {
    try {
      const response = await fetch('http://127.0.0.1:8000/api/to-khai-hai-quan/delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ma_to_khai_hai_quan: id })
      });
      const data = await response.json();
      if (data.success) { alert("Đã xóa thành công!"); fetchData(); }
      else { alert("Lỗi: " + data.message); }
    } catch (error) {
      alert("Lỗi khi xóa kết nối!");
    }
  }
};

onMounted(() => {
  fetchData(); 
});

const formatDateTime = (str) => {
  if (!str) return '---';
  return new Date(str).toLocaleString('vi-VN');
};

const getPhanLuongStyle = (pl) => {
  if (pl === 'Luồng Xanh') return { backgroundColor: '#27ae60', color: 'white' };
  if (pl === 'Luồng Vàng') return { backgroundColor: '#f1c40f', color: '#333' };
  if (pl === 'Luồng Đỏ') return { backgroundColor: '#e74c3c', color: 'white' };
  return { backgroundColor: '#95a5a6', color: 'white' };
};

const getKetQuaStyle = (kq) => {
  if (kq === 'Đã thông quan') return { backgroundColor: '#27ae60', color: 'white' };
  if (kq === 'Chờ xử lý') return { backgroundColor: '#f39c12', color: 'white' };
  if (kq === 'Từ chối') return { backgroundColor: '#e74c3c', color: 'white' };
  return { backgroundColor: '#95a5a6', color: 'white' };
};
</script>

<style scoped src="../../assets/quanlytaikhoan.css"></style>
