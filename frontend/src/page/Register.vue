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
          <label for="role">註冊身分</label>
          <select id="role" v-model="form.role">
            <option value="member">一般會員 (Member)</option>
            <option value="business">廠商 (Business)</option>
          </select>
        </div>

        <p v-if="errorMessage" class="error-msg">{{ errorMessage }}</p>

        <div class="actions">
          <button type="submit" class="submit-btn" :disabled="loading">
            {{ loading ? '註冊中...' : '註冊' }}
          </button>
          <button type="button" @click="goToLogin" class="link-btn">
            已有帳號？返回登入
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { register } from '@/api/auth' // 1. 記得引入 API

const router = useRouter()
const loading = ref(false)
const errorMessage = ref('')

const form = reactive({
  name: '',
  loginId: '',
  email: '',
  password: '',
  confirmPassword: '',
  role: 'member' // 2. 預設值改為小寫，配合後端驗證
})

const handleRegister = async () => {
  // 基本檢查
  if (form.password !== form.confirmPassword) {
    alert('兩次密碼輸入不一致！')
    return
  }

  try {
    loading.value = true
    errorMessage.value = ''

    // 3. 準備資料：把前端的 camelCase 轉成後端要的 snake_case
    const payload = {
      name: form.name,
      login_id: form.loginId, // 👈 關鍵轉換！後端要 login_id
      email: form.email,
      password: form.password,
      role: form.role // 確保這裡是小寫
    }

    // 4. 真的呼叫後端
    await register(payload)
    
    alert('註冊成功！請登入')
    router.push('/login')

  } catch (error) {
    console.error('註冊失敗:', error)
    // 顯示後端回傳的錯誤 (例如 Email 重複)
    errorMessage.value = error.response?.data?.message || '註冊失敗，請檢查資料是否重複'
  } finally {
    loading.value = false
  }
}

const goToLogin = () => {
  router.push('/login')
}
</script>

<style scoped>
/* 樣式保持不變 */
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
input, select { width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box; }
.actions { margin-top: 1.5rem; display: flex; flex-direction: column; gap: 10px; }
.submit-btn { width: 100%; padding: 10px; background-color: #2563eb; color: white; border: none; border-radius: 6px; cursor: pointer; }
.submit-btn:disabled { background-color: #93c5fd; }
.link-btn { background: none; border: none; color: #666; cursor: pointer; text-decoration: underline; }
.error-msg { color: #dc2626; font-size: 0.9rem; text-align: center; margin-top: 10px; }
</style>