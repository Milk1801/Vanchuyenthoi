<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">
      {{ formData.ma_van_don ? 'Cập Nhật Vận Đơn' : 'Thêm Vận Đơn Mới' }}
    </h3>

    <div class="table-card" style="padding: 20px;">
      <div v-if="isLoadingData" style="text-align: center; padding: 20px; color: #3498db;">
        Đang tải thông tin...
      </div>
      <form v-else @submit.prevent="saveVanDon">
        <div style="display: flex; gap: 15px;">
          <div class="form-group" style="flex: 1;">
            <label>Số Vận Đơn *</label>
            <input v-model="formData.so_van_don" required placeholder="VD: HPH2024001">
          </div>
          <div class="form-group" style="flex: 1;">
            <label>Loại Vận Đơn</label>
            <select v-model="formData.loai_van_don">
              <option v-for="type in listLoaiVanDon" :key="type" :value="type">{{ type }}</option>
            </select>
          </div>
        </div>

        <div style="display: flex; gap: 15px;">
          <div class="form-group" style="flex: 1;">
            <label>Số Vận Đơn Gốc</label>
            <input v-model="formData.so_van_don_goc" placeholder="Nếu có">
          </div>
          <div class="form-group" style="flex: 1;">
            <label>Ngày Phát Hành</label>
            <input type="datetime-local" v-model="formData.ngay_phat_hanh">
          </div>
        </div>

        <div style="display: flex; gap: 15px;">
          <div class="form-group" style="flex: 1;">
            <label>Người Gửi Hàng</label>
            <select v-model="formData.ma_nguoi_gui_hang">
              <option :value="null">-- Chọn người gửi --</option>
              <option v-for="kh in listKhachHang" :key="kh.ma_khach_hang" :value="kh.ma_khach_hang">
                {{ kh.ten_khach_hang }}
              </option>
            </select>
          </div>
          <div class="form-group" style="flex: 1;">
            <label>Người Nhận Hàng</label>
            <select v-model="formData.ma_nguoi_nhan_hang">
              <option :value="null">-- Chọn người nhận --</option>
              <option v-for="kh in listKhachHang" :key="kh.ma_khach_hang" :value="kh.ma_khach_hang">
                {{ kh.ten_khach_hang }}
              </option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label>Bên được thông báo (Notify Party)</label>
          <select v-model="formData.ma_ben_duoc_thong_bao">
            <option :value="null">-- Chọn bên được thông báo --</option>
            <option value="SAME_AS_CONSIGNEE">SAME AS CONSIGNEE (Như người nhận)</option>
            <option v-for="kh in listKhachHang" :key="kh.ma_khach_hang" :value="kh.ma_khach_hang">
              {{ kh.ten_khach_hang }}
            </option>
          </select>
        </div>

        <div style="display: flex; gap: 15px;">
          <div class="form-group" style="flex: 1;">
            <label>Cảng Đi (POL)</label>
            <select v-model="formData.ma_cang_di">
              <option :value="null">-- Chọn cảng --</option>
              <option v-for="c in listCangBien" :key="c.ma_cang" :value="c.ma_cang">
                {{ c.ten_cang }}
              </option>
            </select>
          </div>
          <div class="form-group" style="flex: 1;">
            <label>Cảng Đến (POD)</label>
            <select v-model="formData.ma_cang_den">
              <option :value="null">-- Chọn cảng --</option>
              <option v-for="c in listCangBien" :key="c.ma_cang" :value="c.ma_cang">
                {{ c.ten_cang }}
              </option>
            </select>
          </div>
        </div>

        <div style="display: flex; gap: 15px;">
          <div class="form-group" style="flex: 1;">
            <label>Lô Hàng Liên Kết *</label>
            <select v-model="formData.ma_lo_hang" required>
              <option :value="null">-- Chọn lô hàng --</option>
              <option v-for="lh in listLoHang" :key="lh.ma_lo_hang" :value="lh.ma_lo_hang">
                {{ lh.ten_lo_hang }}
              </option>
            </select>
          </div>
          <div class="form-group" style="flex: 1;">
            <label>Phương Thức Đóng Cont</label>
            <select v-model="formData.phuong_thuc_dong_cont">
              <option value="FCL">FCL (Nguyên cont)</option>
              <option value="LCL">LCL (Hàng lẻ)</option>
            </select>
          </div>
        </div>

        <div style="display: flex; gap: 15px;">
          <div class="form-group" style="flex: 1;">
            <label>Số Container</label>
            <input v-model="formData.so_cont" placeholder="VD: TCNU1234567">
          </div>
          <div class="form-group" style="flex: 1;">
            <label>Số Chì (Seal)</label>
            <input v-model="formData.so_chi" placeholder="VD: S123456">
          </div>
        </div>

        <div class="form-group">
          <label>Điều Kiện Cước</label>
          <select v-model="formData.dieu_kien_cuoc">
            <option value="Freight Prepaid">Freight Prepaid (Cước trả trước)</option>
            <option value="Freight Collect">Freight Collect (Cước trả sau)</option>
          </select>
        </div>

        <div style="margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end;">
          <button type="button" class="btn-cancel" @click="router.back()" style="padding: 10px 25px;">Hủy</button>
          <button type="submit" class="btn-save" :disabled="isSaving" style="padding: 10px 25px;">
             {{ isSaving ? 'Đang lưu...' : 'Lưu Vận Đơn' }}
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

