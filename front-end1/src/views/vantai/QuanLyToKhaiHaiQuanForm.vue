<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">
      {{ formData.ma_to_khai_hai_quan ? 'Cập Nhật Tờ Khai' : 'Thêm Tờ Khai Mới' }}
    </h3>

    <div class="table-card" style="padding: 20px;">
      <div v-if="isLoadingData" style="text-align: center; padding: 20px; color: #3498db;">
        Đang tải thông tin...
      </div>
      <form v-else @submit.prevent="saveToKhai">
        <div class="form-group">
          <label>Lô Hàng Liên Kết *</label>
          <select v-model="formData.ma_lo_hang" required>
            <option value="">-- Chọn lô hàng --</option>
            <option v-for="lh in listLoHang" :key="lh.ma_lo_hang" :value="lh.ma_lo_hang">
              {{ lh.ten_lo_hang }}
            </option>
          </select>
        </div>

        <div class="form-group">
          <label>Ngày Thông Quan</label>
          <input type="datetime-local" v-model="formData.ngay_thong_quan">
        </div>

        <div style="display: flex; gap: 15px;">
          <div class="form-group" style="flex: 1;">
            <label>Phân Luồng</label>
            <select v-model="formData.phan_luong">
              <option v-for="pl in listPhanLuong" :key="pl" :value="pl">{{ pl }}</option>
            </select>
          </div>
          <div class="form-group" style="flex: 1;">
            <label>Kết Quả</label>
            <select v-model="formData.ket_qua_thong_quan">
              <option v-for="kq in listKetQua" :key="kq" :value="kq">{{ kq }}</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label>Ghi chú (Tùy chọn)</label>
          <textarea v-model="formData.ghi_chu" rows="2" style="width:100%; border:1px solid #ddd; border-radius:6px; padding:10px;"></textarea>
        </div>

        <div style="margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end;">
          <button type="button" class="btn-cancel" @click="router.back()" style="padding: 10px 25px;">Hủy</button>
          <button type="submit" class="btn-save" :disabled="isSaving" style="padding: 10px 25px;">
             {{ isSaving ? 'Đang lưu...' : 'Lưu Tờ Khai' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';

const router = useRouter();
const route = useRoute();
const isSaving = ref(false);
const isLoadingData = ref(false);

const listLoHang = ref([]);
const listPhanLuong = ['Luồng Xanh', 'Luồng Vàng', 'Luồng Đỏ'];
const listKetQua = ['Chờ xử lý', 'Đã thông quan', 'Từ chối'];

const formData = ref({
  ma_to_khai_hai_quan: null,
  ngay_thong_quan: '',
  phan_luong: 'Luồng Xanh',
  ket_qua_thong_quan: 'Chờ xử lý',
  ma_lo_hang: '',
  ghi_chu: '',
  nguoi_sua_cuoi: null
});

const fetchReferences = async (ma_to_khai = null) => {
  try {
    let url = 'http://127.0.0.1:8000/api/to-khai-hai-quan/references';
    if (ma_to_khai) {
      url += `?ma_to_khai_hai_quan=${ma_to_khai}`;
    }
    const response = await fetch(url);
    const data = await response.json();
    if (data.success) listLoHang.value = data.lo_hang;
  } catch (error) {
    console.error('Fetch refs error:', error);
  }
};

const fetchDetail = async (id) => {
  isLoadingData.value = true;
  try {
    const response = await fetch('http://127.0.0.1:8000/api/to-khai-hai-quan');
    const data = await response.json();
    if (data.success) {
      const found = data.data.find(item => String(item.ma_to_khai_hai_quan) === String(id));
      if (found) {
        formData.value = { 
          ...found,
          ngay_thong_quan: found.ngay_thong_quan ? new Date(found.ngay_thong_quan).toISOString().slice(0, 16) : ''
        };
      } else {
        alert("Không tìm thấy thông tin tờ khai!");
        router.push('/van-tai/to-khai-hai-quan');
      }
    }
  } catch (error) {
    console.error('Fetch detail error:', error);
  } finally {
    isLoadingData.value = false;
  }
};

const saveToKhai = async () => {
  if (!formData.value.ma_lo_hang) {
    alert("Vui lòng chọn lô hàng liên kết!");
    return;
  }

  isSaving.value = true;
  const user = JSON.parse(localStorage.getItem('sincere_user'));
  formData.value.nguoi_sua_cuoi = user ? (user.id || user.ma_tai_khoan) : null;
  
  try {
    const res = await fetch('http://127.0.0.1:8000/api/to-khai-hai-quan/save', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(formData.value)
    });
    const data = await res.json();
    if (data.success) {
      alert(data.message);
      router.push('/van-tai/to-khai-hai-quan');
    } else { alert("Lỗi: " + data.message); }
  } catch (e) { alert("Lỗi kết nối máy chủ!"); }
  finally { isSaving.value = false; }
};

onMounted(async () => {
  const id = route.params.id;
  await fetchReferences(id);
  if (id) {
    fetchDetail(id);
  }
});
</script>

<style scoped src="../../assets/quanlytaikhoan.css"></style>