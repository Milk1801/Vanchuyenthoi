<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">Quản lý Booking Cước Tàu</h3>
    
    <div class="toolbar" style="display: flex; gap: 15px; flex-wrap: wrap;">
      <div class="search-box" style="flex: 1; min-width: 250px;">
        <input 
          type="text" 
          v-model="searchQuery" 
          placeholder="Tìm theo số Booking, Tên tàu, Số chuyến..."
          style="width: 100%; padding: 8px 12px; border-radius: 4px; border: 1px solid #ccc;"
        >
      </div>

      <select 
        v-model="filterHangTau" 
        @mouseenter="loadReferences()" 
        style="padding: 8px; border-radius: 4px; border: 1px solid #ccc; width: 200px;"
      >
        <option value="ALL">- Tất cả Hãng tàu -</option>
        <option v-for="ht in listHangTau" :key="ht.ma_hang_tau" :value="ht.ma_hang_tau">
          {{ ht.ten_hang_tau }}
        </option>
      </select>

      <button 
        @click="loadReferences(); fetchBookings();" 
        style="padding: 8px 15px; border: 1px solid #ccc; border-radius: 4px; background: #fff; cursor: pointer; transition: 0.2s;"
        title="Tải lại dữ liệu mới nhất"
        onmouseover="this.style.background='#f1f2f6'"
        onmouseout="this.style.background='#fff'"
      >
        🔄 Làm mới
      </button>

      <button class="btn btn-success" @click="openModal()">+ TẠO BOOKING MỚI</button>
    </div>

    <div v-if="isLoading" style="text-align: center; padding: 20px; color: #3498db;">
      Đang tải dữ liệu Booking...
    </div>

    <div v-else class="table-card" style="margin-top: 15px;">
      <table>
        <thead>
          <tr>
            <th>Số Booking</th>
            <th>Tên Tàu / Chuyến</th>
            <th>Hãng Tàu</th>
            <th>Cảng Đi (POL)</th>
            <th>Cảng Đến (POD)</th>
            <th>ETD (Dự kiến đi)</th>
            <th>Cut-off (Cắt máng)</th>
            <th style="text-align: center;">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="bk in filteredBookings" :key="bk.ma_booking">
            <td class="fw-bold" style="color: #2980b9;">{{ bk.so_booking }}</td>
            <td>
              <strong>{{ bk.ten_con_tau || 'N/A' }}</strong><br>
              <span style="font-size: 12px; color: #7f8c8d;">V. {{ bk.so_chuyen || 'N/A' }}</span>
            </td>
            <td>{{ bk.ten_hang_tau || 'Chưa rõ' }}</td>
            <td class="fw-bold">{{ bk.ten_cang_di || '---' }}</td>
            <td class="fw-bold">{{ bk.ten_cang_den || '---' }}</td>
            <td>
              <span class="badge badge-active">{{ formatDateTime(bk.etd) }}</span>
            </td>
            <td>
              <span class="badge badge-warning">{{ formatDateTime(bk.gio_cat_mang) }}</span>
            </td>
            <td style="text-align: center;">
              <button class="action-btn text-primary" @click="openModal(bk)" title="Cập nhật">✏️</button>
              <button class="action-btn text-danger" @click="handleDelete(bk.ma_booking)" title="Xóa">🗑️</button>
            </td>
          </tr>
          <tr v-if="filteredBookings.length === 0">
            <td colspan="8" style="text-align: center; padding: 20px; color: #7f8c8d;">
              Không tìm thấy Booking nào!
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="isModalOpen" class="modal-overlay">
      <div class="modal-content" style="max-width: 850px;">
        <h3>{{ formData.ma_booking ? 'Cập nhật Booking' : 'Tạo Booking mới' }}</h3>
        <form @submit.prevent="saveBooking">
          
          <div style="display: flex; gap: 15px;">
            <div class="form-group" style="flex: 1;">
              <label>Số Booking *</label>
              <input v-model="formData.so_booking" required placeholder="Nhập mã Booking từ hãng tàu...">
            </div>
            <div class="form-group" style="flex: 1;">
              <label>Hãng Tàu *</label>
              <select v-model="formData.ma_hang_tau" required>
                <option :value="null">-- Chọn Hãng tàu --</option>
                <option v-for="ht in listHangTau" :key="ht.ma_hang_tau" :value="ht.ma_hang_tau">
                  {{ ht.ten_hang_tau }}
                </option>
              </select>
            </div>
          </div>

          <div style="display: flex; gap: 15px;">
            <div class="form-group" style="flex: 2;">
              <label>Tên Con Tàu</label>
              <input v-model="formData.ten_con_tau" placeholder="VD: EVER GIVEN">
            </div>
            <div class="form-group" style="flex: 1;">
              <label>Số Chuyến (Voyage)</label>
              <input v-model="formData.so_chuyen" placeholder="VD: 045E">
            </div>
          </div>

          <div style="display: flex; gap: 15px;">
            <div class="form-group" style="flex: 1;">
              <label>Cảng Đi (POL)</label>
              <select v-model="formData.ma_cang_di">
                <option :value="null">-- Chọn Cảng Đi --</option>
                <option v-for="cang in listCangBien" :key="cang.ma_cang" :value="cang.ma_cang">
                  {{ cang.ten_cang }}
                </option>
              </select>
            </div>
            <div class="form-group" style="flex: 1;">
              <label>Cảng Đến (POD)</label>
              <select v-model="formData.ma_cang_den">
                <option :value="null">-- Chọn Cảng Đến --</option>
                <option v-for="cang in listCangBien" :key="cang.ma_cang" :value="cang.ma_cang">
                  {{ cang.ten_cang }}
                </option>
              </select>
            </div>
          </div>

          <div style="display: flex; gap: 15px; flex-wrap: wrap;">
            
            <div class="form-group" style="flex: 1 1 45%; min-width: 220px;">
              <label>Giờ Cắt Máng (Cut-off)</label>
              <input type="datetime-local" v-model="formData.gio_cat_mang" style="width: 100%; box-sizing: border-box;">
            </div>
            
            <div class="form-group" style="flex: 1 1 45%; min-width: 220px;">
              <label>ETD (Dự kiến đi)</label>
              <input type="datetime-local" v-model="formData.etd" style="width: 100%; box-sizing: border-box;">
            </div>
            
            <div class="form-group" style="flex: 1 1 100%; min-width: 220px;">
              <label>ETA (Dự kiến đến)</label>
              <input type="datetime-local" v-model="formData.eta" style="width: 100%; box-sizing: border-box;">
            </div>

          </div>

          <div class="modal-actions">
            <button type="button" class="btn-cancel" @click="isModalOpen = false">Hủy</button>
            <button type="submit" class="btn-save" :disabled="isSaving">
              {{ isSaving ? 'Đang lưu...' : 'Lưu Booking' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';

const listBookings = ref([]);
const listCangBien = ref([]);
const listHangTau = ref([]);
const isLoading = ref(true);
const isSaving = ref(false);
const isModalOpen = ref(false);

const searchQuery = ref('');
const filterHangTau = ref('ALL');

const formData = ref({
  ma_booking: null, so_booking: '', ten_con_tau: '', so_chuyen: '',
  etd: '', eta: '', gio_cat_mang: '', ma_cang_di: null, ma_cang_den: null,
  ma_hang_tau: null, nguoi_sua_cuoi: null
});

// Hàm format ngày giờ đẹp (VD: 15:30 12/05/2024)
const formatDateTime = (dateString) => {
  if (!dateString) return "--";
  const d = new Date(dateString);
  return `${d.getHours().toString().padStart(2, '0')}:${d.getMinutes().toString().padStart(2, '0')} ${d.toLocaleDateString('vi-VN')}`;
};

// Hàm chuyển đổi datetime của MySQL để hiển thị đúng trong thẻ <input type="datetime-local">
const formatForInput = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toISOString().slice(0, 16);
};

// Lọc dữ liệu
const filteredBookings = computed(() => {
  return listBookings.value.filter(bk => {
    const search = searchQuery.value.toLowerCase();
    const matchSearch = !search || 
      bk.so_booking.toLowerCase().includes(search) || 
      (bk.ten_con_tau && bk.ten_con_tau.toLowerCase().includes(search)) ||
      (bk.so_chuyen && bk.so_chuyen.toLowerCase().includes(search));
      
    const matchHangTau = filterHangTau.value === 'ALL' || bk.ma_hang_tau === filterHangTau.value;

    return matchSearch && matchHangTau;
  });
});

const loadReferences = async () => {
  try {
    const res = await fetch('http://127.0.0.1:8000/api/bookings/references');
    const data = await res.json();
    if (data.success) {
      listCangBien.value = data.cang_bien;
      listHangTau.value = data.hang_tau;
    }
  } catch (error) {
    console.error("Lỗi lấy dữ liệu Cảng/Hãng tàu");
  }
};

const fetchBookings = async () => {
  isLoading.value = true;
  try {
    const res = await fetch('http://127.0.0.1:8000/api/bookings');
    const data = await res.json();
    if (data.success) listBookings.value = data.data;
  } catch (error) {
    console.error("Lỗi mạng!");
  } finally {
    isLoading.value = false;
  }
};

const openModal = async (bk = null) => {
  // Bắt buộc lấy lại danh sách Hãng tàu & Cảng mới nhất từ DB
  await loadReferences();

  if (bk) {
    formData.value = { 
      ...bk,
      etd: formatForInput(bk.etd),
      eta: formatForInput(bk.eta),
      gio_cat_mang: formatForInput(bk.gio_cat_mang)
    };
  } else {
    formData.value = {
      ma_booking: null, so_booking: '', ten_con_tau: '', so_chuyen: '',
      etd: '', eta: '', gio_cat_mang: '', ma_cang_di: null, ma_cang_den: null,
      ma_hang_tau: null, nguoi_sua_cuoi: null
    };
  }
  isModalOpen.value = true;
};

const saveBooking = async () => {
  isSaving.value = true;
  const user = JSON.parse(localStorage.getItem('sincere_user'));
  formData.value.nguoi_sua_cuoi = user ? (user.id || user.ma_tai_khoan) : null;

  try {
    const res = await fetch('http://127.0.0.1:8000/api/bookings/save', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(formData.value)
    });
    const data = await res.json();
    if (data.success) {
      alert(data.message);
      isModalOpen.value = false;
      fetchBookings();
    } else {
      alert("Lỗi: " + data.message);
    }
  } catch (e) {
    alert("Lỗi server!");
  } finally {
    isSaving.value = false;
  }
};

const handleDelete = async (id) => {
  if (!confirm("Hủy Booking này?")) return;
  const user = JSON.parse(localStorage.getItem('sincere_user'));
  
  try {
    const res = await fetch('http://127.0.0.1:8000/api/bookings/delete', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ 
        ma_booking: id,
        nguoi_sua_cuoi: user ? (user.id || user.ma_tai_khoan) : null 
      })
    });
    const data = await res.json();
    if (data.success) fetchBookings();
  } catch (e) {
    alert("Lỗi kết nối!");
  }
};

onMounted(() => {
  loadReferences(); // Gọi 1 lần lấy data Cảng và Hãng Tàu
  fetchBookings();
});
</script>

<style scoped src="../assets/quanlytaikhoan.css"></style>
<style scoped>
.badge-warning { background-color: #f39c12; color: white; }
</style>