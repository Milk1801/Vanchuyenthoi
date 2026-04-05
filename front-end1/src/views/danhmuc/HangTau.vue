<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">Quản lý Danh Mục Hãng Tàu</h3>
    
    <div class="toolbar" style="display:flex; flex-wrap:wrap; align-items:flex-end; gap:12px; justify-content:space-between;">
      <div class="search-group" style="display:flex; flex-wrap:wrap; gap:12px; flex:1; min-width:0;">
        <div class="search-box" style="flex:1 1 220px; min-width:200px;">
          <input type="text" v-model="searchFilters.ten_hang_tau" placeholder="Tìm theo tên hãng tàu...">
        </div>
        <div class="search-box" style="flex:1 1 220px; min-width:200px;">
          <input type="text" v-model="searchFilters.dia_chi" placeholder="Tìm theo địa chỉ...">
        </div>
        <div class="search-box" style="flex:1 1 220px; min-width:200px;">
          <input type="text" v-model="searchFilters.so_dien_thoai" placeholder="Tìm theo điện thoại...">
        </div>
        <div class="search-box" style="flex:1 1 220px; min-width:200px;">
          <input type="text" v-model="searchFilters.so_fax" placeholder="Tìm theo fax...">
        </div>
        <div class="search-box" style="flex:1 1 220px; min-width:200px;">
          <select v-model="searchFilters.nguoi_sua_cuoi">
            <option value="">Tất cả người sửa cuối</option>
            <option v-for="acc in accountOptions" :key="acc.ma_tai_khoan" :value="acc.ma_tai_khoan">
              {{ acc.ho_ten }}
            </option>
          </select>
        </div>
        <div class="search-box" style="flex:0 0 auto; min-width:140px; display:flex; align-items:flex-end;">
          <button type="button" @click="clearFilters()" style="width:100%; background-color:#e74c3c; color:white; border:none; padding:8px 12px; border-radius:4px; cursor:pointer; font-weight:500;">Xóa bộ lọc</button>
        </div>
      </div>
      <button class="btn btn-success" @click="openModal()">+ TẠO HÃNG TÀU MỚI</button>
    </div>

    <div v-if="isLoading" style="text-align: center; padding: 20px; color: #3498db;">
      Đang tải dữ liệu...
    </div>

    <div v-else class="table-card">
      <table>
        <thead>
          <tr>
            <th>Mã Hãng</th>
            <th>Tên Hãng Tàu</th>
            <th>Địa chỉ</th>
            <th>Điện thoại</th>
            <th>Fax</th>
            <th>Ghi chú</th>
            <th>Người sửa cuối</th>
            <th style="text-align: center;">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="ht in filteredData" :key="ht.ma_hang_tau">
            <td class="fw-bold">{{ ht.ma_hang_tau }}</td>
            <td class="fw-bold">{{ ht.ten_hang_tau }}</td>
            <td>{{ ht.dia_chi || 'Chưa cập nhật' }}</td>
            <td>{{ ht.so_dien_thoai || 'Chưa cập nhật' }}</td>
            <td>{{ ht.so_fax || 'Chưa cập nhật' }}</td>
            <td>{{ ht.ghi_chu || 'Không có' }}</td>
            <td>{{ ht.nguoi_sua_doi || 'N/A' }}</td>
            <td style="text-align: center;">
              <button class="action-btn text-primary" @click="openModal(ht)" title="Sửa">✏️</button>
              <button class="action-btn text-danger" @click="handleDelete(ht.ma_hang_tau)" title="Xóa">🗑️</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="isModalOpen" class="modal-overlay">
      <div class="modal-content">
        <h3>{{ formData.ma_hang_tau ? 'Cập nhật hãng tàu' : 'Thêm hãng tàu mới' }}</h3>
        <form @submit.prevent="saveData">
          <div class="form-group">
            <label>Tên Hãng Tàu</label>
            <input v-model="formData.ten_hang_tau" required placeholder="Nhập tên hãng tàu...">
          </div>
          <div class="form-group">
            <label>Địa chỉ</label>
            <input v-model="formData.dia_chi" placeholder="Nhập địa chỉ...">
          </div>
          <div class="form-group">
            <label>Điện thoại</label>
            <input
              v-model="formData.so_dien_thoai"
              placeholder="0123456789"
              pattern="[0-9()+\-\.\s]{7,25}"
              title="Nhập số điện thoại hợp lệ"
              maxlength="25"
            >
          </div>
          <div class="form-group">
            <label>Fax</label>
            <input
              v-model="formData.so_fax"
              placeholder="Nhập Fax..."
              pattern="[0-9()+\-\.\s]{7,25}"
              title="Nhập số fax hợp lệ"
              maxlength="25"
            >
          </div>
          <div class="form-group">
            <label>Ghi chú</label>
            <textarea v-model="formData.ghi_chu" placeholder="Nhập ghi chú..."></textarea>
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

const listData = ref([]);
const accountOptions = ref([]);
const isLoading = ref(true);
const isSaving = ref(false);
const isModalOpen = ref(false);
const searchFilters = ref({
  ten_hang_tau: '',
  dia_chi: '',
  so_dien_thoai: '',
  so_fax: '',
  nguoi_sua_cuoi: ''
});

