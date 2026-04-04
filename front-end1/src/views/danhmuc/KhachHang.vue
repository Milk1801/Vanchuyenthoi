<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">Quản lý Danh Mục Khách Hàng</h3>
    
    <div class="toolbar" style="display:flex; flex-wrap:wrap; align-items:flex-end; gap:12px; justify-content:space-between;">
      <div class="search-group" style="display:flex; flex-wrap:wrap; gap:12px; flex:1; min-width:0;">
        <div class="search-box" style="flex:1 1 220px; min-width:200px;">
          <input type="text" v-model="searchFilters.ten_khach_hang" placeholder="Tìm theo tên khách hàng...">
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
      <button class="btn btn-success" @click="openModal()">+ TẠO KHÁCH HÀNG MỚI</button>
    </div>

    <div v-if="isLoading" style="text-align: center; padding: 20px; color: #3498db;">
      Đang tải dữ liệu...
    </div>

    <div v-else class="table-card">
      <table>
        <thead>
          <tr>
            <th>Mã KH</th>
            <th>Tên Khách Hàng</th>
            <th>Địa chỉ</th>
            <th>Điện thoại</th>
            <th>Fax</th>
            <th>Ghi chú</th>
            <th>Người sửa cuối</th>
            <th style="text-align: center;">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="kh in filteredCustomers" :key="kh.ma_khach_hang">
            <td class="fw-bold">{{ kh.ma_khach_hang }}</td>
            <td class="fw-bold">{{ kh.ten_khach_hang }}</td>
            <td>{{ kh.dia_chi || 'Chưa cập nhật' }}</td>
            <td>{{ kh.so_dien_thoai || 'Chưa cập nhật' }}</td>
            <td>{{ kh.so_fax || 'Chưa cập nhật' }}</td>
            <td>{{ kh.ghi_chu || 'Không có' }}</td>
            <td>{{ kh.nguoi_sua_doi || 'N/A' }}</td>
            <td style="text-align: center;">
              <button class="action-btn text-primary" @click="openModal(kh)" title="Sửa">✏️</button>
              <button class="action-btn text-danger" @click="handleDelete(kh.ma_khach_hang)" title="Xóa">🗑️</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="isModalOpen" class="modal-overlay">
      <div class="modal-content">
        <h3>{{ formData.ma_khach_hang ? 'Cập nhật khách hàng' : 'Thêm khách hàng mới' }}</h3>
        <form @submit.prevent="saveCustomer">
          <div class="form-group">
            <label>Tên Khách Hàng</label>
            <input v-model="formData.ten_khach_hang" required placeholder="Nhập tên khách hàng...">
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
              title="Nhập số điện thoại hợp lệ, chỉ gồm chữ số và các ký tự + - . ()"
              maxlength="25"
            >
          </div>
          <div class="form-group">
            <label>Fax</label>
            <input
              v-model="formData.so_fax"
              placeholder="Nhập Fax..."
              pattern="[0-9()+\-\.\s]{7,25}"
              title="Nhập số fax hợp lệ, chỉ gồm chữ số và các ký tự + - . ()"
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

const listCustomers = ref([]);
const accountOptions = ref([]);
const isLoading = ref(true);
const isSaving = ref(false);
const isModalOpen = ref(false);
const searchFilters = ref({
  ten_khach_hang: '',
  dia_chi: '',
  so_dien_thoai: '',
  so_fax: '',
  nguoi_sua_cuoi: ''
});

const formData = ref({
  ma_khach_hang: null,
  ten_khach_hang: '',
  dia_chi: '',
  so_dien_thoai: '', 
  so_fax: '',
  ghi_chu: '',
  nguoi_sua_cuoi: null
});

const filteredCustomers = computed(() => {
  return listCustomers.value.filter(kh => {
    const nameMatch = !searchFilters.value.ten_khach_hang || kh.ten_khach_hang.toLowerCase().includes(searchFilters.value.ten_khach_hang.toLowerCase());
    const addressMatch = !searchFilters.value.dia_chi || (kh.dia_chi && kh.dia_chi.toLowerCase().includes(searchFilters.value.dia_chi.toLowerCase()));
    const phoneMatch = !searchFilters.value.so_dien_thoai || (kh.so_dien_thoai && kh.so_dien_thoai.includes(searchFilters.value.so_dien_thoai));
    const faxMatch = !searchFilters.value.so_fax || (kh.so_fax && kh.so_fax.includes(searchFilters.value.so_fax));
    const editorMatch = !searchFilters.value.nguoi_sua_cuoi || String(kh.nguoi_sua_cuoi) === String(searchFilters.value.nguoi_sua_cuoi);

    return nameMatch && addressMatch && phoneMatch && faxMatch && editorMatch;
  });
});

const fetchCustomers = async () => {
  isLoading.value = true;
  try {
    const response = await fetch('http://127.0.0.1:8000/api/khach-hang');
    const data = await response.json();
    if (data.success) {
      listCustomers.value = data.data;
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

const openModal = (kh = null) => {
  const user = JSON.parse(localStorage.getItem('sincere_user'));
  const currentUserId = user ? user.ma_tai_khoan : null;

  if (kh) {
    formData.value = { ...kh }; 
  } else {
    formData.value = { 
      ma_khach_hang: null, 
      ten_khach_hang: '', 
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
    ten_khach_hang: '',
    dia_chi: '',
    so_dien_thoai: '',
    so_fax: '',
    nguoi_sua_cuoi: ''
  };
};

const saveCustomer = async () => {
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
    const response = await fetch('http://127.0.0.1:8000/api/khach-hang/save', {
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
      fetchCustomers(); 
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

const handleDelete = async (ma_khach_hang) => {
  if (confirm('Bạn có chắc chắn muốn xóa khách hàng này không?')) {
    try {
      const response = await fetch('http://127.0.0.1:8000/api/khach-hang/delete', {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ ma_khach_hang: ma_khach_hang })
      });
      const data = await response.json();
      if (data.success) {
        alert("Đã xóa thành công!");
        fetchCustomers();
      } else {
        alert("Lỗi từ server: " + data.message);
      }
    } catch (error) {
      alert("Lỗi khi xóa kết nối!");
    }
  }
};

onMounted(() => {
  fetchCustomers();
  fetchAccounts();
});
</script>

<style scoped src="../../assets/quanlytaikhoan.css"></style>
