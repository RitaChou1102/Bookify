<template>
  <el-carousel height="400px" trigger="click" :interval="5000" arrow="always">
    <el-carousel-item v-for="(item, i) in banners" :key="i">
      <div class="banner-container">
        <img :src="item.image" class="banner-img" />
        <div class="banner-content">
          <h2>{{ item.title }}</h2>
          <p>{{ item.desc }}</p>
        </div>
      </div>
    </el-carousel-item>
  </el-carousel>
</template>

<script setup>
// 使用 Unsplash 的高品質圖片，並加上一點文字說明
const banners = [
  {
    image: 'https://images.unsplash.com/photo-1507842217343-583bb7270b66?auto=format&fit=crop&q=80&w=1600',
    title: '探索知識的海洋',
    desc: '每一本書，都是一個新世界'
  },
  {
    image: 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&q=80&w=1600',
    title: '舒適的閱讀時光',
    desc: '為您精選最值得閱讀的好書'
  },
  {
    image: 'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?auto=format&fit=crop&q=80&w=1600',
    title: '隨時隨地，享受閱讀',
    desc: '加入會員，開啟您的閱讀之旅'
  }
]
</script>

<style scoped>
.banner-container {
  position: relative;
  width: 100%;
  height: 100%;
}

.banner-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  /* 加一點濾鏡讓文字更清楚 */
  filter: brightness(0.7); 
}

.banner-content {
  position: absolute;
  top: 50%;
  left: 10%;
  transform: translateY(-50%);
  color: white;
  text-shadow: 2px 2px 4px rgba(0,0,0,0.6);
}

.banner-content h2 {
  font-size: 3rem;
  margin-bottom: 1rem;
  font-weight: bold;
}

.banner-content p {
  font-size: 1.5rem;
  opacity: 0.9;
}
</style>