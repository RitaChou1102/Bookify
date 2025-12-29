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

        <p v-if="errorMessage" class="error-msg">{{ errorMessage }}</p>

        <div class="actions">
          <button type="submit" class="submit-btn" :disabled="loading">
            {{ loading ? '登入中...' : '登入' }}
          </button>
          <button type="button" @click="goToRegister" class="link-btn">
            註冊新帳號
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { login } from '@/api/auth'

const router = useRouter()
const loading = ref(false)
const errorMessage = ref('')

const form = reactive({
  loginId: '',
  password: ''
})

const handleLogin = async () => {
  if (!form.loginId || !form.password) {
    alert('請輸入帳號密碼')
    return
  }

  loading.value = true
  errorMessage.value = ''

  try {
    // 調用登入 API
    // 注意：後端需要 login_id，我們這裡做個對應
    const res = await login({
      login_id: form.loginId,
      password: form.password
    })

    // 登入成功處理
    const { token, user, role } = res.data

    if (token) {
      // 1. 存 Token
      localStorage.setItem('token', token)
      // 2. 存使用者資訊 (轉成字串)
      localStorage.setItem('user', JSON.stringify(user))
      // 3. 存身分 (方便之後判斷是否顯示管理員後台按鈕)
      localStorage.setItem('role', role || user.role)
      
      alert('登入成功！')
      
      // 根據身分跳轉 (可選，目前先統一回首頁)
      router.push('/')
    }
  } catch (err) {
    console.error('登入失敗:', err)
    errorMessage.value = err.response?.data?.message || '登入失敗，請檢查帳號密碼'
  } finally {
    loading.value = false
  }
}

const goToRegister = () => {
  router.push('/register')
}
</script>

<style scoped>
/* 樣式保持與註冊頁一致 */
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
input { width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box; }
.actions { display: flex; flex-direction: column; gap: 10px; }
.submit-btn {
  width: 100%; padding: 10px; background-color: #2563eb; color: white;
  border: none; border-radius: 6px; cursor: pointer; font-size: 1rem;
}
.submit-btn:disabled { background-color: #93c5fd; }
.link-btn {
  background: none; border: none; color: #666; cursor: pointer; text-decoration: underline;
}
.error-msg { color: #dc2626; font-size: 0.9rem; text-align: center; margin-bottom: 1rem; }
</style>