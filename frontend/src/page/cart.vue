<template>
<div class="cart-page">
    <div class="cart-container">
        <h1 class="title">購物車</h1>

        <div class="cart-layout">
        <!-- 左欄：書籍列表 -->
        <div class="cart-left">
            <div
            class="cart-item"
            v-for="item in cartItems"
            :key="item.id"
            >
            <div class="item-info">
                <div class="item-name">{{ item.name }}</div>
                <div class="item-author">{{ item.author }}</div>
            </div>
            <div class="item-price">NT$ {{ item.price }}</div>
            </div>
        </div>

        <!-- 右欄：結帳資訊 -->
        <div class="cart-right">
            <p class="checkout-title">結帳資訊</p>

            <p>商品總數：{{ cartStore.totalCount }}</p>
            <p>商品小計：NT$ {{ cartStore.totalPrice }}</p>

            <el-button
                type="primary"
                class="checkout-btn"
                @click="goCheckout"
            >
                前往結帳
            </el-button>
        </div>
        </div>
    </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '@/store/cartStore'

const router = useRouter()
const cartStore = useCartStore()

function goCheckout() {
  router.push('/checkout')
}

const cartItems = ref([
    {
    id: 1,
    name: '被討厭的勇氣',
    author: '岸見一郎',
    price: 300,
    qty: 1
    },
    {
    id: 2,
    name: '原子習慣',
    author: 'James Clear',
    price: 330,
    qty: 1
    },
    {
    id: 3,
    name: '底層邏輯',
    author: '劉潤',
    price: 350,
    qty: 1
    },
    {
    id: 4,
    name: '蛤蟆先生去看心理師',
    author: 'Robert de Board',
    price: 280,
    qty: 1
    }
])
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

.cart-right {
    flex: 1;
    border: 1px solid #e5e5e5;
    border-radius: 6px;
    padding: 16px;
}

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
    color: #666;
    font-size: 14px;
}

.item-price {
    font-weight: 500;
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
