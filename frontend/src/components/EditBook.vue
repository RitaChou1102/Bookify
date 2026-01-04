<template>
  <div class="edit-book-container">
    <el-card class="form-card">
      <template #header>
        <div class="card-header">
          <h2>✏️ 編輯書籍</h2>
          <el-button @click="$router.push('/my-books')">取消返回</el-button>
        </div>
      </template>

      <el-form :model="form" label-width="100px" v-loading="loading">
        
        <el-form-item label="書籍名稱">
          <el-input v-model="form.name" />
        </el-form-item>

        <el-form-item label="作者">
           <el-input v-model="form.author" disabled placeholder="作者名稱 (不可修改)" />
        </el-form-item>

        <el-form-item label="價格 (NT$)">
          <el-input-number v-model="form.price" :min="0" style="width: 100%" />
        </el-form-item>

        <el-form-item label="庫存數量">
          <el-input-number v-model="form.stock" :min="0" style="width: 100%" />
        </el-form-item>

        <el-form-item label="書籍介紹">
          <el-input v-model="form.description" type="textarea" rows="4" />
        </el-form-item>
        
        <el-form-item label="圖片網址">
           <el-input v-model="form.image_url" placeholder="請輸入圖片 URL" />
           <div class="preview-area" v-if="form.image_url">
              <p>預覽：</p>
              <img :src="form.image_url" class="preview-img" />
           </div>
        </el-form-item>

        <el-form-item>
          <el-button type="primary" @click="handleUpdate" :loading="saving">
            儲存修改
          </el-button>
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import { ElMessage } from 'element-plus'

const route = useRoute()
const router = useRouter()
const bookId = route.params.id // 1. 從網址抓 ID
const loading = ref(true)
const saving = ref(false)

const form = ref({
  name: '',
  author: '',
  price: 0,
  stock: 1,
  description: '',
  image_url: ''
})

// 2. 載入這本書的舊資料
const fetchBookData = async () => {
  try {
    // 這裡我們直接用公開的 show API 查舊資料即可
    const res = await axios.get(`http://localhost:8000/api/books/${bookId}`)
    const book = res.data

    form.value = {
      name: book.name,
      author: book.author?.name || '未知',
      price: Number(book.price),
      stock: book.stock,
      description: book.description,
      // 如果有封面圖，抓出來填進去
      image_url: book.cover_image?.image_url || ''
    }
  } catch (error) {
    ElMessage.error('無法載入書籍資料')
    router.push('/my-books')
  } finally {
    loading.value = false
  }
}

// 3. 送出修改
const handleUpdate = async () => {
  saving.value = true
  const token = localStorage.getItem('token')

  try {
    // 呼叫 PUT API
    await axios.put(`http://localhost:8000/api/books/${bookId}`, {
        name: form.value.name,
        price: form.value.price,
        stock: form.value.stock,
        description: form.value.description,
        image_url: form.value.image_url // 把新的圖片網址傳給後端
    }, {
      headers: { Authorization: `Bearer ${token}` }
    })
    
    ElMessage.success('修改成功！')
    router.push('/my-books') // 修改完跳回列表

  } catch (error) {
    console.error(error)
    ElMessage.error('修改失敗：' + (error.response?.data?.message || '未知錯誤'))
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  fetchBookData()
})
</script>

<style scoped>
.edit-book-container {
  max-width: 800px;
  margin: 40px auto;
  padding: 0 20px;
}
.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.preview-area {
    margin-top: 15px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 8px;
    text-align: center;
}
.preview-img {
    height: 200px;
    object-fit: contain;
    border-radius: 4px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
</style>