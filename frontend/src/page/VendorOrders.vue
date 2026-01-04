<template>
  <div class="vendor-orders-container" v-loading="loading">
    <div class="page-header">
      <div class="title-section">
        <h1>廠商訂單管理</h1>
        <p>查看並處理您的銷售訂單</p>
      </div>
      <el-radio-group v-model="filterStatus" size="large">
        <el-radio-button label="all" value="all">全部</el-radio-button>
        <el-radio-button label="Received" value="Received">待出貨</el-radio-button>
        <el-radio-button label="Shipped" value="Shipped">已出貨</el-radio-button>
        <el-radio-button label="Completed" value="Completed">已完成</el-radio-button>
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
              
              <h3>購買商品 (本賣場)</h3>
              <ul>
                <li v-for="item in props.row.items" :key="item.detail_id">
                  {{ item.book?.name }} x {{ item.quantity }} (單價: NT$ {{ Math.floor(item.piece_price) }})
                </li>
              </ul>
            </div>
          </template>
        </el-table-column>

        <el-table-column prop="id" label="訂單編號" width="100" align="center">
            <template #default="scope">#{{ scope.row.id }}</template>
        </el-table-column>
        
        <el-table-column prop="date" label="下單日期" width="180">
             <template #default="scope">{{ formatDate(scope.row.date) }}</template>
        </el-table-column>

        <el-table-column prop="total" label="本單營收" width="120">
          <template #default="scope">
            <span class="price-text">NT$ {{ Math.floor(scope.row.total) }}</span>
          </template>
        </el-table-column>

        <el-table-column prop="status" label="狀態" width="120" align="center">
          <template #default="scope">
            <el-tag :type="getStatusType(scope.row.status)">
              {{ getStatusText(scope.row.status) }}
            </el-tag>
          </template>
        </el-table-column>

        <el-table-column label="操作" min-width="150">
          <template #default="scope">
            <el-button 
              v-if="scope.row.status === 'Received'"
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

    <div v-if="!loading && orders.length === 0" class="empty-state">
        <el-empty description="目前沒有訂單資料" />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { ElMessage, ElMessageBox } from 'element-plus'

const filterStatus = ref('all')
const orders = ref([])
const loading = ref(false)

// 1. 從後端撈取資料
const fetchOrders = async () => {
  loading.value = true
  try {
    const token = localStorage.getItem('token')
    // 呼叫我們之前寫好的 sellerSales API
    const res = await axios.get('http://localhost:8000/api/vendor/orders', {
      headers: { 'Authorization': `Bearer ${token}` }
    })
    
    // 轉換資料結構
    orders.value = transformData(res.data)

  } catch (err) {
    console.error(err)
    ElMessage.error('無法載入訂單資料')
  } finally {
    loading.value = false
  }
}

// 2. [關鍵] 資料轉換函式：把扁平的銷售紀錄 Group 成訂單
const transformData = (flatSales) => {
  const groups = {}

  flatSales.forEach(sale => {
    const orderId = sale.order_id
    
    // 如果這張訂單還沒被建立過，就初始化
    if (!groups[orderId]) {
      groups[orderId] = {
        id: orderId,
        date: sale.order?.order_time,
        status: sale.order?.order_status,
        customerName: sale.order?.member?.name || sale.order?.recipient_name || '未知',
        customerPhone: sale.order?.member?.phone || sale.order?.recipient_phone || '無',
        customerAddress: sale.order?.recipient_address || '無',
        items: [],
        total: 0 // 計算屬於"這個賣家"的總金額
      }
    }

    // 把書本加入這張訂單的 items 列表
    groups[orderId].items.push(sale)
    // 累加金額
    groups[orderId].total += (Number(sale.piece_price) * sale.quantity)
  })

  // 把物件轉回陣列 (Array) 並依照時間排序
  return Object.values(groups).sort((a, b) => new Date(b.date) - new Date(a.date))
}

// 3. 處理出貨
const handleShip = (order) => {
  ElMessageBox.confirm(
    `確定要將訂單 #${order.id} 標記為已出貨嗎？`,
    '出貨確認',
    { confirmButtonText: '確定出貨', cancelButtonText: '取消', type: 'warning' }
  ).then(async () => {
    try {
      const token = localStorage.getItem('token')
      await axios.put(`http://localhost:8000/api/orders/${order.id}/status`, 
        { status: 'Shipped' },
        { headers: { 'Authorization': `Bearer ${token}` } }
      )
      
      ElMessage.success('訂單已更新為「已出貨」！')
      order.status = 'Shipped' // 前端即時更新顯示
    } catch (err) {
      console.error(err)
      ElMessage.error('更新失敗')
    }
  }).catch(() => {})
}

// 4. 篩選邏輯
const filteredOrders = computed(() => {
  if (filterStatus.value === 'all') {
    return orders.value
  }
  return orders.value.filter(order => order.status === filterStatus.value)
})

// 工具函式
const formatDate = (d) => d ? new Date(d).toLocaleString() : '-'
const getStatusType = (s) => {
  const map = { 'Received': 'warning', 'Processing': 'primary', 'Shipped': 'success', 'Completed': 'success', 'Cancelled': 'info' }
  return map[s] || 'info'
}
const getStatusText = (s) => {
  const map = { 'Received': '待出貨', 'Processing': '處理中', 'Shipped': '已出貨', 'Completed': '已完成', 'Cancelled': '已取消' }
  return map[s] || s
}

onMounted(() => {
  fetchOrders()
})
</script>

<style scoped>
.vendor-orders-container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 20px; }
.title-section h1 { margin: 0; color: #333; }
.title-section p { color: #666; margin: 5px 0 0; }
.table-card { border-radius: 8px; }
.order-detail { padding: 15px 25px; background-color: #f8f9fa; border-radius: 4px; border-left: 4px solid #409EFF; }
.order-detail p { margin: 8px 0; color: #555; font-size: 14px; }
.order-detail h3 { margin-top: 0; color: #303133; font-size: 16px; }
.price-text { color: #f56c6c; font-weight: bold; }
.text-gray { color: #999; font-size: 0.9rem; }
.empty-state { text-align: center; margin-top: 50px; }
</style>