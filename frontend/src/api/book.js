import axios from 'axios';

// 建立一個 axios 實體，設定基礎網址
const apiClient = axios.create({
  // 讀取 docker-compose 或 .env 設定的環境變數
  baseURL: import.meta.env.VITE_API_BASE || 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  // 如果需要攜帶 Cookie (Sanctum 認證)，請開啟此選項
  // withCredentials: true, 
});

// 取得熱門書籍 (對應後端 GET /api/books)
export function getHotBooks() {
  // 這裡假設你的後端路由是 /api/books
  // 如果你的 BookController 回傳格式是 { data: [...] }，axios 會包在 response.data 裡
  return apiClient.get('/books')
    .then(response => {
       // 根據你的後端回傳結構回傳資料
       // 假設 Laravel Resource 回傳的是 { data: [...] }
       // axios 的 response 結構是 { data: { data: [...] }, status: 200, ... }
       return response.data; 
    })
    .catch(error => {
      console.error('API Error:', error);
      throw error;
    });
}