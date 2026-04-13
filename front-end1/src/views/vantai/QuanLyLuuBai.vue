<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">Quản lý Thông Tin Lưu Bãi</h3>
    
    <div class="toolbar" style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 15px;">
      <div class="search-box" style="flex: 1; min-width: 250px;">
        <input type="text" v-model="searchQuery" placeholder="🔍 Tìm theo lô hàng..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #ccc;">
      </div>

      <select v-model="filterTrangThai" style="padding: 8px; border-radius: 6px; border: 1px solid #ccc; width: 180px;">
        <option value="ALL">-- Tất cả Trạng thái --</option>
        <option v-for="st in ['Đang lưu bãi', 'Đã rút hàng', 'Đã trả vỏ']" :key="st" :value="st">{{ st }}</option>
      </select>

      <button class="btn btn-success" @click="router.push('/van-tai/luu-bai/add')">+ THÊM THÔNG TIN LƯU BÃI</button>
    </div>

    <div v-if="isLoading" style="text-align: center; padding: 20px; color: #3498db;">Đang tải dữ liệu...</div>

    <div v-else class="table-card">
      <table>
        <thead>
          <tr>
            <th style="width: 50px; text-align: center;">STT</th>
            <th>Lô Hàng</th>
            <th>Ngày Bắt Đầu</th>
            <th>Ngày Miễn Phí (Ngày)</th>
            <th>Cược Vỏ</th>
            <th>Trạng Thái</th>
            <th>Người Sửa</th>
            <th style="text-align: center;">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in filteredData" :key="item.ma_luu_bai">
            <td style="text-align: center;">{{ index + 1 }}</td>
            <td class="fw-bold" style="color: #2980b9;">{{ item.ten_lo_hang }}</td>
            <td>{{ item.ngay_bat_dau_luu_bai ? new Date(item.ngay_bat_dau_luu_bai).toLocaleString('vi-VN') : '---' }}</td>
            <td style="text-align: center;">{{ item.ngay_luu_bai_mien_phi }}</td>
            <td>{{ item.cuoc_vo }}</td>
            <td>
              <span class="badge" :class="item.trang_thai_luu_bai === 'Đã trả vỏ' ? 'badge-active' : 'badge-warning'">
                {{ item.trang_thai_luu_bai }}
              </span>
            </td>
            <td>{{ item.ten_nguoi_sua || 'N/A' }}</td>
            <td style="text-align: center;">
              <div style="display: flex; gap: 8px; justify-content: center;">
                <button class="action-btn text-primary" @click="router.push('/van-tai/luu-bai/edit/' + item.ma_luu_bai)">✏️</button>
                <button class="action-btn text-danger" @click="handleDelete(item.ma_luu_bai)">🗑️</button>
              </div>
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
const listLuuBai = ref([]);
const isLoading = ref(true);
const searchQuery = ref('');
const filterTrangThai = ref('ALL');

const filteredData = computed(() => {
  return listLuuBai.value.filter(item => {
    const matchSearch = !searchQuery.value || item.ten_lo_hang.toLowerCase().includes(searchQuery.value.toLowerCase());
    const matchStatus = filterTrangThai.value === 'ALL' || item.trang_thai_luu_bai === filterTrangThai.value;
    return matchSearch && matchStatus;
  });
});

const fetchData = async () => {
  isLoading.value = true;
  try {
    const res = await fetch('http://127.0.0.1:8000/api/luu-bai');
    const data = await res.json();
    if (data.success) listLuuBai.value = data.data;
  } catch (e) { console.error(e); }
  finally { isLoading.value = false; }
};

const handleDelete = async (id) => {
  if (!confirm("Xác nhận xóa thông tin này?")) return;
  try {
    const res = await fetch('http://127.0.0.1:8000/api/luu-bai/delete', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ma_luu_bai: id })
    });
    const data = await res.json();
    if (data.success) fetchData();
  } catch (e) { alert("Lỗi kết nối"); }
};

onMounted(fetchData);
</script>

<style scoped src="../../assets/quanlytaikhoan.css"></style>
<style scoped>
.badge-warning { background-color: #f39c12; color: white; }
.badge-active { background-color: #27ae60; color: white; }
</style>