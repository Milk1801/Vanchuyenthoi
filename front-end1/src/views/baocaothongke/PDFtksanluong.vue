<template>
  <div class="pdf-export-wrapper">
    <div id="pdf-dashboard-content" class="print-dashboard-wrapper">
      
      <div class="dashboard-header page-break-avoid">
        <div class="header-title">
          <h2>BÁO CÁO THỐNG KÊ SẢN LƯỢNG VẬN CHUYỂN</h2>
        </div>
        <div class="header-filters">
          <div class="filter-item"><strong>Kỳ báo cáo:</strong> Từ {{ filters.tu_ky || 'Đầu kỳ' }} đến {{ filters.den_ky || 'Hiện tại' }}</div>
          <div class="filter-item"><strong>Tìm kiếm:</strong> {{ filters.tim_kiem || 'Tất cả khách hàng' }}</div>
        </div>
      </div>

      <div class="kpi-row page-break-avoid">
        <div class="kpi-card" style="background-color: #a04060;">
          <div class="kpi-title">📦 TỔNG SỐ LÔ HÀNG</div>
          <div class="kpi-value">{{ formatNumber(kpiStats.tong_lo) }}</div>
        </div>
        <div class="kpi-card" style="background-color: #209eb4;">
          <div class="kpi-title">👥 TỔNG KHÁCH HÀNG</div>
          <div class="kpi-value">{{ listData.length }}</div>
        </div>
        <div class="kpi-card" style="background-color: #e37474;">
          <div class="kpi-title">⚖️ TRỌNG LƯỢNG (KGS)</div>
          <div class="kpi-value">{{ formatNumber(kpiStats.tong_trong_luong) }}</div>
        </div>
        <div class="kpi-card" style="background-color: #f3a660;">
          <div class="kpi-title">📐 THỂ TÍCH (CBM)</div>
          <div class="kpi-value">{{ formatNumber(kpiStats.tong_the_tich) }}</div>
        </div>
      </div>

      <div class="vip-banner page-break-avoid" v-if="vipCustomer">
        <div class="vip-icon">👑</div>
        <div class="vip-text">
          <div class="vip-title">KHÁCH HÀNG VIP (THEO CBM)</div>
          <div class="vip-desc">
            <strong style="color: #d35400; font-size: 15pt;">{{ vipCustomer.ten_khach_hang }}</strong>
            <span style="color: #555;"> - Đóng góp <strong>{{ formatNumber(vipCustomer.tong_the_tich) }} CBM</strong> (Chiếm {{ vipCustomer.phan_tram_cbm }}% tổng thể tích)</span>
          </div>
        </div>
      </div>

      <div class="charts-row page-break-avoid">
        <div class="chart-box pie-box">
          <h4 class="box-title">Tỷ trọng CBM theo Khách hàng</h4>
          <div class="chart-container">
            <Doughnut v-if="pieChartData.datasets.length > 0" :data="pieChartData" :options="pieChartOptions" />
          </div>
        </div>

        <div class="chart-box bar-box">
          <h4 class="box-title">Top Khách hàng theo Trọng lượng (KGS)</h4>
          <div class="chart-container">
            <Bar v-if="barChartData.datasets.length > 0" :data="barChartData" :options="barChartOptions" />
          </div>
        </div>
      </div>

      <div class="table-section page-break-avoid">
        <h3 class="section-title">📊 BẢNG XẾP HẠNG SẢN LƯỢNG CHI TIẾT</h3>
        <table class="excel-table">
          <thead>
            <tr>
              <th style="width: 80px; background: #34495e;">Xếp hạng</th>
              <th style="background: #2c3e50; text-align: left;">Tên Khách hàng</th>
              <th style="background: #f39c12; width: 100px;">Số Lô</th>
              <th style="background: #27ae60; width: 160px;">Trọng lượng (KGS)</th>
              <th style="background: #2980b9; width: 160px;">Thể tích (CBM)</th>
              <th style="background: #8e44ad; width: 160px;">Tỷ trọng CBM (%)</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in rankedData" :key="index">
              <td style="text-align: center; font-weight: bold; color: #e74c3c;">#{{ index + 1 }}</td>
              <td style="font-weight: bold;">{{ item.ten_khach_hang }}</td>
              <td style="text-align: center;">{{ item.tong_so_lo }}</td>
              <td style="text-align: right; font-weight: bold;">{{ formatNumber(item.tong_trong_luong) }}</td>
              <td style="text-align: right; font-weight: bold;">{{ formatNumber(item.tong_the_tich) }}</td>
              <td style="text-align: center; font-weight: bold; color: #8e44ad;">{{ item.phan_tram_cbm }}%</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="2" style="text-align: right;">TỔNG CỘNG:</td>
              <td style="text-align: center;">{{ kpiStats.tong_lo }}</td>
              <td style="text-align: right;">{{ formatNumber(kpiStats.tong_trong_luong) }}</td>
              <td style="text-align: right;">{{ formatNumber(kpiStats.tong_the_tich) }}</td>
              <td style="text-align: center;">100%</td>
            </tr>
          </tfoot>
        </table>
      </div>

      <div class="summary-section page-break-avoid" v-if="vipCustomer">
        <h3 class="section-title">📌 NHẬN XÉT KẾT QUẢ HOẠT ĐỘNG</h3>
        <ul class="summary-list">
          <li><strong>Tổng quan:</strong> Xử lý thành công <strong>{{ kpiStats.tong_lo }} lô hàng</strong> từ <strong>{{ listData.length }} khách hàng</strong>.</li>
          <li><strong>Sản lượng:</strong> Đạt <strong>{{ formatNumber(kpiStats.tong_trong_luong) }} KGS</strong> và <strong>{{ formatNumber(kpiStats.tong_the_tich) }} CBM</strong>.</li>
          <li><strong>Khách hàng trọng điểm:</strong> <strong>{{ vipCustomer.ten_khach_hang }}</strong> là đối tác lớn nhất kỳ này.</li>
        </ul>
      </div>

      <div class="print-signatures page-break-avoid">
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
</template>

