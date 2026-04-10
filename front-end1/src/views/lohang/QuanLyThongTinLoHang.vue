<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">Quản lý thông tin Lô Hàng</h3>
    
    <div class="toolbar" style="display: flex; gap: 15px; flex-wrap: wrap;">
      <div class="search-box" style="flex: 1; min-width: 250px;">
        <input 
          type="text" 
          v-model="searchQuery" 
          placeholder="Tìm theo tên lô hàng, mã..."
          style="width: 100%; padding: 8px 12px; border-radius: 4px; border: 1px solid #ccc;"
        >
      </div>

      <select v-model="filterTrangThai" style="padding: 8px; border-radius: 4px; border: 1px solid #ccc; width: 200px;">
        <option value="ALL">- Tất cả Trạng thái -</option>
        <option v-for="st in listTrangThai" :key="st" :value="st">{{ st }}</option>
      </select>

      <button 
        @click="fetchData(); fetchReferences();" 
        style="padding: 8px 15px; border: 1px solid #ccc; border-radius: 4px; background: #fff; cursor: pointer; transition: 0.2s;"
        title="Tải lại dữ liệu"
      >
        🔄 Làm mới
      </button>

      <button class="btn btn-success" @click="router.push('/lo-hang/thong-tin-lo-hang/add')">+ TẠO LÔ HÀNG MỚI</button>
    </div>

    <div v-if="isLoading" style="text-align: center; padding: 20px; color: #3498db;">
      Đang tải dữ liệu Lô hàng...
    </div>

    <div v-else class="table-card" style="margin-top: 15px;">
      <table>
        <thead>
          <tr>
            <th>Mã Lô</th>
            <th>Tên Lô Hàng</th>
            <th>Khách Hàng</th>
            <th>Điều kiện (Incoterms)</th>
            <th>Booking</th>
            <th>Nguồn gốc</th>
            <th>Trạng thái</th>
            <th>Người sửa cuối</th>
            <th style="text-align: center;">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="lh in filteredLoHang" :key="lh.ma_lo_hang">
            <td class="fw-bold">#{{ lh.ma_lo_hang }}</td>
            <td class="fw-bold" style="color: #2980b9;">{{ lh.ten_lo_hang }}</td>
            <td>{{ lh.ten_khach_hang || '---' }}</td>
            <td><span class="badge" style="background-color: #9b59b6; color: white;">{{ lh.dieu_kien_thuong_mai }}</span></td>
            <td>{{ lh.so_booking || 'Chưa gắn' }}</td>
            <td style="max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" :title="lh.nguon_goc">{{ lh.nguon_goc || '---' }}</td>
            <td>
              <span class="badge" :class="statusClass(lh.trang_thai_lo_hang)">
                {{ lh.trang_thai_lo_hang }}
              </span>
            </td>
            <td style="color: #2c3e50; font-weight: 500;">{{ lh.nguoi_sua_doi || '---' }}</td>
            <td style="text-align: center; display: flex; gap: 10px; justify-content: center;">
              <button class="action-btn text-primary" @click="router.push('/lo-hang/thong-tin-lo-hang/edit/' + lh.ma_lo_hang)" title="Sửa">✏️</button>
              <button class="action-btn text-danger" @click="handleDelete(lh.ma_lo_hang)" title="Xóa">🗑️</button>
            </td>
          </tr>
          <tr v-if="filteredLoHang.length === 0">
            <td colspan="9" style="text-align: center; padding: 20px; color: #7f8c8d;">
              Không tìm thấy lô hàng nào!
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
const listLoHang = ref([]);
const listKhachHang = ref([]);
const listBooking = ref([]);
const isLoading = ref(true);
const searchQuery = ref('');
const filterTrangThai = ref('ALL');

const listTrangThai = ['Mới tạo', 'Đang chờ xử lý', 'Đang vận chuyển', 'Đã thông quan', 'Hoàn tất', 'Hủy'];

const statusClass = (status) => {
  if (['Hoàn tất', 'Đang vận chuyển', 'Đã thông quan'].includes(status)) return 'badge-active';
  if (status === 'Hủy') return 'badge-locked';
  return 'badge-warning';
};

const filteredLoHang = computed(() => {
  return listLoHang.value.filter(lh => {
    const search = searchQuery.value.toLowerCase();
    const matchSearch = !search || 
      (lh.ten_lo_hang && lh.ten_lo_hang.toLowerCase().includes(search)) || 
      (lh.ma_lo_hang && lh.ma_lo_hang.toString().includes(search));
      
    const matchTrangThai = filterTrangThai.value === 'ALL' || lh.trang_thai_lo_hang === filterTrangThai.value;
    return matchSearch && matchTrangThai;
  });
});

const fetchReferences = async () => {
  try {
    const res = await fetch('http://127.0.0.1:8000/api/lo-hang/references');
    const data = await res.json();

    if (data.success) {
      listKhachHang.value = data.khach_hang;
      listBooking.value = data.booking;
    }
  } catch (error) {
    console.error("Lỗi lấy dữ liệu tham chiếu");
  }
};

const fetchData = async () => {
  isLoading.value = true;
  try {
    const res = await fetch('http://127.0.0.1:8000/api/lo-hang');
    const data = await res.json();
    if (data.success) listLoHang.value = data.data;
  } catch (error) { console.error("Lỗi lấy dữ liệu Lô hàng!"); }
  finally { isLoading.value = false; }
};

const handleDelete = async (id) => {
  if (!confirm("Bạn có chắc muốn xóa lô hàng này?")) return;
  const user = JSON.parse(localStorage.getItem('sincere_user'));
  try {
    const res = await fetch('http://127.0.0.1:8000/api/lo-hang/delete', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ma_lo_hang: id, nguoi_sua_cuoi: user ? (user.id || user.ma_tai_khoan) : null })
    });
    const data = await res.json();
    if (data.success) { alert("Đã xóa lô hàng!"); fetchData(); }
    else { alert("Lỗi: " + data.message); }
  } catch (e) { alert("Lỗi kết nối!"); }
};

onMounted(() => { fetchData(); fetchReferences(); });
</script>

<style scoped src="../../assets/quanlytaikhoan.css"></style>
<style scoped>
.badge-warning { background-color: #f39c12; color: white; }
</style>