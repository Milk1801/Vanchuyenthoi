<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">Quản lý Vận đơn (Bill of Lading)</h3>
    
    <div class="toolbar" style="display: flex; gap: 15px; flex-wrap: wrap;">
      <div class="search-box" style="flex: 1; min-width: 250px;">
        <input 
          type="text" 
          v-model="searchQuery" 
          placeholder="Tìm theo tên vận đơn, mã..."
          style="width: 100%; padding: 8px 12px; border-radius: 4px; border: 1px solid #ccc;"
        >
      </div>

      <select v-model="filterLoaiVanDon" style="padding: 8px; border-radius: 4px; border: 1px solid #ccc; width: 200px;">
        <option value="ALL">- Tất cả Loại vận đơn -</option>
        <option v-for="type in listLoaiVanDon" :key="type" :value="type">{{ type }}</option>
      </select>

      <button 
        @click="fetchData();" 
        style="padding: 8px 15px; border: 1px solid #ccc; border-radius: 4px; background: #fff; cursor: pointer; transition: 0.2s;"
        title="Tải lại dữ liệu"
      >
        🔄 Làm mới
      </button>

      <button class="btn btn-success" @click="router.push('/van-tai/quan-ly-van-don/add')">+ TẠO VẬN ĐƠN MỚI</button>
    </div>

    <div v-if="isLoading" style="text-align: center; padding: 20px; color: #3498db;">
      Đang tải dữ liệu Vận đơn...
    </div>

    <div v-else class="table-card" style="margin-top: 15px;">
      <table>
        <thead>
          <tr>
            <th>Mã Vận Đơn</th>
            <th>Số Vận Đơn</th>
            <th>Loại Vận Đơn</th>
            <th>Lô hàng</th>
            <th>POL / POD</th>
            <th>Ngày PH</th>
            <th>Cont/Seal</th>
            <th style="text-align: center;">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="vd in filteredVanDon" :key="vd.ma_van_don">
            <td class="fw-bold">{{ vd.ma_van_don }}</td>
            <td class="fw-bold" style="color: #2980b9;">{{ vd.so_van_don }}</td>
            <td><span class="badge badge-active">{{ vd.loai_van_don }}</span></td>
            <td>{{ vd.ten_lo_hang || '---' }}</td>
            <td><strong>{{ vd.ten_cang_di }}</strong> ➔ <strong>{{ vd.ten_cang_den }}</strong></td>
            <td>{{ formatDate(vd.ngay_phat_hanh) }}</td>
            <td>
              <span style="font-size: 11px;">
                C: {{ vd.so_cont || 'N/A' }}<br>S: {{ vd.so_chi || 'N/A' }}
              </span>
            </td>
            <td style="text-align: center;">
              <button class="action-btn text-primary" @click="router.push('/van-tai/quan-ly-van-don/edit/' + vd.ma_van_don)" title="Cập nhật">✏️</button>
              <button class="action-btn text-danger" @click="handleDelete(vd.ma_van_don)" title="Xóa">🗑️</button>
            </td>
          </tr>
          <tr v-if="filteredVanDon.length === 0">
            <td colspan="8" style="text-align: center; padding: 20px; color: #7f8c8d;">
              Không tìm thấy vận đơn nào!
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
const listVanDon = ref([]);
const isLoading = ref(true);
const searchQuery = ref('');
const filterLoaiVanDon = ref('ALL');

const listLoaiVanDon = ['Original B/L', 'Surrendered B/L', 'Seaway Bill'];

const formatDate = (dateString) => {
  if (!dateString) return "---";
  return new Date(dateString).toLocaleDateString('vi-VN');
};

const filteredVanDon = computed(() => {
  return listVanDon.value.filter(vd => {
    const search = searchQuery.value.toLowerCase();
    const matchSearch = !search || 
      (vd.so_van_don && vd.so_van_don.toLowerCase().includes(search)) || 
      (vd.ma_van_don && vd.ma_van_don.toString().includes(search));
      
    const matchLoai = filterLoaiVanDon.value === 'ALL' || vd.loai_van_don === filterLoaiVanDon.value;
    return matchSearch && matchLoai;
  });
});

const fetchData = async () => {
  isLoading.value = true;
  try {
    const res = await fetch('http://127.0.0.1:8000/api/van-don');
    const data = await res.json();
    if (data.success) listVanDon.value = data.data;
  } catch (error) { console.error("Lỗi lấy dữ liệu Vận đơn!"); }
  finally { isLoading.value = false; }
};

const handleDelete = async (id) => {
  if (!confirm("Bạn có chắc muốn xóa lô hàng này?")) return;
  const user = JSON.parse(localStorage.getItem('sincere_user'));
  try {
    const res = await fetch('http://127.0.0.1:8000/api/van-don/delete', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ma_van_don: id, nguoi_sua_cuoi: user ? (user.id || user.ma_tai_khoan) : null })
    });
    const data = await res.json();
    if (data.success) { alert("Đã xóa vận đơn!"); fetchData(); }
    else { alert("Lỗi xóa: " + data.message); }
  } catch (e) { alert("Lỗi kết nối!"); }
};

onMounted(() => { fetchData(); });
</script>

<style scoped src="../../assets/quanlytaikhoan.css"></style>
<style scoped>
.badge-warning { background-color: #f39c12; color: white; }
</style>