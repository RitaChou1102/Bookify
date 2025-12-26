<template>
  <div class="admin-layout">
    <el-menu
      active-text-color="#ffd04b"
      background-color="#545c64"
      class="admin-menu"
      default-active="/admin/users"
      text-color="#fff"
      :router="true"
    >
      <div class="admin-logo">Admin Panel</div>
      <el-menu-item index="/admin/dashboard"><el-icon><Odometer /></el-icon><span>儀表板總覽</span></el-menu-item>
      <el-menu-item index="/admin/users"><el-icon><User /></el-icon><span>使用者管理</span></el-menu-item>
      <el-menu-item index="/admin/complaints"><el-icon><Warning /></el-icon><span>客訴處理</span></el-menu-item>
      <el-menu-item index="/"><el-icon><SwitchButton /></el-icon><span>返回前台</span></el-menu-item>
    </el-menu>

    <div class="admin-content">
      <div class="page-header">
        <h2>使用者管理</h2>
        <el-input v-model="search" placeholder="搜尋名稱或 Email" style="width: 300px">
          <template #append><el-button icon="Search" /></template>
        </el-input>
      </div>

      <el-card>
        <el-table :data="filteredUsers" stripe style="width: 100%">
          <el-table-column prop="id" label="ID" width="80" />
          <el-table-column prop="name" label="名稱" width="120" />
          <el-table-column prop="email" label="Email" width="200" />
          <el-table-column prop="role" label="身分" width="100">
            <template #default="scope">
              <el-tag :type="scope.row.role === 'business' ? 'warning' : 'success'">
                {{ scope.row.role === 'business' ? '廠商' : '會員' }}
              </el-tag>
            </template>
          </el-table-column>
          
          <el-table-column prop="status" label="狀態" width="100">
            <template #default="scope">
              <el-tag :type="scope.row.isBlacklisted ? 'danger' : 'info'">
                {{ scope.row.isBlacklisted ? '黑名單' : '正常' }}
              </el-tag>
            </template>
          </el-table-column>

          <el-table-column label="操作">
            <template #default="scope">
              <el-button 
                v-if="!scope.row.isBlacklisted"
                type="danger" 
                size="small" 
                @click="toggleBlacklist(scope.row)"
              >
                封鎖
              </el-button>
              <el-button 
                v-else
                type="success" 
                size="small" 
                @click="toggleBlacklist(scope.row)"
              >
                解鎖
              </el-button>
            </template>
          </el-table-column>
        </el-table>
      </el-card>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Odometer, User, Warning, SwitchButton, Search } from '@element-plus/icons-vue'

const search = ref('')
const users = ref([
  { id: 1, name: '王小明', email: 'ming@test.com', role: 'member', isBlacklisted: false },
  { id: 2, name: '好書廠商', email: 'book_seller@test.com', role: 'business', isBlacklisted: false },
  { id: 3, name: '惡意洗版仔', email: 'bad@test.com', role: 'member', isBlacklisted: true },
])

const filteredUsers = computed(() => {
  return users.value.filter(u => 
    u.name.includes(search.value) || u.email.includes(search.value)
  )
})

const toggleBlacklist = (user) => {
  const action = user.isBlacklisted ? '解鎖' : '封鎖'
  if (confirm(`確定要${action}使用者 ${user.name} 嗎？`)) {
    // API 呼叫：POST /api/admin/blacklist
    user.isBlacklisted = !user.isBlacklisted
    alert(`已${action}`)
  }
}
</script>

<style scoped>
/* 復用 layout 樣式，實務上應使用共用 CSS 或 Layout Component */
.admin-layout { display: flex; min-height: 100vh; }
.admin-menu { width: 250px; min-height: 100vh; display: flex; flex-direction: column; }
.admin-logo { height: 60px; line-height: 60px; text-align: center; color: white; font-size: 1.5rem; font-weight: bold; background-color: #434a50; }
.admin-content { flex: 1; padding: 20px; background-color: #f0f2f5; }
.page-header { display: flex; justify-content: space-between; margin-bottom: 20px; align-items: center; }
</style>