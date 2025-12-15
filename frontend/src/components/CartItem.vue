<template>
  <div class="cart-item">
    <!-- 左側：書籍基本資訊 -->
    <div class="item-info">
      <div class="item-name">{{ item.name }}</div>
      <div class="item-author">{{ item.author }}</div>
    </div>

    <!-- 右側：價格（之後可擴充 qty / 小計 / 刪除） -->
    <div class="item-price">NT$ {{ item.price }}</div>
  </div>
</template>

<script setup>
// CartItem 是「展示型元件」，只負責顯示資料
// 不在這裡處理商業邏輯（計算、狀態）

defineProps({
  item: {
    type: Object,
    required: true
  }
})
</script>

<style scoped>
.cart-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 0;
  border-bottom: 1px solid #eee;
}

.cart-item:last-child {
  border-bottom: none;
}

.item-name {
  font-size: 16px;
  font-weight: 500;
}

.item-author {
  margin-top: 4px;
  font-size: 14px;
  color: #666;
}

.item-price {
  font-weight: 500;
}
</style>
