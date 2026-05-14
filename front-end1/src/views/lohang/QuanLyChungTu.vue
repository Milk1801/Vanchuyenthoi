<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">Quản lý Chứng từ theo Lô hàng</h3>
    
    <div class="toolbar" style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 15px;">
      <div class="search-box" style="flex: 1; min-width: 250px;">
        <input 
          type="text" 
          v-model="searchQuery" 
          placeholder="🔍 Tìm theo Tên lô hàng, Mã lô, Booking..."
          style="width: 100%; padding: 8px 12px; border-radius: 4px; border: 1px solid #ccc;"
        >
      </div>
      <button @click="fetchData" class="btn btn-secondary">🔄 Làm mới</button>
    </div>

    <div v-if="isLoading" style="text-align: center; padding: 20px; color: #3498db;">
      Đang tải dữ liệu lô hàng...
    </div>

    <div v-else class="table-card">
      <table>
        <thead>
          <tr>
            <th style="width: 50px; text-align: center;">STT</th>
            <th>Mã Lô</th>
            <th>Tên Lô Hàng</th>
            <th>Khách Hàng</th>
            <th>Số Booking</th>
            <th>Trạng thái</th>
            <th style="text-align: center;">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(lh, index) in filteredLoHang" :key="lh.ma_lo_hang">
            <td style="text-align: center; color: #7f8c8d;">{{ index + 1 }}</td>
            <td>{{ lh.ma_lo_hang }}</td>
            <td style="font-weight: bold; color: #2c3e50;">{{ lh.ten_lo_hang }}</td>
            <td>{{ lh.ten_khach_hang || '---' }}</td>
            <td style="color: #2980b9; font-weight: bold;">{{ lh.so_booking || 'Chưa gắn' }}</td>
            <td>
              <span class="badge" :class="statusClass(lh.trang_thai_lo_hang)">
                {{ lh.trang_thai_lo_hang }}
              </span>
            </td>
            <td style="text-align: center;">
              <button 
                class="btn-manage" 
                @click="router.push(`/lo-hang/chung-tu/chi-tiet/${lh.ma_lo_hang}`)"
                title="Quản lý chứng từ cho lô hàng này"
              >
                📂 Quản lý chứng từ
              </button>
            </td>
          </tr>
          <tr v-if="filteredLoHang.length === 0">
            <td colspan="7" style="text-align: center; padding: 30px; color: #95a5a6;">
              Không tìm thấy lô hàng nào phù hợp với tìm kiếm.
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const listLoHang = ref([]);
const isLoading = ref(true);
const searchQuery = ref(''); 

const statusClass = (status) => {
  if (['Hoàn tất', 'Đang vận chuyển', 'Đã thông quan'].includes(status)) return 'badge-active';
  if (status === 'Hủy') return 'badge-locked';
  return 'badge-warning';
};

const filteredLoHang = computed(() => {
  const q = searchQuery.value.toLowerCase().trim();
  if (!q) return listLoHang.value;
  return listLoHang.value.filter(lh => 
    (lh.ten_lo_hang && lh.ten_lo_hang.toLowerCase().includes(q)) ||
    (lh.ma_lo_hang && lh.ma_lo_hang.toString().includes(q)) ||
    (lh.so_booking && lh.so_booking.toLowerCase().includes(q)) ||
    (lh.ten_khach_hang && lh.ten_khach_hang.toLowerCase().includes(q)))
  });

const fetchData = async () => {
  isLoading.value = true;
  try {
    const res = await fetch(`${import.meta.env.VITE_API_URL}/lo-hang`);
    const data = await res.json();
    if (data.success) listLoHang.value = data.data;
  } catch (error) {
    console.error("Lỗi lấy dữ liệu Lô hàng!");
  } finally { isLoading.value = false; }
};

onMounted(fetchData);
</script>

<style scoped src="../../assets/quanlytaikhoan.css"></style>
<style scoped>
.btn-manage {
  background: #3498db;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 4px;
  cursor: pointer;
  font-weight: bold;
  transition: 0.3s;
}
.btn-manage:hover {
  background: #2980b9;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(52, 152, 219, 0.3);
}
.badge-warning { background-color: #f39c12; color: white; }
</style>