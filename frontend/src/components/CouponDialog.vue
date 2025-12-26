<template>
  <el-dialog
    title="選擇優惠券"
    :model-value="visible"
    width="400px"
    @close="close"
  >
    <div class="coupon-list">
      <el-card
        v-for="coupon in coupons"
        :key="coupon.id"
        class="coupon-item"
        shadow="hover"
      >
        <div class="coupon-info">
          <div class="coupon-title">{{ coupon.title }}</div>
          <div class="coupon-desc">{{ coupon.desc }}</div>
        </div>

        <el-button
          type="primary"
          size="small"
          @click="selectCoupon(coupon)"
        >
          套用
        </el-button>
      </el-card>
    </div>

    <template #footer>
      <el-button @click="close">取消</el-button>
    </template>
  </el-dialog>
</template>

<script setup>
const props = defineProps({
  visible: Boolean
})

const emit = defineEmits(['close', 'select'])

const coupons = [
  { id: 1, title: '滿 500 折 100', discount: 100, desc: '消費滿 NT$500 可折抵 NT$100' },
  { id: 2, title: '新會員折 50', discount: 50, desc: '新會員專屬優惠' },
  { id: 3, title: '滿 1000 折 200', discount: 200, desc: '高額訂單優惠' }
]

function selectCoupon(coupon) {
  emit('select', coupon)
  emit('close')
}

function close() {
  emit('close')
}
</script>

<style scoped>
.coupon-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.coupon-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.coupon-title {
  font-weight: bold;
}

.coupon-desc {
  font-size: 12px;
  color: #666;
}
</style>
