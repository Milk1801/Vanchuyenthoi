<template>
  <div class="pdf-export-wrapper">
    <div id="pdf-dashboard-content" class="print-dashboard-wrapper">
      
      <div class="dashboard-header page-break-avoid">
        <div class="header-title">
          <h2>BÁO CÁO THỐNG KÊ CHI PHÍ TỒN ĐỌNG</h2>
        </div>
        <div class="header-filters">
          <div class="filter-item"><strong>Kỳ báo cáo:</strong> Từ {{ filters.tu_ky || 'Đầu kỳ' }} đến {{ filters.den_ky || 'Hiện tại' }}</div>
          <div class="filter-item">
            <strong>Giao dịch:</strong> {{ filters.loai_giao_dich === 'THU' ? 'Khoản Thu' : (filters.loai_giao_dich === 'CHI' ? 'Khoản Chi' : 'Tất cả') }} | 
            <strong>Trạng thái:</strong> {{ filters.trang_thai_tt || 'Tất cả' }}
          </div>
        </div>
      </div>

      <div class="kpi-row page-break-avoid">
        <div class="kpi-card" style="background-color: #27ae60;">
          <div class="kpi-title">💰 TỔNG DOANH THU</div>
          <div class="kpi-value" style="font-size: 16pt;">{{ formatCurrency(stats.tong_doanh_thu) }}</div>
        </div>
        <div class="kpi-card" style="background-color: #e74c3c;">
          <div class="kpi-title">💸 TỔNG CHI PHÍ</div>
          <div class="kpi-value" style="font-size: 16pt;">{{ formatCurrency(stats.tong_chi_phi) }}</div>
        </div>
        <div class="kpi-card" style="background-color: #2980b9;">
          <div class="kpi-title">📈 LỢI NHUẬN</div>
          <div class="kpi-value" style="font-size: 16pt;">{{ formatCurrency(stats.loi_nhuan) }}</div>
        </div>
        <div class="kpi-card" style="background-color: #8e44ad;">
          <div class="kpi-title">🏆 TOP NHÂN VIÊN</div>
          <div class="kpi-value" style="font-size: 14pt;">{{ stats.top_nv }}</div>
        </div>
      </div>

      <div class="vip-banner page-break-avoid" v-if="stats.top_nv && stats.top_nv !== '---'">
        <div class="vip-icon">💎</div>
        <div class="vip-text">
          <div class="vip-title">NHÂN VIÊN ĐẠT DOANH THU CAO NHẤT</div>
          <div class="vip-desc">
            <strong style="color: #8e44ad; font-size: 15pt;">{{ stats.top_nv }}</strong>
            <span style="color: #555;"> - Ghi nhận tổng giá trị: <strong>{{ formatCurrency(stats.max_doanh_thu) }}</strong></span>
          </div>
        </div>
      </div>

      <div class="charts-row page-break-avoid">
        <div class="chart-box pie-box">
          <h4 class="box-title">Tỷ trọng Giá trị theo Trạng thái TT</h4>
          <div class="chart-container">
            <Doughnut v-if="pieChartData.datasets.length > 0" :data="pieChartData" :options="pieChartOptions" />
          </div>
        </div>

        <div class="chart-box bar-box">
          <h4 class="box-title">Top Nhân viên theo Tổng giá trị (VNĐ)</h4>
          <div class="chart-container">
            <Bar v-if="barChartData.datasets.length > 0" :data="barChartData" :options="barChartOptions" />
          </div>
        </div>
      </div>

      <div class="table-section">
        <h3 class="section-title">📊 DANH SÁCH CHI PHÍ CHI TIẾT</h3>
        <table class="excel-table">
          <thead>
            <tr>
              <th style="width: 40px; background: #34495e;">STT</th>
              <th style="background: #2c3e50; width: 80px;">Mã Lô</th>
              <th style="background: #2980b9;">Tên chi phí</th>
              <th style="background: #8e44ad; width: 100px;">Giao dịch</th>
              <th style="background: #e74c3c; width: 130px;">Tổng tiền (VNĐ)</th>
              <th style="background: #f39c12; width: 120px;">Trạng thái TT</th>
              <th style="background: #34495e; width: 120px;">Người xử lý</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in listData" :key="index">
              <td style="text-align: center; font-weight: bold;">{{ index + 1 }}</td>
              <td style="font-weight: bold; color: #2c3e50; text-align: center;">L-{{ item.ma_lo_hang }}</td>
              <td style="font-weight: bold;">{{ item.ten_chi_phi }}</td>
              <td style="text-align: center; font-weight: bold;" :style="{ color: item.loai_giao_dich === 'THU' ? '#27ae60' : '#e74c3c' }">
                {{ item.loai_giao_dich === 'THU' ? 'KHOẢN THU' : 'KHOẢN CHI' }}
              </td>
              <td style="text-align: right; font-weight: bold;">{{ formatNumberStr(item.tong_tien) }}</td>
              <td style="text-align: center;">{{ item.trang_thai_thanh_toan }}</td>
              <td style="text-align: center;">{{ item.nguoi_xu_ly || '---' }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="footer-wrapper page-break-avoid">
        <div class="summary-section">
          <h3 class="section-title">📌 NHẬN XÉT KẾT QUẢ HOẠT ĐỘNG</h3>
          <ul class="summary-list">
            <li><strong>Tài chính:</strong> Hệ thống ghi nhận tổng doanh thu đạt <strong>{{ formatCurrency(stats.tong_doanh_thu) }}</strong>, tổng chi phí là <strong>{{ formatCurrency(stats.tong_chi_phi) }}</strong>. Lợi nhuận gộp đạt <strong>{{ formatCurrency(stats.loi_nhuan) }}</strong>.</li>
            <li><strong>Giao dịch & Tồn đọng:</strong> Đã rà soát <strong>{{ listData.length }}</strong> khoản mục thu/chi. Cần chú ý bám sát các khoản "Chưa thanh toán" để thu hồi công nợ hoặc tất toán kịp thời.</li>
            <li v-if="stats.top_nv && stats.top_nv !== '---'"><strong>Năng suất:</strong> Nhân sự <strong>{{ stats.top_nv }}</strong> dẫn đầu với tổng doanh thu mang về đạt <strong>{{ formatCurrency(stats.max_doanh_thu) }}</strong>.</li>
          </ul>
        </div>

        <div class="print-signatures">
          <div class="sign-box">
            <p><strong>Người lập biểu</strong></p>
            <p class="sign-note">(Ký, ghi rõ họ tên)</p>
          </div>
          <div class="sign-box">
            <p><strong>Kế toán trưởng</strong></p>
            <p class="sign-note">(Ký, ghi rõ họ tên)</p>
          </div>
          <div class="sign-box">
            <p style="margin-bottom: 5px;"><em>Ngày ..... tháng ..... năm 20...</em></p>
            <p><strong>Giám đốc</strong></p>
            <p class="sign-note">(Ký, đóng dấu, ghi rõ họ tên)</p>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Chart as ChartJS, ArcElement, Tooltip, Legend, CategoryScale, LinearScale, BarElement, Title } from 'chart.js';
