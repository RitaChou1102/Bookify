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
            :src="book.cover_image?.image_url || 'https://via.placeholder.com/150x200?text=No+Image'" 
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

    <div class="pagination" v-if="total > 0">
       </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { searchBooks } from '@/api/book'

const route = useRoute()
const router = useRouter()

const keyword = ref('')
const books = ref([])
const total = ref(0)
const loading = ref(false)

// 執行搜尋
const doSearch = async () => {
  loading.value = true
  keyword.value = route.query.keyword || '' // 從網址參數拿關鍵字
  
  try {
    const res = await searchBooks(keyword.value)
    // Laravel paginate 回傳結構通常是: { data: [...], total: 10, ... }
    // 如果你的 API 回傳結構不同，請這裡微調
    if (res.data) {
        books.value = res.data
        total.value = res.total || res.data.length
    } else {
        // 如果沒分頁直接回傳陣列
        books.value = res
        total.value = res.length
    }
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

// 1. 進入頁面時搜尋
onMounted(() => {
  doSearch()
})

// 2. 監聽網址變化 (例如從搜尋 A 變搜尋 B)
watch(() => route.query.keyword, () => {
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
}
.header {
  margin-bottom: 30px;
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
.image-wrapper img {
  width: 100%;
  height: 100%;
  object-fit: cover; /* 讓圖片填滿 */
}
.book-info {
  padding: 15px;
}
.book-title {
  font-size: 16px;
  font-weight: bold;
  margin-bottom: 5px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
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