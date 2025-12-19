<template>
  <div class="vendor-orders-container">
    <div class="page-header">
      <div class="title-section">
        <h1>廠商訂單管理</h1>
        <p>查看並處理您的銷售訂單</p>
      </div>
      <el-radio-group v-model="filterStatus" size="large">
        <el-radio-button label="all" value="all">全部</el-radio-button>
        <el-radio-button label="pending" value="pending">待出貨</el-radio-button>
        <el-radio-button label="shipped" value="shipped">已出貨</el-radio-button>
        <el-radio-button label="completed" value="completed">已完成</el-radio-button>
      </el-radio-group>
    </div>

    <el-card class="table-card">
      <el-table :data="filteredOrders" style="width: 100%" stripe border>
        <el-table-column type="expand">
          <template #default="props">
            <div class="order-detail">
              <h3>收件人資訊</h3>
              <p><strong>姓名：</strong> {{ props.row.customerName }}</p>
              <p><strong>電話：</strong> {{ props.row.customerPhone }}</p>
              <p><strong>地址：</strong> {{ props.row.customerAddress }}</p>
              
              <el-divider />
              
              <h3>購買商品</h3>
              <ul>
                <li v-for="item in props.row.items" :key="item.id">
                  {{ item.name }} x {{ item.quantity }} (單價: ${{ item.price }})
                </li>
              </ul>
            </div>
          </template>
        </el-table-column>

        <el-table-column prop="id" label="訂單編號" width="120" />
        <el-table-column prop="date" label="下單日期" width="180" />
        <el-table-column prop="total" label="訂單金額" width="120">
          <template #default="scope">
            NT$ {{ scope.row.total }}
          </template>
        </el-table-column>

        <el-table-column prop="status" label="狀態" width="120">
          <template #default="scope">
            <el-tag :type="getStatusType(scope.row.status)">
              {{ getStatusText(scope.row.status) }}
            </el-tag>
          </template>
        </el-table-column>

        <el-table-column label="操作" min-width="150">
          <template #default="scope">
            <el-button 
              v-if="scope.row.status === 'pending'"
              type="primary" 
              size="small" 
              @click="handleShip(scope.row)"
            >
              出貨
            </el-button>
            <span v-else class="text-gray">無可執行動作</span>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const filterStatus = ref('all')

// 模擬訂單資料 (未來從 API GET /api/vendor/orders 取得)
const orders = ref([
  {
    id: 'ORD-001',
    date: '2025-12-08 14:30',
    total: 630,
    status: 'pending', // 待出貨
    customerName: '王小明',
    customerPhone: '0912-345-678',
    customerAddress: '台北市信義區信義路五段7號',
    items: [
      { id: 1, name: '被討厭的勇氣', price: 300, quantity: 1 },
      { id: 2, name: '原子習慣', price: 330, quantity: 1 }
    ]
  },
  {
    id: 'ORD-002',
    date: '2025-12-07 09:15',
    total: 350,
    status: 'shipped', // 已出貨
    customerName: '陳大文',
    customerPhone: '0988-777-666',
    customerAddress: '台中市西屯區台灣大道三段',
    items: [
      { id: 3, name: '底層邏輯', price: 350, quantity: 1 }
    ]
  },
  {
    id: 'ORD-003',
    date: '2025-12-06 18:20',
    total: 1200,
    status: 'completed', // 已完成
    customerName: '李雅婷',
    customerPhone: '0955-444-333',
    customerAddress: '高雄市左營區博愛二路',
    items: [
      { id: 1, name: '被討厭的勇氣', price: 300, quantity: 4 }
    ]
  }
])

// 根據篩選器顯示訂單
const filteredOrders = computed(() => {
  if (filterStatus.value === 'all') {
    return orders.value
  }
  return orders.value.filter(order => order.status === filterStatus.value)
})

// 狀態顯示輔助函式
const getStatusType = (status) => {
  const map = {
    pending: 'warning',
    shipped: 'primary',
    completed: 'success',
    cancelled: 'info'
  }
  return map[status] || 'info'
}

const getStatusText = (status) => {
  const map = {
    pending: '待出貨',
    shipped: '已出貨',
    completed: '已完成',
    cancelled: '已取消'
  }
  return map[status] || status
}

// 處理出貨動作
const handleShip = (order) => {
  if (confirm(`確定要將訂單 ${order.id} 標記為已出貨嗎？`)) {
    // 呼叫 API 更新狀態 (POST /api/vendor/orders/ship)
    console.log('出貨訂單:', order.id)
    order.status = 'shipped'
    alert('訂單已更新為「已出貨」！')
  }
}
</script>

<style scoped>
.vendor-orders-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 40px 20px;
}
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  flex-wrap: wrap;
  gap: 20px;
}
.title-section h1 { margin: 0; color: #333; }
.title-section p { color: #666; margin: 5px 0 0; }
.table-card { border-radius: 8px; }
.order-detail { padding: 10px 20px; background-color: #f9fafb; border-radius: 4px; }
.order-detail p { margin: 5px 0; color: #555; }
.text-gray { color: #999; font-size: 0.9rem; }
</style>