<template>
  <div class="my-books-container">
    <div class="header">
      <h2>📦 我的商品管理</h2>
      <el-button type="primary" @click="$router.push('/product/upload')">
        + 上架新書
      </el-button>
    </div>
    
    <el-table :data="books" style="width: 100%" v-loading="loading" empty-text="您目前還沒有上架任何書籍">
      
      <el-table-column label="圖片" width="100">
        <template #default="scope">
          <img 
            :src="getBookCover(scope.row)" 
            class="thumb-img"
          />
        </template>
      </el-table-column>

      <el-table-column prop="name" label="書名" min-width="150"></el-table-column>
      
      <el-table-column prop="price" label="價格" width="120">
        <template #default="scope">
          <span style="color: #e15536; font-weight: bold;">
            NT$ {{ scope.row.price }}
          </span>
        </template>
      </el-table-column>

      <el-table-column prop="stock" label="庫存" width="100">
         <template #default="scope">
            <el-tag :type="scope.row.stock > 0 ? 'success' : 'danger'">
               {{ scope.row.stock }} 本
            </el-tag>
         </template>
      </el-table-column>

      <el-table-column label="操作" width="180" fixed="right">
        <template #default="scope">
          <el-button size="small" @click="handleEdit(scope.row)">
            編輯
          </el-button>
          
          <el-button 
            size="small" 
            type="danger" 
            @click="handleDelete(scope.row)"
            :loading="scope.row.isDeleting"
          >
            刪除
          </el-button>
        </template>
      </el-table-column>
    </el-table>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'

const router = useRouter()
const books = ref([])
const loading = ref(true)

// 🎨 備用封面庫 (跟首頁一樣)
const fallbackCovers = [
  'https://images.unsplash.com/photo-1544947950-fa07a98d237f?auto=format&fit=crop&w=200&q=80',
  'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=200&q=80',
  'https://images.unsplash.com/photo-1589829085413-56de8ae18c73?auto=format&fit=crop&w=200&q=80',
  'https://images.unsplash.com/photo-1532012197267-da84d127e765?auto=format&fit=crop&w=200&q=80',
  'https://images.unsplash.com/photo-1495446815901-a7297e633e8d?auto=format&fit=crop&w=200&q=80',
  'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?auto=format&fit=crop&w=200&q=80',
]

// 🧠 智慧圖片選擇器
const getBookCover = (book) => {
  if (book.cover_image?.image_url) {
    return book.cover_image.image_url
  }
  const index = book.book_id % fallbackCovers.length
  return fallbackCovers[index]
}

// 取得列表
const fetchMyBooks = async () => {
  try {
    const token = localStorage.getItem('token')
    if (!token) {
        router.push('/login')
        return
    }
    // 直連後端 8000
    const res = await axios.get('http://localhost:8000/api/my-books', {
      headers: { Authorization: `Bearer ${token}` }
    })
    books.value = res.data
  } catch (error) {
    console.error('載入失敗', error)
    ElMessage.error('無法載入書籍列表')
  } finally {
    loading.value = false
  }
}

// 📝 編輯功能：跳轉到編輯頁面
const handleEdit = (book) => {
  // 我們等一下要去設定這個路由
  router.push(`/book/edit/${book.book_id}`)
}

// 🗑️ 刪除功能：真的呼叫 API
const handleDelete = async (book) => {
  try {
    // 1. 跳出確認框
    await ElMessageBox.confirm(
      `確定要下架刪除「${book.name}」嗎？此動作無法復原。`,
      '刪除確認',
      {
        confirmButtonText: '確定刪除',
        cancelButtonText: '取消',
        type: 'warning',
      }
    )

    // 2. 設定該行按鈕為讀取中
    book.isDeleting = true

    // 3. 呼叫後端 API
    const token = localStorage.getItem('token')
    await axios.delete(`http://localhost:8000/api/books/${book.book_id}`, {
        headers: { Authorization: `Bearer ${token}` }
    })

    // 4. 成功後，從前端列表中移除這本書
    books.value = books.value.filter(item => item.book_id !== book.book_id)
    ElMessage.success('刪除成功！')

  } catch (error) {
    if (error !== 'cancel') { // 如果不是使用者按取消
        console.error(error)
        ElMessage.error('刪除失敗，請稍後再試')
    }
  } finally {
    if (book) book.isDeleting = false
  }
}

onMounted(() => {
  fetchMyBooks()
})
</script>

<style scoped>
.my-books-container {
  padding: 30px;
  max-width: 1000px;
  margin: 0 auto;
}
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
.thumb-img {
  width: 60px;
  height: 80px;
  object-fit: cover;
  border-radius: 4px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
</style>