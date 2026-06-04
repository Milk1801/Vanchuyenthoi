<template>
  <div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
      <div style="display: flex; align-items: center; gap: 15px;">
        <h3 style="margin: 0; color: #2c3e50;">
          Quản lý Chứng từ: <span v-if="shipmentInfo" style="color: #3498db;">{{ shipmentInfo.ten_lo_hang }} ({{ shipmentInfo.so_booking || 'N/A' }})</span>
          <span v-else>Đang tải...</span>
        </h3>
      </div>
      <div>
        <button v-if="canModifyDocs" class="btn-success" @click="openModal()">
        TẢI LÊN CHỨNG TỪ
      </button>
      <button @click="router.back" class=" btn-cancel">Quay lại</button>
      </div>
    </div>

    <div v-if="isLoading" style="text-align: center; padding: 40px; color: #3498db;">
      <div class="loader"></div>
      <p>Đang tải danh sách tài liệu...</p>
    </div>

    <div v-else class="document-grid">
      <div class="doc-card" v-for="doc in listDocs" :key="doc.ma_chung_tu">
        <div class="doc-image-wrapper" @click="handleViewFile(doc.hinh_anh)">
          <template v-if="isImage(doc.hinh_anh)">
            <img :src="getImageUrl(doc.hinh_anh)" alt="Chứng từ" class="doc-image" onerror="this.src='https://cdn-icons-png.flaticon.com/512/2965/2965335.png'">
          </template>
          <template v-else>
            <div class="pdf-placeholder" style="height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #f8f9fa;">
              <img src="https://cdn-icons-png.flaticon.com/512/337/337946.png" style="width: 60px; margin-bottom: 10px;">
              <span style="font-size: 12px; color: #7f8c8d;">Tài liệu PDF</span>
            </div>
          </template>
          <div class="zoom-overlay">🔍 Xem chi tiết</div>
        </div>
        
        <div class="doc-info">
          <span class="badge-type">{{ doc.loai_chung_tu }}</span>
          <h4 class="doc-title" :title="doc.hinh_anh.split('/').pop()">{{ doc.hinh_anh.split('/').pop() }}</h4>
          <p class="doc-subtitle">Ngày cập nhật: {{ new Date().toLocaleDateString('vi-VN') }}</p>
        </div>
        
        <div class="doc-actions" style="display: flex; justify-content: space-between; align-items: center;">
          <button v-if="canModifyDocs" class="btn-icon text-warning" @click="openModal(doc)" title="Sửa loại chứng từ">
            ✏️ Sửa
          </button>
          <button class="btn-icon text-primary download-btn" @click="downloadFile(doc.ma_chung_tu)" title="Tải xuống">
            ⬇️ Tải về
          </button>
          <button v-if="canModifyDocs" class="btn-icon text-danger" @click="handleDelete(doc.ma_chung_tu)" title="Xóa tài liệu">
            🗑️
          </button>
        </div>
      </div>
      
      <div v-if="listDocs.length === 0" style="grid-column: 1 / -1; text-align: center; color: #95a5a6; padding: 60px; background: #fff; border-radius: 8px; border: 1px dashed #ccc;">
        <h3 style="margin-bottom: 10px;">Chưa có chứng từ nào cho lô hàng này!</h3>
        <p>Vui lòng nhấn nút "Tải lên chứng từ" để bắt đầu số hóa tài liệu.</p>
      </div>
    </div>

    <!-- MODAL THÊM/SỬA CHỨNG TỪ -->
    <div v-if="isModalOpen" class="modal-overlay">
      <div class="modal-content">
        <h3>{{ formData.ma_chung_tu ? 'Cập nhật chứng từ' : 'Tải lên chứng từ mới' }}</h3>
        <form @submit.prevent="saveDoc">
          <div class="form-group">
            <label>Loại chứng từ *</label>
            <select v-model="formData.loai_chung_tu" required>
              <option value="SC">Sales Contract (SC)</option>
              <option value="INV">Commercial Invoice (INV)</option>
              <option value="PKL">Packing List (PKL)</option>
              <option value="CO">Certificate of Origin (CO)</option>
              <option value="BL">Bill of Lading (BL)</option>
              <option value="DO">Delivery Order (DO)</option>
              <option value="Khác">Tài liệu Khác</option>
            </select>
          </div>

          <div class="form-group upload-area">
            <label>Chọn file chứng từ (Ảnh/PDF) {{ formData.ma_chung_tu ? '(Bỏ trống nếu giữ nguyên)' : '*' }}</label>
            <input type="file" @change="handleFileUpload" accept="image/*,.pdf" :required="!formData.ma_chung_tu" class="file-input">
            <div v-if="previewUrl" class="preview-container">
              <img v-if="isImage(previewUrl) || (fileToUpload && fileToUpload.type.includes('image'))" :src="previewUrl" class="preview-img">
              <div v-else class="pdf-preview-box" style="padding: 20px; text-align: center; background: #eee; border-radius: 8px;">
                <img src="https://cdn-icons-png.flaticon.com/512/337/337946.png" style="width: 40px;">
                <p style="margin-top: 5px; font-size: 13px;">File đã chọn: {{ fileToUpload?.name }}</p>
              </div>
            </div>
          </div>

          <div class="modal-actions">
            <button type="button" class="btn-cancel" @click="isModalOpen = false">Hủy</button>
            <button type="submit" class="btn-save" :disabled="isSaving">
              {{ isSaving ? 'Đang lưu ⏳...' : 'Lưu lại 💾' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <div v-if="zoomedImage" class="lightbox-overlay" @click="zoomedImage = null">
      <img :src="getImageUrl(zoomedImage)" class="lightbox-img">
      <span class="lightbox-close">✖</span>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';

const router = useRouter();
const route = useRoute();
const maLoHang = route.params.id;

// Logic phân quyền giống QuanLyThongTinLoHang.vue
const currentUser = JSON.parse(localStorage.getItem('sincere_user') || '{}');
const hasRole = (roleIdOrArray) => {
  if (!currentUser.ds_quyen) return false;
  const roles = currentUser.ds_quyen.map(q => q.ma_quyen);
  if (roles.includes(5)) return true; // Mã quyền 5: Toàn quyền (Admin)
  
  const requiredRoles = Array.isArray(roleIdOrArray) ? roleIdOrArray : [roleIdOrArray];
  return requiredRoles.some(r => roles.includes(r));
};

// Kiểm tra quyền thao tác dựa trên trạng thái lô hàng
const canModifyDocs = computed(() => {
  if (!shipmentInfo.value || !shipmentInfo.value.trang_thai_lo_hang) return false;

  const finalizedStatuses = ['Hoàn tất', 'Hủy'];
  const isFinalized = finalizedStatuses.includes(shipmentInfo.value.trang_thai_lo_hang);

  if (isFinalized) {
    // Nếu đã hoàn tất hoặc hủy: BẮT BUỘC phải có mã quyền 5 (Admin)
    return hasRole(5);
  }
  // Trạng thái khác: Quyền 1 hoặc 5 đều được phép
  return hasRole([1, 5]);
});

const listDocs = ref([]);
const shipmentInfo = ref(null);
const isLoading = ref(true);
const isSaving = ref(false);
const isModalOpen = ref(false);
const zoomedImage = ref(null);
const fileToUpload = ref(null);
const previewUrl = ref('');

const formData = ref({
  ma_chung_tu: null,
  loai_chung_tu: 'INV',
  ma_lo_hang: maLoHang
});

const isImage = (path) => {
  if (!path) return true;
  const extension = path.split('.').pop().toLowerCase();
  return ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension);
};

const getImageUrl = (path) => {
  if (!path) return 'https://cdn-icons-png.flaticon.com/512/2965/2965335.png';
  if (path.startsWith('http') || path.startsWith('blob:')) return path;
  return `http://127.0.0.1:8000/${path}`;
};

const fetchData = async () => {
  isLoading.value = true;
  try {
    const res = await fetch(`${import.meta.env.VITE_API_URL}/chung-tu`);
    const data = await res.json();
    if (data.success) {
      // Lọc danh sách chứng từ cho lô hàng hiện tại
      listDocs.value = data.data.filter(d => String(d.ma_lo_hang) === String(maLoHang));
      // Lấy thông tin lô hàng từ danh sách lo_hang trả về bởi controller
      shipmentInfo.value = data.lo_hang.find(lh => String(lh.ma_lo_hang) === String(maLoHang));
    }
  } catch (error) {
    console.error("Lỗi lấy dữ liệu chứng từ!");
  } finally {
    isLoading.value = false;
  }
};

const handleFileUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    fileToUpload.value = file;
    previewUrl.value = URL.createObjectURL(file);
  }
};

