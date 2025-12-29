import axios from 'axios';

const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE || 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true,
});

// 註冊
export function register(userData) {
  return apiClient.post('/register', userData);
}

// 登入 (前台用戶：會員/廠商)
export function login(credentials) {
  return apiClient.post('/login', credentials);
}

// 登出
export function logout() {
  const token = localStorage.getItem('token');
  return apiClient.post('/logout', {}, {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
}

// 取得用戶資料
export function getProfile() {
  const token = localStorage.getItem('token');
  return apiClient.get('/profile', {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
}

// 更新用戶資料
export function updateProfile(userData) {
  const token = localStorage.getItem('token');
  return apiClient.put('/profile', userData, {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
}