import { Doughnut, Bar } from 'vue-chartjs';
import ChartDataLabels from 'chartjs-plugin-datalabels';

ChartJS.register(ArcElement, Tooltip, Legend, CategoryScale, LinearScale, BarElement, Title, ChartDataLabels);

const props = defineProps({
  listData: { type: Array, required: true },
  stats: { type: Object, required: true },
  filters: { type: Object, required: true }
});

const formatCurrency = (val) => {
  if (!val) return '0 ₫';
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val);
};

const formatNumberStr = (val) => {
  if (!val) return '0';
  return new Intl.NumberFormat('vi-VN').format(val);
};

// TÍNH TOÁN DỮ LIỆU BIỂU ĐỒ TRÒN (Tỷ trọng tiền theo Trạng thái Thanh Toán)
const pieChartData = computed(() => {
  const sums = {};
  props.listData.forEach(item => {
    let tt = item.trang_thai_thanh_toan || 'Khác';
    sums[tt] = (sums[tt] || 0) + Number(item.tong_tien || 0);
  });

  const labels = Object.keys(sums);
  const data = Object.values(sums);

  const backgroundColors = labels.map(label => {
    if (label === 'Đã thanh toán') return '#27ae60'; // Xanh lá
    if (label === 'Chưa thanh toán') return '#e74c3c'; // Đỏ
    if (label === 'Thanh toán một phần') return '#f39c12'; // Cam/Vàng
    return '#3498db'; // Khác (Xanh dương)
  });

  return {
    labels: labels,
    datasets: [{
      backgroundColor: backgroundColors,
      data: data, 
      borderWidth: 2, 
      borderColor: '#ffffff'
    }]
  };
});

const pieChartOptions = {
  responsive: true, maintainAspectRatio: false, cutout: '50%', animation: { duration: 0 },
  layout: { 
    // Giữ y nguyên khoảng thở bên phải (right: 40) đã fix cho biểu đồ Booking
    padding: { top: 0, bottom: 20, left: 10, right: 40 } 
  },
  plugins: { 
    legend: { 
      position: 'right', 
      labels: { font: { size: 12, family: 'Arial' }, boxWidth: 15, padding: 15 } 
    },
    datalabels: { 
      display: true, color: '#ffffff', font: { weight: 'bold', size: 14, family: 'Arial' },
      anchor: 'center', align: 'center',
      formatter: (value, context) => {
        let sum = 0;
        let dataArr = context.chart.data.datasets[0].data;
        dataArr.forEach(data => { sum += Number(data); });
        if(sum === 0) return "";
        let percentage = (Number(value) * 100 / sum).toFixed(1);
        return percentage > 4 ? percentage + "%" : "";
      }
    } 
  }
};

