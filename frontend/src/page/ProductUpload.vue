<template>
  <div class="upload-page">
    <div class="form-container">
      <div class="page-header">
        <h1>商品上架</h1>
        <p>賣家中心 / 新增書籍</p>
      </div>

      <el-form 
        :model="form" 
        :rules="rules" 
        ref="formRef" 
        label-position="top" 
        class="product-form"
      >
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="書籍名稱" prop="name">
              <el-input v-model="form.name" placeholder="輸入書名" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="作者" prop="author">
              <el-input v-model="form.author" placeholder="輸入作者名稱" />
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="價格 (NT$)" prop="price">
              <el-input-number v-model="form.price" :min="0" class="w-full" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="庫存數量" prop="stock">
              <el-input-number v-model="form.stock" :min="1" class="w-full" />
            </el-form-item>
          </el-col>
        </el-row>

        <el-form-item label="書籍簡介" prop="description">
          <el-input 
            v-model="form.description" 
            type="textarea" 
            rows="4" 
            placeholder="請輸入書籍詳細介紹、書況描述..." 
          />
        </el-form-item>

        <el-form-item label="書籍封面圖片" prop="image_url">
          <div class="upload-area">
            <input 
              type="file" 
              ref="fileInput" 
              style="display: none" 
              accept="image/*"
              @change="handleFileChange"
            />

            <div v-if="!form.image_url" class="upload-placeholder" @click="triggerFileInput">
              <el-icon class="upload-icon" :size="30"><Plus /></el-icon>
              <div class="upload-text">點擊上傳封面圖片</div>
              <div v-if="uploading" class="uploading-text">正在上傳中...</div>
            </div>

            <div v-else class="preview-box">
              <img :src="form.image_url" class="preview-img" />
              <div class="preview-actions">
                <el-button type="danger" size="small" @click="resetImage">更換圖片</el-button>
              </div>
            </div>
          </div>
        </el-form-item>

        <div class="form-actions">
          <el-button size="large" @click="router.back()">取消</el-button>
          <el-button 
            type="primary" 
            size="large" 
            @click="submitProduct" 
            :loading="submitting"
          >
            確認上架
          </el-button>
        </div>
      </el-form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'
import axios from 'axios'
import { createBook } from '@/api/book' // 引入 API

const router = useRouter()
const formRef = ref(null)
const fileInput = ref(null)
const uploading = ref(false)
const submitting = ref(false)

// 表單資料
const form = reactive({
  name: '',
  author: '',
  price: 100,
  stock: 1,
  description: '',
  image_url: '' // 存放 Cloudinary 網址
})

// 驗證規則
const rules = {
  name: [{ required: true, message: '請輸入書名', trigger: 'blur' }],
  author: [{ required: true, message: '請輸入作者', trigger: 'blur' }],
  price: [{ required: true, message: '請輸入價格', trigger: 'blur' }],
  stock: [{ required: true, message: '請輸入庫存', trigger: 'blur' }],
  image_url: [{ required: true, message: '請上傳書籍封面', trigger: 'change' }]
}

// 觸發檔案選擇
const triggerFileInput = () => {
  if (!uploading.value) {
    fileInput.value.click()
  }
}

// 重置圖片
const resetImage = () => {
  form.image_url = ''
  if (fileInput.value) fileInput.value.value = ''
}

// 處理檔案上傳
const handleFileChange = async (e) => {
  const file = e.target.files[0]
  if (!file) return

  // 驗證大小 (2MB)
  if (file.size > 2 * 1024 * 1024) {
    ElMessage.warning('圖片大小請勿超過 2MB')
    return
  }

  uploading.value = true
  const formData = new FormData()
  formData.append('image', file)

  try {
    const token = localStorage.getItem('token')
    // 呼叫後端上傳 API
    const res = await axios.post('/api/upload-image', formData, {
      headers: { 
        'Content-Type': 'multipart/form-data',
        'Authorization': `Bearer ${token}`
      }
    })
    
    form.image_url = res.data.url
    ElMessage.success('圖片上傳成功！')
  } catch (err) {
    console.error(err)
    ElMessage.error('圖片上傳失敗，請稍後再試')
  } finally {
    uploading.value = false
  }
}

// 送出商品資料
const submitProduct = async () => {
  if (!formRef.value) return

  await formRef.value.validate(async (valid) => {
    if (valid) {
      submitting.value = true
      try {
        await createBook(form)
        ElMessage.success('書籍上架成功！')
        router.push('/') // 之後可以改導向「賣家商品列表」
      } catch (err) {
        console.error(err)
        ElMessage.error(err.response?.data?.message || '上架失敗')
      } finally {
        submitting.value = false
      }
    }
  })
}
</script>

<style scoped>
.upload-page {
  background-color: #f3f4f6;
  min-height: 100vh;
  padding: 40px 20px;
  display: flex;
  justify-content: center;
}
.form-container {
  background: white;
  width: 100%;
  max-width: 800px;
  padding: 40px;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}
.page-header { margin-bottom: 30px; text-align: center; }
.page-header h1 { margin: 0; color: #333; }
.page-header p { color: #666; margin-top: 5px; }

.w-full { width: 100%; }

.upload-area {
  border: 2px dashed #dcdfe6;
  border-radius: 6px;
  padding: 20px;
  text-align: center;
  background-color: #fafafa;
  min-height: 200px;
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  transition: 0.3s;
}
.upload-area:hover { border-color: #409EFF; }

.upload-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  color: #909399;
}
.upload-icon { margin-bottom: 10px; }
.uploading-text { margin-top: 10px; color: #409EFF; font-size: 14px; }

.preview-box { text-align: center; width: 100%; }
.preview-img {
  max-height: 300px;
  max-width: 100%;
  object-fit: contain;
  border-radius: 4px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  margin-bottom: 15px;
  display: block;
  margin-left: auto;
  margin-right: auto;
}
.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 15px;
  margin-top: 30px;
  padding-top: 20px;
  border-top: 1px solid #eee;
}
</style>