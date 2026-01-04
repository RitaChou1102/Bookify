<template>
  <el-header class="navbar">
    <div class="nav-content">
      <div class="logo" @click="router.push('/')">
        Bookify
      </div>

      <div class="search-bar">
        <el-input
          v-model="keyword"
          placeholder="搜尋書名、作者"
          class="search-input"
          @keyup.enter="handleSearch"
        >
          <template #append>
            <el-button @click="handleSearch">搜尋</el-button>
          </template>
        </el-input>
      </div>

      <div class="nav-actions">
        <el-button text @click="router.push('/cart')">
          <el-icon><ShoppingCart /></el-icon> 購物車
        </el-button>

        <div v-if="!isLoggedIn" class="auth-buttons">
          <el-button type="primary" @click="router.push('/login')">登入</el-button>
        </div>

        <div v-else class="user-menu">
          
          <el-dropdown @command="handleCommand">
            <span class="el-dropdown-link user-profile">
              <el-avatar :size="32" :src="userAvatar" />
              <span class="username">{{ userName }}</span>
              <el-icon class="el-icon--right"><ArrowDown /></el-icon>
            </span>
            <template #dropdown>
              <el-dropdown-menu>
                <el-dropdown-item command="profile">個人資料</el-dropdown-item>
                <el-dropdown-item command="orders">歷史訂單 (買家)</el-dropdown-item>
                
                <template v-if="currentUser?.business">
                  <el-dropdown-item command="vendor-products" divided>
                    <el-icon><Goods /></el-icon> 我的賣場 (商品管理)
                  </el-dropdown-item>

                  <el-dropdown-item command="vendor-orders">
                    <el-icon><List /></el-icon> 銷售紀錄
                  </el-dropdown-item>
                  <el-dropdown-item command="sell">
                    <el-icon><Plus /></el-icon> 我要賣書
                  </el-dropdown-item>
                </template>

                <el-dropdown-item v-else command="register-vendor" divided>
                  成為賣家
                </el-dropdown-item>

                <el-dropdown-item divided command="logout">登出</el-dropdown-item>
              </el-dropdown-menu>
            </template>
          </el-dropdown>
        </div>
      </div>
    </div>
  </el-header>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
// 🟢 [新增] 引入 Goods 圖標
import { ShoppingCart, ArrowDown, Plus, List, Goods } from '@element-plus/icons-vue'
import { ElMessage } from 'element-plus'

const router = useRouter()
const keyword = ref('')
const isLoggedIn = ref(false)
const userName = ref('')
const userAvatar = ref('')
const currentUser = ref(null)

// 檢查登入狀態
const checkLoginStatus = () => {
  const token = localStorage.getItem('token')
  const userStr = localStorage.getItem('user')
  
  if (token && userStr) {
    isLoggedIn.value = true
    const user = JSON.parse(userStr)
    currentUser.value = user
    userName.value = user.name || '會員'
    userAvatar.value = user.avatar || 'https://cube.elemecdn.com/3/7c/3ea6beec64369c2642b92c6726f1epng.png'
  } else {
    isLoggedIn.value = false
    currentUser.value = null
  }
}

onMounted(() => {
  checkLoginStatus()
  window.addEventListener('storage', checkLoginStatus)
})

const handleSearch = () => {
  if (keyword.value.trim()) {
    router.push({ path: '/search', query: { q: keyword.value } })
  }
}

const handleCommand = (command) => {
  if (command === 'logout') {
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    isLoggedIn.value = false
    currentUser.value = null
    ElMessage.success('已登出')
    router.push('/login')
  } else if (command === 'profile') {
    router.push('/user/profile')
  } else if (command === 'orders') {
    router.push('/orders') 
  } else if (command === 'vendor-products') {
    // 🟢 [新增] 跳轉到商品管理頁面
    router.push('/vendor/products')
  } else if (command === 'vendor-orders') {
    router.push('/vendor/orders') 
  } else if (command === 'sell') {
    router.push('/product/upload')
  } else if (command === 'register-vendor') {
    router.push('/vendor/register')
  }
}
</script>

<style scoped>
.navbar {
  background-color: #fff;
  border-bottom: 1px solid #dcdfe6;
  padding: 0 20px;
  height: 60px;
  display: flex;
  align-items: center;
  position: sticky;
  top: 0;
  z-index: 1000;
}
.nav-content {
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.logo {
  font-size: 24px;
  font-weight: bold;
  color: #409EFF;
  cursor: pointer;
  margin-right: 40px;
}
.search-bar {
  flex: 1;
  max-width: 500px;
}
.nav-actions {
  display: flex;
  align-items: center;
  gap: 20px;
}
.user-menu {
  display: flex;
  align-items: center;
  gap: 15px;
}
.user-profile {
  display: flex;
  align-items: center;
  cursor: pointer;
  color: #606266;
}
.username {
  margin: 0 8px;
  font-size: 14px;
}
</style>