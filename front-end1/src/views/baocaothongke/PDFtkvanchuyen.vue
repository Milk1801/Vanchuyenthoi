<template>
  <div class="pdf-export-wrapper">
    <div id="pdf-dashboard-content" class="print-dashboard-wrapper">
      
      <div class="dashboard-header page-break-avoid">
        <div class="header-title">
          <h2>BÁO CÁO THỐNG KÊ VẬN CHUYỂN</h2>
        </div>
        <div class="header-filters">
          <div class="filter-item"><strong>Kỳ báo cáo:</strong> Từ {{ filters.tu_ngay ? formatDate(filters.tu_ngay) : 'Đầu kỳ' }} đến {{ filters.den_ngay ? formatDate(filters.den_ngay) : 'Hiện tại' }}</div>
          <div class="filter-item"><strong>Trạng thái:</strong> {{ filters.trang_thai || 'Tất cả' }} | <strong>Tìm kiếm:</strong> {{ filters.tim_kiem || 'Không' }}</div>
        </div>
      </div>

      <div class="kpi-row page-break-avoid">
        <div class="kpi-card" style="background-color: #3498db;">
          <div class="kpi-title">📦 TỔNG SỐ LÔ HÀNG</div>
          <div class="kpi-value">{{ stats.tong_so || 0 }}</div>
        </div>
        <div class="kpi-card" style="background-color: #f39c12;">
          <div class="kpi-title">🚚 ĐANG VẬN CHUYỂN</div>
          <div class="kpi-value">{{ stats.dang_van_chuyen || 0 }}</div>
        </div>
        <div class="kpi-card" style="background-color: #27ae60;">
          <div class="kpi-title">✅ HOÀN THÀNH</div>
          <div class="kpi-value">{{ stats.hoan_thanh || 0 }}</div>
        </div>
        <div class="kpi-card" style="background-color: #34495e;">
          <div class="kpi-title">👤 TB ĐƠN / NHÂN VIÊN</div>
          <div class="kpi-value">{{ stats.don_trung_binh || 0 }}</div>
        </div>
      </div>

      <div class="vip-banner page-break-avoid" v-if="stats.top_nhan_vien && stats.top_nhan_vien !== '---'">
        <div class="vip-icon">🏆</div>
        <div class="vip-text">
          <div class="vip-title">NHÂN VIÊN XUẤT SẮC NHẤT KỲ</div>
          <div class="vip-desc">
            <strong style="color: #8e44ad; font-size: 15pt;">{{ stats.top_nhan_vien }}</strong>
            <span style="color: #555;"> - Xử lý thành công <strong>{{ stats.max_don }} đơn hàng</strong></span>
          </div>
        </div>
      </div>

      <div class="charts-row page-break-avoid">
        <div class="chart-box pie-box">
          <h4 class="box-title">Tỷ trọng Trạng thái Đơn hàng</h4>
          <div class="chart-container">
            <Doughnut v-if="pieChartData.datasets.length > 0" :data="pieChartData" :options="pieChartOptions" />
          </div>
        </div>

        <div class="chart-box bar-box">
          <h4 class="box-title">Top Nhân viên theo số Lô hàng xử lý</h4>
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
              <th style="background: #2c3e50; width: 100px;">Mã Lô</th>
              <th style="background: #f39c12;">Tên Hàng</th>
              <th style="background: #2980b9;">Tuyến đường (Cảng Đi ➔ Đến)</th>
              <th style="background: #8e44ad; width: 90px;">ETD</th>
              <th style="background: #27ae60; width: 110px;">Trạng thái</th>
              <th style="background: #34495e; width: 130px;">Người xử lý</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in listData" :key="index">
              <td style="text-align: center; font-weight: bold;">{{ index + 1 }}</td>
              <td style="font-weight: bold; color: #2980b9; text-align: center;">L-{{ item.ma_lo_hang }}</td>
              <td style="font-weight: bold;">{{ item.ten_hang_hoa || '---' }}</td>
              <td>{{ item.cang_di || '?' }} ➔ {{ item.cang_den || '?' }}</td>
              <td style="text-align: center;">{{ formatDate(item.etd) }}</td>
              <td style="text-align: center; font-weight: bold;">{{ item.trang_thai_lo_hang }}</td>
              <td style="text-align: center;">{{ item.nguoi_xu_ly || '---' }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="footer-wrapper page-break-avoid">
        
        <div class="summary-section">
          <h3 class="section-title">📌 NHẬN XÉT KẾT QUẢ HOẠT ĐỘNG</h3>
          <ul class="summary-list">
            <li><strong>Tổng quan:</strong> Toàn bộ đội ngũ đã tiếp nhận và theo dõi <strong>{{ stats.tong_so }} lô hàng</strong>.</li>
            <li><strong>Tiến độ:</strong> Đã hoàn thành <strong>{{ stats.hoan_thanh }} lô</strong>, hiện đang vận chuyển/chờ xử lý <strong>{{ stats.dang_van_chuyen }} lô</strong>.</li>
            <li v-if="stats.top_nhan_vien && stats.top_nhan_vien !== '---'"><strong>Năng suất:</strong> Trung bình mỗi nhân sự xử lý <strong>{{ stats.don_trung_binh }} đơn</strong>. Trong đó, <strong>{{ stats.top_nhan_vien }}</strong> dẫn đầu với <strong>{{ stats.max_don }} đơn</strong>.</li>
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

// TÍNH TOÁN DỮ LIỆU BIỂU ĐỒ TRÒN (Trạng thái)
const pieChartData = computed(() => {
  const counts = {};
  props.listData.forEach(item => {
    let status = item.trang_thai_lo_hang || 'Khác';
    counts[status] = (counts[status] || 0) + 1;
  });

  const labels = Object.keys(counts);
  const data = Object.values(counts);

  return {
    labels: labels,
    datasets: [{
      backgroundColor: ['#27ae60', '#f39c12', '#3498db', '#e74c3c', '#9b59b6', '#95a5a6'],
      data: data, borderWidth: 2, borderColor: '#ffffff'
    }]
  };
});

const pieChartOptions = {
  responsive: true, maintainAspectRatio: false, cutout: '50%', animation: { duration: 0 },
  layout: { padding: { top: 0, bottom: 25, left: 10, right: 10 } },
  plugins: { 
    legend: { position: 'right', labels: { font: { size: 12, family: 'Arial' } } },
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

  // Chuyển object thành array, sắp xếp giảm dần và lấy Top 5
  const sortedArr = Object.keys(counts).map(key => ({ name: key, count: counts[key] }))
                                       .sort((a, b) => b.count - a.count)
                                       .slice(0, 5);

  return {
    labels: sortedArr.map(item => item.name.length > 15 ? item.name.substring(0, 15) + '...' : item.name),
    datasets: [{ 
      label: 'Số lô hàng', backgroundColor: '#8e44ad', 
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
      display: true, clip: false, color: '#8e44ad',
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

.section-title { margin: 0 0 15px 0; font-size: 13pt; color: #2c3e50; text-transform: uppercase; border-bottom: 2px solid #eee; padding-bottom: 8px;}
.excel-table { width: 100%; border-collapse: collapse; font-size: 10pt; }
.excel-table th, .excel-table td { border: 1px solid #dcdde1; padding: 10px; }
.excel-table th { color: white; font-weight: bold; text-align: center; }
.excel-table tbody tr:nth-child(even) { background-color: #f9f9f9; }
/* Thêm dòng này để khi sang trang, nó không chém đứt đôi dòng chữ của 1 lô hàng */
.excel-table tr { 
  page-break-inside: avoid; 
  break-inside: avoid; 
}
/* TẠO KHOẢNG THỞ RỘNG RÃI Ở DƯỚI BẢNG */
.table-section { 
  margin-bottom: 50px; 
}

/* ĐẢM BẢO KHỐI CUỐI CÙNG TỰ ĐỘNG NHẢY TRANG NẾU BỊ CHẬT */
.footer-wrapper {
  display: block;
  width: 100%;
}

.summary-section { 
  background: #f8f9fa; 
  padding: 25px 20px; 
  border-radius: 6px; 
  border: 1px solid #e9ecef; 
  margin-bottom: 50px; /* Đẩy chữ ký xuống dưới một khoảng cho thoáng */
}
.summary-list { margin: 0; padding-left: 20px; font-size: 12pt; line-height: 1.8; color: #2c3e50;}

.print-signatures { display: flex; justify-content: space-between; text-align: center; margin-bottom: 40px; }
.sign-box { width: 30%; font-size: 12pt; }
.sign-note { font-style: italic; font-size: 11pt; font-weight: normal; margin-top: 5px; color: #7f8c8d;}
</style>