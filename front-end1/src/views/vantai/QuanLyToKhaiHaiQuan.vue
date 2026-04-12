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
        <div class="search-box" style="flex:1 1 200px; min-width:180px;">
          <input type="date" v-model="searchFilters.ngay_thong_quan" title="Lọc theo ngày thông quan">
        </div>
        <div class="search-box" style="flex:1 1 200px; min-width:180px;">
          <select v-model="searchFilters.ten_nguoi_sua">
            <option value="">-- Tất cả người sửa --</option>
            <option v-for="name in uniqueNguoiSua" :key="name" :value="name">{{ name }}</option>
          </select>
        </div>

        <div class="search-box" style="flex:0 0 auto; min-width:140px; display:flex; align-items:flex-end;">
          <button type="button" @click="clearFilters()" style="width:100%; background-color:#e74c3c; color:white; border:none; padding:8px 12px; border-radius:4px; cursor:pointer; font-weight:500;">Xóa lọc</button>
        </div>
      </div>
      <button class="btn btn-success" @click="router.push('/van-tai/to-khai-hai-quan/add')">+ TẠO TỜ KHAI MỚI</button>
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
              <button class="action-btn text-primary" @click="router.push('/van-tai/to-khai-hai-quan/edit/' + tk.ma_to_khai_hai_quan)" title="Sửa">✏️</button>
              <button class="action-btn text-danger" @click="handleDelete(tk.ma_to_khai_hai_quan)" title="Xóa">🗑️</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const listToKhai = ref([]);
const isLoadingData = ref(true);

const listPhanLuong = ['Luồng Xanh', 'Luồng Vàng', 'Luồng Đỏ'];
const listKetQua = ['Chờ xử lý', 'Đã thông quan', 'Từ chối'];

const searchFilters = ref({
  ten_lo_hang: '',
  phan_luong: '',
  ket_qua_thong_quan: '',
  ngay_thong_quan: '',
  ten_nguoi_sua: ''
});

const uniqueNguoiSua = computed(() => {
  const names = listToKhai.value.map(item => item.ten_nguoi_sua).filter(Boolean);
  return [...new Set(names)];
});

const filteredData = computed(() => {
  return listToKhai.value.filter(item => {
    const matchLoHang = !searchFilters.value.ten_lo_hang || 
      (item.ten_lo_hang && item.ten_lo_hang.toLowerCase().includes(searchFilters.value.ten_lo_hang.toLowerCase()));
    const matchPhanLuong = !searchFilters.value.phan_luong || item.phan_luong === searchFilters.value.phan_luong;
    const matchKetQua = !searchFilters.value.ket_qua_thong_quan || item.ket_qua_thong_quan === searchFilters.value.ket_qua_thong_quan;
    
    const matchDate = !searchFilters.value.ngay_thong_quan || (item.ngay_thong_quan && item.ngay_thong_quan.includes(searchFilters.value.ngay_thong_quan));
    const matchNguoiSua = !searchFilters.value.ten_nguoi_sua || item.ten_nguoi_sua === searchFilters.value.ten_nguoi_sua;

    return matchLoHang && matchPhanLuong && matchKetQua && matchDate && matchNguoiSua;
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
  } catch (error) {
    console.error('Fetch error:', error);
    alert("Không thể kết nối API lấy danh sách!");
  } finally {
    isLoadingData.value = false;
  }
};

const clearFilters = () => {
  searchFilters.value = {
    ten_lo_hang: '',
    phan_luong: '',
    ket_qua_thong_quan: '',
    ngay_thong_quan: '',
    ten_nguoi_sua: ''
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
