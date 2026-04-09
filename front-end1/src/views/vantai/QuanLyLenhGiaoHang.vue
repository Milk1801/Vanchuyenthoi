<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">Quản Lý Lệnh Giao Hàng (D/O)</h3>
    
    <div class="toolbar" style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 20px;">
      <div class="search-box" style="flex: 1; min-width: 250px;">
        <input 
          type="text" 
          v-model="searchQuery" 
          placeholder="🔍 Tìm theo Số Booking hoặc Tên lô hàng..."
          style="width: 100%; padding: 10px 15px; border-radius: 6px; border: 1px solid #ccc; font-size: 14px;"
        >
      </div>
      <button @click="fetchData()" class="btn-refresh" style="padding: 10px 20px; border-radius: 6px;">🔄 Làm mới</button>
      <button class="btn btn-success" @click="openModal()" style="border-radius: 6px; padding: 10px 20px;">
        ➕ TẠO LỆNH GIAO HÀNG
      </button>
    </div>

    <div class="table-container" style="background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
          <tr>
            <th style="padding: 12px 15px; width: 100px;">Mã Lệnh</th>
            <th style="padding: 12px 15px;">Thuộc Lô Hàng</th>
            <th style="padding: 12px 15px;">Số Booking</th>
            <th style="padding: 12px 15px;">Ngày phát hành</th>
            <th style="padding: 12px 15px; text-align: center; width: 150px;">Hành động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="isLoading">
            <td colspan="5" style="text-align: center; padding: 20px;">Đang tải dữ liệu...</td>
          </tr>
          <tr v-else-if="filteredList.length === 0">
            <td colspan="5" style="text-align: center; padding: 20px;">Không có dữ liệu!</td>
          </tr>
          <tr v-for="item in filteredList" :key="item.ma_lenh_giao_hang" style="border-bottom: 1px solid #eee;">
            <td style="padding: 12px 15px; font-weight: bold; color: #2980b9;">#{{ item.ma_lenh_giao_hang }}</td>
            <td style="padding: 12px 15px;">{{ item.ten_lo_hang || 'N/A' }}</td>
            <td style="padding: 12px 15px;">{{ item.so_booking || 'N/A' }}</td>
            <td style="padding: 12px 15px;">{{ formatDateTime(item.ngay_phat_hanh) }}</td>
            <td style="padding: 12px 15px; text-align: center;">
              <button @click="openModal(item)" style="margin-right: 10px; cursor: pointer; border: none; background: none;" title="Sửa">✏️</button>
              <button @click="handleDelete(item.ma_lenh_giao_hang)" style="cursor: pointer; border: none; background: none;" title="Xóa">🗑️</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="isModalOpen" class="modal-overlay">
      <div class="modal-content" style="max-width: 500px; padding: 20px; background: white; border-radius: 8px; width: 100%;">
        <h3 style="margin-top: 0;">{{ formData.ma_lenh_giao_hang ? 'Cập nhật Lệnh Giao Hàng' : 'Thêm Lệnh Giao Hàng' }}</h3>
        
        <form @submit.prevent="saveData">
          
          <div style="margin-bottom: 15px; position: relative;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Thuộc Lô hàng *</label>
            
            <div v-if="isDropdownOpen" @click="isDropdownOpen = false" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9;"></div>
            
            <div style="position: relative; z-index: 10;">
              <input 
                type="text" 
                v-model="searchLoHangInput" 
                @focus="isDropdownOpen = true"
                @input="isDropdownOpen = true"
                placeholder="🔍 Gõ số booking hoặc tên lô hàng..." 
                required
                style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;"
              >
              
              <div v-if="isDropdownOpen" style="position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ccc; border-radius: 4px; max-height: 200px; overflow-y: auto; box-shadow: 0 4px 8px rgba(0,0,0,0.1); margin-top: 2px;">
                <div v-if="filteredLoHangOptions.length === 0" style="padding: 10px; text-align: center; color: #999;">
                  Không tìm thấy kết quả nào
                </div>
                <div 
                  v-for="lo in filteredLoHangOptions" 
                  :key="lo.ma_lo_hang" 
                  @click="selectLoHang(lo)"
                  style="padding: 10px; border-bottom: 1px solid #eee; cursor: pointer;"
                  onmouseover="this.style.background='#f0f8ff'"
                  onmouseout="this.style.background='white'"
                >
                  <strong style="color: #2980b9;">[{{ lo.so_booking }}]</strong> - {{ lo.ten_lo_hang }}
                </div>
              </div>
            </div>
            <small v-if="!formData.ma_lo_hang && searchLoHangInput" style="color: #e74c3c; display: block; margin-top: 5px;">
              Vui lòng chọn một lô hàng từ danh sách xổ xuống!
            </small>
          </div>

          <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Ngày phát hành *</label>
            <input type="datetime-local" v-model="formData.ngay_phat_hanh" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
          </div>

          <div style="text-align: right;">
            <button type="button" @click="isModalOpen = false" style="padding: 8px 15px; margin-right: 10px; cursor: pointer; border: 1px solid #ccc; background: #fff; border-radius: 4px;">Hủy</button>
            <button type="submit" :disabled="isSaving || !formData.ma_lo_hang" style="padding: 8px 15px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer;">
              {{ isSaving ? 'Đang lưu...' : 'Lưu lại' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';

const listData = ref([]);
const listLoHang = ref([]);
const isLoading = ref(true);
const isSaving = ref(false);
const isModalOpen = ref(false);

const searchQuery = ref(''); 

// Biến cho Combobox Tìm kiếm
const searchLoHangInput = ref('');
const isDropdownOpen = ref(false);

const formData = ref({
  ma_lenh_giao_hang: null,
  ma_lo_hang: null,
  ngay_phat_hanh: ''
});

// Hàm format thời gian hiển thị
const formatDateTime = (dateStr) => {
  if (!dateStr) return 'N/A';
  const date = new Date(dateStr);
  return date.toLocaleString('vi-VN');
};

// 1. Lọc danh sách Bảng chính
const filteredList = computed(() => {
  return listData.value.filter(item => {
    const search = searchQuery.value.toLowerCase();
    return !search || 
      (item.ten_lo_hang && item.ten_lo_hang.toLowerCase().includes(search)) || 
      (item.so_booking && item.so_booking.toLowerCase().includes(search));
  });
});

// 2. Lọc danh sách Lô hàng trong Combobox
const filteredLoHangOptions = computed(() => {
  if (!searchLoHangInput.value) return listLoHang.value;
  const term = searchLoHangInput.value.toLowerCase();
  return listLoHang.value.filter(lo => 
    (lo.ten_lo_hang && lo.ten_lo_hang.toLowerCase().includes(term)) || 
    (lo.so_booking && lo.so_booking.toLowerCase().includes(term))
  );
});

// 3. Xử lý khi click chọn Lô hàng từ danh sách thả xuống
const selectLoHang = (lo) => {
  formData.value.ma_lo_hang = lo.ma_lo_hang; // Lưu ID để gửi xuống DB
  searchLoHangInput.value = `[${lo.so_booking}] - ${lo.ten_lo_hang}`; // Hiện text đẹp mắt lên ô input
  isDropdownOpen.value = false; // Đóng menu
};

// Theo dõi nếu người dùng tự ý xóa ô tìm kiếm thì reset ID
watch(searchLoHangInput, (newVal) => {
  if (newVal === '') {
    formData.value.ma_lo_hang = null;
  }
});

const fetchData = async () => {
  isLoading.value = true;
  try {
    const res = await fetch('http://127.0.0.1:8000/api/lenh-giao-hang');
    const data = await res.json();
    if (data.success) {
      listData.value = data.data;
      listLoHang.value = data.lo_hang;
    }
  } catch (error) {
    console.error("Lỗi lấy dữ liệu!");
  } finally {
    isLoading.value = false;
  }
};

const openModal = (item = null) => {
  if (item) {
    // Nếu là SỬA: gán giá trị cũ vào form
    formData.value = { 
      ma_lenh_giao_hang: item.ma_lenh_giao_hang, 
      ma_lo_hang: item.ma_lo_hang,
      ngay_phat_hanh: item.ngay_phat_hanh ? new Date(item.ngay_phat_hanh).toISOString().slice(0, 16) : ''
    };
    
    // Tìm lô hàng tương ứng để hiển thị tên lên ô tìm kiếm
    const selectedLo = listLoHang.value.find(l => l.ma_lo_hang === item.ma_lo_hang);
    if (selectedLo) {
      searchLoHangInput.value = `[${selectedLo.so_booking}] - ${selectedLo.ten_lo_hang}`;
    } else {
      searchLoHangInput.value = '';
    }
  } else {
    // Nếu là THÊM MỚI
    formData.value = { ma_lenh_giao_hang: null, ma_lo_hang: null, ngay_phat_hanh: '' };
    searchLoHangInput.value = '';
  }
  
  isDropdownOpen.value = false;
  isModalOpen.value = true;
};

const saveData = async () => {
  if (!formData.value.ma_lo_hang) {
    alert("Vui lòng chọn Lô hàng hợp lệ từ danh sách!");
    return;
  }

  isSaving.value = true;
  try {
    const res = await fetch('http://127.0.0.1:8000/api/lenh-giao-hang/save', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(formData.value)
    });
    const data = await res.json();
    if (data.success) {
      isModalOpen.value = false;
      fetchData();
    } else {
      alert("Lỗi: " + data.message);
    }
  } catch (e) {
    alert("Lỗi kết nối server!");
  } finally {
    isSaving.value = false;
  }
};

const handleDelete = async (id) => {
  if (!confirm("Hủy vĩnh viễn lệnh giao hàng này?")) return;
  try {
    const res = await fetch('http://127.0.0.1:8000/api/lenh-giao-hang/delete', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ma_lenh_giao_hang: id })
    });
    const data = await res.json();
    if (data.success) fetchData();
  } catch (e) {
    alert("Lỗi!");
  }
};

onMounted(fetchData);
</script>

<style scoped>
.modal-overlay {
  position: fixed; top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000;
}
</style>