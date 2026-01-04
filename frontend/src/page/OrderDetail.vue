<template>
  <div class="order-detail-container" v-loading="loading">
    <div class="page-header">
      <el-button @click="$router.back()">
        <el-icon><ArrowLeft /></el-icon> 返回
      </el-button>
      <h2 v-if="order">訂單詳情 #{{ order.order_id }}</h2>
    </div>

    <div v-if="order">
      <el-card class="mb-4">
        <div class="info-row">
          <span><strong>下單日期：</strong> {{ order.order_time }}</span>
          <span>
            <strong>狀態：</strong> 
            <el-tag :type="statusType(order.order_status)">
              {{ statusText(order.order_status) }}
            </el-tag>
          </span>
          <span><strong>總金額：</strong> <span class="price">NT$ {{ Math.floor(order.total_amount) }}</span></span>
        </div>
        <div class="info-row mt-2">
           <span><strong>付款方式：</strong> {{ order.payment_method }}</span>
        </div>
      </el-card>

      <el-card>
        <h3>購買商品</h3>
        <el-table :data="order.order_details" style="width: 100%">
          <el-table-column label="商品資訊">
            <template #default="scope">
              <div class="item-info">
                <img 
                  :src="scope.row.book?.cover_image?.image_url || 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?w=200'" 
                  class="item-thumb"
                  @error="(e) => e.target.src = 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?w=200'"
                />
                <div>
                  <div class="item-name">{{ scope.row.book.name }}</div>
                  <div class="item-price">NT$ {{ Math.floor(scope.row.piece_price) }}</div>
                </div>
              </div>
            </template>
          </el-table-column>
          <el-table-column prop="quantity" label="數量" width="100" />
          <el-table-column label="小計" width="120">
            <template #default="scope">
              NT$ {{ Math.floor(scope.row.piece_price * scope.row.quantity) }}
            </template>
          </el-table-column>

          <el-table-column label="操作" width="120" fixed="right">
            <template #default="scope">
              <el-button 
                v-if="order.order_status === 'Completed'" 
                size="small" 
                type="primary"
                plain
                @click="openReviewDialog(scope.row)"
              >
                評價
              </el-button>
              <el-tag v-else type="info" size="small">無法評價</el-tag>
            </template>
          </el-table-column>
        </el-table>
      </el-card>
    </div>
    
    <div v-else-if="!loading" class="empty-state">
      找不到訂單資料
    </div>

    <el-dialog v-model="reviewDialogVisible" title="撰寫評價" width="500px">
      <div v-if="currentItem" class="review-book-info">
        正在評論：<strong>{{ currentItem.book.name }}</strong>
      </div>
      <el-form :model="reviewForm" label-position="top">
        <el-form-item label="整體評分">
          <el-rate v-model="reviewForm.rating" show-text />
        </el-form-item>
        <el-form-item label="心得感想">
          <el-input 
            v-model="reviewForm.comment" 
            type="textarea" 
            rows="4" 
            placeholder="這本書讀起來如何？書況符合預期嗎？分享給其他讀者吧！"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="reviewDialogVisible = false">取消</el-button>
        <el-button type="primary" @click="submitReviewHandler" :loading="submitting">送出評價</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { ArrowLeft } from '@element-plus/icons-vue'
import { ElMessage } from 'element-plus'
import { getOrder } from '@/api/order'
// [新增] 引入評價 API
import { submitReview } from '@/api/review'

const route = useRoute()
const orderId = route.params.id
const order = ref(null)
const loading = ref(true)

// [新增] 評價相關變數
const reviewDialogVisible = ref(false)
const submitting = ref(false)
const currentItem = ref(null)
const reviewForm = reactive({
  rating: 5,
  comment: ''
})

onMounted(async () => {
  try {
    const data = await getOrder(orderId)
    order.value = data
  } catch (err) {
    console.error("無法載入訂單", err)
  } finally {
    loading.value = false
  }
})

// [新增] 打開評價視窗
function openReviewDialog(item) {
  currentItem.value = item
  // 重置表單
  reviewForm.rating = 5
  reviewForm.comment = ''
  reviewDialogVisible.value = true
}

// [新增] 送出評價邏輯
async function submitReviewHandler() {
  if (reviewForm.rating === 0) {
    return ElMessage.warning('請至少給予一顆星評分')
  }

  submitting.value = true
  try {
    await submitReview({
      book_id: currentItem.value.book.book_id,
      order_id: order.value.order_id,
      rating: reviewForm.rating,
      comment: reviewForm.comment
    })

    ElMessage.success('評價提交成功！')
    reviewDialogVisible.value = false
    
    // (選擇性) 這裡可以重新整理頁面，或者標記該按鈕為已評價
    // window.location.reload() 

  } catch (err) {
    console.error(err)
    // 顯示後端回傳的錯誤 (例如：已經評論過了)
    ElMessage.error(err.response?.data?.message || '評價提交失敗')
  } finally {
    submitting.value = false
  }
}

// 狀態對應函式
function statusType(status) {
  const map = { 'Received': 'info', 'Processing': 'primary', 'Shipped': 'warning', 'Completed': 'success', 'Cancelled': 'danger' }
  return map[status] || ''
}

function statusText(status) {
  const map = { 'Received': '已接收', 'Processing': '處理中', 'Shipped': '已出貨', 'Completed': '已完成', 'Cancelled': '已取消' }
  return map[status] || status
}
</script>

<style scoped>
.order-detail-container { max-width: 800px; margin: 40px auto; padding: 0 20px; min-height: 50vh; }
.page-header { display: flex; align-items: center; gap: 20px; margin-bottom: 20px; }
.info-row { display: flex; gap: 20px; flex-wrap: wrap; color: #555; align-items: center; }
.price { color: #f56c6c; font-weight: bold; font-size: 1.1em; }
.item-info { display: flex; align-items: center; gap: 10px; }
.item-thumb { width: 50px; height: 70px; object-fit: cover; border-radius: 4px; border: 1px solid #eee; }
.mb-4 { margin-bottom: 20px; }
.mt-2 { margin-top: 10px; }
.empty-state { text-align: center; margin-top: 50px; color: #888; }
.review-book-info { margin-bottom: 15px; font-size: 14px; color: #666; }
</style>