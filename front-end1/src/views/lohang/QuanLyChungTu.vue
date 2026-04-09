<template>
  <div>
    <h3 style="margin-top: 0; color: #2c3e50; margin-bottom: 20px;">Kho Chứng Từ Số Hóa</h3>
    
    <div class="toolbar" style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 20px;">
      
      <div class="search-box" style="flex: 1; min-width: 250px;">
        <input 
          type="text" 
          v-model="searchQuery" 
          placeholder="🔍 Tìm theo Số Booking, Tên Lô hàng..."
          style="width: 100%; padding: 10px 15px; border-radius: 6px; border: 1px solid #ccc; font-size: 14px;"
        >
      </div>

      <select v-model="filterLoai" style="padding: 10px; border-radius: 6px; border: 1px solid #ccc; width: 200px; font-weight: bold;">
        <option value="ALL">📂 Tất cả loại chứng từ</option>
        <option value="SC">Hợp đồng (SC)</option>
        <option value="INV">Hóa đơn (INV)</option>
        <option value="PKL">Phiếu đóng gói (PKL)</option>
        <option value="CO">C/O (Xuất xứ)</option>
        <option value="BL">Vận đơn (B/L)</option>
        <option value="DO">Lệnh giao hàng (D/O)</option>
        <option value="Khác">Khác</option>
      </select>

      <button @click="fetchData()" class="btn-refresh">🔄 Cập nhật kho</button>
      <button class="btn btn-success" @click="openModal()" style="border-radius: 20px; padding: 10px 20px; box-shadow: 0 4px 6px rgba(46, 204, 113, 0.3);">
        ☁️ TẢI LÊN CHỨNG TỪ
      </button>
    </div>

    <div v-if="isLoading" class="loading-text">Đang tải tài liệu...</div>
    
    <div v-else class="document-grid">
      <div class="doc-card" v-for="doc in filteredDocs" :key="doc.ma_chung_tu">
        <div class="doc-image-wrapper" @click="zoomImage(doc.hinh_anh)">
          <img :src="getImageUrl(doc.hinh_anh)" alt="Chứng từ" class="doc-image" onerror="this.src='https://cdn-icons-png.flaticon.com/512/2965/2965335.png'">
          <div class="zoom-overlay">🔍 Xem chi tiết</div>
        </div>
        
        <div class="doc-info">
          <span class="badge-type">{{ doc.loai_chung_tu }}</span>
          <h4 class="doc-title" :title="doc.ten_lo_hang">Lô: {{ doc.ten_lo_hang || 'Chưa gắn' }}</h4>
          <p class="doc-subtitle" style="font-weight: bold; color: #34495e;">B/K: {{ doc.so_booking || 'N/A' }}</p>
        </div>
        
        <div class="doc-actions" style="display: flex; justify-content: space-between; align-items: center;">
          <button class="btn-icon text-warning" @click="openModal(doc)" title="Sửa chứng từ này">
            ✏️ Sửa
          </button>
          
          <button class="btn-icon text-primary download-btn" @click="downloadFile(doc.hinh_anh, `${doc.loai_chung_tu}_${doc.so_booking}`)" title="Tải xuống máy tính">
            ⬇️ Tải về
          </button>
          
          <button class="btn-icon text-danger" @click="handleDelete(doc.ma_chung_tu)" title="Xóa tài liệu">
            🗑️
          </button>
        </div>
      </div>
      
      <div v-if="filteredDocs.length === 0" style="grid-column: 1 / -1; text-align: center; color: #95a5a6; padding: 40px; background: #fff; border-radius: 8px; border: 1px dashed #ccc;">
        <h3 style="margin-bottom: 10px;">Không tìm thấy chứng từ nào!</h3>
        <p>Thử thay đổi từ khóa tìm kiếm hoặc tải lên chứng từ mới.</p>
      </div>
    </div>

    <div v-if="isModalOpen" class="modal-overlay">
      <div class="modal-content" style="max-width: 500px;">
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

          <div class="form-group">
            <label>Thuộc Lô hàng *</label>
            <select v-model="formData.ma_lo_hang" required>
              <option :value="null">-- Chọn Lô hàng --</option>
              <option v-for="lo in listLoHang" :key="lo.ma_lo_hang" :value="lo.ma_lo_hang">
                [{{ lo.so_booking }}] - {{ lo.ten_lo_hang }}
              </option>
            </select>
          </div>

          <div class="form-group upload-area">
            <label>Chọn file ảnh chứng từ {{ formData.ma_chung_tu ? '(Bỏ trống nếu giữ nguyên)' : '*' }}</label>
            <input type="file" @change="handleFileUpload" accept="image/*" :required="!formData.ma_chung_tu" class="file-input">
            <div v-if="previewUrl" class="preview-container">
              <img :src="previewUrl" class="preview-img">
            </div>
          </div>

          <div class="modal-actions" style="margin-top: 20px;">
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

const listDocs = ref([]);
const listLoHang = ref([]);
const isLoading = ref(true);
const isSaving = ref(false);
const isModalOpen = ref(false);
const zoomedImage = ref(null);

