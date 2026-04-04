<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">Quản lý Danh sách Tài khoản (1.1)</h3>
    
    <div class="toolbar">
      <div class="search-box">
        <input type="text" v-model="searchQuery" placeholder="Tìm kiếm tên, mã tài khoản...">
      </div>
      <button class="btn btn-success" @click="openModal()">+ TẠO TÀI KHOẢN MỚI</button>
    </div>

    <div v-if="isLoading" style="text-align: center; padding: 20px; color: #3498db;">
      Đang tải dữ liệu...
    </div>

    <div v-else class="table-card">
      <table>
        <thead>
          <tr>
            <th>Mã TK</th>
            <th>Họ và Tên</th>
            <th>Email</th>
            <th>Chức vụ (Quyền)</th>
            <th>Ngày sinh</th>
            <th>Trạng thái</th>
            <th style="text-align: center;">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="acc in filteredAccounts" :key="acc.ma_tai_khoan">
            <td class="fw-bold">{{ acc.ma_tai_khoan }}</td>
            <td class="fw-bold">{{ acc.ho_ten }}</td>
            <td>{{ acc.email }}</td>
            <td>{{ acc.ten_quyen || 'Chưa phân quyền' }}</td>
            <td>{{ formatDate(acc.ngay_sinh) }}</td>
            <td>
              <span class="badge" :class="acc.trang_thai === 'Hoạt động' ? 'badge-active' : 'badge-locked'">
                {{ acc.trang_thai }}
              </span>
            </td>
            <td style="text-align: center;">
              <button class="action-btn text-primary" @click="openModal(acc)" title="Sửa">✏️</button>
              <button class="action-btn text-danger" @click="handleDelete(acc.ma_tai_khoan)" title="Xóa">🗑️</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="isModalOpen" class="modal-overlay">
      <div class="modal-content">
        <h3>{{ formData.ma_tai_khoan ? 'Cập nhật tài khoản' : 'Thêm tài khoản mới' }}</h3>
        <form @submit.prevent="saveAccount">
          <div class="form-group">
            <label>Họ và Tên</label>
            <input v-model="formData.ho_ten" required placeholder="Nhập tên nhân viên...">
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" v-model="formData.email" required placeholder="email@sincere.com">
          </div>
          <div class="form-group">
            <label>Ngày sinh</label>
            <input type="date" v-model="formData.ngay_sinh">
          </div>
          <div class="form-group">
            <label>Mật khẩu {{ formData.ma_tai_khoan ? '(Để trống nếu không đổi)' : '' }}</label>
            <input type="password" v-model="formData.mat_khau" :required="!formData.ma_tai_khoan">
          </div>
          <div class="form-group">
            <label>Quyền hệ thống</label>
            <select v-model="formData.ma_quyen">
              <option value="1">Chứng từ</option>
              <option value="2">Kế toán</option>
              <option value="3">Giao nhận</option>
              <option value="4">Khai báo Hải quan</option>
            </select>
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

const listAccounts = ref([]);
const isLoading = ref(true);
const isSaving = ref(false);
const isModalOpen = ref(false);
const searchQuery = ref('');

const formData = ref({
  ma_tai_khoan: null,
  ho_ten: '',
  email: '',
  ngay_sinh: '', 
  mat_khau: '',
  ma_quyen: 1,
  trang_thai: 'Hoạt động'
});

const formatDate = (dateString) => {
  if (!dateString) return "Chưa cập nhật";
  const date = new Date(dateString);
  return date.toLocaleDateString('vi-VN');
};

const filteredAccounts = computed(() => {
  if (!searchQuery.value) return listAccounts.value;
  const search = searchQuery.value.toLowerCase();
  
  return listAccounts.value.filter(acc => {
    return (
      acc.ho_ten.toLowerCase().includes(search) || 
      acc.ma_tai_khoan.toString().includes(search) || 
      acc.email.toLowerCase().includes(search) || 
      (acc.ten_quyen && acc.ten_quyen.toLowerCase().includes(search))
    );
  });
});

const fetchAccounts = async () => {
  isLoading.value = true;
  try {
    const response = await fetch('http://127.0.0.1:8000/api/accounts');
    const data = await response.json();
    if (data.success) {
      listAccounts.value = data.data;
    }
  } catch (error) {
    alert("Không thể kết nối API lấy danh sách!");
  } finally {
    isLoading.value = false;
  }
};

const openModal = (acc = null) => {
  if (acc) {
    formData.value = { ...acc, mat_khau: '' }; 
  } else {
    formData.value = { 
      ma_tai_khoan: null, 
      ho_ten: '', 
      email: '', 
      ngay_sinh: '', 
      mat_khau: '', 
      ma_quyen: 1, 
      trang_thai: 'Hoạt động' 
    };
  }
  isModalOpen.value = true;
};

// ĐÃ SỬA: Trỏ về API Laravel
const saveAccount = async () => {
  isSaving.value = true;
  try {
    const response = await fetch('http://127.0.0.1:8000/api/accounts/save', {
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
      fetchAccounts(); 
    } else {
      alert("Lỗi: " + data.message);
    }
  } catch (error) {
    alert("Lỗi kết nối máy chủ khi lưu!");
  } finally {
    isSaving.value = false;
  }
};

// ĐÃ SỬA: Trỏ về API Laravel
const handleDelete = async (ma_tai_khoan) => {
  if (ma_tai_khoan === 1) return alert("Không được xóa tài khoản Admin hệ thống!");
  
  if (confirm('Bạn có chắc chắn muốn xóa nhân viên này không?')) {
    try {
      const response = await fetch('http://127.0.0.1:8000/api/accounts/delete', {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        // Sửa lại key gửi lên server cũng là chữ thường
        body: JSON.stringify({ ma_tai_khoan: ma_tai_khoan })
      });
      const data = await response.json();
      if (data.success) {
        alert("Đã xóa thành công!");
        fetchAccounts();
      } else {
        alert("Lỗi từ server: " + data.message);
      }
    } catch (error) {
      alert("Lỗi khi xóa kết nối!");
    }
  }
};

onMounted(fetchAccounts);
</script>

<style scoped src="../assets/quanlytaikhoan.css"></style>
