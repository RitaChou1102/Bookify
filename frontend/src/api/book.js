import axios from 'axios';

const apiClient = axios.create({
  baseURL: '/api', // 確保這裡有逗號
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

export function searchBooks(keyword) {
  return apiClient.get('/books/search', {
    params: { keyword: keyword }
  }).then(response => response.data);
}

// 取得熱門書籍
export function getHotBooks() {
  return apiClient.get('/books')
    .then(response => response.data);
}

export function createBook(data) {
  return apiClient.post('/books', data)
    .then(res => res.data);
}

// --- [新增] 取得單一書籍詳情 ---
export function getBook(id) {
  return apiClient.get(`/books/${id}`)
    .then(response => {
        // 後端 BookController 回傳的是 response()->json($book)
        // Axios 會把它包在 response.data 裡面
        return response.data; 
    })
    .catch(error => {
      console.error(`Error fetching book ${id}:`, error);
      throw error;
    });
}