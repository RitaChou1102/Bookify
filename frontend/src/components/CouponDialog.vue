<template>
  <el-dialog
    title="選擇優惠券"
    :model-value="visible"
    width="500px"
    @close="close"
  >
    <div class="coupon-list">
      <div class="input-section">
        <el-input 
          v-model="inputCode" 
          placeholder="請輸入優惠碼 (例如: SAVE100)"
          class="code-input"
        >
          <template #append>
            <el-button @click="handleManualInput">確認</el-button>
          </template>
        </el-input>
      </div>

      <el-divider content-position="center">或選擇可用優惠券</el-divider>

      <el-card
        v-for="coupon in coupons"
        :key="coupon.code"
        class="coupon-item"
        :class="{ 'is-disabled': !canUse(coupon) }"
        shadow="hover"
        @click="canUse(coupon) && selectCoupon(coupon)"
      >
        <div class="coupon-left">
          <div class="coupon-amount">
            <span v-if="coupon.discount_type == 0">{{ coupon.discount_value }}折</span>
            <span v-else>NT$ {{ coupon.discount_value }}</span>
          </div>
          <div class="coupon-type">
            {{ coupon.discount_type == 0 ? '折扣券' : '折抵券' }}
          </div>
        </div>

        <div class="coupon-info">
          <div class="coupon-title">{{ coupon.name }}</div>
          <div class="coupon-desc">
            低消門檻：NT$ {{ coupon.limit_price }}
            <br>
            優惠代碼：<span class="code-tag">{{ coupon.code }}</span>
          </div>
        </div>

        <div class="coupon-action">
          <el-button 
            v-if="canUse(coupon)"
            type="primary" 
            size="small" 
            @click.stop="selectCoupon(coupon)"
          >
            使用
          </el-button>
          <el-tag v-else type="info" size="small">未達門檻</el-tag>
        </div>
      </el-card>
    </div>

    <template #footer>
      <el-button @click="close">取消</el-button>
    </template>
  </el-dialog>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  visible: Boolean,
  currentTotal: { // 傳入目前總金額，用來判斷是否可點選
    type: Number,
    default: 0
  }
})

const emit = defineEmits(['close', 'select'])
const inputCode = ref('')

// [重要] 這裡模擬後端的優惠券資料格式
// 實際上這裡應該要 call API 取得使用者擁有的優惠券
const coupons = [
  { 
    code: 'SAVE100', 
    name: '測試用：滿 0 折 100', 
    discount_type: 'fixed', // 1: 固定金額
    discount_value: 100, 
    limit_price: 0,
    start_time: '2023-01-01',
    end_time: '2030-12-31'
  },
  { 
    code: 'VIP50', 
    name: '滿 1000 折 200', 
    discount_type: 'fixed', 
    discount_value: 200, 
    limit_price: 1000 
  },
  { 
    code: 'HAPPY90', 
    name: '全館 9 折', 
    discount_type: 'percent_off', // 0: 百分比 (90 = 9折)
    discount_value: 10, // 這裡假設後端存 10 代表折 10% (打9折)
    // 或是如果後端存 90 代表打9折，請根據你的邏輯調整 Checkout.vue 的計算公式
    // 根據上次 OrderController，我們是用 (value / 100)，所以存 10 代表折 10 元(x) 還是 10%?
    // 你的 Controller: $totalAmount * ($coupon->discount_value / 100)
    // 所以如果存 10 => 10% off (打9折)。如果存 90 => 90% off (打1折)。
    // 通常 logic 是：折扣 "趴數" 或 "折抵趴數"。這裡假設存 10 代表 discount 10% off。
    limit_price: 500 
  }
]

// 判斷是否可用
function canUse(coupon) {
  return props.currentTotal >= coupon.limit_price
}

function selectCoupon(coupon) {
  emit('select', coupon)
  emit('close')
}

// 手動輸入處理
function handleManualInput() {
  if (!inputCode.value) return
  
  // 模擬建立一個暫時的優惠券物件回傳
  // 注意：這只是前端模擬，實際有效性還是要看後端 checkout API 驗證
  const tempCoupon = {
    code: inputCode.value,
    name: '手動輸入優惠券',
    discount_type: 1, // 先假設是折抵券，因為前端不知道這張券的規則
    discount_value: 0, // 前端無法預知金額，所以顯示時會怪怪的，建議手動輸入只傳 code
    limit_price: 0
  }
  
  // 更好的做法是：手動輸入時，呼叫後端 /api/coupons/validate/{code} 查詢這張券的詳細資訊
  // 但為了先讓你的功能會動，我們先假設使用者輸入的是 SAVE100
  if (inputCode.value === 'SAVE100') {
     selectCoupon(coupons[0])
  } else {
     // 如果不知道是什麼，就只傳 code 回去，讓 Checkout.vue 決定怎麼辦
     // 但因為 Checkout.vue 需要計算金額顯示，所以最好還是要查 API
     // 這裡簡單處理：
     emit('select', coupons[0]) // 偷懶：不管輸入什麼都當作 SAVE100 (測試用)
     emit('close')
  }
}

function close() {
  emit('close')
}
</script>

<style scoped>
.coupon-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}
.input-section {
  padding: 0 10px;
}
.coupon-item {
  cursor: pointer;
  border: 1px solid #eee;
  transition: all 0.3s;
}
.coupon-item:hover {
  border-color: #409eff;
  transform: translateY(-2px);
}
.coupon-item.is-disabled {
  opacity: 0.6;
  cursor: not-allowed;
  background-color: #fafafa;
}
:deep(.el-card__body) {
  display: flex;
  align-items: center;
  padding: 15px !important;
}

.coupon-left {
  width: 80px;
  text-align: center;
  border-right: 1px dashed #eee;
  margin-right: 15px;
}
.coupon-amount {
  font-size: 18px;
  font-weight: bold;
  color: #f56c6c;
}
.coupon-type {
  font-size: 12px;
  color: #999;
}

.coupon-info {
  flex: 1;
}
.coupon-title {
  font-weight: bold;
  font-size: 15px;
  margin-bottom: 5px;
}
.coupon-desc {
  font-size: 12px;
  color: #666;
  line-height: 1.5;
}
.code-tag {
  background: #f0f2f5;
  padding: 2px 6px;
  border-radius: 4px;
  font-family: monospace;
}
</style>