import axios from 'axios';

const apiClient = axios.create({
  baseURL: '/api',
});

// 自動帶入 Token
apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// 取得個人資料
export function getUserProfile() {
  return apiClient.get('/user/profile')
    .then(res => res.data);
}

// 更新個人資料
export function updateUserProfile(data) {
  return apiClient.put('/user/profile', data)
    .then(res => res.data);
}

export function changePassword(data) {
  return apiClient.put('/user/password', data)
    .then(res => res.data);
}