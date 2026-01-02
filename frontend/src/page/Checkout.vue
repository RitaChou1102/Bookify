<template>
  <div class="checkout-container">
    <div class="checkout-layout">
      <div class="form-section">
        <h2>結帳確認</h2>
        
        <el-card class="mb-4">
          <template #header>
            <div class="card-header">
              <span>收件人資訊</span>
            </div>
          </template>
          <el-form :model="form" label-width="100px">
            <el-form-item label="收件人姓名">
              <el-input v-model="form.name" placeholder="請輸入真實姓名" />
            </el-form-item>
            <el-form-item label="聯絡電話">
              <el-input v-model="form.phone" placeholder="09xx-xxx-xxx" />
            </el-form-item>
            <el-form-item label="收件地址">
              <el-input v-model="form.address" placeholder="請輸入完整地址" type="textarea" />
            </el-form-item>
          </el-form>
        </el-card>

        <el-card>
          <template #header>
            <div class="card-header">
              <span>付款方式</span>
            </div>
          </template>
          <el-radio-group v-model="form.payment">
            <el-radio value="Credit_card" size="large" border>信用卡付款</el-radio>
            <el-radio value="Cash" size="large" border>貨到付款</el-radio>
            <el-radio value="Bank_transfer" size="large" border>銀行轉帳</el-radio>
          </el-radio-group>
        </el-card>
      </div>

      <div class="summary-section">
        <el-card class="summary-card" v-loading="loading">
          <h3>訂單摘要</h3>
          
          <div v-if="cartItems.length > 0" class="order-items">
            <div v-for="item in cartItems" :key="item.id" class="order-item">
              <img :src="item.book.cover_image?.image_url || 'https://via.placeholder.com/60'" 
                   class="item-img" alt="book">
              
              <div class="item-info">
                <div class="item-name">{{ item.book.name }}</div>
                <div class="item-meta">x {{ item.quantity }}</div>
              </div>
              <div class="item-price">NT$ {{ Math.floor(item.book.price * item.quantity) }}</div>
            </div>
          </div>
          <div v-else class="empty-cart-msg">
            購物車是空的
          </div>

          <el-divider />

          <div class="price-row">
            <span>商品小計</span>
            <span>NT$ {{ subtotal }}</span>
          </div>
          <div class="price-row">
            <span>運費</span>
            <span>NT$ {{ shippingFee }}</span>
          </div>
          <div class="price-row">
            <span>優惠券</span>
            <span>
              <template v-if="selectedCoupon">
                - NT$ {{ discountAmount }}
              </template>
              <template v-else>
                尚未套用
              </template>
            </span>
          </div>

          <el-button
            text
            type="primary"
            @click="showCouponDialog = true"
            :disabled="cartItems.length === 0"
          >
            選擇優惠券
          </el-button>

          <el-divider />

          <div class="price-row total">
            <span>總金額</span>
            <span class="total-price">NT$ {{ finalTotal }}</span>
          </div>

          <el-button 
            type="primary" 
            class="w-full mt-4" 
            size="large" 
            @click="submitOrder"
            :loading="submitting"
            :disabled="cartItems.length === 0"
          >
            確認下單
          </el-button>
        </el-card>
      </div>
    </div>

    <CouponDialog
      :visible="showCouponDialog"
      :current-total="subtotal"  @close="showCouponDialog = false"
      @select="handleCouponSelect"
    />
  </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import CouponDialog from '@/components/CouponDialog.vue'
import { getCart, checkout } from '@/api/cart' // 引入 API

const router = useRouter()
const loading = ref(true)      // 載入購物車狀態
const submitting = ref(false)  // 送出訂單狀態

// 1. 表單資料
const form = reactive({
  name: '',
  phone: '',
  address: '',
  payment: 'CreditCard'
})

// 2. 購物車資料
const cartItems = ref([])
const shippingFee = 60 // 固定運費

// 3. 優惠券
const showCouponDialog = ref(false)
const selectedCoupon = ref(null)

// 4. 計算金額邏輯
const subtotal = computed(() => {
  if (!cartItems.value) return 0
  return cartItems.value.reduce((sum, item) => {
    return sum + (Number(item.book.price) * item.quantity)
  }, 0)
})