<script setup>
import { computed } from 'vue';
import { Chart as ChartJS, ArcElement, Tooltip, Legend, CategoryScale, LinearScale, BarElement, Title } from 'chart.js';
import { Doughnut, Bar } from 'vue-chartjs';
import ChartDataLabels from 'chartjs-plugin-datalabels';

ChartJS.register(ArcElement, Tooltip, Legend, CategoryScale, LinearScale, BarElement, Title, ChartDataLabels);

const props = defineProps({
  listData: { type: Array, required: true },
  filters: { type: Object, required: true }
});

const formatNumber = (num) => {
  if (!num) return '0';
  return new Intl.NumberFormat('vi-VN').format(Number(num));
};

const kpiStats = computed(() => {
  let lo = 0, kien = 0, theTich = 0, trongLuong = 0;
  props.listData.forEach(item => {
    lo += Number(item.tong_so_lo) || 0;
    kien += Number(item.tong_so_kien) || 0;
    theTich += Number(item.tong_the_tich) || 0;
    trongLuong += Number(item.tong_trong_luong) || 0;
  });
  return { tong_lo: lo, tong_kien: kien, tong_the_tich: theTich, tong_trong_luong: trongLuong };
});

const rankedData = computed(() => {
  if (!props.listData || props.listData.length === 0) return [];
  const totalCBM = kpiStats.value.tong_the_tich || 1; 
  const totalKGS = kpiStats.value.tong_trong_luong || 1;

  let sorted = [...props.listData].sort((a, b) => Number(b.tong_the_tich) - Number(a.tong_the_tich));
  return sorted.map(item => {
    return {
      ...item,
      phan_tram_cbm: ((Number(item.tong_the_tich) / totalCBM) * 100).toFixed(1),
      phan_tram_kgs: ((Number(item.tong_trong_luong) / totalKGS) * 100).toFixed(1)
    };
  });
});

const vipCustomer = computed(() => {
  return rankedData.value.length > 0 ? rankedData.value[0] : null; 
});

// CẤU HÌNH BIỂU ĐỒ TRÒN (Đã ép hiển thị % bằng display: true)
const pieChartData = computed(() => {
  let top5 = rankedData.value.slice(0, 5);
  let others = rankedData.value.slice(5);
  
  let labels = top5.map(item => item.ten_khach_hang.length > 15 ? item.ten_khach_hang.substring(0, 15) + '...' : item.ten_khach_hang);
  let data = top5.map(item => Number(item.tong_the_tich) || 0); 
  
  if (others.length > 0) {
    labels.push('Khác');
    data.push(others.reduce((sum, item) => sum + (Number(item.tong_the_tich) || 0), 0));
  }
  return {
    labels: labels,
    datasets: [{
      backgroundColor: ['#e74c3c', '#f39c12', '#2980b9', '#27ae60', '#8e44ad', '#95a5a6'],
      data: data, borderWidth: 2, borderColor: '#ffffff'
    }]
  };
});

const pieChartOptions = {
  responsive: true, 
  maintainAspectRatio: false, 
  cutout: '50%', 
  animation: { duration: 0 },
  plugins: { 
    legend: { position: 'right', labels: { font: { size: 12, family: 'Arial' } } },
    datalabels: { display: false } // Tắt hoàn toàn hiện số % trên bánh để tránh rối
  }
};

// CẤU HÌNH BIỂU ĐỒ CỘT (Ép nhãn leo hẳn lên trên nóc cột bằng align: 'end')
const barChartData = computed(() => {
  let sortedKGS = [...rankedData.value].sort((a, b) => Number(b.tong_trong_luong) - Number(a.tong_trong_luong)).slice(0, 5); 
  return {
    labels: sortedKGS.map(item => item.ten_khach_hang.length > 12 ? item.ten_khach_hang.substring(0, 12) + '...' : item.ten_khach_hang),
    datasets: [{ 
      label: 'KGS', backgroundColor: '#209eb4', 
      data: sortedKGS.map(item => Number(item.tong_trong_luong) || 0) 
    }]
  };
});