const listKhachHang = ref([]);
const listCangBien = ref([]);
const listLoHang = ref([]);
const listLoaiVanDon = ['Original B/L', 'Surrendered B/L', 'Seaway Bill'];

const formData = ref({
  ma_van_don: null, loai_van_don: 'Original B/L', ngay_phat_hanh: '',
  so_van_don_goc: '', so_van_don: '', so_cont: '', so_chi: '',
  phuong_thuc_dong_cont: 'FCL', dieu_kien_cuoc: 'Freight Prepaid',
  ma_nguoi_gui_hang: null, ma_nguoi_nhan_hang: null, ma_ben_duoc_thong_bao: null,
  ma_cang_di: null, ma_cang_den: null, ma_lo_hang: null, nguoi_sua_cuoi: null
});

const fetchReferences = async () => {
  try {
    const res = await fetch('http://127.0.0.1:8000/api/van-don/references');
    const data = await res.json();
    if (data.success) {
      listKhachHang.value = data.khach_hang;
      listCangBien.value = data.cang_bien;
      listLoHang.value = data.lo_hang;
    }
  } catch (error) { console.error("Lỗi lấy dữ liệu tham chiếu"); }
};

const fetchDetail = async (id) => {
  isLoadingData.value = true;
  try {
    const res = await fetch('http://127.0.0.1:8000/api/van-don');
    const data = await res.json();
    if (data.success) {
      const found = data.data.find(item => String(item.ma_van_don) === String(id));
      if (found) {
        let benThongBao = found.ma_ben_duoc_thong_bao;
        // Nếu ID bên thông báo trùng với ID người nhận, hiển thị là SAME AS CONSIGNEE
        if (benThongBao && benThongBao === found.ma_nguoi_nhan_hang) {
          benThongBao = 'SAME_AS_CONSIGNEE';
        }

        formData.value = { 
          ...found,
          ma_ben_duoc_thong_bao: benThongBao,
          ngay_phat_hanh: found.ngay_phat_hanh ? new Date(found.ngay_phat_hanh).toISOString().slice(0, 16) : ''
        };
      }
    }
  } catch (e) { console.error(e); }
  finally { isLoadingData.value = false; }
};

const saveVanDon = async () => {
  isSaving.value = true;
  const user = JSON.parse(localStorage.getItem('sincere_user'));
  
  const dataToSend = { ...formData.value };
  dataToSend.nguoi_sua_cuoi = user ? (user.id || user.ma_tai_khoan) : null;

  // Xử lý logic SAME AS CONSIGNEE trước khi gửi lên server
  if (dataToSend.ma_ben_duoc_thong_bao === 'SAME_AS_CONSIGNEE') {
    dataToSend.ma_ben_duoc_thong_bao = dataToSend.ma_nguoi_nhan_hang;
  }

  try {
    const res = await fetch('http://127.0.0.1:8000/api/van-don/save', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(dataToSend)
    });
    const data = await res.json();
    if (data.success) { alert(data.message); router.push('/van-tai/quan-ly-van-don'); }
    else { alert("Lỗi: " + data.message); }
  } catch (e) { alert("Lỗi server!"); }
  finally { isSaving.value = false; }
};

onMounted(async () => {
  await fetchReferences();
  const id = route.params.id;
  if (id) fetchDetail(id);
});
</script>

<style scoped src="../../assets/quanlytaikhoan.css"></style>