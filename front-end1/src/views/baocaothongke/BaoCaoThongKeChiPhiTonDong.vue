<template>
  <div style="padding: 20px;">
    <h2 style="text-align: center; margin-bottom: 30px; color: #2c3e50;">THỐNG KÊ BÁO CÁO CHI PHÍ TỒN ĐỌNG</h2>

    <div class="filter-section" style="display: flex; gap: 15px; margin-bottom: 25px; align-items: flex-end; flex-wrap: wrap;">
      <div>
        <label style="display: block; font-size: 13px; font-weight: bold; margin-bottom: 5px;">Từ kỳ (Tháng/Năm)</label>
        <input type="month" v-model="filters.tu_ky" class="form-control">
      </div>
      <div>
        <label style="display: block; font-size: 13px; font-weight: bold; margin-bottom: 5px;">Đến kỳ (Tháng/Năm)</label>
        <input type="month" v-model="filters.den_ky" class="form-control">
      </div>
      
      <div style="min-width: 150px;">
        <label style="display: block; font-size: 13px; font-weight: bold; margin-bottom: 5px;">Loại giao dịch</label>
        <select v-model="filters.loai_giao_dich" class="form-control">
          <option value="">-- Tất cả --</option>
          <option value="THU">Khoản phải THU</option>
          <option value="CHI">Khoản phải CHI</option>
        </select>
      </div>

      <div style="min-width: 170px;">
        <label style="display: block; font-size: 13px; font-weight: bold; margin-bottom: 5px;">Trạng thái TT</label>
        <select v-model="filters.trang_thai_tt" class="form-control">
          <option value="">-- Tất cả trạng thái --</option>
          <option value="Chưa thanh toán">🔴 Chưa thanh toán</option>
          <option value="Thanh toán một phần">🟡 Thanh toán 1 phần</option>
          <option value="Đã thanh toán">🟢 Đã thanh toán</option>
        </select>
      </div>

      <div style="flex: 1; min-width: 150px;">
        <label style="display: block; font-size: 13px; font-weight: bold; margin-bottom: 5px;">Tìm kiếm</label>
        <input type="text" v-model="filters.tim_kiem" placeholder="Mã lô, tên chi phí..." class="form-control">
      </div>
      <div>
        <button @click="fetchData" class="btn btn-primary" style="margin-right: 10px;">🔍 Tìm kiếm</button>
        <button @click="exportExcel" class="btn btn-success" style="margin-right: 10px;">📊 Excel</button>
        <button @click="printPDF" class="btn btn-warning" :disabled="isPrinting" style="background: #e67e22; color: white; border: none; padding: 9px 15px; border-radius: 4px; cursor: pointer; font-weight: bold;">
          {{ isPrinting ? '⏳ Đang tạo PDF...' : '⬇️ Tải xuống PDF' }}
        </button>
      </div>
    </div>

    <div class="stats-cards" style="display: flex; gap: 15px; margin-bottom: 25px; flex-wrap: wrap;">
      <div class="card" style="border-left: 5px solid #2ecc71; flex: 1; min-width: 200px;">
        <div class="card-title">💰 Tổng Doanh Thu:</div>
        <div class="card-value" style="color: #2ecc71;">{{ formatCurrency(stats.tong_doanh_thu) }}</div>
      </div>
      <div class="card" style="border-left: 5px solid #e74c3c; flex: 1; min-width: 200px;">
        <div class="card-title">💸 Tổng Chi Phí:</div>
        <div class="card-value" style="color: #e74c3c;">{{ formatCurrency(stats.tong_chi_phi) }}</div>
      </div>
      <div class="card" style="border-left: 5px solid #3498db; flex: 1; min-width: 200px; background: #f0f8ff;">
        <div class="card-title">📈 Lợi Nhuận:</div>
        <div class="card-value" style="color: #3498db;">{{ formatCurrency(stats.loi_nhuan) }}</div>
      </div>
      <div class="card" style="border-left: 5px solid #9b59b6; flex: 1.5; min-width: 250px; background: #fdfaef;">
        <div class="card-title">🏆 Top Nhân Viên (Doanh thu):</div>
        <div class="card-value" style="color: #9b59b6; font-size: 22px;">
          {{ stats.top_nv }} 
          <div style="font-size: 14px; color: #555; margin-top: 5px;">{{ formatCurrency(stats.max_doanh_thu) }}</div>
        </div>
      </div>
    </div>

    <div class="table-container" style="background: white; border: 1px solid #ddd; border-radius: 8px; overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead style="background: #f8f9fa; border-bottom: 2px solid #ccc;">
          <tr>
            <th style="padding: 12px; border-right: 1px solid #eee; text-align: center;">STT</th>
            <th style="padding: 12px; border-right: 1px solid #eee;">Mã lô hàng</th>
            <th style="padding: 12px; border-right: 1px solid #eee;">Mã chi phí</th>
            <th style="padding: 12px; border-right: 1px solid #eee;">Tên chi phí</th>
            <th style="padding: 12px; border-right: 1px solid #eee; text-align: center;">Loại giao dịch</th>
            <th style="padding: 12px; border-right: 1px solid #eee; text-align: right;">Tổng tiền</th>
            <th style="padding: 12px; border-right: 1px solid #eee; text-align: center;">Người xử lý</th>
            <th style="padding: 12px; text-align: center;">Trạng thái</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="isLoading"><td colspan="7" style="text-align: center; padding: 20px;">Đang tải dữ liệu...</td></tr>
          <tr v-else-if="listData.length === 0"><td colspan="7" style="text-align: center; padding: 20px;">Không có dữ liệu</td></tr>
          
          <tr v-for="(item, index) in listData" :key="item.ma_chi_phi" style="border-bottom: 1px solid #eee;">
            <td style="padding: 12px; border-right: 1px solid #eee; text-align: center;">{{ index + 1 }}</td>
            <td style="padding: 12px; border-right: 1px solid #eee; font-weight: bold; color: #2c3e50;">L-{{ item.ma_lo_hang }}</td>
            <td style="padding: 12px; border-right: 1px solid #eee; color: #7f8c8d;">CP-{{ item.ma_chi_phi }}</td>
            <td style="padding: 12px; border-right: 1px solid #eee; font-weight: bold;">{{ item.ten_chi_phi }}</td>
            <td style="padding: 12px; border-right: 1px solid #eee; text-align: center;">
              <span :style="{ color: item.loai_giao_dich === 'THU' ? '#27ae60' : '#e74c3c', fontWeight: 'bold' }">
                {{ item.loai_giao_dich === 'THU' ? 'KHOẢN THU 📥' : 'KHOẢN CHI 📤' }}
              </span>
            </td>
           <td style="padding: 12px; border-right: 1px solid #eee; text-align: right; font-weight: bold; font-size: 15px;">
              {{ formatCurrency(item.tong_tien) }}
            </td>
            <td style="padding: 12px; border-right: 1px solid #eee; text-align: center; color: #7f8c8d; font-size: 13px;">
              👤 {{ item.nguoi_xu_ly || '---' }}
            </td>
            <td style="padding: 12px; text-align: center;">
              <span class="badge" :class="{
                'badge-danger': item.trang_thai_thanh_toan === 'Chưa thanh toán',
                'badge-warning': item.trang_thai_thanh_toan === 'Thanh toán một phần',
                'badge-success': item.trang_thai_thanh_toan === 'Đã thanh toán'
              }">
                {{ item.trang_thai_thanh_toan }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <PDFtkchiphitondong :listData="listData" :stats="stats" :filters="filters" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import PDFtkchiphitondong from './PDFtkchiphitondong.vue';
import html2pdf from 'html2pdf.js';

const isPrinting = ref(false);
const listData = ref([]);
const stats = ref({ 
  tong_phai_thu: 0, 
  tong_phai_tra: 0,
  tong_doanh_thu: 0,
  tong_chi_phi: 0,
  loi_nhuan: 0,
  top_nv: '---',
  max_doanh_thu: 0
});
const isLoading = ref(false);

const printPDF = () => {
  if (listData.value.length === 0) {
    alert("Không có dữ liệu để xuất PDF!");
    return;
  }
  
  isPrinting.value = true;
  
  setTimeout(() => {
    const element = document.getElementById('pdf-dashboard-content');
    
    const opt = {
      margin:       [10, 5, 10, 5], 
      filename:     `Bao_Cao_Chi_Phi_${new Date().getTime()}.pdf`,
      image:        { type: 'jpeg', quality: 1 },
      html2canvas:  { 
        scale: 2, 
        useCORS: true, 
        width: 1000, 
        windowWidth: 1000, 
        scrollX: 0, scrollY: 0
      },
      jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' },
      pagebreak:    { mode: ['css', 'legacy'] } 
    };

    html2pdf().set(opt).from(element).save().then(() => {
      isPrinting.value = false;
    });
  }, 1000); 
};

const filters = ref({
  tu_ky: '',
  den_ky: '',
  loai_giao_dich: '',
  trang_thai_tt: '', // Khai báo thêm biến lọc trạng thái
  tim_kiem: ''
});

// Format Tiền VNĐ
const formatCurrency = (val) => {
  if (!val) return '0 ₫';
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val);
};

