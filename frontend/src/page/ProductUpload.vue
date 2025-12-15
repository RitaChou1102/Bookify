<template>
  <div class="upload-page">
    <div class="form-container">
      <div class="page-header">
        <h1>商品上架</h1>
        <p>廠商專用後台 / 新增書籍</p>
      </div>

      <el-form :model="form" label-position="top" class="product-form">
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="書籍名稱">
              <el-input v-model="form.name" placeholder="輸入書名" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="作者">
              <el-input v-model="form.author" placeholder="輸入作者名稱" />
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="8">
            <el-form-item label="價格 (NT$)">
              <el-input-number v-model="form.price" :min="0" class="w-full" />
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item label="庫存數量">
              <el-input-number v-model="form.stock" :min="1" class="w-full" />
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item label="分類">
              <el-select v-model="form.category" placeholder="選擇分類">
                <el-option label="商業理財" value="business" />
                <el-option label="心理勵志" value="psychology" />
                <el-option label="文學小說" value="fiction" />
                <el-option label="程式設計" value="programming" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>

        <el-form-item label="書籍簡介">
          <el-input v-model="form.description" type="textarea" rows="4" placeholder="請輸入書籍詳細介紹..." />
        </el-form-item>

        <el-form-item label="書籍封面圖片">
          <div class="upload-area">
            <el-button 
              v-if="!form.image" 
              type="primary" 
              plain 
              @click="openUploadWidget"
              :loading="!isScriptLoaded"
            >
              <span v-if="isScriptLoaded">點擊上傳圖片</span>
              <span v-else>載入上傳元件中...</span>
            </el-button>

            <div v-else class="preview-box">
              <img :src="form.image" class="preview-img" />
              <div class="preview-actions">
                <el-button type="danger" size="small" @click="form.image = ''">更換圖片</el-button>
              </div>
            </div>
          </div>
        </el-form-item>

        <div class="form-actions">
          <el-button size="large" @click="router.back()">取消</el-button>
          <el-button type="primary" size="large" @click="submitProduct">確認上架</el-button>
        </div>
      </el-form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// 表單資料
const form = reactive({
  name: '',
  author: '',
  price: 0,
  stock: 10,
  category: '',
  description: '',
  image: '' // 這裡會存放 Cloudinary 回傳的 URL
})

// --- Cloudinary Widget 相關邏輯 (與您之前的 Test 頁面類似) ---
const cloudName = import.meta.env.VITE_CLOUDINARY_CLOUD_NAME;
const uploadPreset = 'bookify_unpreset_name'; // 請確認您的 Preset 名稱
const isScriptLoaded = ref(false)
let myWidget = null

onMounted(() => {
  // 動態載入 Cloudinary script，確保不影響全站效能
  if (window.cloudinary) {
    isScriptLoaded.value = true
  } else {
    const script = document.createElement('script')
    script.src = 'https://upload-widget.cloudinary.com/global/all.js'
    script.onload = () => { isScriptLoaded.value = true }
    document.head.appendChild(script)
  }
})

const openUploadWidget = () => {
  if (!window.cloudinary) return;

  myWidget = window.cloudinary.createUploadWidget({
    cloudName: cloudName,
    uploadPreset: uploadPreset,
    sources: ['local', 'url'],
    multiple: false, // 一次只傳一張封面
    clientAllowedFormats: ['image'], // 只允許圖片
  }, (error, result) => {
    if (!error && result && result.event === "success") {
      console.log('上傳成功:', result.info);
      // 將回傳的圖片網址存入表單
      form.image = result.info.secure_url; 
    }
  })
  
  myWidget.open();
}

// 送出商品資料
const submitProduct = () => {
  if (!form.name || !form.price || !form.image) {
    alert('請填寫完整資訊並上傳圖片')
    return
  }

  // 這裡之後會串接後端 API (POST /api/books)
  console.log('商品上架資料:', form)
  
  alert('商品上架成功！')
  router.push('/') // 回首頁或商品列表
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
}
.preview-box { text-align: center; }
.preview-img {
  max-height: 200px;
  border-radius: 4px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  margin-bottom: 10px;
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