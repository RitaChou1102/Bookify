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
          <el-button type="primary" native-type="submit" class="w-full" :loading="loading">登入</el-button>
          <el-button @click="goToRegister" class="w-full mt-2">註冊新帳號</el-button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { login } from '@/api/auth'
import { ElMessage } from 'element-plus'

const router = useRouter()
const loading = ref(false)
const form = reactive({
  loginId: '',
  password: '',
  role: 'Member'
})

const handleLogin = async () => {
  if (!form.loginId || !form.password) {
    ElMessage.warning('請輸入帳號密碼')
    return
  }

  loading.value = true
  try {
    // 調用登入 API
    const res = await login({
      login_id: form.loginId,
      password: form.password
    })

    // 保存 token 到 localStorage
    if (res.data.token) {
      localStorage.setItem('token', res.data.token)
      localStorage.setItem('user', JSON.stringify(res.data.user))
      
      ElMessage.success('登入成功！')
      
      // 登入成功後，跳轉回首頁
      router.push('/')
    } else {
      ElMessage.error('登入失敗：未收到 token')
    }
  } catch (err) {
    console.error('登入失敗:', err)
    if (err.response?.data?.message) {
      ElMessage.error(err.response.data.message)
    } else {
      ElMessage.error('登入失敗，請檢查帳號密碼')
    }
  } finally {
    loading.value = false
  }
}

const goToRegister = () => {
  router.push('/register')
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