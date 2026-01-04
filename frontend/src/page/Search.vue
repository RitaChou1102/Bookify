<template>
  <div class="search-page">
    <div class="header">
      <h2>搜尋結果：{{ keyword || '全部書籍' }}</h2>
      <p class="count-text" v-if="!loading">共找到 {{ total }} 筆資料</p>
    </div>

    <div v-if="loading" class="loading">
      <el-skeleton :rows="3" animated />
    </div>

    <div v-else-if="books.length > 0" class="book-grid">
      <div 
        v-for="book in books" 
        :key="book.book_id" 
        class="book-card"
        @click="goToDetail(book.book_id)"
      >
        <div class="image-wrapper">
          <img 
            :src="book.cover_image?.image_url || 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?w=200'" 
            class="book-img"
            @error="(e) => e.target.src = 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?w=200'"
            :alt="book.name"
          />
        </div>
        <div class="book-info">
          <h3 class="book-title">{{ book.name }}</h3>
          <p class="book-author">{{ book.author?.name || '未知作者' }}</p>
          <div class="book-price">NT$ {{ Math.floor(book.price) }}</div>
        </div>
      </div>
    </div>

    <div v-else class="empty-state">
      <el-empty description="找不到相關書籍，換個關鍵字試試？" />
      <el-button type="primary" @click="$router.push('/')">回首頁逛逛</el-button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
// 假設您的 API 封裝在這裡，如果路徑不同請自行調整
import { searchBooks } from '@/api/book' 
import { ElMessage } from 'element-plus'

const route = useRoute()
const router = useRouter()

const keyword = ref('')
const books = ref([])
const total = ref(0)
const loading = ref(false)

// 執行搜尋
const doSearch = async () => {
  loading.value = true
  // 注意：這裡抓取的是網址上的 ?keyword=... 
  // 如果你的網址是 ?q=... 請改成 route.query.q
  keyword.value = route.query.keyword || route.query.q || '' 
  
  try {
    const res = await searchBooks(keyword.value)
    
    // 處理 Laravel 分頁回傳結構 (res.data.data 或 res.data)
    const resultList = res.data?.data || res.data || res;
    
    books.value = Array.isArray(resultList) ? resultList : [];
    total.value = res.total || books.value.length;

  } catch (err) {
    console.error(err)
    ElMessage.error('搜尋發生錯誤')
  } finally {
    loading.value = false
  }
}

// 1. 進入頁面時搜尋
onMounted(() => {
  doSearch()
})

// 2. 監聽網址變化 (相容 keyword 和 q 參數)
watch(() => [route.query.keyword, route.query.q], () => {
  doSearch()
})

function goToDetail(id) {
  router.push(`/book/${id}`)
}
</script>

<style scoped>
.search-page {
  max-width: 1200px;
  margin: 0 auto;
  padding: 40px 20px;
  min-height: 60vh;
}
.header {
  margin-bottom: 30px;
  text-align: center;
}
.count-text {
    color: #666;
    margin-top: 5px;
}
.book-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 30px;
}
.book-card {
  cursor: pointer;
  border: 1px solid #eee;
  border-radius: 8px;
  overflow: hidden;
  transition: transform 0.2s, box-shadow 0.2s;
  background: #fff;
  display: flex;
  flex-direction: column;
}
.book-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.image-wrapper {
  width: 100%;
  height: 260px; /* 固定圖片高度 */
  background: #f9f9f9;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}
/* 🟢 修正：圖片填滿樣式 */
.book-img {
  width: 100%;
  height: 100%;
  object-fit: cover; 
  transition: transform 0.3s;
}
.book-card:hover .book-img {
    transform: scale(1.05);
}

.book-info {
  padding: 15px;
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}
.book-title {
  font-size: 16px;
  font-weight: bold;
  margin-bottom: 5px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.book-author {
  font-size: 14px;
  color: #666;
  margin-bottom: 10px;
}
.book-price {
  color: #f56c6c;
  font-weight: bold;
  font-size: 18px;
}
.empty-state {
  text-align: center;
  padding: 60px 0;
}
.loading {
  padding: 20px;
}
</style>