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
      <!-- 購物車按鈕 -->
      <el-button type="primary" @click="goCart">
        🛒 購物車
      </el-button>

      <!-- 未登入 -->
      <el-button v-if="!user" @click="goLogin">登入</el-button>

      <!-- 已登入 -->
      <el-dropdown v-else>
        <span class="el-dropdown-link">
          👤 {{ user.name }}
        </span>
        <template #dropdown>
          <el-dropdown-menu>
            <el-dropdown-item @click="goProfile">個人資料</el-dropdown-item>
            <el-dropdown-item @click="goOrders">訂單</el-dropdown-item>

            <el-dropdown-item v-if="user.role === 'seller'" @click="goSeller">
              賣家中心
            </el-dropdown-item>

            <el-dropdown-item v-if="user.role === 'admin'" @click="goAdmin">
              管理者後台
            </el-dropdown-item>

            <el-dropdown-item divided @click="logout">登出</el-dropdown-item>
          </el-dropdown-menu>
        </template>
      </el-dropdown>
    </div>
  </el-header>
</template>

<script setup>
import { ref } from "vue"
import { useRouter } from "vue-router"

const router = useRouter()
const keyword = ref("")
const user = ref(null) // 你可以從 Pinia 改成: useUserStore()

function goHome() {
  router.push("/")
}

function search() {
  router.push(`/search?keyword=${keyword.value}`)
}

function goCart() {
  router.push("/cart")
}

function goLogin() {
  router.push("/login")
}

function goProfile() {
  router.push("/profile")
}

function goOrders() {
  router.push("/orders")
}

function goSeller() {
  router.push("/seller/dashboard")
}

function goAdmin() {
  router.push("/admin")
}

function logout() {
  user.value = null  // 改成 pinia 的登出邏輯
}
</script>

<style scoped>
.navbar {
  padding: 10px 40px;
  display: flex;
  align-items: center;
  background: #ffffff;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}
.logo {
  cursor: pointer;
}
.left, .center, .right {
  display: flex;
  align-items: center;
}
.center {
  flex: 1;
  justify-content: center;
}
.search-bar {
  width: 350px;
}
.right > * {
  margin-left: 12px;
}
</style>
