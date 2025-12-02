<template>
  <div>
    <Navbar />

    <HeroBanner />

    <section class="section">
      <h2 class="section-title">熱門書籍</h2>
      <HotBooks />
    </section>

    <Footer />
  </div>
</template>

<script setup>
import Navbar from "../components/Navbar.vue"
import HeroBanner from "../components/HeroBanner.vue"
import HotBooks from "../components/HotBooks.vue"
import Footer from "../components/Footer.vue"
</script>

<style scoped>
.section {
  padding: 40px 80px;
}

.section-title {
  font-size: 22px;
  font-weight: bold;
  margin-bottom: 20px;
}
</style>