const fetchData = async () => {
  isLoading.value = true;
  try {
    const query = new URLSearchParams(filters.value).toString();
    const res = await fetch(`${import.meta.env.VITE_API_URL}/bao-cao/chi-phi-ton-dong?${query}`);
    const data = await res.json();
    if (data.success) {
      listData.value = data.data;
      stats.value = data.thong_ke;
    }
  } catch (error) {
    console.error("Lỗi:", error);
  } finally {
    isLoading.value = false;
  }
};

const exportExcel = () => {
  if (listData.value.length === 0) {
    alert("Không có dữ liệu để xuất!");
    return;
  }
  
  let csvContent = "data:text/csv;charset=utf-8,\uFEFF";
  csvContent += "Mã lô hàng,Mã chi phí,Tên chi phí,Loại giao dịch,Tổng tiền,Người Xử Lý,Trạng thái\r\n";

  listData.value.forEach(row => {
    let tr = [
      `L-${row.ma_lo_hang}`,
      `CP-${row.ma_chi_phi}`,
      `"${row.ten_chi_phi}"`,
      row.loai_giao_dich,
      row.tong_tien,
      `"${row.nguoi_xu_ly || '---'}"`,
      row.trang_thai_thanh_toan
    ];
    csvContent += tr.join(",") + "\r\n";
  });

  const encodedUri = encodeURI(csvContent);
  const link = document.createElement("a");
  link.setAttribute("href", encodedUri);
  link.setAttribute("download", "Bao_Cao_Chi_Phi.csv");
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

onMounted(fetchData);
</script>

<style scoped>
.form-control { width: 100%; padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; background: white;}
.btn { padding: 9px 15px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
.btn-primary { background: #3498db; color: white; }
.btn-success { background: #27ae60; color: white; }

.stats-cards .card { flex: 1; padding: 25px; background: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-align: center; }
.card-title { font-size: 16px; font-weight: bold; color: #34495e; margin-bottom: 15px; text-transform: uppercase; }
.card-value { font-size: 32px; font-weight: bold; }

.badge { padding: 5px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; }
.badge-danger { background: #fadbd8; color: #c0392b; }
.badge-warning { background: #fcf3cf; color: #d35400; }
.badge-success { background: #d4efdf; color: #27ae60; } /* Màu xanh lá cho Đã thanh toán */
</style>