<template>
  <el-carousel height="300px" trigger="click" indicator-position="outside">
    <el-carousel-item v-for="(img, i) in bannerImages" :key="i">
      <img :src="img" class="banner-img" />
    </el-carousel-item>
  </el-carousel>
</template>

<script setup>
const bannerImages = [
  "/banner1.jpg",
  "/banner2.jpg",
  "/banner3.jpg"
]
</script>

<style scoped>
.banner-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
</style>
