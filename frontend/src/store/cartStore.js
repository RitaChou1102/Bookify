import { defineStore } from "pinia"
import { ref, computed } from "vue"

export const useCartStore = defineStore("cart", () => {
  // 購物車商品列表
    const cartItems = ref([])

  // 新增商品到購物車
    function addToCart(book, qty = 1) {
    const existing = cartItems.value.find(item => item.id === book.id)

    if (existing) {
        existing.qty += qty
    } else {
        cartItems.value.push({
        id: book.id,
        title: book.title,
        price: book.price,
        image: book.image,
        qty
        })
    }
    }

  // 更新商品數量
    function updateQty(id, qty) {
    const item = cartItems.value.find(item => item.id === id)
    if (item && qty > 0) {
        item.qty = qty
    }
    }

  // 移除商品
    function removeFromCart(id) {
    cartItems.value = cartItems.value.filter(item => item.id !== id)
    }

  // 清空購物車
    function clearCart() {
    cartItems.value = []
    }

  // 商品總數
    const totalCount = computed(() =>
    cartItems.value.reduce((sum, item) => sum + item.qty, 0)
    )

  // 總金額
    const totalPrice = computed(() =>
    cartItems.value.reduce((sum, item) => sum + item.qty * item.price, 0)
    )

    return {
    cartItems,
    addToCart,
    updateQty,
    removeFromCart,
    clearCart,
    totalCount,
    totalPrice
    }
})