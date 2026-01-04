<template>
  <div class="orders-page">
    <div class="orders-container">
      <h1 class="title">我的訂單</h1>

      <div v-if="loading" class="loading">載入中...</div>

      <el-table v-else-if="orders.length > 0" :data="orders" style="width: 100%">
        <el-table-column prop="order_id" label="訂單編號" width="200">
           <template #default="scope">#{{ scope.row.order_id }}</template>
        </el-table-column>
        
        <el-table-column prop="order_time" label="下單日期" width="180" />
        
        <el-table-column prop="total_amount" label="總金額" width="120">
          <template #default="scope">
            NT$ {{ Math.floor(scope.row.total_amount) }}
          </template>
        </el-table-column>
        
        <el-table-column prop="order_status" label="狀態" width="140">
          <template #default="scope">
            <el-tag :type="statusType(scope.row.order_status)">
              {{ statusText(scope.row.order_status) }}
            </el-tag>
          </template>
        </el-table-column>
        
        <el-table-column label="操作">
          <template #default="scope">
            <el-button
              type="primary"
              size="small"
              @click="viewOrder(scope.row.order_id)"
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
import { ref, onMounted } from "vue"
import { useRouter } from "vue-router"
import { getOrders } from "@/api/order" // 引入 API

const router = useRouter()
const orders = ref([])
const loading = ref(true)

onMounted(async () => {
  try {
    const res = await getOrders();
    // 🔍 修正點：通常資料在 res.data 中，如果是 Laravel API 可能還有 res.data.data
    console.log("API 原始回傳:", res); // 先打開 F12 看看 console
    orders.value = res.data || res; 
  } catch (err) {
    console.error("載入訂單失敗", err);
  } finally {
    loading.value = false;
  }
});

function viewOrder(id) {
  // 注意：這裡使用 id 作為參數，路由請確保是 /orders/:id
  router.push(`/order/${id}`)
}

// 狀態顏色對應
function statusType(status) {
  const map = {
    'Received': 'info',      // 已接收
    'Processing': 'primary', // 處理中
    'Shipped': 'warning',    // 已出貨
    'Completed': 'success',  // 已完成
    'Cancelled': 'danger'    // 已取消
  }
  return map[status] || ''
}

// 狀態中文對應
function statusText(status) {
  const map = {
    'Received': '已接收',
    'Processing': '處理中',
    'Shipped': '已出貨',
    'Completed': '已完成',
    'Cancelled': '已取消'
  }
  return map[status] || status
}
</script>

<style scoped>
.orders-page { padding: 40px 16px; }
.orders-container { max-width: 1000px; margin: 0 auto; background: #fff; padding: 24px; border-radius: 8px; min-height: 50vh;}
.title { font-size: 22px; font-weight: 600; margin-bottom: 24px; }
.empty { margin-top: 40px; text-align: center; color: #888; }
.loading { text-align: center; padding: 20px; color: #666; }
</style>