import axios from 'axios';

const apiClient = axios.create({
  baseURL: '/api', // 確保這裡是指向後端 API 的基礎路徑
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  // 如果你需要 Sanctum Cookie 認證，可以打開這個
  // withCredentials: true, 
});

// [🔥修正重點] 補上這段攔截器，每次發送請求前自動帶上 Token
apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem('token'); // 從 localStorage 拿 Token
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
}, (error) => {
  return Promise.reject(error);
});

// 取得訂單列表
export function getOrders() {
  return apiClient.get('/orders')
    .then(response => response.data);
}

// 取得單一訂單詳情
export function getOrder(id) {
  return apiClient.get(`/orders/${id}`)
    .then(response => response.data);
}