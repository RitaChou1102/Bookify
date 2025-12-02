<template>
  <div class="login-container">
    <div class="login-card">
      <div class="brand-header">
        <h1>Bookify</h1>
        <p>歡迎回來，請登入您的帳戶</p>
      </div>

      <form @submit.prevent="handleLogin">
        <div class="form-group">
          <label for="loginId">Login ID / 帳號</label>
          <input id="loginId" v-model="form.loginId" type="text" placeholder="請輸入帳號" required />
        </div>

        <div class="form-group">
          <label for="password">Password / 密碼</label>
          <input id="password" v-model="form.password" type="password" placeholder="請輸入密碼" required />
        </div>

        <div class="form-group">
          <label for="role" class="dev-label"><span class="badge">DEV</span> 模擬身分</label>
          <select id="role" v-model="form.role">
            <option value="Member">一般會員</option>
            <option value="Business">廠商</option>
            <option value="Admin">管理員</option>
          </select>
        </div>

        <div class="actions">
          <el-button type="primary" native-type="submit" class="w-full">登入</el-button>
          <el-button @click="goToRegister" class="w-full mt-2">註冊新帳號</el-button>
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
  loginId: '',
  password: '',
  role: 'Member'
})

const handleLogin = () => {
  if (form.loginId && form.password) {
    console.log('Login success:', form)
    // 登入成功後，跳轉回首頁
    router.push('/')
  } else {
    alert('請輸入帳號密碼')
  }
}

const goToRegister = () => {
  alert('註冊頁面開發中')
}
</script>

<style scoped>
.login-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f3f4f6;
}
.login-card {
  background: white;
  padding: 2.5rem;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  width: 100%;
  max-width: 400px;
}
.brand-header { text-align: center; margin-bottom: 2rem; }
.brand-header h1 { font-size: 2rem; color: #2563eb; margin: 0; font-weight: bold; }
.form-group { margin-bottom: 1.5rem; }
.form-group label { display: block; margin-bottom: 0.5rem; color: #374151; font-weight: 500; }
input, select { width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box; }
.badge { background-color: #f59e0b; color: white; font-size: 0.7rem; padding: 2px 6px; border-radius: 4px; margin-right: 5px; }
.w-full { width: 100%; }
.mt-2 { margin-top: 0.5rem; }
</style>