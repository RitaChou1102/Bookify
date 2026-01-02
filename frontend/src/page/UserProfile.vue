<template>
  <div class="profile-container">
    <el-card class="box-card">
      <template #header>
        <div class="card-header">
          <span>個人資料設定</span>
        </div>
      </template>

      <el-form 
        v-if="!loading" 
        :model="form" 
        :rules="rules"
        ref="formRef"
        label-width="100px"
        class="profile-form"
      >
        <el-form-item label="電子信箱">
          <el-input v-model="form.email" disabled />
          <span class="tip">帳號信箱無法修改</span>
        </el-form-item>

        <el-form-item label="姓名" prop="name">
          <el-input v-model="form.name" placeholder="請輸入您的稱呼" />
        </el-form-item>

        <el-form-item label="聯絡電話" prop="phone">
          <el-input v-model="form.phone" placeholder="用於訂單聯絡" />
        </el-form-item>

        <el-form-item label="收件地址" prop="address">
          <el-input 
            v-model="form.address" 
            type="textarea" 
            placeholder="預設收件地址" 
          />
        </el-form-item>

        <el-form-item>
          <el-button type="primary" @click="submitForm" :loading="saving">
            儲存基本資料
          </el-button>
        </el-form-item>
      </el-form>

      <div v-else class="loading-state">
        資料載入中...
      </div>

      <div v-if="!loading" class="password-section">
        <el-divider content-position="left">安全性設定</el-divider>

        <el-form 
          ref="pwdFormRef"
          :model="pwdForm"
          :rules="pwdRules"
          label-width="100px"
          class="profile-form"
        >
          <el-form-item label="目前密碼" prop="current_password">
            <el-input 
              v-model="pwdForm.current_password" 
              type="password" 
              show-password 
              placeholder="請輸入現在使用的密碼"
            />
          </el-form-item>
          
          <el-form-item label="新密碼" prop="new_password">
            <el-input 
              v-model="pwdForm.new_password" 
              type="password" 
              show-password 
              placeholder="請輸入新密碼 (至少8碼)"
            />
          </el-form-item>
          
          <el-form-item label="確認新密碼" prop="new_password_confirmation">
            <el-input 
              v-model="pwdForm.new_password_confirmation" 
              type="password" 
              show-password 
              placeholder="請再次輸入新密碼"
            />
          </el-form-item>

          <el-form-item>
            <el-button type="warning" @click="submitPwd" :loading="pwdSaving">
              修改密碼
            </el-button>
          </el-form-item>
        </el-form>
      </div>

    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
// [修正] 記得引入 changePassword
import { getUserProfile, updateUserProfile, changePassword } from '@/api/user'

const formRef = ref(null)
const loading = ref(true)
const saving = ref(false)

// 基本資料表單
const form = reactive({
  name: '',
  email: '',
  phone: '',
  address: ''
})

const rules = {
  name: [{ required: true, message: '請輸入姓名', trigger: 'blur' }],
  phone: [{ required: true, message: '請輸入電話', trigger: 'blur' }]
}

// [新增] 密碼表單相關變數
const pwdFormRef = ref(null)
const pwdSaving = ref(false)
const pwdForm = reactive({
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
})

// [新增] 密碼驗證規則
const pwdRules = {
  current_password: [
    { required: true, message: '請輸入目前密碼', trigger: 'blur' }
  ],
  new_password: [
    { required: true, message: '請輸入新密碼', trigger: 'blur' },
    { min: 8, message: '密碼長度需至少 8 碼', trigger: 'blur' }
  ],
  new_password_confirmation: [
    { required: true, message: '請再次輸入新密碼', trigger: 'blur' },
    { 
      validator: (rule, value, callback) => {
        if (value === '') {
          callback(new Error('請再次輸入新密碼'))
        } else if (value !== pwdForm.new_password) {
          callback(new Error('兩次輸入的密碼不一致'))
        } else {
          callback()
        }
      }, 
      trigger: 'blur' 
    }
  ]
}

onMounted(async () => {
  try {
    const data = await getUserProfile()
    form.name = data.name
    form.email = data.email
    form.phone = data.phone
    form.address = data.address
  } catch (err) {
    console.error(err)
    ElMessage.error('無法載入個人資料')
  } finally {
    loading.value = false
  }
})

// 更新基本資料
const submitForm = async () => {
  if (!formRef.value) return
  
  await formRef.value.validate(async (valid) => {
    if (valid) {
      saving.value = true
      try {
        await updateUserProfile({
          name: form.name,
          phone: form.phone,
          address: form.address
        })

        ElMessage.success('基本資料更新成功！')

        const currentUser = JSON.parse(localStorage.getItem('user') || '{}')
        currentUser.name = form.name
        localStorage.setItem('user', JSON.stringify(currentUser))
        
      } catch (err) {
        console.error(err)
        ElMessage.error(err.response?.data?.message || '更新失敗')
      } finally {
        saving.value = false
      }
    }
  })
}

// [新增] 更新密碼邏輯
const submitPwd = async () => {
  if (!pwdFormRef.value) return
  
  await pwdFormRef.value.validate(async (valid) => {
    if (valid) {
      pwdSaving.value = true
      try {
        await changePassword(pwdForm)
        ElMessage.success('密碼修改成功！')
        // 清空密碼欄位
        pwdFormRef.value.resetFields()
      } catch (err) {
        console.error(err)
        // 嘗試顯示後端回傳的具體錯誤 (例如舊密碼錯誤)
        const errorMsg = err.response?.data?.errors?.current_password?.[0] 
                         || err.response?.data?.message 
                         || '密碼修改失敗'
        ElMessage.error(errorMsg)
      } finally {
        pwdSaving.value = false
      }
    }
  })
}
</script>

<style scoped>
.profile-container {
  max-width: 800px;
  margin: 40px auto;
  padding: 0 20px;
}
.box-card {
  width: 100%;
}
.card-header {
  font-weight: bold;
  font-size: 18px;
}
.profile-form {
  max-width: 500px;
  margin-top: 20px;
}
.tip {
  font-size: 12px;
  color: #999;
  margin-left: 10px;
}
.loading-state {
  text-align: center;
  padding: 40px;
  color: #909399;
}
/* 新增樣式 */
.password-section {
  margin-top: 40px;
}
</style>