const openModal = (doc = null) => {
  if (doc) {
    formData.value = { 
      ma_chung_tu: doc.ma_chung_tu, 
      loai_chung_tu: doc.loai_chung_tu, 
      ma_lo_hang: maLoHang 
    };
    previewUrl.value = getImageUrl(doc.hinh_anh);
  } else {
    formData.value = { ma_chung_tu: null, loai_chung_tu: 'INV', ma_lo_hang: maLoHang };
    previewUrl.value = '';
  }
  fileToUpload.value = null;
  isModalOpen.value = true;
};

const saveDoc = async () => {
  if (!canModifyDocs.value) {
    alert("Bạn không có quyền thực hiện thao tác này khi lô hàng đã hoàn tất hoặc bị hủy.");
    return;
  }

  isSaving.value = true;
  const payload = new FormData();
  if (formData.value.ma_chung_tu) payload.append('ma_chung_tu', formData.value.ma_chung_tu);
  payload.append('loai_chung_tu', formData.value.loai_chung_tu);
  payload.append('ma_lo_hang', formData.value.ma_lo_hang);
  if (fileToUpload.value) payload.append('hinh_anh', fileToUpload.value);

  try {
    const res = await fetch(`${import.meta.env.VITE_API_URL}/chung-tu/save`, { method: 'POST', body: payload });
    const data = await res.json();
    if (data.success) {
      isModalOpen.value = false;
      fetchData();
    } else alert("Lỗi: " + data.message);
  } catch (e) { alert("Lỗi kết nối server!"); }
  finally { isSaving.value = false; }
};

