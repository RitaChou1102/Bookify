<template>
  <div class="books-grid">
    <el-card
      v-for="book in books"
      :key="book.book_id"
      class="book-card"
      shadow="hover"
    >
      <img :src="getBookCover(book)" class="book-img" />
      
      <h3 class="book-title">{{ book.name }}</h3>
      <p class="book-author">{{ book.author?.name || '未知作者' }}</p>
      <p class="book-price">NT$ {{ book.price }}</p>

      <div class="button-group">
        <el-button type="primary" size="small" @click="goDetail(book.book_id)">
          查看詳情
        </el-button>
        <el-button type="success" size="small" @click="handleAddToCart(book.book_id)">
          加入購物車
        </el-button>
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue"
import { useRouter } from "vue-router"
import { getHotBooks } from "../api/book"
import { addToCart } from "../api/cart"
import { ElMessage } from "element-plus"

const books = ref([])
const router = useRouter()

// 🎨 準備一組精美的備用封面庫 (這樣看起來就不會都一樣了！)
const fallbackCovers = [
  'https://images.unsplash.com/photo-1544947950-fa07a98d237f?auto=format&fit=crop&w=400&q=80', // 經典書架
  'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=400&q=80', // 閱讀時光
  'https://images.unsplash.com/photo-1589829085413-56de8ae18c73?auto=format&fit=crop&w=400&q=80', // 法律/厚書
  'https://images.unsplash.com/photo-1532012197267-da84d127e765?auto=format&fit=crop&w=400&q=80', // 白色極簡
  'https://images.unsplash.com/photo-1495446815901-a7297e633e8d?auto=format&fit=crop&w=400&q=80', // 咖啡與書
  'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?auto=format&fit=crop&w=400&q=80', // 開放式書本
]

// 🧠 智慧圖片選擇器
function getBookCover(book) {
  // 1. 如果後端有真的傳圖片網址過來，就用真的
  if (book.cover_image?.image_url) {
    return book.cover_image.image_url
  }
  
  // 2. 如果沒有，就用書的 ID 來算命，決定要用哪一張備用圖
  // (這樣同一本書永遠會是同一張圖，但不同書會有不同圖)
  const index = book.book_id % fallbackCovers.length
  return fallbackCovers[index]
}

onMounted(async () => {
  try {
    const res = await getHotBooks()
    books.value = res.data
    // 如果後端回傳的是分頁格式 (Laravel Pagination)，資料可能在 res.data.data
    if (res.data && res.data.data) {
        books.value = res.data.data;
    } else {
        books.value = res.data;
    }
  } catch (err) {
    console.error("Error loading hot books", err)
  }
})

function goDetail(id) {
  router.push(`/book/${id}`)
}

async function handleAddToCart(bookId) {
  const token = localStorage.getItem('token')
  if (!token) {
    ElMessage.warning('請先登入')
    router.push('/login')
    return
  }

  try {
    await addToCart(bookId, 1)
    ElMessage.success('已加入購物車！')
  } catch (err) {
    console.error('加入購物車失敗:', err)
    if (err.response?.status === 401) {
      ElMessage.warning('請先登入')
      router.push('/login')
    } else if (err.response?.data?.message) {
      ElMessage.error(err.response.data.message)
    } else {
      ElMessage.error('加入購物車失敗')
    }
  }
}
</script>

<style scoped>
.books-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 25px;
  padding: 20px 0;
}

.book-card {
  padding-bottom: 10px;
  transition: transform 0.2s;
}

.book-card:hover {
  transform: translateY(-5px);
}

.book-img {
  width: 100%;
  height: 260px; /*稍微調高一點比較好看*/
  object-fit: cover;
  border-radius: 4px;
}

.book-title {
  font-size: 16px;
  font-weight: 600;
  margin: 12px 0 4px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.book-author {
  font-size: 14px;
  color: #666;
  margin-bottom: 8px;
}

.book-price {
  margin: 6px 0;
  font-weight: bold;
  color: #e15536; /* 價格改個顯眼的顏色 */
  font-size: 18px;
}

.button-group {
  display: flex;
  gap: 8px;
  margin-top: 15px;
}

.button-group .el-button {
  flex: 1;
}
</style>