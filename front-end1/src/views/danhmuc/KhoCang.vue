<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">Quản lý Danh Mục Cảng Biển</h3>
    
    <div class="toolbar" style="display:flex; flex-wrap:wrap; align-items:flex-end; gap:12px; justify-content:space-between;">
      <div class="search-group" style="display:flex; flex-wrap:wrap; gap:12px; flex:1; min-width:0;">
        <div class="search-box" style="flex:1 1 220px; min-width:200px;">
          <input type="text" v-model="searchFilters.ten_cang" placeholder="Tìm theo tên cảng...">
        </div>
        <div class="search-box" style="flex:1 1 220px; min-width:200px;">
          <input type="text" v-model="searchFilters.dia_chi" placeholder="Tìm theo địa chỉ...">
        </div>
        <div class="search-box" style="flex:0 0 auto; min-width:140px; display:flex; align-items:flex-end;">
          <button type="button" @click="clearFilters()" style="width:100%; background-color:#e74c3c; color:white; border:none; padding:8px 12px; border-radius:4px; cursor:pointer; font-weight:500;">Xóa bộ lọc</button>
        </div>
      </div>
      <button class="btn btn-success" @click="openModal()">+ TẠO CẢNG MỚI</button>
    </div>

    <div v-if="isLoading" style="text-align: center; padding: 20px; color: #3498db;">
      Đang tải dữ liệu...
    </div>

    <div v-else class="table-card">
      <table>
        <thead>
          <tr>
            <th>Mã Cảng</th>
            <th>Tên Cảng</th>
            <th>Địa chỉ</th>
            <th>Ghi chú</th>
            <th style="text-align: center;">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="c in filteredData" :key="c.ma_cang">
            <td class="fw-bold">{{ c.ma_cang }}</td>
            <td class="fw-bold">{{ c.ten_cang }}</td>
            <td class="fw-bold">{{ c.dia_chi  || 'Chưa cập nhật' }}</td>
            <td>{{ c.ghi_chu || 'Không có' }}</td>
            <td style="text-align: center;">
              <button class="action-btn text-primary" @click="openModal(c)" title="Sửa">✏️</button>
              <button class="action-btn text-danger" @click="handleDelete(c.ma_cang)" title="Xóa">🗑️</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="isModalOpen" class="modal-overlay">
      <div class="modal-content">
        <h3>{{ formData.ma_cang ? 'Cập nhật cảng' : 'Thêm cảng mới' }}</h3>
        <form @submit.prevent="saveData">
          <div class="form-group">
            <label>Tên Cảng</label>
            <input v-model="formData.ten_cang" required placeholder="Nhập tên cảng...">
          </div>
          <div class="form-group">
            <label>Địa chỉ</label>
            <input v-model="formData.dia_chi" placeholder="Nhập địa chỉ...">
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
const isLoading = ref(true);
const isSaving = ref(false);
const isModalOpen = ref(false);
const searchFilters = ref({
  ten_cang: '',
  dia_chi: '' 
});

const formData = ref({
  ma_cang: null,
  ten_cang: '',
  dia_chi: '',
  ghi_chu: ''
});

const filteredData = computed(() => {
  return listData.value.filter(item => {
    const tenMatch = !searchFilters.value.ten_cang || item.ten_cang.toLowerCase().includes(searchFilters.value.ten_cang.toLowerCase());
    const diaChiMatch = !searchFilters.value.dia_chi || (item.dia_chi && item.dia_chi.toLowerCase().includes(searchFilters.value.dia_chi.toLowerCase()));
    return tenMatch && diaChiMatch ;
  });
});

const fetchData = async () => {
  isLoading.value = true;
  try {
    const response = await fetch('http://127.0.0.1:8000/api/cang-bien');
    const data = await response.json();
    if (data.success) {
      listData.value = data.data;
    }
  } catch (error) {
    alert("Không thể kết nối API lấy danh sách!");
  } finally {
    isLoading.value = false;
  }
};

const openModal = (item = null) => {
  if (item) {
    formData.value = { ...item }; 
  } else {
    formData.value = { 
      ma_cang: null, 
      ten_cang: '', 
      dia_chi: '', 
      ghi_chu: ''
    };
  }

  isModalOpen.value = true;
};

const clearFilters = () => {
  searchFilters.value = {
    ten_cang: '',
    dia_chi: ''
  };
};

const saveData = async () => {
  isSaving.value = true;
  try {
    const response = await fetch('http://127.0.0.1:8000/api/cang-bien/save', {
      method: 'POST',
      headers: { 
        'Content-Type': 'application/json',
        'Accept': 'application/json' 
      },
      body: JSON.stringify(formData.value)
    });
    const data = await response.json();
    if (data.success) {
      alert(data.message);
      isModalOpen.value = false;
      fetchData(); 
    } else {
      alert("Lỗi: " + data.message);
    }
  } catch (error) {
    alert("Lỗi kết nối máy chủ khi lưu!");
  } finally {
    isSaving.value = false;
  }
};

const handleDelete = async (ma_cang) => {
  if (confirm('Bạn có chắc chắn muốn xóa cảng này không?')) {
    try {
      const response = await fetch('http://127.0.0.1:8000/api/cang-bien/delete', {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ ma_cang: ma_cang })
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
});
</script>

<style scoped src="../../assets/quanlytaikhoan.css"></style>
