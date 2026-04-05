<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">Quản lý Quyền</h3>
    
    <div class="toolbar" style="display:flex; flex-wrap:wrap; align-items:flex-end; gap:12px; justify-content:space-between;">
      <div class="search-group" style="display:flex; flex-wrap:wrap; gap:12px; flex:1; min-width:0;">
        <div class="search-box" style="flex:1 1 300px; min-width:200px;">
          <input type="text" v-model="searchFilters.ten_quyen" placeholder="Tìm theo tên quyền...">
        </div>
        <div class="search-box" style="flex:0 0 auto; min-width:140px; display:flex; align-items:flex-end;">
          <button type="button" @click="clearFilters()" style="width:100%; background-color:#e74c3c; color:white; border:none; padding:8px 12px; border-radius:4px; cursor:pointer; font-weight:500;">Xóa bộ lọc</button>
        </div>
      </div>
      <button class="btn btn-success" @click="openModal()">+ TẠO QUYỀN MỚI</button>
    </div>

    <div v-if="isLoading" style="text-align: center; padding: 20px; color: #3498db;">
      Đang tải dữ liệu...
    </div>

    <div v-else class="table-card">
      <table>
        <thead>
          <tr>
            <th style="width: 100px;">Mã Quyền</th>
            <th>Tên Quyền</th>
            <th style="width: 150px;">Trạng thái</th>
            <th style="text-align: center; width: 120px;">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="q in filteredRoles" :key="q.ma_quyen">
            <td class="fw-bold">{{ q.ma_quyen }}</td>
            <td class="fw-bold">{{ q.ten_quyen }}</td>
            <td>
              <span :style="{ color: q.trang_thai === 'Hoạt động' ? '#27ae60' : '#e74c3c', fontWeight: 'bold' }">
                {{ q.trang_thai }}
              </span>
            </td>
            <td style="text-align: center;">
              <button class="action-btn text-primary" @click="openModal(q)" title="Sửa">✏️</button>
              <button class="action-btn text-danger" @click="handleDelete(q.ma_quyen)" title="Xóa">🗑️</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="isModalOpen" class="modal-overlay">
      <div class="modal-content">
        <h3>{{ formData.ma_quyen ? 'Cập nhật quyền' : 'Thêm quyền mới' }}</h3>
        <form @submit.prevent="saveRole">
          <div class="form-group">
            <label>Tên Quyền</label>
            <input v-model="formData.ten_quyen" required placeholder="Nhập tên quyền (VD: Kế toán, Nhân sự...)">
          </div>
          <div class="form-group">
            <label>Trạng thái</label>
            <select v-model="formData.trang_thai">
              <option value="Hoạt động">Hoạt động</option>
              <option value="Tạm khóa">Tạm khóa</option>
            </select>
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

const listRoles = ref([]);
const isLoading = ref(true);
const isSaving = ref(false);
const isModalOpen = ref(false);
const searchFilters = ref({
  ten_quyen: ''
});

const formData = ref({
  ma_quyen: null,
  ten_quyen: '',
  trang_thai: 'Hoạt động'
});

const filteredRoles = computed(() => {
  return listRoles.value.filter(q => {
    return !searchFilters.value.ten_quyen || q.ten_quyen.toLowerCase().includes(searchFilters.value.ten_quyen.toLowerCase());
  });
});

const fetchRoles = async () => {
  isLoading.value = true;
  try {
    const response = await fetch('http://127.0.0.1:8000/api/quyen');
    const data = await response.json();
    if (data.success) {
      listRoles.value = data.data;
    }
  } catch (error) {
    alert("Không thể kết nối API lấy danh sách quyền!");
  } finally {
    isLoading.value = false;
  }
};

const openModal = (role = null) => {
  if (role) {
    formData.value = { ...role }; 
  } else {
    formData.value = { 
      ma_quyen: null, 
      ten_quyen: '', 
      trang_thai: 'Hoạt động'
    };
  }
  isModalOpen.value = true;
};

const clearFilters = () => {
  searchFilters.value.ten_quyen = '';
};

const saveRole = async () => {
  isSaving.value = true;
  try {
    const response = await fetch('http://127.0.0.1:8000/api/quyen/save', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify(formData.value)
    });
    const data = await response.json();
    if (data.success) {
      alert(data.message);
      isModalOpen.value = false;
      fetchRoles(); 
    } else {
      alert("Lỗi: " + data.message);
    }
  } catch (error) {
    alert("Lỗi kết nối máy chủ khi lưu!");
  } finally {
    isSaving.value = false;
  }
};

const handleDelete = async (ma_quyen) => {
  if (confirm('Bạn có chắc chắn muốn xóa quyền này không?')) {
    try {
      const response = await fetch('http://127.0.0.1:8000/api/quyen/delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ ma_quyen: ma_quyen })
      });
      const data = await response.json();
      if (data.success) { fetchRoles(); }
    } catch (error) { alert("Lỗi khi xóa!"); }
  }
};

onMounted(fetchRoles);
</script>

<style scoped src="../../assets/quanlytaikhoan.css"></style>