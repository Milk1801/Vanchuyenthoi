<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">Quản lý Danh Mục Hãng Vận Tải</h3>
    
    <div class="toolbar" style="display:flex; flex-wrap:wrap; align-items:flex-end; gap:12px; justify-content:space-between;">
      <div class="search-group" style="display:flex; flex-wrap:wrap; gap:12px; flex:1; min-width:0;">
        <div class="search-box" style="flex:1 1 220px; min-width:200px;">
          <input type="text" v-model="searchFilters.ten_hang_van_tai" placeholder="Tìm theo tên hãng vận tải...">
        </div>
        <div class="search-box" style="flex:0 0 auto; min-width:140px; display:flex; align-items:flex-end;">
          <button type="button" @click="clearFilters()" style="width:100%; background-color:#e74c3c; color:white; border:none; padding:8px 12px; border-radius:4px; cursor:pointer; font-weight:500;">Xóa bộ lọc</button>
        </div>
      </div>
      <button class="btn btn-success" @click="openModal()">+ TẠO HÃNG VẬN TẢI MỚI</button>
    </div>

    <div v-if="isLoading" style="text-align: center; padding: 20px; color: #3498db;">
      Đang tải dữ liệu...
    </div>

    <div v-else class="table-card">
      <table>
        <thead>
          <tr>
            <th>Mã Hãng</th>
            <th>Tên Hãng Vận Tải</th>
            <th>Ghi chú</th>
            <th style="text-align: center;">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="h in filteredData" :key="h.ma_hang_van_tai">
            <td class="fw-bold">{{ h.ma_hang_van_tai }}</td>
            <td class="fw-bold">{{ h.ten_hang_van_tai }}</td>
            <td>{{ h.ghi_chu || 'Không có' }}</td>
            <td style="text-align: center;">
              <button class="action-btn text-primary" @click="openModal(h)" title="Sửa">✏️</button>
              <button class="action-btn text-danger" @click="handleDelete(h.ma_hang_van_tai)" title="Xóa">🗑️</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="isModalOpen" class="modal-overlay">
      <div class="modal-content">
        <h3>{{ formData.ma_hang_van_tai ? 'Cập nhật hãng vận tải' : 'Thêm hãng vận tải mới' }}</h3>
        <form @submit.prevent="saveData">
          <div class="form-group">
            <label>Tên Hãng Vận Tải</label>
            <input v-model="formData.ten_hang_van_tai" required placeholder="Nhập tên hãng vận tải...">
          </div>
          <div class="form-group">
            <label>Tuyến Thường Xuyên (JSON)</label>
            <textarea v-model="formData.tuyen_thuong_xuyen" placeholder="Nhập dạng JSON hoặc để trống..." rows="3"></textarea>
          </div>
          <div class="form-group">
            <label>Các Loại Xe (JSON)</label>
            <textarea v-model="formData.cac_loai_xe" placeholder="Nhập dạng JSON hoặc để trống..." rows="3"></textarea>
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
  ten_hang_van_tai: ''
});

const formData = ref({
  ma_hang_van_tai: null,
  ten_hang_van_tai: '',
  tuyen_thuong_xuyen: '',
  cac_loai_xe: '',
  ghi_chu: ''
});

const filteredData = computed(() => {
  return listData.value.filter(item => {
    const nameMatch = !searchFilters.value.ten_hang_van_tai || item.ten_hang_van_tai.toLowerCase().includes(searchFilters.value.ten_hang_van_tai.toLowerCase());
    return nameMatch;
  });
});

const fetchData = async () => {
  isLoading.value = true;
  try {
    const response = await fetch('http://127.0.0.1:8000/api/hang-van-tai');
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
    formData.value = { 
      ...item,
      tuyen_thuong_xuyen: typeof item.tuyen_thuong_xuyen === 'string' ? item.tuyen_thuong_xuyen : (item.tuyen_thuong_xuyen ? JSON.stringify(item.tuyen_thuong_xuyen) : ''),
      cac_loai_xe: typeof item.cac_loai_xe === 'string' ? item.cac_loai_xe : (item.cac_loai_xe ? JSON.stringify(item.cac_loai_xe) : '')
    };
  } else {
    formData.value = { 
      ma_hang_van_tai: null, 
      ten_hang_van_tai: '', 
      tuyen_thuong_xuyen: '',
      cac_loai_xe: '',
      ghi_chu: ''
    };
  }

  isModalOpen.value = true;
};

const clearFilters = () => {
  searchFilters.value = {
    ten_hang_van_tai: ''
  };
};

const saveData = async () => {
  isSaving.value = true;
  try {
    const response = await fetch('http://127.0.0.1:8000/api/hang-van-tai/save', {
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

const handleDelete = async (ma_hang_van_tai) => {
  if (confirm('Bạn có chắc chắn muốn xóa hãng vận tải này không?')) {
    try {
      const response = await fetch('http://127.0.0.1:8000/api/hang-van-tai/delete', {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ ma_hang_van_tai: ma_hang_van_tai })
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

<style scoped>
</style>