// 計算折扣金額
const discountAmount = computed(() => {
  if (!selectedCoupon.value) return 0
  const coupon = selectedCoupon.value
  
  // 檢查門檻
  if (subtotal.value < coupon.limit_price) {
    return 0
  }

  // 計算折扣 (0: 百分比, 1: 定額)
  if (coupon.discount_type === 'percent_off' || coupon.discount_type == 0) {
    // 百分比折扣 (例如 10 代表 10% off)
    return Math.floor(subtotal.value * (coupon.discount_value / 100))
  } else {
    // 定額折扣 (fixed)
    return Number(coupon.discount_value)
  }
})

const finalTotal = computed(() => {
  const total = subtotal.value + shippingFee - discountAmount.value
  return total > 0 ? total : 0
})

// 5. 初始化：撈取購物車
onMounted(async () => {
  try {
    const res = await getCart()
    // 假設後端回傳結構為 res.data 或 res.data.items
    // 根據 Laravel Resource 常見格式，通常在 res.data.items
    cartItems.value = res.data.items || [] 
    
    // 如果購物車是空的，提示使用者
    if (cartItems.value.length === 0) {
      ElMessage.warning('購物車是空的，請先選購商品')
      router.push('/')
    }
  } catch (err) {
    console.error('載入購物車失敗', err)
    ElMessage.error('無法載入購物車資訊')
  } finally {
    loading.value = false
  }
})

// 處理優惠券選擇
function handleCouponSelect(coupon) {
  // 簡單前端驗證門檻
  if (subtotal.value < coupon.limit_price) {
    ElMessage.warning(`未滿 ${coupon.limit_price} 元無法使用此優惠券`)
    return
  }
  selectedCoupon.value = coupon
  showCouponDialog.value = false
  ElMessage.success('已套用優惠券')
}

// 6. 送出訂單
const submitOrder = async () => {
  // 基本驗證
  if (!form.name || !form.phone || !form.address) {
    ElMessage.warning('請填寫完整的收件人資訊')
    return
  }

  submitting.value = true

  try {
    // 準備 API 需要的資料
    const payload = {
      payment_method: form.payment,
      coupon_code: selectedCoupon.value ? selectedCoupon.value.code : null,
      // 雖然目前的 OrderController 可能沒存地址，但建議還是送過去，以備未來擴充
      recipient_name: form.name,
      recipient_phone: form.phone,
      recipient_address: form.address,
    }

    const res = await checkout(payload)
    
    ElMessage.success('訂單建立成功！')
    
    // 跳轉到成功頁面，帶上訂單編號
    router.push({
      path: '/order/success',
      query: {
        orderId: res.data.order_id,
        total: finalTotal.value
      }
    })

  } catch (err) {
    console.error('結帳失敗', err)
    if (err.response?.status === 400) {
      ElMessage.error(err.response.data.message || '結帳失敗')
    } else {
      ElMessage.error('系統發生錯誤，請稍後再試')
    }
  } finally {
    submitting.value = false
  }
}
</script>

<style scoped>
.checkout-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 40px 20px;
}
.checkout-layout {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 30px;
}
.mb-4 { margin-bottom: 20px; }
.w-full { width: 100%; }
.mt-4 { margin-top: 20px; }

/* 訂單摘要樣式 */
.order-item {
  display: flex;
  align-items: center;
  margin-bottom: 15px;
  padding-bottom: 15px;
  border-bottom: 1px solid #eee;
}
.item-img {
  width: 60px;
  height: 80px;
  object-fit: cover;
  border-radius: 4px;
  margin-right: 15px;
  background-color: #f0f0f0;
}
.item-info { flex: 1; }
.item-name { 
  font-size: 14px; 
  font-weight: bold; 
  margin-bottom: 4px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.item-meta { font-size: 12px; color: #666; }
.item-price { font-weight: bold; color: #333; }

.price-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
  color: #555;
  font-size: 14px;
}
.price-row.total {
  font-size: 18px;
  color: #000;
  font-weight: bold;
  margin-top: 15px;
  padding-top: 15px;
  border-top: 2px solid #eee;
}
.total-price { color: #e67e22; }

.empty-cart-msg {
  text-align: center;
  color: #999;
  padding: 20px 0;
}

@media (max-width: 768px) {
  .checkout-layout { grid-template-columns: 1fr; }
}
</style>