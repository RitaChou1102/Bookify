<template>
  <div class="order-detail-page">
    <div class="order-detail-container">
      <h1 class="title">訂單詳情</h1>

      <!-- 訂單不存在 -->
      <div v-if="!order.orderId" class="not-found">
        <p>找不到此訂單</p>
        <el-button @click="goBack">返回訂單列表</el-button>
      </div>

      <!-- 訂單資訊 -->
      <div v-else>
      <div class="order-info">
        <p><strong>訂單編號：</strong>{{ order.orderId }}</p>
        <p><strong>下單日期：</strong>{{ order.date }}</p>
        <p>
          <strong>狀態：</strong>
          <el-tag :type="statusType(order.status)">
            {{ order.status }}
          </el-tag>
        </p>
        <p><strong>總金額：</strong> NT$ {{ orderTotal }}</p>
      </div>

      <!-- 商品列表 -->
      <el-table :data="order.items" style="width: 100%; margin-top: 24px">
        <el-table-column prop="title" label="書名" />
        <el-table-column prop="price" label="單價" width="120">
          <template #default="scope">
            NT$ {{ scope.row.price }}
          </template>
        </el-table-column>
        <el-table-column prop="qty" label="數量" width="100" />
        <el-table-column label="小計" width="120">
          <template #default="scope">
            NT$ {{ scope.row.price * scope.row.qty }}
          </template>
        </el-table-column>
      </el-table>

      <!-- 返回 -->
      <div class="actions">
        <el-button @click="goBack">返回訂單列表</el-button>
      </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue"
import { useRoute, useRouter } from "vue-router"

const route = useRoute()
const router = useRouter()

// 假訂單資料庫（模擬後端資料，之後改成 API）
const mockOrders = [
  {
    orderId: "ORDER-20240301-001",
    date: "2024-03-01",
    status: "Completed",
    items: [
      { title: "被討厭的勇氣", price: 300, qty: 1 },
      { title: "原子習慣", price: 330, qty: 2 }
    ]
  },
  {
    orderId: "ORDER-20240305-002",
    date: "2024-03-05",
    status: "Paid",
    items: [
      { title: "原子習慣", price: 300, qty: 1 }
    ]
  },
  {
    orderId: "ORDER-20240308-003",
    date: "2024-03-08",
    status: "Pending",
    items: [
      { title: "被討厭的勇氣", price: 280, qty: 1 },
      { title: "人生勝利聖經", price: 170, qty: 1 }
    ]
  }
]

// 當前訂單
const order = ref({
  orderId: "",
  date: "",
  status: "",
  items: []
})

// 動態計算總金額
const orderTotal = computed(() => {
  return order.value.items.reduce((sum, item) => sum + item.price * item.qty, 0)
})

onMounted(() => {
  const orderId = route.params.orderId

  // 根據 orderId 查找對應的訂單
  const foundOrder = mockOrders.find(o => o.orderId === orderId)
  
  if (foundOrder) {
    order.value = { ...foundOrder }
  } else {
    // 訂單不存在，保持空狀態
    console.warn(`訂單 ${orderId} 不存在`)
  }
})

function goBack() {
  router.push("/orders")
}

function statusType(status) {
  if (status === "Completed") return "success"
  if (status === "Paid") return "warning"
  if (status === "Pending") return "info"
  return ""
}
</script>

<style scoped>
.order-detail-page {
  padding: 40px 16px;
}

.order-detail-container {
  max-width: 1000px;
  margin: 0 auto;
  background: #fff;
  padding: 24px;
  border-radius: 8px;
}

.title {
  font-size: 22px;
  font-weight: 600;
  margin-bottom: 16px;
}

.order-info p {
  margin: 6px 0;
}

.actions {
  margin-top: 24px;
}

.not-found {
  text-align: center;
  padding: 60px 20px;
  color: #999;
}

.not-found p {
  font-size: 18px;
  margin-bottom: 20px;
}
</style>
