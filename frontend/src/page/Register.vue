<template>
  <div class="register-container">
    <div class="register-card">
      <div class="brand-header">
        <h1>Bookify</h1>
        <p>註冊新帳號</p>
      </div>

      <form @submit.prevent="handleRegister">
        <div class="form-group">
          <label for="name">Name / 名稱</label>
          <input id="name" v-model="form.name" type="text" placeholder="請輸入您的名稱" required />
        </div>

        <div class="form-group">
          <label for="loginId">Login ID / 帳號</label>
          <input id="loginId" v-model="form.loginId" type="text" placeholder="設定登入帳號" required />
        </div>

        <div class="form-group">
          <label for="email">Email / 電子郵件</label>
          <input id="email" v-model="form.email" type="email" placeholder="example@email.com" required />
        </div>

        <div class="form-group">
          <label for="password">Password / 密碼</label>
          <input id="password" v-model="form.password" type="password" placeholder="設定密碼" required />
        </div>

        <div class="form-group">
          <label for="confirmPassword">Confirm Password / 確認密碼</label>
          <input id="confirmPassword" v-model="form.confirmPassword" type="password" placeholder="再次輸入密碼" required />
        </div>

        <div class="form-group">
          <label for="role" class="dev-label"><span class="badge">DEV</span> 註冊身分</label>
          <select id="role" v-model="form.role">
            <option value="Member">一般會員</option>
            <option value="Business">廠商</option>
          </select>
        </div>

        <div class="actions">
          <el-button type="primary" native-type="submit" class="w-full">註冊</el-button>
          <el-button @click="goToLogin" class="w-full mt-2">返回登入</el-button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const form = reactive({
  name: '',
  loginId: '',
  email: '',
  password: '',
  confirmPassword: '',
  role: 'Member'
})

const handleRegister = () => {
  // 簡單的密碼確認檢查
  if (form.password !== form.confirmPassword) {
    alert('兩次密碼輸入不一致！')
    return
  }

  // 這裡之後會串接後端 API
  console.log('Register data:', form)
  alert('註冊成功！(模擬)')
  
  // 註冊成功後跳轉回登入頁
  router.push('/login')
}

const goToLogin = () => {
  router.push('/login')
}
</script>

<style scoped>
.register-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f3f4f6;
  padding: 20px;
}
.register-card {
  background: white;
  padding: 2.5rem;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  width: 100%;
  max-width: 400px;
}
.brand-header { text-align: center; margin-bottom: 1.5rem; }
.brand-header h1 { font-size: 2rem; color: #2563eb; margin: 0; font-weight: bold; }
.form-group { margin-bottom: 1rem; }
.form-group label { display: block; margin-bottom: 0.3rem; color: #374151; font-weight: 500; font-size: 0.9rem; }
input, select { width: 100%; padding: 0.6rem; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box; }
.badge { background-color: #f59e0b; color: white; font-size: 0.7rem; padding: 2px 6px; border-radius: 4px; margin-right: 5px; }
.w-full { width: 100%; }
.mt-2 { margin-top: 0.5rem; }
</style>