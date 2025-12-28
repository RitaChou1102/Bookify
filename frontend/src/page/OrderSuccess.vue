<template>
  <div class="success-container">
    <el-result
      icon="success"
      title="訂單成立成功"
      sub-title="感謝您的購買，我們將盡快為您出貨"
    >
      <template #extra>
        <div class="order-info">
          <p>訂單編號：<strong>{{ orderId }}</strong></p>
          <p>訂單金額：<strong>NT$ {{ total }}</strong></p>
        </div>

        <div class="action-buttons">
          <el-button type="primary" @click="goToOrders">
            查看我的訂單
          </el-button>
          <el-button @click="goHome">
            回首頁
          </el-button>
        </div>
      </template>
    </el-result>
  </div>
</template>

<script setup>
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

// 從路由 query 取得資料（Demo 用）
const orderId = route.query.orderId || 'ORDER-0001'
const total = route.query.total || 0

const goHome = () => {
  router.push('/')
}

const goToOrders = () => {
  router.push('/orders')
}
</script>

<style scoped>
.success-container {
  max-width: 600px;
  margin: 80px auto;
  text-align: center;
}

.order-info {
  margin: 20px 0;
  font-size: 16px;
}

.action-buttons {
  display: flex;
  justify-content: center;
  gap: 15px;
  margin-top: 20px;
}
</style>
