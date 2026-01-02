import axios from 'axios';

const apiClient = axios.create({ baseURL: '/api' });

apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

// 取得某本書的評論
export function getBookReviews(bookId, page = 1) {
  return apiClient.get(`/books/${bookId}/reviews?page=${page}`)
    .then(res => res.data);
}

// 提交評論
export function submitReview(data) {
  return apiClient.post('/reviews', data)
    .then(res => res.data);
}