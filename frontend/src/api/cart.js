import axios from 'axios';

const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE || 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true, // 攜帶 Cookie (Sanctum 認證)
});

// 設置請求攔截器，自動附加 token
apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// 取得購物車
export function getCart() {
  return apiClient.get('/cart');
}

// 加入商品到購物車
export function addToCart(bookId, quantity = 1) {
  return apiClient.post('/cart/items', {
    book_id: bookId,
    quantity: quantity
  });
}

// 更新購物車商品數量
export function updateCartItem(cartItemId, quantity) {
  return apiClient.put(`/cart/items/${cartItemId}`, {
    quantity: quantity
  });
}

// 移除購物車商品
export function removeCartItem(cartItemId) {
  return apiClient.delete(`/cart/items/${cartItemId}`);
}

// 清空購物車
export function clearCart() {
  return apiClient.delete('/cart/clear');
}

export function checkout(data) {
  // data 格式預期: { payment_method: 'CreditCard', coupon_code: '...', ... }
  return apiClient.post('/cart/checkout', data);
}