const searchQuery = ref(''); 
const filterLoai = ref('ALL');
const fileToUpload = ref(null);
const previewUrl = ref('');

const formData = ref({
  ma_chung_tu: null,
  loai_chung_tu: 'INV',
  ma_lo_hang: null
});

const getImageUrl = (path) => {
  if (!path) return 'https://cdn-icons-png.flaticon.com/512/2965/2965335.png';
  if (path.startsWith('http')) return path;
  return `http://127.0.0.1:8000/${path}`;
};

// Hàm Download File bằng Blob để giải quyết lỗi tải ảnh
const downloadFile = async (path, fileName) => {
  try {
    const fileUrl = getImageUrl(path);
    const response = await fetch(fileUrl);
    if (!response.ok) throw new Error('Không thể tải file từ server');
    
    const blob = await response.blob();
    const blobUrl = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = blobUrl;
    
    const extension = path.includes('.') ? path.split('.').pop() : 'jpg';
    link.setAttribute('download', `${fileName}.${extension}`); 
    
    document.body.appendChild(link);
    link.click();
    
    document.body.removeChild(link);
    window.URL.revokeObjectURL(blobUrl);
  } catch (error) {
    console.error("Lỗi khi tải file:", error);
    // Fallback: Mở sang tab mới nếu lỗi CORS
    window.open(getImageUrl(path), '_blank');
  }
};

const filteredDocs = computed(() => {
  return listDocs.value.filter(doc => {
    const matchLoai = filterLoai.value === 'ALL' || doc.loai_chung_tu === filterLoai.value;
    const search = searchQuery.value.toLowerCase();
    const matchSearch = !search || 
      (doc.so_booking && doc.so_booking.toLowerCase().includes(search)) || 
      (doc.ten_lo_hang && doc.ten_lo_hang.toLowerCase().includes(search));
    
    return matchLoai && matchSearch;
  });
});

const fetchData = async () => {
  isLoading.value = true;
  try {
    const res = await fetch('http://127.0.0.1:8000/api/chung-tu');
    const data = await res.json();
    if (data.success) {
      listDocs.value = data.data;
      listLoHang.value = data.lo_hang;
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

// Cập nhật hàm openModal để xử lý cả Thêm và Sửa
const openModal = async (doc = null) => {
  try {
    // Lấy lại danh sách lô hàng phòng khi có dữ liệu mới
    const res = await fetch('http://127.0.0.1:8000/api/chung-tu');
    const data = await res.json();
    if (data.success) listLoHang.value = data.lo_hang;
  } catch (error) {
    console.error("Lỗi ngầm khi lấy Lô hàng", error);
  }

  if (doc) {
    // Chế độ Sửa: Đổ dữ liệu cũ vào form
    formData.value = { 
      ma_chung_tu: doc.ma_chung_tu, 
      loai_chung_tu: doc.loai_chung_tu, 
      ma_lo_hang: doc.ma_lo_hang 
    };
    fileToUpload.value = null; // Chưa chọn file mới
    previewUrl.value = getImageUrl(doc.hinh_anh); // Hiển thị ảnh cũ
  } else {
    // Chế độ Thêm mới
    formData.value = { ma_chung_tu: null, loai_chung_tu: 'INV', ma_lo_hang: null };
    fileToUpload.value = null;
    previewUrl.value = '';
  }
  
  isModalOpen.value = true;
};

const zoomImage = (path) => {
  if (path) zoomedImage.value = path;
};

// Cập nhật hàm saveDoc để hỗ trợ API Update
const saveDoc = async () => {
  isSaving.value = true;
  const payload = new FormData();
  
  // Nếu có ID tức là đang sửa, gửi thêm ID lên server
  if (formData.value.ma_chung_tu) {
    payload.append('ma_chung_tu', formData.value.ma_chung_tu);
  }
  payload.append('loai_chung_tu', formData.value.loai_chung_tu);
  payload.append('ma_lo_hang', formData.value.ma_lo_hang);
  
  // Chỉ gửi file lên nếu người dùng chọn file mới
  if (fileToUpload.value) {
    payload.append('hinh_anh', fileToUpload.value);
  }

  try {
    const res = await fetch('http://127.0.0.1:8000/api/chung-tu/save', {
      method: 'POST',
      body: payload
    });
    const data = await res.json();
    if (data.success) {
      isModalOpen.value = false;
      fetchData();
    } else {
      alert("Lỗi: " + data.message);
    }
  } catch (e) {
    alert("Lỗi kết nối upload server!");
  } finally {
    isSaving.value = false;
  }
};

const handleDelete = async (id) => {
  if (!confirm("Hủy vĩnh viễn chứng từ này khỏi hệ thống?")) return;
  try {
    const res = await fetch('http://127.0.0.1:8000/api/chung-tu/delete', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ma_chung_tu: id })
    });
    const data = await res.json();
    if (data.success) fetchData();
  } catch (e) {
    alert("Lỗi!");
  }
};

onMounted(fetchData);
</script>

<style scoped src="../../assets/quanlytaikhoan.css"></style>
<style scoped src="../../assets/quanlychungtu.css"></style>