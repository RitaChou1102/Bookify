<template>
<div class="cart-page">
    <div class="cart-container">
        <h1 class="title">購物車</h1>

        <div class="cart-layout">
        <!-- 左欄：書籍列表 -->
        <div class="cart-left">
            <div v-if="loading" class="loading">載入中...</div>
            <div v-else-if="cartItems.length === 0" class="empty-cart">
                購物車是空的
            </div>
            <div
            v-else
            class="cart-item"
            v-for="item in cartItems"
            :key="item.cart_item_id"
            >
            <img :src="item.book?.cover_image?.image_url || '/placeholder.jpg'" class="item-image" />
            <div class="item-info">
                <div class="item-name">{{ item.book?.name }}</div>
                <div class="item-author">單價: NT$ {{ item.price }}</div>
                <div class="item-store">賣家: {{ item.book?.business?.store_name }}</div>
            </div>
            <div class="item-actions">
                <div class="quantity-control">
                    <el-button size="small" @click="updateQuantity(item, item.quantity - 1)">-</el-button>
                    <span class="quantity">{{ item.quantity }}</span>
                    <el-button size="small" @click="updateQuantity(item, item.quantity + 1)">+</el-button>
                </div>
                <div class="item-price">小計: NT$ {{ item.subtotal }}</div>
                <el-button 
                    type="danger" 
                    size="small" 
                    @click="removeItem(item.cart_item_id)"
                    link
                >
                    刪除
                </el-button>
            </div>
            </div>
        </div>

        <!-- 右欄：結帳資訊 -->
        <div class="cart-right">
            <p class="checkout-title">結帳資訊</p>

            <p>商品總數：{{ totalQuantity }}</p>
            <p>商品小計：NT$ {{ totalPrice.toFixed(2) }}</p>

            <el-button
                type="primary"
                class="checkout-btn"
                @click="goCheckout"
                :disabled="cartItems.length === 0"
            >
                前往結帳
            </el-button>
        </div>
        </div>
    </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { getCart, updateCartItem, removeCartItem } from '@/api/cart'
import { ElMessage } from 'element-plus'

const router = useRouter()
const loading = ref(false)
const cartItems = ref([])

// 計算總數量
const totalQuantity = computed(() => {
  return cartItems.value.reduce((sum, item) => sum + item.quantity, 0)
})

// 計算總價格
const totalPrice = computed(() => {
  return cartItems.value.reduce((sum, item) => sum + parseFloat(item.subtotal || 0), 0)
})

// 載入購物車資料
async function loadCart() {
  loading.value = true
  try {
    const res = await getCart()
    // 後端返回 res.data = { cart_id, items: [...], summary: {...} }
    cartItems.value = res.data?.items || []
    console.log('購物車資料:', cartItems.value)
  } catch (err) {
    console.error('載入購物車失敗:', err)
    if (err.response?.status === 401) {
      ElMessage.warning('請先登入')
      router.push('/login')
    } else {
      ElMessage.error('載入購物車失敗')
    }
  } finally {
    loading.value = false
  }
}

// 更新數量
async function updateQuantity(item, newQuantity) {
  if (newQuantity < 1) return
  
  try {
    await updateCartItem(item.cart_item_id, newQuantity)
    item.quantity = newQuantity
    ElMessage.success('已更新數量')
  } catch (err) {
    console.error('更新數量失敗:', err)
    ElMessage.error('更新數量失敗')
  }
}

// 移除商品
async function removeItem(cartItemId) {
  try {
    await removeCartItem(cartItemId)
    cartItems.value = cartItems.value.filter(item => item.cart_item_id !== cartItemId)
    ElMessage.success('已移除商品')
  } catch (err) {
    console.error('移除商品失敗:', err)
    ElMessage.error('移除商品失敗')
  }
}

function goCheckout() {
  router.push('/checkout')
}

onMounted(() => {
  loadCart()
})
</script>

<style scoped>
/* 頁面只負責留空間，背景由全站 dark layout 提供 */
.cart-page {
    padding: 40px 16px;
}

/* 中央白色卡片容器 */
.cart-container {
    max-width: 1200px;
    margin: 0 auto;
    background: #ffffff;
    border-radius: 8px;
    padding: 24px;
}

.title {
    font-size: 22px;
    font-weight: 600;
    margin-bottom: 24px;
    }

.cart-layout {
    display: flex;
    gap: 24px;
}

.cart-left {
    flex: 2;
}

.loading, .empty-cart {
    text-align: center;
    padding: 40px;
    color: #999;
}

.cart-right {
    flex: 1;
    border: 1px solid #e5e5e5;
    border-radius: 6px;
    padding: 16px;
    height: fit-content;
}

.cart-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 0;
    border-bottom: 1px solid #eee;
}

.cart-item:last-child {
    border-bottom: none;
}

.item-image {
    width: 80px;
    height: 100px;
    object-fit: cover;
    border-radius: 4px;
}

.item-info {
    flex: 1;
}

.item-name {
    font-size: 16px;
    font-weight: 500;
}

.item-author {
    margin-top: 4px;
    color: #666;
    font-size: 14px;
}

.item-actions {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 8px;
}

.quantity-control {
    display: flex;
    align-items: center;
    gap: 8px;
}

.quantity {
    min-width: 30px;
    text-align: center;
}

.item-price {
    font-weight: 500;
    color: #409eff;
}

.checkout-title {
    font-weight: 600;
    margin-bottom: 8px;
}

.checkout-hint {
    font-size: 14px;
    color: #888;
}
</style>
