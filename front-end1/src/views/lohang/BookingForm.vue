<template>
  <div class="booking-form-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
      <h3 style="margin: 0; color: #2c3e50;">
        {{ formData.ma_booking ? '🚢 Cập nhật Booking Note #' + formData.so_booking : '🚢 Tạo Booking Note mới' }}
      </h3>
    </div>

    <div class="table-card" style="padding: 25px;">
      <form @submit.prevent="handleSave">
        <div style="display: flex; gap: 15px;">
          <div class="form-group" style="flex: 1;">
            <label>Số Booking*</label>
            <input v-model="formData.so_booking" required placeholder="Nhập mã Booking Note từ hãng tàu...">
          </div>
          <div class="form-group" style="flex: 1;">
            <label>Hãng Tàu *</label>
            <div class="combobox-wrapper">
              <input 
                type="text" 
                v-model="hangTauSearchText" 
                placeholder="Tìm tên hãng tàu..." 
                @focus="showHangTauDropdown = true"
                class="combobox-input"
              >
              <ul v-if="showHangTauDropdown" class="combobox-list">
                <li v-for="ht in filteredHangTau" :key="ht.ma_hang_tau" @click="selectHangTau(ht)">
                  {{ ht.ten_hang_tau }}
                </li>
                <li v-if="filteredHangTau.length === 0" class="no-result">Không tìm thấy hãng tàu</li>
              </ul>
            </div>
          </div>
        </div>

        <div style="display: flex; gap: 15px;">
          <div class="form-group" style="flex: 2;">
            <label>Tên Con Tàu *</label>
            <input v-model="formData.ten_con_tau" placeholder="VD: EVER GIVEN" required>
          </div>
          <div class="form-group" style="flex: 1;">
            <label>Số Chuyến (Voyage) *</label>
            <input v-model="formData.so_chuyen" placeholder="VD: 045E" required>
          </div>
        </div>

        <div style="display: flex; gap: 15px;">
          <div class="form-group" style="flex: 1;">
            <label>Cảng Đi (POL) *</label>
            <div class="combobox-wrapper">
              <input 
                type="text" 
                v-model="polSearchText" 
                placeholder="Nhập tên cảng đi..." 
                @focus="showPolDropdown = true"
                class="combobox-input"
              >
              <ul v-if="showPolDropdown" class="combobox-list">
                <li v-for="c in filteredPol" :key="c.ma_cang" @click="selectCang(c, 'pol')">
                  {{ c.ten_cang }}
                </li>
                <li v-if="filteredPol.length === 0" class="no-result">Không tìm thấy cảng</li>
              </ul>
            </div>
          </div>
          <div class="form-group" style="flex: 1;">
            <label>Cảng Đến (POD) *</label>
            <div class="combobox-wrapper">
              <input 
                type="text" 
                v-model="podSearchText" 
                placeholder="Nhập tên cảng đến..." 
                @focus="showPodDropdown = true"
                class="combobox-input"
              >
              <ul v-if="showPodDropdown" class="combobox-list">
                <li v-for="c in filteredPod" :key="c.ma_cang" @click="selectCang(c, 'pod')">
                  {{ c.ten_cang }}
                </li>
                <li v-if="filteredPod.length === 0" class="no-result">Không tìm thấy cảng</li>
              </ul>
            </div>
          </div>
        </div>

        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
          <div class="form-group" style="flex: 1 1 30%;">
            <label>Giờ Cắt Máng (Cut-off) *</label>
            <input type="datetime-local" v-model="formData.gio_cat_mang" required>
          </div>
          <div class="form-group" style="flex: 1 1 30%;">
            <label>ETD (Dự kiến đi) *</label>
            <input type="datetime-local" v-model="formData.etd" required>
          </div>
          <div class="form-group" style="flex: 1 1 30%;">
            <label>ETA (Dự kiến đến) *</label>
            <input type="datetime-local" v-model="formData.eta" required>
          </div>
        </div>

        <div class="modal-actions" style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
          <button type="button" class="btn-cancel" @click="handleCancel">Hủy bỏ</button>
          <button type="submit" class="btn-save" :disabled="isSaving">
            {{ isSaving ? 'Đang lưu...' : 'Lưu dữ liệu Booking Note 💾' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';

const router = useRouter();
const route = useRoute();

const listCangBien = ref([]);
const listHangTau = ref([]);
const isSaving = ref(false);

// State cho Combobox Tìm kiếm
const polSearchText = ref('');
const showPolDropdown = ref(false);
const podSearchText = ref('');
const showPodDropdown = ref(false);
const hangTauSearchText = ref('');
const showHangTauDropdown = ref(false);

const filteredPol = computed(() => listCangBien.value.filter(c => c.ten_cang.toLowerCase().includes(polSearchText.value.toLowerCase())));
const filteredPod = computed(() => listCangBien.value.filter(c => c.ten_cang.toLowerCase().includes(podSearchText.value.toLowerCase())));
const filteredHangTau = computed(() => listHangTau.value.filter(ht => ht.ten_hang_tau.toLowerCase().includes(hangTauSearchText.value.toLowerCase())));

const formData = ref({
  ma_booking: null,
  so_booking: '',
  ten_con_tau: '',
  so_chuyen: '',
  etd: '',
  eta: '',
  gio_cat_mang: '',
  ma_cang_di: null,
  ma_cang_den: null,
  ma_hang_tau: null,
  nguoi_sua_cuoi: null
});

const selectCang = (cang, target) => {
  if (target === 'pol') {
    formData.value.ma_cang_di = cang.ma_cang;
    polSearchText.value = cang.ten_cang;
    showPolDropdown.value = false;
  } else {
    formData.value.ma_cang_den = cang.ma_cang;
    podSearchText.value = cang.ten_cang;
    showPodDropdown.value = false;
  }
};

const selectHangTau = (ht) => {
  formData.value.ma_hang_tau = ht.ma_hang_tau;
  hangTauSearchText.value = ht.ten_hang_tau;
  showHangTauDropdown.value = false;
};

const formatForInput = (dateString) => {
  if (!dateString || dateString.startsWith('0000') || dateString.startsWith('1970')) return '';
  try {
    return new Date(dateString).toISOString().slice(0, 16);
  } catch (e) {
    return '';
  }
};

const loadReferences = async () => {
  try {
    const res = await fetch(`${import.meta.env.VITE_API_URL}/bookings/references`);
    const data = await res.json();
    if (data.success) {
      listCangBien.value = data.cang_bien;
      listHangTau.value = data.hang_tau;

      // Đóng các dropdown khi click ra ngoài
      window.addEventListener('click', (e) => {
        if (!e.target.closest('.combobox-wrapper')) {
          showPolDropdown.value = showPodDropdown.value = showHangTauDropdown.value = false;
        }
      });
    }
  } catch (error) {
    console.error("Lỗi lấy dữ liệu tham chiếu");
  }
};

const fetchBookingData = async (id) => {
  try {
    const res = await fetch(`${import.meta.env.VITE_API_URL}/bookings`);
    const data = await res.json();
    if (data.success) {
      const found = data.data.find(x => String(x.ma_booking) === String(id));
      if (found) {
        formData.value = {
          ...found,
          etd: formatForInput(found.etd),
          eta: formatForInput(found.eta),
          gio_cat_mang: formatForInput(found.gio_cat_mang)
        };

        // Cập nhật text hiển thị cho combobox khi sửa
        hangTauSearchText.value = listHangTau.value.find(ht => ht.ma_hang_tau === found.ma_hang_tau)?.ten_hang_tau || '';
        polSearchText.value = listCangBien.value.find(c => c.ma_cang === found.ma_cang_di)?.ten_cang || '';
        podSearchText.value = listCangBien.value.find(c => c.ma_cang === found.ma_cang_den)?.ten_cang || '';
      }
    }
  } catch (error) {
    console.error("Lỗi lấy dữ liệu Booking Note!");
  }
};

const handleSave = async () => {
  if (!formData.value.so_booking) {
    alert("Vui lòng nhập Số Booking!");
    return;
  }
  if (!formData.value.ma_hang_tau) {
    alert("Vui lòng chọn Hãng Tàu!");
    return;
  }
  if (!formData.value.ma_cang_di) {
    alert("Vui lòng chọn Cảng Đi!");
    return;
  }
  if (!formData.value.ma_cang_den) {
    alert("Vui lòng chọn Cảng Đến!");
    return;
  }
  if (formData.value.ma_cang_di == formData.value.ma_cang_den) {
    alert("Vui lòng chọn Cảng Đi và Cảng Đến khác nhau!");
    return;
  }

  const cutOff = formData.value.gio_cat_mang ? new Date(formData.value.gio_cat_mang) : null;
  const etd = formData.value.etd ? new Date(formData.value.etd) : null;
  const eta = formData.value.eta ? new Date(formData.value.eta) : null;

  if (cutOff && etd && cutOff >= etd) {
    alert("Lỗi: Giờ Cắt Máng phải trước ETD (Dự kiến đi)!");
    return;
  }
  if (etd && eta && etd >= eta) {
    alert("Lỗi: ETD (Dự kiến đi) phải trước ETA (Dự kiến đến)!");
    return;
  }

  isSaving.value = true;
  const user = JSON.parse(localStorage.getItem('sincere_user'));
  formData.value.nguoi_sua_cuoi = user ? (user.id || user.ma_tai_khoan) : null;

  try {
    const res = await fetch(`${import.meta.env.VITE_API_URL}/bookings/save`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(formData.value)
    });
    const data = await res.json();
    if (data.success) {
      alert(data.message);
      router.push('/lo-hang/booking');
    } else {
      alert("Lỗi: " + data.message);
    }
  } catch (e) {
    alert("Lỗi server!");
  } finally {
    isSaving.value = false;
  }
};

const handleCancel = () => {
  if (confirm("Dữ liệu chưa lưu sẽ bị mất. Bạn có muốn quay lại không?")) {
    router.push('/lo-hang/booking');
  }
};

onMounted(async () => {
  await loadReferences();
  const id = route.params.id;
  if (id) {
    await fetchBookingData(id);
  }
});
</script>

<style scoped src="../../assets/quanlytaikhoan.css"></style>
<style scoped>
.booking-form-container {
  padding: 20px;
  /* max-width: 1000px; */
  margin: 0 auto;
}

/* CSS cho Combobox Tìm kiếm */
.combobox-wrapper { position: relative; width: 100%; }
.combobox-input {
  width: 100%; height: 44px; padding: 10px; border: 1px solid #ddd;
  border-radius: 6px; box-sizing: border-box; background: #fff;
  transition: border-color 0.2s;
}
.combobox-input:focus { border-color: #3498db; outline: none; }
.combobox-list {
  position: absolute; top: 100%; left: 0; right: 0; background: #fff;
  border: 1px solid #ddd; border-radius: 6px; margin: 2px 0 0 0; padding: 0;
  list-style: none; z-index: 1000; max-height: 200px; overflow-y: auto;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.combobox-list li {
  padding: 10px; cursor: pointer; transition: background 0.2s;
  font-size: 14px; color: #2c3e50; border-bottom: 1px solid #f9f9f9;
}
.combobox-list li:hover { background: #f0f7ff; color: #2980b9; }
.combobox-list li.no-result { color: #95a5a6; cursor: default; font-style: italic; }
</style>
