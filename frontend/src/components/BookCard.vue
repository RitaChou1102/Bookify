<template>
  <div class="book-card" @click="goDetail">
    <img :src="book.image" alt="book cover" />

    <div class="info">
      <h3 class="title">{{ book.title }}</h3>
      <p class="author">{{ book.author }}</p>
      <p class="price">NT$ {{ book.price }}</p>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'

const props = defineProps({
  book: {
    type: Object,
    required: true
  }
})

const router = useRouter()

function goDetail() {
  router.push(`/book/${props.book.id}`)
}
</script>

<style scoped>
.book-card {
  cursor: pointer;
  border: 1px solid #e5e5e5;
  border-radius: 8px;
  overflow: hidden;
  background: #fff;
  transition: transform 0.2s, box-shadow 0.2s;
}

.book-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 6px 16px rgba(0,0,0,0.12);
}

.book-card img {
  width: 100%;
  height: 220px;
  object-fit: cover;
}

.info {
  padding: 12px;
}

.title {
  font-size: 15px;
  font-weight: 600;
  margin-bottom: 4px;
}

.author {
  font-size: 13px;
  color: #666;
}

.price {
  margin-top: 8px;
  font-weight: 600;
  color: #e67e22;
}
</style>
