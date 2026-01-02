<template>
    <div class="book-detail-page" v-if="book">
        <div class="book-image">
            <img :src="coverImage" :alt="book.name" />
        </div>

        <div class="book-info">
            <h1>{{ book.name }}</h1>
            
            <p class="author">作者：{{ book.author ? book.author.name : '未知作者' }}</p>
            
            <p class="price">NT$ {{ Math.floor(book.price) }}</p>

            <p class="description">{{ book.description || '暫無描述' }}</p>
            
            <div class="meta-info">
                <p>出版社：{{ book.publisher }}</p>
                <p>出版日期：{{ book.publish_date }}</p>
            </div>

            <div class="quantity">
                <label>數量：</label>
                <el-input-number v-model="qty" :min="1" :max="book.stock" />
                <span class="stock-text">(庫存: {{ book.stock }})</span>
            </div>

            <el-button 
                type="primary" 
                size="large"
                @click="handleAddToCart" 
                :loading="loading"
                :disabled="book.stock <= 0"
            >
                {{ book.stock > 0 ? '加入購物車' : '已售完' }}
            </el-button>
        </div>
    </div>
    
    <div v-else-if="loadingBook" class="loading-state">
        <p>載入中...</p>
    </div>
    
    <div v-else class="error-state">
        <p>找不到該書籍資料</p>
        <el-button @click="router.push('/')">回首頁</el-button>
    </div>
    <div class="review-section">
  <h3>商品評價</h3>
  
  <div v-if="reviews.length > 0">
    <div v-for="review in reviews" :key="review.review_id" class="review-item">
      <div class="review-header">
        <el-avatar :size="32" :src="review.user?.avatar || 'https://cube.elemecdn.com/0/88/03b0d39583f48206768a7534e55bcpng.png'" />
        <span class="review-user">{{ review.user?.name || '匿名買家' }}</span>
        <el-rate
          v-model="review.rating"
          disabled
          show-score
          text-color="#ff9900"
        />
        <span class="review-time">{{ new Date(review.created_at).toLocaleDateString() }}</span>
      </div>
      <div class="review-content">
        {{ review.comment }}
      </div>
    </div>
    
    </div>
  
  <el-empty v-else description="目前還沒有評論，快來成為第一個評論的人吧！" />
</div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue"
import { useRoute, useRouter } from "vue-router"
import { ElMessage } from "element-plus"
import { getBook } from "@/api/book"  // 引入剛剛寫好的 API
import { addToCart } from "@/api/cart"
import { getBookReviews } from '@/api/review'

const route = useRoute()
const router = useRouter()

const book = ref(null)      // 存放書籍資料
const qty = ref(1)          // 購買數量
const loading = ref(false)  // 按鈕讀取狀態
const loadingBook = ref(true) // 頁面載入狀態

const reviews = ref([])

// 計算封面圖片路徑
const coverImage = computed(() => {
    if (!book.value) return '';
    
    // 檢查 images 陣列
    if (book.value.images && book.value.images.length > 0) {
        // [修正這裡] 你的資料庫欄位是 image_url，不是 url
        return book.value.images[0].image_url; 
    }
    
    // 如果真的沒圖，回傳預設圖
    return "https://via.placeholder.com/300x400?text=No+Image";
})

onMounted(async () => {
    const id = route.params.id
    if (!id) {
        ElMessage.error("無效的書籍連結")
        router.push('/')
        return
    }

    try {
        console.log("正在載入書籍 ID:", id)
        const data = await getBook(id)
        book.value = data
        console.log("書籍資料載入成功:", data)
    } catch (err) {
        console.error("載入書籍失敗:", err)
        ElMessage.error("無法載入書籍資料")
    } finally {
        loadingBook.value = false
    }
    try {
        const reviewData = await getBookReviews(route.params.id)
        reviews.value = reviewData.data // Laravel paginate 回傳結構在 data 裡
    } catch (e) {
        console.error(e)
    }
})

async function handleAddToCart() {
    // 簡單檢查登入 (這只是一個基本檢查，實際應依賴全域狀態)
    const token = localStorage.getItem('token') // 假設你的 token 存這裡
    // if (!token) {
    //     ElMessage.warning('請先登入')
    //     router.push('/login')
    //     return
    // }

    loading.value = true
    try {
        // 注意：這裡使用 book.book_id (因為 Laravel 自訂主鍵)
        await addToCart(book.value.book_id, qty.value)
        ElMessage.success(`已加入 ${qty.value} 本《${book.value.name}》到購物車！`)
        qty.value = 1 // 重置數量
    } catch (err) {
        console.error('加入購物車失敗:', err)
        if (err.response?.status === 401) {
            ElMessage.warning('請先登入')
            router.push('/login')
        } else {
            ElMessage.error(err.response?.data?.message || '加入購物車失敗')
        }
    } finally {
        loading.value = false
    }
}
</script>

<style scoped>
.book-detail-page {
    display: flex;
    gap: 40px;
    padding: 40px;
    max-width: 1200px;
    margin: 0 auto;
    min-height: 80vh;
}

.book-image img {
    width: 350px;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.book-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
}

h1 {
    font-size: 2rem;
    margin-bottom: 10px;
    color: #333;
}

.author {
    font-size: 1.1rem;
    color: #666;
    margin-bottom: 20px;
}

.price {
    color: #e67e22;
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 20px;
}

.description {
    line-height: 1.6;
    color: #555;
    margin-bottom: 30px;
    white-space: pre-wrap; /* 保留換行 */
}

.meta-info {
    margin-bottom: 20px;
    color: #888;
    font-size: 0.9rem;
}

.quantity {
    margin: 20px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.stock-text {
    font-size: 0.9rem;
    color: #999;
}

.loading-state, .error-state {
    text-align: center;
    padding: 100px;
    font-size: 1.2rem;
    color: #666;
}

/* 響應式設計 */
@media (max-width: 768px) {
    .book-detail-page {
        flex-direction: column;
        padding: 20px;
    }
    
    .book-image img {
        width: 100%;
        max-width: 300px;
        margin: 0 auto;
        display: block;
    }
}

.review-section {
  max-width: 1000px;
  margin: 40px auto;
  background: #fff;
  padding: 24px;
  border-radius: 8px;
}
.review-item {
  border-bottom: 1px solid #f0f0f0;
  padding: 20px 0;
}
.review-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
}
.review-user {
  font-weight: bold;
  font-size: 14px;
}
.review-time {
  color: #999;
  font-size: 12px;
  margin-left: auto;
}
.review-content {
  color: #333;
  line-height: 1.6;
  padding-left: 42px; /* 對齊頭像右側 */
}
</style>