<template>
  <div class="search-page">
    <div class="search-container">

      <!-- 頁面標題 -->
      <div class="search-header">
        <h1>搜尋結果</h1>
        <p class="keyword">
          關鍵字：「{{ keyword }}」
        </p>
      </div>

      <!-- 搜尋結果 -->
      <div class="search-results">
        <!-- 有結果 -->
        <div v-if="books.length > 0" class="book-grid">
          <!-- 之後這裡會換成 <BookCard /> -->
          <div
            class="book-card"
            v-for="book in books"
            :key="book.id"
          >
            <img :src="book.image" alt="" />
            <h3 class="title">{{ book.title }}</h3>
            <p class="author">{{ book.author }}</p>
            <p class="price">NT$ {{ book.price }}</p>
          </div>
        </div>

        <!-- 空狀態 -->
        <div v-else class="empty-state">
          <p>查無相關書籍</p>
          <p class="hint">請嘗試其他關鍵字</p>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

// 取得搜尋關鍵字（來自 ?keyword=xxx）
const keyword = computed(() => route.query.keyword || '')

// 假資料（之後改成 API）
const allBooks = ref([
  {
    id: 1,
    title: '被討厭的勇氣',
    author: '岸見一郎',
    price: 300,
    image: 'https://picsum.photos/200/280?1'
  },
  {
    id: 2,
    title: '原子習慣',
    author: 'James Clear',
    price: 330,
    image: 'https://picsum.photos/200/280?2'
  },
  {
    id: 3,
    title: '底層邏輯',
    author: '劉潤',
    price: 350,
    image: 'https://picsum.photos/200/280?3'
  },
  {
    id: 4,
    title: '蛤蟆先生去看心理師',
    author: 'Robert de Board',
    price: 280,
    image: 'https://picsum.photos/200/280?4'
  }
])

// 搜尋結果（依關鍵字過濾）
const books = computed(() => {
  if (!keyword.value) return []

  return allBooks.value.filter(book =>
    book.title.includes(keyword.value) ||
    book.author.includes(keyword.value)
  )
})

// 點擊書籍 → 詳細頁
function goBookDetail(id) {
  router.push(`/book/${id}`)
}
</script>
