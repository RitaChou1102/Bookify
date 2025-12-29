import axios from 'axios';

// 建立實例
const service = axios.create({
  baseURL: '/api', // 透過 Proxy 轉發，所以不用寫 http://localhost:8000
  timeout: 5000    // 請求超時時間
});

// 請求攔截器 (Request Interceptor) - 之後做登入 Token 會用到
service.interceptors.request.use(
  config => {
    // 例如：config.headers['Authorization'] = 'Bearer ' + token;
    return config;
  },
  error => {
    return Promise.reject(error);
  }
);

// 回應攔截器 (Response Interceptor) - 統一處理錯誤
service.interceptors.response.use(
  response => {
    return response.data; // 直接回傳 data，省去在頁面多寫 .data
  },
  error => {
    console.error('API Error:', error); // 可以在這裡跳出錯誤提示視窗
    return Promise.reject(error);
  }
);

export default service;