<template>
  <div class="edit-book-container">
    <el-card class="form-card">
      <template #header>
        <div class="card-header">
          <h2>✏️ 編輯書籍</h2>
          <el-button @click="$router.push('/vendor/products')">取消返回</el-button>
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
          <el-input v-model="form.description" type="textarea" rows="6" />
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
const bookId = route.params.id
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

// 載入舊資料
const fetchBookData = async () => {
  try {
    const res = await axios.get(`http://localhost:8000/api/books/${bookId}`)
    const book = res.data

    form.value = {
      name: book.name,
      author: book.author?.name || '未知',
      price: Number(book.price),
      stock: book.stock,
      description: book.description,
      image_url: book.cover_image?.image_url || ''
    }
  } catch (error) {
    ElMessage.error('無法載入書籍資料')
    router.push('/vendor/products')
  } finally {
    loading.value = false
  }
}

// 送出修改
const handleUpdate = async () => {
  saving.value = true
  const token = localStorage.getItem('token')

  try {
    await axios.put(`http://localhost:8000/api/books/${bookId}`, {
        name: form.value.name,
        price: form.value.price,
        stock: form.value.stock,
        description: form.value.description,
        // image_url: form.value.image_url 
    }, {
      headers: { Authorization: `Bearer ${token}` }
    })
    
    ElMessage.success('修改成功！')
    router.push('/vendor/products') // 🟢 修正：回到賣家商品列表

  } catch (error) {
    console.error(error)
    ElMessage.error('修改失敗')
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  fetchBookData()
})
</script>

<style scoped>
.edit-book-container { max-width: 800px; margin: 40px auto; padding: 0 20px; }
.card-header { display: flex; justify-content: space-between; align-items: center; }
</style>