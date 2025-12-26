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
            <el-radio value="CreditCard" size="large" border>信用卡付款</el-radio>
            <el-radio value="COD" size="large" border>貨到付款</el-radio>
            <el-radio value="Transfer" size="large" border>銀行轉帳</el-radio>
          </el-radio-group>
        </el-card>
      </div>

      <div class="summary-section">
        <el-card class="summary-card">
          <h3>訂單摘要</h3>
          <div class="order-items">
            <div v-for="item in mockCartItems" :key="item.id" class="order-item">
              <img :src="item.image" alt="book" class="item-img">
              <div class="item-info">
                <div class="item-name">{{ item.name }}</div>
                <div class="item-meta">x {{ item.quantity }}</div>
              </div>
              <div class="item-price">NT$ {{ item.price * item.quantity }}</div>
            </div>
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
                - NT$ {{ selectedCoupon.discount }}
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
          >
            選擇優惠券
          </el-button>

          <el-divider />

          <div class="price-row total">
            <span>總金額</span>
            <span class="total-price">NT$ {{ total }}</span>
          </div>

          <el-button type="primary" class="w-full mt-4" size="large" @click="submitOrder">
            確認下單
          </el-button>
        </el-card>
      </div>
    </div>

    <CouponDialog
      :visible="showCouponDialog"
      @close="showCouponDialog = false"
      @select="selectedCoupon = $event"
    />
  </div>
</template>

<script setup>
import { reactive, ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import CouponDialog from '@/components/CouponDialog.vue'

const router = useRouter()

// 1. 表單資料
const form = reactive({
  name: '',
  phone: '',
  address: '',
  payment: 'CreditCard'
})

// 2. 優惠券相關
const showCouponDialog = ref(false)
const selectedCoupon = ref(null)

const discount = computed(() => {
  return selectedCoupon.value ? selectedCoupon.value.discount : 0
})

// 3. 模擬購物車資料 (因為購物車功能還沒做，先用假資料撐場面)
const mockCartItems = ref([
  { id: 1, name: '被討厭的勇氣', price: 300, quantity: 1, image: 'https://via.placeholder.com/60' },
  { id: 2, name: '原子習慣', price: 330, quantity: 2, image: 'https://via.placeholder.com/60' }
])

// 4. 計算金額
const subtotal = computed(() => {
  return mockCartItems.value.reduce((sum, item) => sum + (item.price * item.quantity), 0)
})
const shippingFee = 60
const total = computed(() =>
  subtotal.value + shippingFee - discount.value
)

// 5. 送出訂單
const submitOrder = () => {
  if (!form.name || !form.phone || !form.address) {
    alert('請填寫完整收件資訊')
    return
  }
  
  // 這裡之後會呼叫後端 API (/api/orders)
  console.log('訂單送出:', {
    user: form,
    items: mockCartItems.value,
    total: total.value
  })

  alert('訂單已成立！將跳轉回首頁')
  router.push('/')
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
}
.item-img {
  width: 50px;
  height: 50px;
  object-fit: cover;
  border-radius: 4px;
  margin-right: 10px;
}
.item-info { flex: 1; }
.item-name { font-size: 14px; font-weight: bold; }
.item-meta { font-size: 12px; color: #666; }
.item-price { font-weight: bold; }

.price-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
  color: #555;
}
.price-row.total {
  font-size: 18px;
  color: #000;
  font-weight: bold;
  margin-top: 10px;
}
.total-price { color: #f56c6c; }

@media (max-width: 768px) {
  .checkout-layout { grid-template-columns: 1fr; }
}
</style>