<template>
  <div class="category-container">
    <h2 class="section-title">書籍分類</h2>

    <div class="category-grid">
      <div
        class="category-item"
        v-for="c in categories"
        :key="c.id"
        @click="goCategory(c.id)"
      >
        <img :src="c.image" class="category-img" />
        <p class="category-name">{{ c.name }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from "vue-router"

const router = useRouter()

// 你之後可以改成要從後端 API 取得分類
const categories = [
  { id: 1, name: "文學小說", image: "/cate_novel.jpg" },
  { id: 2, name: "心理勵志", image: "/cate_psych.jpg" },
  { id: 3, name: "投資理財", image: "/cate_finance.jpg" },
  { id: 4, name: "程式設計", image: "/cate_programming.jpg" },
  { id: 5, name: "學術教材", image: "/cate_academic.jpg" },
  { id: 6, name: "生活風格", image: "/cate_life.jpg" }
]

function goCategory(categoryId) {
  router.push(`/category/${categoryId}`)
}
</script>

<style scoped>
.category-container {
  padding: 40px 80px;
}

.section-title {
  font-size: 22px;
  font-weight: bold;
  margin-bottom: 20px;
}

.category-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
  gap: 20px;
}

.category-item {
  background: #fff;
  border-radius: 10px;
  cursor: pointer;
  transition: 0.2s;
  text-align: center;
  padding: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.category-item:hover {
  transform: translateY(-3px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}

.category-img {
  width: 100%;
  height: 120px;
  object-fit: cover;
  border-radius: 6px;
}

.category-name {
  margin-top: 10px;
  font-size: 15px;
  font-weight: 600;
}
</style>