const handleDelete = async (id) => {
  if (!canModifyDocs.value) {
    alert("Bạn không có quyền xóa chứng từ khi lô hàng đã hoàn tất hoặc bị hủy.");
    return;
  }

  if (!confirm("Xóa vĩnh viễn chứng từ này?")) return;
  try {
    const res = await fetch(`${import.meta.env.VITE_API_URL}/chung-tu/delete`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ma_chung_tu: id })
    });
    if ((await res.json()).success) fetchData();
  } catch (e) { alert("Lỗi!"); }
};

const downloadFile = (id) => {

  window.location.href =
`${import.meta.env.VITE_API_URL}/chung-tu/download/${id}`;

};

const handleViewFile = (path) => {
  if (!path) return;
  if (isImage(path)) zoomedImage.value = path;
  else window.open(getImageUrl(path), '_blank');
};

onMounted(fetchData);
</script>

<style scoped src="../../assets/quanlytaikhoan.css"></style>
<style scoped src="../../assets/quanlychungtu.css"></style>
<style scoped>
.btn-cancel, .btn-success {
  color: white;
  height: 40px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0 20px;
  font-size: 14px;
  box-sizing: border-box;
}
.btn-cancel:hover { background: #dfe4ea; color: #2f3542; }
.btn-success:hover { background: #2ecc71; color: white; }
.loader {
  border: 4px solid #f3f3f3; border-top: 4px solid #3498db;
  border-radius: 50%; width: 30px; height: 30px; animation: spin 2s linear infinite;
  margin: 0 auto 10px;
}
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>
