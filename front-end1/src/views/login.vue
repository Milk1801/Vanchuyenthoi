<template>
  <div class="login-container">
    <div class="login-card">
      <div class="logo-box">
        <h2>SINCERE LOGISTICS</h2>
        <p>Hệ thống Quản lý Giao nhận & ERP</p>
      </div>

      <form @submit.prevent="handleLogin" class="login-form">
        <div v-if="errorMessage" class="alert-error">
          {{ errorMessage }}
        </div>

        <div class="form-group">
          <label>Mã Tài Khoản (ID)</label>
          <input 
            type="text" 
            v-model="maTaiKhoan" 
            placeholder="Nhập mã số tài khoản của bạn (VD: 1, 2, 3...)" 
            required
          />
        </div>

        <div class="form-group">
          <label>Mật Khẩu</label>
          <input 
            type="password" 
            v-model="password" 
            placeholder="Nhập mật khẩu..." 
            required
          />
        </div>

        <button type="submit" class="btn-login" :disabled="isLoading">
          {{ isLoading ? 'ĐANG XỬ LÝ...' : 'ĐĂNG NHẬP HỆ THỐNG' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';

// Đổi biến email thành maTaiKhoan
const maTaiKhoan = ref('');
const password = ref('');
const errorMessage = ref('');
const isLoading = ref(false);
const router = useRouter();

const handleLogin = async () => {
  isLoading.value = true;
  errorMessage.value = '';

  try {
    const response = await fetch('http://localhost/api-QLVC/login.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      // Đổi key gửi đi thành ma_tai_khoan
      body: JSON.stringify({
        ma_tai_khoan: maTaiKhoan.value,
        mat_khau: password.value
      })
    });

    const data = await response.json();

    if (data.success) {
      localStorage.setItem('sincere_user', JSON.stringify(data.user));
      alert(data.message);
      router.push('/home');
    } else {
      errorMessage.value = data.message;
    }
  } catch (error) {
    errorMessage.value = "Không thể kết nối đến máy chủ. Vui lòng bật XAMPP!";
  } finally {
    isLoading.value = false;
  }
};
</script>

<style scoped src="../assets/login.css"></style>