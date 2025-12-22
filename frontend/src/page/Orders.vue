<template>
  <div class="orders-page">
    <div class="orders-container">
      <h1 class="title">我的訂單</h1>

      <el-table v-if="orders.length > 0" :data="orders" style="width: 100%">
        <el-table-column prop="orderId" label="訂單編號" width="200" />
        <el-table-column prop="date" label="下單日期" width="150" />
        <el-table-column prop="total" label="總金額" width="120">
          <template #default="scope">
            NT$ {{ scope.row.total }}
          </template>
        </el-table-column>
        <el-table-column prop="status" label="狀態" width="140">
          <template #default="scope">
            <el-tag
              :type="statusType(scope.row.status)"
            >
              {{ scope.row.status }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作">
          <template #default="scope">
            <el-button
              type="primary"
              size="small"
              @click="viewOrder(scope.row.orderId)"
            >
              查看詳情
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <div v-else class="empty">
        尚無訂單紀錄
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue"
import { useRouter } from "vue-router"

const router = useRouter()

// 假資料（之後可換成 API）
const orders = ref([
  {
    orderId: "ORDER-20240301-001",
    date: "2024-03-01",
    total: 980,
    status: "Completed"
  },
  {
    orderId: "ORDER-20240305-002",
    date: "2024-03-05",
    total: 300,
    status: "Paid"
  },
  {
    orderId: "ORDER-20240308-003",
    date: "2024-03-08",
    total: 450,
    status: "Pending"
  }
])

function viewOrder(orderId) {
  router.push({
    name: 'order-detail',
    params: { orderId }
  })
}

function statusType(status) {
  if (status === "Completed") return "success"
  if (status === "Paid") return "warning"
  if (status === "Pending") return "info"
  return ""
}
</script>

<style scoped>
.orders-page {
  padding: 40px 16px;
}

.orders-container {
  max-width: 1000px;
  margin: 0 auto;
  background: #fff;
  padding: 24px;
  border-radius: 8px;
}

.title {
  font-size: 22px;
  font-weight: 600;
  margin-bottom: 24px;
}

.empty {
  margin-top: 40px;
  text-align: center;
  color: #888;
}
</style>
