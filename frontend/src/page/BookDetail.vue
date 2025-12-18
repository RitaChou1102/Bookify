<template>
    <div class="book-detail-page">

    <!-- 左側：書籍圖片 -->
    <div class="book-image">
        <img :src="book.image" :alt="book.title" />
    </div>

    <!-- 右側：資訊 -->
    <div class="book-info">
        <h1>{{ book.title }}</h1>
        <p class="author">作者：{{ book.author }}</p>
        <p class="price">NT$ {{ book.price }}</p>

        <!-- 描述 -->
        <p class="description">{{ book.description }}</p>

        <!-- 數量 -->
        <div class="quantity">
        <label>數量：</label>
        <el-input-number v-model="qty" :min="1" :max="10" />
        </div>

        <!-- 加入購物車 -->
        <el-button type="primary" @click="addToCart">
        加入購物車
        </el-button>
    </div>

    </div>
</template>

<script setup>
import { ref, onMounted } from "vue"
import { useRoute } from "vue-router"
import { ElMessage } from "element-plus"

// 假資料（後端串好後改成 API）
const book = ref({
    id: 1,
    title: "被討厭的勇氣",
    author: "岸見一郎",
    price: 300,
    description: "《被討厭的勇氣》以對話方式，解釋阿德勒心理學核心思想，強調責任、自我接納與自由，是近年最受歡迎的心理勵志書之一。",
    image: "https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=500&q=80" 
})


const qty = ref(1)

// 抓網址上的 /book/:id
const route = useRoute()

onMounted(() => {
    const id = route.params.id
    console.log("書籍 ID：", id)
  // TODO: call API → getBookById(id)
})

function addToCart() {
  // TODO: 呼叫購物車 API
    ElMessage.success("已加入購物車！")
}
</script>

<style scoped>
.book-detail-page {
    display: flex;
    gap: 40px;
    padding: 40px;
    min-height: 80vh;
}

.book-image img {
    width: 300px;
    border-radius: 8px;
}

.book-info {
    max-width: 600px;
}

.author, .price {
    margin: 10px 0;
    font-size: 18px;
}

.price {
    color: #e67e22;
    font-weight: bold;
}

.quantity {
    margin: 20px 0;
}
</style>