const barChartOptions = {
  responsive: true, 
  maintainAspectRatio: false, 
  animation: { duration: 0 },
  layout: { padding: { top: 10, right: 10 } }, 
  plugins: { 
    legend: { display: false },
    datalabels: { display: false } // Tắt hiện số trên đầu cột
  },
  scales: { 
    x: { grid: { display: false }, ticks: { font: { size: 11 } } },
    y: { 
      beginAtZero: true, 
      ticks: { 
        font: { size: 11 },
        stepSize: 1 // Ép hiện số nguyên để dễ dóng hàng
      },
      grid: { color: '#eee' } // Hiện lưới mờ để dóng hàng dễ hơn
    } 
  }
};
</script>

<style scoped>
/* FIX TRIỆT ĐỂ LỖI BỊ CẮT TRANG BẰNG CÁCH NÉM RA NGOÀI MÀN HÌNH */
.pdf-export-wrapper { 
  position: absolute; 
  top: 0; 
  left: 0; 
  width: 1000px;
  z-index: -9999; 
  opacity: 0.01; /* Vẫn tồn tại vật lý nhưng không nhìn thấy */
  pointer-events: none;
}

.print-dashboard-wrapper {
  width: 1000px; 
  height: auto; 
  background-color: #ffffff; 
  padding: 30px; 
  box-sizing: border-box;
  font-family: 'Segoe UI', Arial, sans-serif; color: #333;
}

.page-break-avoid { page-break-inside: avoid; break-inside: avoid; margin-bottom: 25px; }

/* HEADER & LỌC */
.dashboard-header { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 3px solid #2c3e50; padding-bottom: 15px; }
.header-title h2 { margin: 0; font-size: 18pt; color: #2c3e50; font-weight: bold;}
.header-filters { text-align: right; font-size: 11pt; color: #555; }
.filter-item { margin-top: 5px; }

/* KPI HÀNG 1 */
.kpi-row { display: flex; gap: 15px; }
.kpi-card { flex: 1; padding: 15px; border-radius: 6px; color: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
.kpi-title { font-size: 9pt; font-weight: bold; margin-bottom: 8px; opacity: 0.9; }
.kpi-value { font-size: 20pt; font-weight: bold; }

/* VIP BANNER DÀN HÀNG NGANG NHƯ EXCEL */
.vip-banner { 
  display: flex; align-items: center; background: #fffcf5; 
  border: 1px solid #fceec5; border-left: 6px solid #f39c12; 
  padding: 15px 20px; border-radius: 6px; margin-top: -5px;
}
.vip-icon { font-size: 20pt; margin-right: 15px; }
.vip-title { font-size: 10pt; color: #7f8c8d; font-weight: bold; margin-bottom: 5px; }
.vip-desc { font-size: 13pt; color: #333; }

/* KHU VỰC BIỂU ĐỒ (Nới gap thành 40px cho cực kỳ thoáng) */
.charts-row { display: flex; gap: 50px; justify-content: space-between; } 
.chart-box { background: white; border: 1px solid #ddd; padding: 15px; border-radius: 6px; display: flex; flex-direction: column;}
.pie-box { width: 42%; } /* Thu nhỏ Pie lại một chút */
.bar-box { width: 58%; } 
.box-title { margin: -15px -15px 15px -15px; padding: 10px 15px; font-size: 11pt; font-weight: bold; background: #fdfdfd; border-bottom: 1px solid #eee; border-radius: 6px 6px 0 0;}
.chart-container { position: relative; width: 100%; height: 260px; display: flex; justify-content: center; }

/* BẢNG XẾP HẠNG */
.section-title { margin: 0 0 15px 0; font-size: 13pt; color: #2c3e50; text-transform: uppercase; border-bottom: 2px solid #eee; padding-bottom: 8px;}
.excel-table { width: 100%; border-collapse: collapse; font-size: 11pt; }
.excel-table th, .excel-table td { border: 1px solid #dcdde1; padding: 12px; }
.excel-table th { color: white; font-weight: bold; text-align: center; }
.excel-table tbody tr:nth-child(even) { background-color: #f9f9f9; }
.excel-table tfoot td { font-weight: bold; background-color: #ecf0f1; font-size: 12pt;}

/* NHẬN XÉT TỔNG HỢP */
.summary-section { background: #f8f9fa; padding: 20px; border-radius: 6px; border: 1px solid #e9ecef; }
.summary-list { margin: 0; padding-left: 20px; font-size: 12pt; line-height: 1.8; color: #2c3e50;}

/* CHỮ KÝ */
.print-signatures { display: flex; justify-content: space-between; text-align: center; margin-top: 40px; }
.sign-box { width: 30%; font-size: 12pt; }
.sign-note { font-style: italic; font-size: 11pt; font-weight: normal; margin-top: 5px; color: #7f8c8d;}
</style>