<template>
  <div class="register-container">
    <el-card class="box-card">
      <template #header>
        <div class="card-header">
          <h2>申請成為賣家</h2>
          <p>填寫以下資訊，立即開始販售您的書籍！</p>
        </div>
      </template>
      
      <el-form :model="form" label-width="120px" size="large">
        <el-form-item label="商店名稱">
          <el-input v-model="form.store_name" placeholder="例如：小明的二手書屋" />
        </el-form-item>
        
        <el-form-item label="銀行帳號">
          <el-input v-model="form.bank_account" placeholder="銀行代碼-帳號 (僅供模擬用)" />
        </el-form-item>
        
        <el-form-item label="聯絡信箱">
          <el-input v-model="form.email" placeholder="接收訂單通知用" />
        </el-form-item>
        
        <el-form-item label="聯絡電話">
          <el-input v-model="form.phone" />
        </el-form-item>

        <el-form-item>
          <el-button type="primary" @click="submit" :loading="loading" style="width: 100%">
            提交申請
          </el-button>
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'

const router = useRouter()
const loading = ref(false)
const form = reactive({
  store_name: '',
  bank_account: '',
  email: '',
  phone: ''
})

// 自動帶入目前的 Email 和電話 (方便測試)
onMounted(() => {
  const userStr = localStorage.getItem('user')
  if (userStr) {
    const user = JSON.parse(userStr)
    form.email = user.email || ''
  }
})

const submit = async () => {
  if (!form.store_name || !form.bank_account) {
    ElMessage.warning('請填寫完整資訊')
    return
  }

  loading.value = true
  try {
    const token = localStorage.getItem('token')
    
    // 呼叫剛剛寫好的後端 API
    await axios.post('http://localhost:8000/api/vendor/register', form, {
       headers: { 'Authorization': `Bearer ${token}` }
    })
    
    ElMessage.success('申請成功！請重新登入以啟用權限')
    
    // 登出並跳轉登入頁 (強制刷新權限)
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    setTimeout(() => {
        router.push('/login')
    }, 1500)

  } catch (err) {
    console.error(err)
    // 如果後端回傳 "已經是賣家"，也視為成功，直接導向
    if (err.response && err.response.status === 400) {
       ElMessage.info('您已經是賣家囉！')
       router.push('/')
    } else {
       ElMessage.error('申請失敗，請稍後再試')
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.register-container { max-width: 600px; margin: 60px auto; padding: 0 20px; }
.card-header h2 { margin: 0; color: #303133; }
.card-header p { margin: 10px 0 0; color: #909399; font-size: 14px; }
</style>