const formData = ref({
  ma_hang_tau: null,
  ten_hang_tau: '',
  dia_chi: '',
  so_dien_thoai: '', 
  so_fax: '',
  ghi_chu: '',
  nguoi_sua_cuoi: null
});

const filteredData = computed(() => {
  return listData.value.filter(item => {
    const tenMatch = !searchFilters.value.ten_hang_tau || item.ten_hang_tau.toLowerCase().includes(searchFilters.value.ten_hang_tau.toLowerCase());
    const diaChiMatch = !searchFilters.value.dia_chi || (item.dia_chi && item.dia_chi.toLowerCase().includes(searchFilters.value.dia_chi.toLowerCase()));
    const dienthoaiMatch = !searchFilters.value.so_dien_thoai || (item.so_dien_thoai && item.so_dien_thoai.includes(searchFilters.value.so_dien_thoai));
    const faxMatch = !searchFilters.value.so_fax || (item.so_fax && item.so_fax.includes(searchFilters.value.so_fax));
    const nguoiSuaMatch = !searchFilters.value.nguoi_sua_cuoi || String(item.nguoi_sua_cuoi) === String(searchFilters.value.nguoi_sua_cuoi);

    return tenMatch && diaChiMatch && dienthoaiMatch && faxMatch && nguoiSuaMatch;
  });
});

const fetchData = async () => {
  isLoading.value = true;
  try {
    const response = await fetch('http://127.0.0.1:8000/api/hang-tau');
    const data = await response.json();
    if (data.success) {
      listData.value = data.data;
    }
  } catch (error) {
    console.error('Fetch error:', error);
    alert("Không thể kết nối API lấy danh sách!");
  } finally {
    isLoading.value = false;
  }
};

const fetchAccounts = async () => {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/accounts');
    const data = await response.json();
    if (data.success) {
      accountOptions.value = data.data;
    }
  } catch (error) {
    console.error('Fetch accounts error:', error);
  }
};

const openModal = (item = null) => {
  const user = JSON.parse(localStorage.getItem('sincere_user'));
  const currentUserId = user ? user.ma_tai_khoan : null;

  if (item) {
    formData.value = { ...item }; 
  } else {
    formData.value = { 
      ma_hang_tau: null, 
      ten_hang_tau: '', 
      dia_chi: '', 
      so_dien_thoai: '', 
      so_fax: '',
      ghi_chu: '',
      nguoi_sua_cuoi: currentUserId
    };
  }

  // Luôn cập nhật người sửa cuối thành người dùng hiện tại
  formData.value.nguoi_sua_cuoi = currentUserId;
  isModalOpen.value = true;
};

const isValidContact = (value) => {
  if (!value) return true;
  const normalized = value.replace(/\D/g, '');
  const validChars = /^[0-9()+\-\.\s]+$/;
  return validChars.test(value) && normalized.length >= 7 && normalized.length <= 15;
};

const clearFilters = () => {
  searchFilters.value = {
    ten_hang_tau: '',
    dia_chi: '',
    so_dien_thoai: '',
    so_fax: '',
    nguoi_sua_cuoi: ''
  };
};

const saveData = async () => {
  if (formData.value.so_dien_thoai && !isValidContact(formData.value.so_dien_thoai)) {
    alert('Số điện thoại không hợp lệ. Vui lòng nhập lại.');
    return;
  }
  if (formData.value.so_fax && !isValidContact(formData.value.so_fax)) {
    alert('Số fax không hợp lệ. Vui lòng nhập lại.');
    return;
  }

  isSaving.value = true;
  try {
    const response = await fetch('http://127.0.0.1:8000/api/hang-tau/save', {
      method: 'POST',
      headers: { 
        'Content-Type': 'application/json',
        'Accept': 'application/json' 
      },
      body: JSON.stringify(formData.value)
    });
    const data = await response.json();
    console.log('Response from server:', data);
    if (data.success) {
      alert(data.message);
      isModalOpen.value = false;
      fetchData(); 
    } else {
      alert("Lỗi: " + data.message);
    }
  } catch (error) {
    console.error('Save error:', error);
    alert("Lỗi kết nối máy chủ khi lưu!");
  } finally {
    isSaving.value = false;
  }
};

const handleDelete = async (ma_hang_tau) => {
  if (confirm('Bạn có chắc chắn muốn xóa hãng tàu này không?')) {
    try {
      const response = await fetch('http://127.0.0.1:8000/api/hang-tau/delete', {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ ma_hang_tau: ma_hang_tau })
      });
      const data = await response.json();
      if (data.success) {
        alert("Đã xóa thành công!");
        fetchData();
      } else {
        alert("Lỗi từ server: " + data.message);
      }
    } catch (error) {
      alert("Lỗi khi xóa kết nối!");
    }
  }
};

onMounted(() => {
  fetchData();
  fetchAccounts();
});
</script>

<style scoped src="../../assets/quanlytaikhoan.css"></style>
