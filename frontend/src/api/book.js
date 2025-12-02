// 模擬 API 回傳熱門書籍資料
export function getHotBooks() {
  return new Promise((resolve) => {
    setTimeout(() => {
      resolve({
        data: [
          { 
            id: 1, 
            name: "被討厭的勇氣", 
            author: "岸見一郎", 
            price: 300, 
            image: "https://via.placeholder.com/150" 
          },
          { 
            id: 2, 
            name: "原子習慣", 
            author: "James Clear", 
            price: 330, 
            image: "https://via.placeholder.com/150" 
          },
          { 
            id: 3, 
            name: "底層邏輯", 
            author: "劉潤", 
            price: 350, 
            image: "https://via.placeholder.com/150" 
          },
          { 
            id: 4, 
            name: "蛤蟆先生去看心理師", 
            author: "Robert de Board", 
            price: 280, 
            image: "https://via.placeholder.com/150" 
          }
        ]
      })
    }, 500)
  })
}