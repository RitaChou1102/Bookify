<template>
  <div class="books-grid">
    <el-card
      v-for="book in books"
      :key="book.book_id"
      class="book-card"
      shadow="hover"
    >
      <img :src="book.cover_image?.image_url || '/placeholder.jpg'" class="book-img" />
      <h3 class="book-title">{{ book.name }}</h3>
      <p class="book-author">{{ book.author?.name }}</p>
      <p class="book-price">NT$ {{ book.price }}</p>

      <el-button type="primary" size="small" @click="goDetail(book.book_id)">
        查看詳情
      </el-button>
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue"
import { useRouter } from "vue-router"
import { getHotBooks } from "../api/book"

const books = ref([])
const router = useRouter()

onMounted(async () => {
  try {
    const res = await getHotBooks()
    books.value = res.data
  } catch (err) {
    console.error("Error loading hot books", err)
  }
})

function goDetail(id) {
  router.push(`/book/${id}`)
}
</script>

<style scoped>
.books-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 20px;
}

.book-card {
  padding-bottom: 10px;
}

.book-img {
  width: 100%;
  height: 240px;
  object-fit: cover;
  border-radius: 4px;
}

.book-title {
  font-size: 16px;
  font-weight: 600;
  margin: 10px 0 4px;
}

.book-author {
  font-size: 14px;
  color: #666;
}

.book-price {
  margin: 6px 0;
  font-weight: bold;
  color: #409eff;
}
</style>
