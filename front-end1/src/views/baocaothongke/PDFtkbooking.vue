<template>
  <div class="pdf-export-wrapper">
    <div id="pdf-dashboard-content" class="print-dashboard-wrapper">
      
      <div class="dashboard-header page-break-avoid">
        <div class="header-title">
          <h2>BÁO CÁO THỐNG KÊ BOOKING</h2>
        </div>
        <div class="header-filters">
          <div class="filter-item"><strong>Kỳ báo cáo:</strong> Từ {{ filters.tu_ky || 'Đầu kỳ' }} đến {{ filters.den_ky || 'Hiện tại' }}</div>
          <div class="filter-item"><strong>Hãng tàu:</strong> {{ filters.ma_hang_tau || 'Tất cả' }} | <strong>Tìm kiếm:</strong> {{ filters.tim_kiem || 'Không' }}</div>
        </div>
      </div>

      <div class="kpi-row page-break-avoid">
        <div class="kpi-card" style="background-color: #8e44ad;">
          <div class="kpi-title">📑 TỔNG SỐ BOOKING</div>
          <div class="kpi-value">{{ stats.tong_so || 0 }}</div>
        </div>
        <div class="kpi-card" style="background-color: #e74c3c;">
          <div class="kpi-title">⏳ CHƯA HOÀN THÀNH</div>
          <div class="kpi-value">{{ stats.chua_hoan_thanh || 0 }}</div>
        </div>
        <div class="kpi-card" style="background-color: #27ae60;">
          <div class="kpi-title">✅ ĐÃ HOÀN TẤT</div>
          <div class="kpi-value">{{ stats.da_hoan_tat || 0 }}</div>
        </div>
        <div class="kpi-card" style="background-color: #2980b9;">
          <div class="kpi-title">🚢 TOP HÃNG TÀU</div>
          <div class="kpi-value" style="font-size: 16pt;">{{ stats.top_hang_tau }}</div>
        </div>
      </div>

      <div class="vip-banner page-break-avoid" v-if="stats.top_nv && stats.top_nv !== '---'">
        <div class="vip-icon">🏆</div>
        <div class="vip-text">
          <div class="vip-title">NHÂN VIÊN XỬ LÝ XUẤT SẮC NHẤT</div>
          <div class="vip-desc">
            <strong style="color: #d35400; font-size: 15pt;">{{ stats.top_nv }}</strong>
            <span style="color: #555;"> - Tiếp nhận và xử lý <strong>{{ stats.max_nv }} booking</strong></span>
          </div>
        </div>
      </div>

      <div class="charts-row page-break-avoid">
        <div class="chart-box pie-box">
          <h4 class="box-title">Tỷ trọng Booking theo Hãng Tàu</h4>
          <div class="chart-container">
            <Doughnut v-if="pieChartData.datasets.length > 0" :data="pieChartData" :options="pieChartOptions" />
          </div>
        </div>

        <div class="chart-box bar-box">
          <h4 class="box-title">Top Nhân viên xử lý Booking</h4>
          <div class="chart-container">
            <Bar v-if="barChartData.datasets.length > 0" :data="barChartData" :options="barChartOptions" />
          </div>
        </div>
      </div>

      <div class="table-section">
        <h3 class="section-title">📊 DANH SÁCH LÔ HÀNG CHI TIẾT</h3>
        <table class="excel-table">
          <thead>
            <tr>
              <th style="width: 40px; background: #34495e;">STT</th>
              <th style="background: #2c3e50; width: 110px;">Số Booking</th>
              <th style="background: #2980b9;">Hãng Tàu</th>
              <th style="background: #8e44ad;">Tuyến (Cảng Đi ➔ Đến)</th>
              <th style="background: #27ae60; width: 90px;">ETD</th>
              <th style="background: #e74c3c; width: 110px;">Giờ Cut-off</th>
              <th style="background: #f39c12; width: 100px;">Trạng thái</th>
              <th style="background: #34495e; width: 120px;">Người xử lý</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in listData" :key="index">
              <td style="text-align: center; font-weight: bold;">{{ index + 1 }}</td>
              <td style="font-weight: bold; color: #2980b9; text-align: center;">{{ item.so_booking }}</td>
              <td style="font-weight: bold;">{{ item.ten_hang_tau || '---' }}</td>
              <td>{{ item.cang_di || '?' }} ➔ {{ item.cang_den || '?' }}</td>
              <td style="text-align: center; font-weight: bold;">{{ formatDate(item.etd) }}</td>
              <td style="text-align: center; color: #c0392b; font-weight: bold;">{{ formatDateTime(item.gio_cat_mang) }}</td>
              <td style="text-align: center;">{{ item.trang_thai }}</td>
              <td style="text-align: center;">{{ item.nguoi_xu_ly || '---' }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="footer-wrapper page-break-avoid">
        <div class="summary-section">
          <h3 class="section-title">📌 NHẬN XÉT KẾT QUẢ HOẠT ĐỘNG</h3>
          <ul class="summary-list">
            <li><strong>Tổng quan:</strong> Hệ thống đã ghi nhận tổng cộng <strong>{{ stats.tong_so }} booking</strong> trong kỳ.</li>
            <li><strong>Tiến độ:</strong> Đã xử lý hoàn tất <strong>{{ stats.da_hoan_tat }} booking</strong>, còn lại <strong>{{ stats.chua_hoan_thanh }} booking</strong> đang trong quá trình theo dõi.</li>
            <li><strong>Đối tác & Nhân sự:</strong> <strong>{{ stats.top_hang_tau }}</strong> là hãng tàu được lựa chọn nhiều nhất. Nhân sự <strong>{{ stats.top_nv }}</strong> dẫn đầu năng suất với <strong>{{ stats.max_nv }} booking</strong>.</li>
          </ul>
        </div>

        <div class="print-signatures">
          <div class="sign-box">
            <p><strong>Người lập biểu</strong></p>
            <p class="sign-note">(Ký, ghi rõ họ tên)</p>
          </div>
          <div class="sign-box">
            <p><strong>Trưởng phòng Khai thác</strong></p>
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

const formatDate = (dateStr) => {
  if (!dateStr) return '---';
  const [year, month, day] = dateStr.split('T')[0].split('-');
  return `${day}/${month}/${year}`;
};

const formatDateTime = (dateStr) => {
  if (!dateStr) return '---';
  const date = new Date(dateStr);
  return `${date.toLocaleDateString('vi-VN')} ${date.toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'})}`;
};

// TÍNH TOÁN DỮ LIỆU BIỂU ĐỒ TRÒN (Hãng tàu)
const pieChartData = computed(() => {
  const counts = {};
  props.listData.forEach(item => {
    let ht = item.ten_hang_tau || 'Khác';
    counts[ht] = (counts[ht] || 0) + 1;
  });

  // Top 5 hãng tàu, còn lại gom vào "Khác"
  const sortedArr = Object.keys(counts).map(k => ({ name: k, val: counts[k] })).sort((a, b) => b.val - a.val);
  const top5 = sortedArr.slice(0, 5);
  const others = sortedArr.slice(5);

  let labels = top5.map(i => i.name.length > 15 ? i.name.substring(0, 15) + '...' : i.name);
  let data = top5.map(i => i.val);

  if (others.length > 0) {
    labels.push('Khác');
    data.push(others.reduce((sum, item) => sum + item.val, 0));
  }

  return {
    labels: labels,
    datasets: [{
      backgroundColor: ['#2980b9', '#f39c12', '#27ae60', '#e74c3c', '#8e44ad', '#95a5a6'],
      data: data, borderWidth: 2, borderColor: '#ffffff'
    }]
  };
});

const pieChartOptions = {
  responsive: true, 
  maintainAspectRatio: false, 
  cutout: '50%', 
  animation: { duration: 0 },
  layout: { 
    // TĂNG LỀ PHẢI (right: 40) ĐỂ CHÚ THÍCH CÓ KHOẢNG THỞ, KHÔNG BỊ ÉP VÀO TƯỜNG
    padding: { top: 0, bottom: 20, left: 10, right: 40 } 
  },
  plugins: { 
    legend: { 
      position: 'right', 
      labels: { 
        font: { size: 12, family: 'Arial' },
        boxWidth: 15, // Thu nhỏ ô màu vuông lại một chút cho tinh tế
        padding: 15   // Nới rộng khoảng cách giữa các dòng chú thích
      } 
    },
    datalabels: { 
      display: true, color: '#ffffff', font: { weight: 'bold', size: 14, family: 'Arial' },
      anchor: 'center', align: 'center',
      formatter: (value, context) => {
        let sum = 0;
        let dataArr = context.chart.data.datasets[0].data;
        dataArr.forEach(data => { sum += Number(data); });
        let percentage = (Number(value) * 100 / sum).toFixed(1);
        return percentage > 4 ? percentage + "%" : "";
      }
    } 
  }
};

// TÍNH TOÁN DỮ LIỆU BIỂU ĐỒ CỘT (Top Nhân Viên)
const barChartData = computed(() => {
  const counts = {};
  props.listData.forEach(item => {
    let nv = item.nguoi_xu_ly || 'Chưa phân công';
    counts[nv] = (counts[nv] || 0) + 1;
  });

  const sortedArr = Object.keys(counts).map(key => ({ name: key, count: counts[key] }))
                                       .sort((a, b) => b.count - a.count).slice(0, 5);

  return {
    labels: sortedArr.map(item => item.name.length > 15 ? item.name.substring(0, 15) + '...' : item.name),
    datasets: [{ 
      label: 'Số booking', backgroundColor: '#d35400', 
      data: sortedArr.map(item => item.count) 
    }]
  };
});

const barChartOptions = {
  responsive: true, maintainAspectRatio: false, animation: { duration: 0 },
  layout: { padding: { top: 30, right: 15, left: 15 } }, 
  plugins: { 
    legend: { display: false },
    datalabels: { 
      display: true, clip: false, color: '#d35400',
      anchor: 'end', align: 'bottom', 
      font: { weight: 'bold', size: 13, family: 'Arial' },
      formatter: (value) => value
    } 
  },
  scales: { 
    x: { grid: { display: false }, ticks: { font: { size: 11 } } },
    y: { beginAtZero: true, min: 0, grace: '20%', ticks: { font: { size: 11 }, stepSize: 1 } } 
  }
};
</script>

<style scoped>
/* 100% CÔNG THỨC CSS TỪ FILE TRƯỚC */
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
.kpi-title { font-size: 9pt; font-weight: bold; margin-bottom: 8px; opacity: 0.9; }
.kpi-value { font-size: 20pt; font-weight: bold; }

.vip-banner { display: flex; align-items: center; background: #fdfaef; border: 1px solid #fae5b6; border-left: 6px solid #d35400; padding: 15px 20px; border-radius: 6px; margin-top: -5px;}
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