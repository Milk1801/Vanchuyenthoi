<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">
      {{ formData.ma_luu_bai ? 'Cập Nhật Thông Tin Lưu Bãi' : 'Thêm Thông Tin Lưu Bãi' }}
    </h3>

    <div class="table-card" style="padding: 25px; max-width: 800px;">
      <form @submit.prevent="saveData">
        <div class="form-group">
          <label>Lô Hàng Liên Kết *</label>
          <select v-model="formData.ma_lo_hang" required style="height: 44px;">
            <option :value="null">-- Chọn lô hàng --</option>
            <option v-for="lh in listLoHang" :key="lh.ma_lo_hang" :value="lh.ma_lo_hang">{{ lh.ten_lo_hang }}</option>
          </select>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
          <div class="form-group">
            <label>Ngày Bắt Đầu Lưu Bãi</label>
            <input type="datetime-local" v-model="formData.ngay_bat_dau_luu_bai" style="height: 44px;">
          </div>
          <div class="form-group">
            <label>Số Ngày Lưu Bãi Miễn Phí</label>
            <input type="number" v-model="formData.ngay_luu_bai_mien_phi" min="0" style="height: 44px;">
          </div>
          <div class="form-group">
            <label>Trạng Thái Lưu Bãi</label>
            <select v-model="formData.trang_thai_luu_bai" style="height: 44px;">
              <option v-for="st in ['Đang lưu bãi', 'Đã rút hàng', 'Đã trả vỏ']" :key="st" :value="st">{{ st }}</option>
            </select>
          </div>
          <div class="form-group">
            <label>Cược Vỏ</label>
            <select v-model="formData.cuoc_vo" style="height: 44px;">
              <option value="Không">Không</option>
              <option value="Có">Có</option>
            </select>
          </div>
        </div>

        <div style="margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end;">
          <button type="button" class="btn-cancel" @click="router.back()">Hủy</button>
          <button type="submit" class="btn-save" :disabled="isSaving">
            {{ isSaving ? 'Đang lưu...' : 'Lưu Thông Tin' }}
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
const listLoHang = ref([]);

const formData = ref({
  ma_luu_bai: null, ma_lo_hang: null, ngay_bat_dau_luu_bai: '',
  ngay_luu_bai_mien_phi: 0, trang_thai_luu_bai: 'Đang lưu bãi', cuoc_vo: 'Không',
  nguoi_sua_cuoi: null
});

const fetchReferences = async () => {
  const id = route.params.id || '';
  const res = await fetch(`http://127.0.0.1:8000/api/luu-bai/references?ma_luu_bai=${id}`);
  const data = await res.json();
  if (data.success) listLoHang.value = data.lo_hang;
};

const fetchDetail = async (id) => {
  const res = await fetch('http://127.0.0.1:8000/api/luu-bai');
  const data = await res.json();
  if (data.success) {
    const found = data.data.find(x => String(x.ma_luu_bai) === String(id));
    if (found) {
      formData.value = { 
        ...found,
        ngay_bat_dau_luu_bai: found.ngay_bat_dau_luu_bai ? new Date(found.ngay_bat_dau_luu_bai).toISOString().slice(0, 16) : ''
      };
    }
  }
};

const saveData = async () => {
  isSaving.value = true;
  const user = JSON.parse(localStorage.getItem('sincere_user'));
  formData.value.nguoi_sua_cuoi = user ? (user.id || user.ma_tai_khoan) : null;

  try {
    const res = await fetch('http://127.0.0.1:8000/api/luu-bai/save', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(formData.value)
    });
    const data = await res.json();
    if (data.success) { alert(data.message); router.push('/van-tai/luu-bai'); }
    else alert(data.message);
  } catch (e) { alert("Lỗi server"); }
  finally { isSaving.value = false; }
};

onMounted(async () => {
  await fetchReferences();
  if (route.params.id) fetchDetail(route.params.id);
});
</script>

<style scoped src="../../assets/quanlytaikhoan.css"></style>