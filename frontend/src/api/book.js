import axios from 'axios';

// 建立一個直連後端的 axios 實例 (備用)
const directClient = axios.create({
  baseURL: 'http://localhost:8000/api', // ✅ 強制直連後端 Port 8000
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

export function searchBooks(keyword) {
  return directClient.get('/books/search', {
    params: { keyword: keyword }
  }).then(response => response.data);
}

// ✅ [關鍵修改] 取得熱門書籍 (直連後端，解決首頁 500 錯誤)
export function getHotBooks() {
  return directClient.get('/books')
    .then(response => response.data);
}

// ✅ [關鍵修改] 新增書籍 (帶 Token 直連後端)
export const createBook = async (data) => {
  const token = localStorage.getItem('token')
  return axios.post('http://localhost:8000/api/books', data, {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    }
  })
}

// ✅ [關鍵修改] 取得單一書籍詳情 (直連後端)
export function getBook(id) {
  return directClient.get(`/books/${id}`)
    .then(response => response.data)
    .catch(error => {
      console.error(`Error fetching book ${id}:`, error);
      throw error;
    });
}