// TÍNH TOÁN DỮ LIỆU BIỂU ĐỒ CỘT (Top Nhân Viên theo Tổng tiền giao dịch)
const barChartData = computed(() => {
  const sums = {};
  props.listData.forEach(item => {
    let nv = item.nguoi_xu_ly || 'Chưa phân công';
    sums[nv] = (sums[nv] || 0) + Number(item.tong_tien || 0);
  });

  const sortedArr = Object.keys(sums).map(key => ({ name: key, val: sums[key] }))
                                       .sort((a, b) => b.val - a.val).slice(0, 5);

  return {
    labels: sortedArr.map(item => item.name.length > 15 ? item.name.substring(0, 15) + '...' : item.name),
    datasets: [{ 
      label: 'Giá trị (VNĐ)', backgroundColor: '#3498db', 
      data: sortedArr.map(item => item.val) 
    }]
  };
});

const barChartOptions = {
  responsive: true, maintainAspectRatio: false, animation: { duration: 0 },
  layout: { padding: { top: 30, right: 15, left: 15 } }, 
  plugins: { 
    legend: { display: false },
    datalabels: { 
      display: true, clip: false, color: '#2c3e50',
      anchor: 'end', align: 'bottom', 
      font: { weight: 'bold', size: 11, family: 'Arial' },
      // Convert số tiền lớn sang dạng ngắn gọn (Ví dụ: 1.000.000)
      formatter: (value) => formatNumberStr(value)
    } 
  },
  scales: { 
    x: { grid: { display: false }, ticks: { font: { size: 11 } } },
    y: { beginAtZero: true, min: 0, grace: '20%', ticks: { font: { size: 11 } } } 
  }
};
</script>

<style scoped>
/* 100% CÔNG THỨC CSS TỪ CÁC FILE TRƯỚC */
.pdf-export-wrapper { 
  position: fixed; top: 0; left: 0; width: 1000px;
  z-index: -9999; opacity: 0.001; pointer-events: none;
}
.print-dashboard-wrapper {
  width: 1000px; height: auto; background-color: #ffffff; 
  padding: 30px; box-sizing: border-box; font-family: 'Segoe UI', Arial, sans-serif; color: #333;
}
.page-break-avoid { page-break-inside: avoid; break-inside: avoid; margin-bottom: 25px; }

.dashboard-header { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 3px solid #2c3e50; padding-bottom: 15px; }
.header-title h2 { margin: 0; font-size: 18pt; color: #2c3e50; font-weight: bold;}
.header-filters { text-align: right; font-size: 11pt; color: #555; }
.filter-item { margin-top: 5px; }

.kpi-row { display: flex; gap: 15px; }
.kpi-card { flex: 1; padding: 15px; border-radius: 6px; color: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
.kpi-title { font-size: 9pt; font-weight: bold; margin-bottom: 8px; opacity: 0.9; text-transform: uppercase;}
.kpi-value { font-weight: bold; }

.vip-banner { display: flex; align-items: center; background: #faf5ff; border: 1px solid #e8daef; border-left: 6px solid #8e44ad; padding: 15px 20px; border-radius: 6px; margin-top: -5px;}
.vip-icon { font-size: 20pt; margin-right: 15px; }
.vip-title { font-size: 10pt; color: #7f8c8d; font-weight: bold; margin-bottom: 5px; }
.vip-desc { font-size: 13pt; color: #333; }

.charts-row { display: flex; gap: 50px; justify-content: space-between; } 
.chart-box { background: white; border: 1px solid #ddd; padding: 15px; border-radius: 6px; display: flex; flex-direction: column;}
.pie-box { width: 42%; } 
.bar-box { width: 58%; } 
.box-title { margin: -15px -15px 15px -15px; padding: 10px 15px; font-size: 11pt; font-weight: bold; background: #fdfdfd; border-bottom: 1px solid #eee; border-radius: 6px 6px 0 0;}
.chart-container { position: relative; width: 100%; height: 260px; display: flex; justify-content: center; align-items: center; }

/* BẢNG NGẮT TRANG THÔNG MINH */
.table-section { margin-bottom: 50px; }
.section-title { margin: 0 0 15px 0; font-size: 13pt; color: #2c3e50; text-transform: uppercase; border-bottom: 2px solid #eee; padding-bottom: 8px;}
.excel-table { width: 100%; border-collapse: collapse; font-size: 10pt; }
.excel-table th, .excel-table td { border: 1px solid #dcdde1; padding: 10px; }
.excel-table th { color: white; font-weight: bold; text-align: center; }
.excel-table tbody tr:nth-child(even) { background-color: #f9f9f9; }
.excel-table tr { page-break-inside: avoid; break-inside: avoid; } /* Không cắt đứt hàng */

/* KHỐI FOOTER GÓI CHUNG */
.footer-wrapper { display: block; width: 100%; }
.summary-section { background: #f8f9fa; padding: 25px 20px; border-radius: 6px; border: 1px solid #e9ecef; margin-bottom: 50px;}
.summary-list { margin: 0; padding-left: 20px; font-size: 12pt; line-height: 1.8; color: #2c3e50;}

.print-signatures { display: flex; justify-content: space-between; text-align: center; margin-bottom: 40px; }
.sign-box { width: 30%; font-size: 12pt; }
.sign-note { font-style: italic; font-size: 11pt; font-weight: normal; margin-top: 5px; color: #7f8c8d;}
</style>