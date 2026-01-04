import axios from 'axios';

// 建立一個直連後端的 axios 實例
const directClient = axios.create({
  baseURL: 'http://localhost:8000/api', // ✅ 統一管理後端網址
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// 1. 搜尋書籍 (參數正確)
export function searchBooks(keyword) {
  return directClient.get('/books/search', {
    params: { keyword: keyword }
  }).then(response => response.data);
}

// 2. 取得熱門書籍
export function getHotBooks() {
  return directClient.get('/books')
    .then(response => response.data);
}

// 3. 取得單一書籍詳情
export function getBook(id) {
  return directClient.get(`/books/${id}`)
    .then(response => response.data)
    .catch(error => {
      console.error(`Error fetching book ${id}:`, error);
      throw error;
    });
}

// 4. [優化] 新增書籍 (統一使用 directClient，保持程式碼整潔)
export const createBook = async (data) => {
  const token = localStorage.getItem('token') // 確保有 Token
  
  // 使用 directClient.post，它會自動拼上 baseURL
  return directClient.post('/books', data, {
    headers: {
      'Authorization': `Bearer ${token}`, // 補上 Token
      // Content-Type 已經在 directClient 預設有了，這裡不寫也行，寫了也沒錯
    }
  })
}

// 5. [新增] 賣家編輯書籍 (如果您之後要做編輯功能會用到)
export const updateBook = async (id, data) => {
  const token = localStorage.getItem('token')
  return directClient.put(`/books/${id}`, data, {
    headers: { 'Authorization': `Bearer ${token}` }
  })
}