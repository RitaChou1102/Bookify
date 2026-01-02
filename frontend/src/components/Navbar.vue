<template>
  <el-header class="navbar">
    <div class="left">
      <h2 class="logo" @click="goHome">Bookify</h2>
    </div>

    <div class="center">
      <el-input
        v-model="keyword"
        placeholder="搜尋書名、作者..."
        class="search-bar"
        @keyup.enter="search"
      >
        <template #append>
          <el-button @click="search">搜尋</el-button>
        </template>
      </el-input>
    </div>

    <div class="right">
      <el-button class="cart-btn" text @click="goCart">
        🛒 <span class="cart-text">購物車</span>
      </el-button>

      <div v-if="!user">
        <el-button type="primary" @click="goLogin">登入</el-button>
      </div>

      <el-dropdown v-else trigger="click">
        <div class="user-profile-trigger">
          <el-avatar 
            :size="36" 
            :src="user.avatar || 'https://cube.elemecdn.com/0/88/03b0d39583f48206768a7534e55bcpng.png'" 
          />
          <span class="username">{{ user.name || '會員' }}</span>
          <el-icon><CaretBottom /></el-icon>
        </div>

        <template #dropdown>
          <el-dropdown-menu>
            <el-dropdown-item @click="goProfile">
                <el-icon><User /></el-icon> 個人資料
            </el-dropdown-item>
            
            <el-dropdown-item @click="goOrders">
                <el-icon><List /></el-icon> 我的訂單
            </el-dropdown-item>

            <el-dropdown-item v-if="user.role === 'seller'" @click="goSeller" divided>
               賣家中心
            </el-dropdown-item>
            <el-dropdown-item v-if="user.role === 'admin'" @click="goAdmin" divided>
               管理者後台
            </el-dropdown-item>

            <el-dropdown-item divided @click="logout" style="color: #f56c6c;">
               <el-icon><SwitchButton /></el-icon> 登出
            </el-dropdown-item>
          </el-dropdown-menu>
        </template>
      </el-dropdown>
    </div>
  </el-header>
</template>

<script setup>
import { ref, onMounted } from "vue"
import { useRouter } from "vue-router"
import { CaretBottom, User, List, SwitchButton } from '@element-plus/icons-vue' // 引入圖示

const router = useRouter()
const keyword = ref("")
const user = ref(null) 

// 1. 初始化：檢查是否已登入
onMounted(() => {
  const token = localStorage.getItem('token')
  if (token) {
    // 嘗試從 localStorage 抓取使用者資訊 (假設你在登入時有存 user JSON)
    // 如果沒存，這裡就先給個假資料讓畫面顯示
    const savedUser = localStorage.getItem('user')
    if (savedUser) {
        try {
            user.value = JSON.parse(savedUser)
        } catch (e) {
            user.value = { name: '會員', role: 'buyer' }
        }
    } else {
        // 有 token 但沒 user info，先給預設值
        user.value = { name: '親愛的會員', role: 'buyer' }
    }
  }
})

function goHome() { router.push("/") }

function search() { 
  if(keyword.value.trim()) {
      router.push(`/search?keyword=${keyword.value}`) 
  }
}

function goCart() { router.push("/cart") }
function goLogin() { router.push("/login") }
function goProfile() { router.push("/profile") }
function goOrders() { router.push("/orders") } // 跳轉到訂單列表頁
function goSeller() { router.push("/seller/dashboard") }
function goAdmin() { router.push("/admin/dashboard") }

// 2. 登出邏輯
function logout() {
  // 清除資料
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  user.value = null
  
  // 強制重新整理頁面，確保所有狀態重置
  window.location.href = '/'
}
</script>

<style scoped>
.navbar {
  padding: 0 40px;
  height: 64px; /* 固定高度 */
  display: flex;
  align-items: center;
  background: #ffffff;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08); /* 陰影稍微調柔和一點 */
  position: sticky; /* 讓導覽列固定在上方 */
  top: 0;
  z-index: 1000;
}

.logo {
  cursor: pointer;
  color: #409EFF; /* 使用主色調 */
  margin: 0;
  font-weight: 800;
  font-size: 24px;
}

.left, .center, .right {
  display: flex;
  align-items: center;
}

.center {
  flex: 1;
  justify-content: center;
  margin: 0 20px;
}

.search-bar {
  width: 100%;
  max-width: 500px; /* 搜尋列最大寬度 */
}

/* 右側選單樣式 */
.right {
  gap: 15px;
}

.cart-btn {
  font-size: 16px;
  color: #606266;
}
.cart-btn:hover {
  color: #409EFF;
}

/* 使用者頭像區塊樣式 */
.user-profile-trigger {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 4px;
  transition: background 0.3s;
}

.user-profile-trigger:hover {
  background: #f5f7fa;
}

.username {
  font-size: 14px;
  font-weight: 500;
  color: #333;
  max-width: 100px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* 響應式調整 */
@media (max-width: 768px) {
  .navbar { padding: 0 15px; }
  .cart-text { display: none; } /* 手機版隱藏購物車文字 */
  .username { display: none; } /* 手機版隱藏使用者名稱 */
}
</style>