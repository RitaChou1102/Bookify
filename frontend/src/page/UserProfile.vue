<template>
  <div class="profile-container">
    <el-card class="profile-card">
      <template #header>
        <div class="card-header">
          <h2>個人資料設定</h2>
        </div>
      </template>

      <el-tabs v-model="activeTab">
        <el-tab-pane label="基本資料" name="info">
          <el-form :model="userInfo" label-width="100px" class="profile-form">
            <el-form-item label="帳號">
              <el-input v-model="userInfo.loginId" disabled />
            </el-form-item>
            <el-form-item label="姓名">
              <el-input v-model="userInfo.name" />
            </el-form-item>
            <el-form-item label="Email">
              <el-input v-model="userInfo.email" />
            </el-form-item>
            <el-form-item label="聯絡電話">
              <el-input v-model="userInfo.phone" />
            </el-form-item>
            <el-form-item label="預設地址">
              <el-input v-model="userInfo.address" type="textarea" rows="2" />
            </el-form-item>
            <el-form-item>
              <el-button type="primary" @click="saveInfo">儲存變更</el-button>
            </el-form-item>
          </el-form>
        </el-tab-pane>

        <el-tab-pane label="修改密碼" name="security">
          <el-form :model="passwordForm" label-width="100px" class="profile-form">
            <el-form-item label="目前密碼">
              <el-input v-model="passwordForm.current" type="password" show-password />
            </el-form-item>
            <el-form-item label="新密碼">
              <el-input v-model="passwordForm.new" type="password" show-password />
            </el-form-item>
            <el-form-item label="確認新密碼">
              <el-input v-model="passwordForm.confirm" type="password" show-password />
            </el-form-item>
            <el-form-item>
              <el-button type="danger" @click="changePassword">更新密碼</el-button>
            </el-form-item>
          </el-form>
        </el-tab-pane>
      </el-tabs>
    </el-card>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'

const activeTab = ref('info')

// 模擬使用者資料 (未來從 Pinia 或 API 取得)
const userInfo = reactive({
  loginId: 'user123',
  name: '王小明',
  email: 'ming@example.com',
  phone: '0912-345-678',
  address: '台北市信義區信義路五段7號'
})

const passwordForm = reactive({
  current: '',
  new: '',
  confirm: ''
})

const saveInfo = () => {
  // 呼叫 API PUT /api/user/profile
  console.log('儲存資料:', userInfo)
  alert('個人資料已更新！')
}

const changePassword = () => {
  if (passwordForm.new !== passwordForm.confirm) {
    alert('兩次新密碼輸入不一致')
    return
  }
  // 呼叫 API PUT /api/user/password
  console.log('修改密碼:', passwordForm)
  alert('密碼已更新，請重新登入')
}
</script>

<style scoped>
.profile-container {
  max-width: 800px;
  margin: 40px auto;
  padding: 0 20px;
}
.profile-card {
  border-radius: 8px;
}
.card-header h2 { margin: 0; font-size: 1.5rem; color: #333; }
.profile-form {
  max-width: 500px;
  margin-top: 20px;
}
</style>