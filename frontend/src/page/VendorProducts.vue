<template>
  <div class="vendor-products-container">
    <div class="page-header">
      <div class="title-section">
        <h1>商品管理</h1>
        <p>管理您上架的書籍資訊、庫存與狀態</p>
      </div>
      <el-button type="primary" @click="goToUpload">
        <el-icon class="el-icon--left"><Plus /></el-icon> 新增書籍
      </el-button>
    </div>

    <el-card class="table-card" v-loading="loading">
      <el-table :data="products" style="width: 100%" stripe>
        
        <el-table-column label="封面" width="100">
          <template #default="scope">
            <img 
              :src="scope.row.display_cover" 
              alt="cover" 
              class="product-thumb" 
            />
          </template>
        </el-table-column>

        <el-table-column prop="name" label="書名" min-width="150" />
        
        <el-table-column label="價格" width="120">
          <template #default="scope">
            NT$ {{ Math.floor(scope.row.price) }}
          </template>
        </el-table-column>

        <el-table-column prop="stock" label="庫存" width="100">
          <template #default="scope">
            <el-tag :type="scope.row.stock > 0 ? 'success' : 'danger'">
              {{ scope.row.stock }}
            </el-tag>
          </template>
        </el-table-column>

        <el-table-column label="狀態" width="100">
          <template #default="scope">
            <el-tag :type="scope.row.listing ? 'primary' : 'info'" effect="dark">
              {{ scope.row.listing ? '上架中' : '已下架' }}
            </el-tag>
          </template>
        </el-table-column>

        <el-table-column label="操作" width="200" fixed="right">
          <template #default="scope">
            <el-button size="small" type="primary" icon="Edit" @click="handleEdit(scope.row)">
              編輯
            </el-button>
            
            <el-button 
              size="small" 
              type="danger" 
              icon="Delete"
              @click="handleDelete(scope.row)"
            >
              刪除/下架
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <el-empty v-if="!loading && products.length === 0" description="您還沒有上架任何書籍" />
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus, Edit, Delete } from '@element-plus/icons-vue'

const router = useRouter()
const products = ref([])
const loading = ref(false)

// 🟢 定義一組隨機預設圖片庫 (Unsplash 高畫質書籍圖)
const placeholders = [
  'https://images.unsplash.com/photo-1544947950-fa07a98d237f?w=200',
  'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=200',
  'https://images.unsplash.com/photo-1589829085413-56de8ae18c73?w=200',
  'https://images.unsplash.com/photo-1495446815901-a7297e633e8d?w=200',
  'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?w=200',
  'https://images.unsplash.com/photo-1532012197267-da84d127e765?w=200',
  'https://images.unsplash.com/photo-1519681393784-d120267933ba?w=200'
]

// 輔助函式：隨機選一張
const getRandomImage = () => placeholders[Math.floor(Math.random() * placeholders.length)]

const fetchMyBooks = async () => {
  loading.value = true
  try {
    const token = localStorage.getItem('token')
    const res = await axios.get('http://localhost:8000/api/my-books', {
      headers: { 'Authorization': `Bearer ${token}` }
    })
    
    // 🟢 處理資料：如果沒有真實封面，就隨機分配一張預設圖
    // 這樣做的好處是，重新整理頁面時圖片會變，但在同一頁操作時圖片會固定住，不會一直閃爍
    products.value = res.data.map(book => {
      return {
        ...book,
        // 如果有後端回傳的圖就用，沒有就隨機挑一張
        display_cover: book.cover_image?.image_url || getRandomImage()
      }
    })

  } catch (err) {
    console.error(err)
    ElMessage.error('無法載入商品列表')
  } finally {
    loading.value = false
  }
}

const goToUpload = () => {
  router.push('/product/upload')
}

const handleEdit = (product) => {
  router.push(`/book/edit/${product.book_id}`)
}

const handleDelete = (product) => {
  ElMessageBox.confirm(
    `確定要刪除或下架「${product.name}」嗎？`,
    '警告',
    { confirmButtonText: '確定', cancelButtonText: '取消', type: 'warning' }
  ).then(async () => {
    try {
      const token = localStorage.getItem('token')
      const res = await axios.delete(`http://localhost:8000/api/books/${product.book_id}`, {
        headers: { 'Authorization': `Bearer ${token}` }
      })
      
      ElMessage.success(res.data.message || '操作成功')
      fetchMyBooks()
      
    } catch (err) {
      console.error(err)
      ElMessage.error('刪除失敗')
    }
  }).catch(() => {})
}

onMounted(() => {
  fetchMyBooks()
})
</script>

<style scoped>
.vendor-products-container { max-width: 1200px; margin: 0 auto; padding: 40px 20px; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.product-thumb { width: 60px; height: 80px; object-fit: cover; border-radius: 4px; border: 1px solid #eee; }
.table-card { border-radius: 8px; }
</style>