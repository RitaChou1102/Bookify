<template>
  <div class="order-detail-container">
    <div class="page-header">
      <el-button @click="$router.back()">
        <el-icon><ArrowLeft /></el-icon> 返回
      </el-button>
      <h2>訂單詳情 #{{ orderId }}</h2>
    </div>

    <el-card class="mb-4">
      <div class="info-row">
        <span><strong>下單日期：</strong> {{ order.date }}</span>
        <span>
          <strong>狀態：</strong> 
          <el-tag :type="getStatusType(order.status)">{{ getStatusText(order.status) }}</el-tag>
        </span>
        <span><strong>總金額：</strong> <span class="price">NT$ {{ order.total }}</span></span>
      </div>
      <div class="info-row mt-2">
        <span><strong>收件人：</strong> {{ order.customerName }}</span>
        <span><strong>地址：</strong> {{ order.customerAddress }}</span>
      </div>
    </el-card>

    <el-card>
      <h3>購買商品</h3>
      <el-table :data="order.items" style="width: 100%">
        <el-table-column label="商品資訊">
          <template #default="scope">
            <div class="item-info">
              <img :src="scope.row.image" class="item-thumb"/>
              <div>
                <div class="item-name">{{ scope.row.name }}</div>
                <div class="item-price">NT$ {{ scope.row.price }}</div>
              </div>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="quantity" label="數量" width="100" />
        <el-table-column label="小計" width="120">
          <template #default="scope">NT$ {{ scope.row.price * scope.row.quantity }}</template>
        </el-table-column>
        
        <el-table-column label="操作" width="150" v-if="order.status === 'completed'">
          <template #default="scope">
            <el-button 
              v-if="!scope.row.hasReviewed" 
              type="primary" 
              plain 
              size="small" 
              @click="openReviewDialog(scope.row)"
            >
              撰寫評價
            </el-button>
            <el-tag v-else type="success" size="small">已評價</el-tag>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <el-dialog v-model="reviewDialogVisible" title="商品評價" width="500px">
      <div v-if="currentItem">
        <p class="mb-2">您正在評價：<strong>{{ currentItem.name }}</strong></p>
        
        <el-form>
          <el-form-item label="評分">
            <el-rate v-model="reviewForm.rating" show-text />
          </el-form-item>
          <el-form-item label="心得">
            <el-input 
              v-model="reviewForm.comment" 
              type="textarea" 
              rows="4" 
              placeholder="這本書讀起來如何？分享您的心得..." 
            />
          </el-form-item>
        </el-form>
      </div>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="reviewDialogVisible = false">取消</el-button>
          <el-button type="primary" @click="submitReview">送出評價</el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { ArrowLeft } from '@element-plus/icons-vue'

const route = useRoute()
const orderId = route.params.id

// 模擬訂單資料 (包含 hasReviewed 欄位判斷是否已評價)
const order = ref({
  id: 'ORD-003',
  date: '2025-12-06',
  total: 1200,
  status: 'completed',
  customerName: '王小明',
  customerAddress: '台北市信義區...',
  items: [
    { id: 1, name: '被討厭的勇氣', price: 300, quantity: 4, image: 'https://via.placeholder.com/50', hasReviewed: false },
    { id: 5, name: 'Clean Code', price: 500, quantity: 1, image: 'https://via.placeholder.com/50', hasReviewed: true }
  ]
})

// 評價相關變數
const reviewDialogVisible = ref(false)
const currentItem = ref(null)
const reviewForm = reactive({
  rating: 5,
  comment: ''
})

const getStatusType = (status) => status === 'completed' ? 'success' : (status === 'shipped' ? 'primary' : 'warning')
const getStatusText = (status) => ({ completed: '已完成', shipped: '已出貨', pending: '處理中' }[status] || status)

const openReviewDialog = (item) => {
  currentItem.value = item
  reviewForm.rating = 5
  reviewForm.comment = ''
  reviewDialogVisible.value = true
}

const submitReview = () => {
  if (reviewForm.rating === 0) {
    alert('請給予評分！')
    return
  }

  // 呼叫 API: POST /api/reviews
  console.log('送出評價:', {
    bookId: currentItem.value.id,
    orderId: order.value.id,
    ...reviewForm
  })

  // 更新前端狀態 (標記為已評價)
  const item = order.value.items.find(i => i.id === currentItem.value.id)
  if(item) item.hasReviewed = true

  reviewDialogVisible.value = false
  alert('感謝您的評價！')
}
</script>

<style scoped>
.order-detail-container { max-width: 800px; margin: 40px auto; padding: 0 20px; }
.page-header { display: flex; align-items: center; gap: 20px; margin-bottom: 20px; }
.info-row { display: flex; gap: 20px; flex-wrap: wrap; color: #555; }
.price { color: #f56c6c; font-weight: bold; }
.item-info { display: flex; align-items: center; gap: 10px; }
.item-thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; }
.mb-2 { margin-bottom: 10px; }
.mt-2 { margin-top: 10px; }
.mb-4 { margin-bottom: 20px; }
</style>