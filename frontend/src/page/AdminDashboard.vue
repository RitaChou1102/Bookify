<template>
  <div class="admin-layout">
    <el-menu
      active-text-color="#ffd04b"
      background-color="#545c64"
      class="admin-menu"
      default-active="dashboard"
      text-color="#fff"
      :router="true"
    >
      <div class="admin-logo">Admin Panel</div>
      
      <el-menu-item index="/admin/dashboard">
        <el-icon><Odometer /></el-icon>
        <span>儀表板總覽</span>
      </el-menu-item>

      <el-menu-item index="/admin/users">
        <el-icon><User /></el-icon>
        <span>使用者管理 (黑名單)</span>
      </el-menu-item>

      <el-menu-item index="/admin/complaints">
        <el-icon><Warning /></el-icon>
        <span>客訴與檢舉處理</span>
      </el-menu-item>

      <el-menu-item index="/" style="margin-top: auto;">
        <el-icon><SwitchButton /></el-icon>
        <span>返回前台</span>
      </el-menu-item>
    </el-menu>

    <div class="admin-content">
      <header class="content-header">
        <h2>儀表板總覽</h2>
        <span class="date">{{ currentDate }}</span>
      </header>

      <el-row :gutter="20" class="stats-row">
        <el-col :span="6">
          <el-card shadow="hover" class="stat-card">
            <template #header>總銷售額</template>
            <div class="stat-value">NT$ 1,250,000</div>
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card shadow="hover" class="stat-card">
            <template #header>總會員數</template>
            <div class="stat-value">3,500 人</div>
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card shadow="hover" class="stat-card">
            <template #header>總訂單數</template>
            <div class="stat-value">8,900 筆</div>
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card shadow="hover" class="stat-card warning">
            <template #header>待處理客訴</template>
            <div class="stat-value danger">12 件</div>
          </el-card>
        </el-col>
      </el-row>

      <el-card class="mt-4">
        <template #header>
          <div class="card-header">
            <span>近期高風險活動 / 警示</span>
          </div>
        </template>
        <el-table :data="recentActivities" style="width: 100%">
          <el-table-column prop="time" label="時間" width="180" />
          <el-table-column prop="type" label="類型" width="120">
             <template #default="scope">
               <el-tag :type="scope.row.type === 'report' ? 'danger' : 'warning'">
                 {{ scope.row.type === 'report' ? '檢舉' : '異常登入' }}
               </el-tag>
             </template>
          </el-table-column>
          <el-table-column prop="message" label="內容" />
        </el-table>
      </el-card>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Odometer, User, Warning, SwitchButton } from '@element-plus/icons-vue' // 需確認 main.js 有註冊圖示

const currentDate = computed(() => new Date().toLocaleDateString())

const recentActivities = ref([
  { time: '2025-12-08 10:30', type: 'report', message: '使用者 UserA 檢舉了書籍「危險心理學」內容不當' },
  { time: '2025-12-08 09:15', type: 'login', message: '廠商 VendorB 短時間內嘗試登入失敗 5 次' },
  { time: '2025-12-07 18:20', type: 'report', message: '使用者 UserC 投訴 訂單#99881 商品未收到' },
])
</script>

<style scoped>
.admin-layout { display: flex; min-height: 100vh; }
.admin-menu { width: 250px; min-height: 100vh; border-right: none; display: flex; flex-direction: column; }
.admin-logo { height: 60px; line-height: 60px; text-align: center; color: white; font-size: 1.5rem; font-weight: bold; background-color: #434a50; }
.admin-content { flex: 1; padding: 20px; background-color: #f0f2f5; }
.content-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.stat-value { font-size: 1.8rem; font-weight: bold; margin-top: 10px; }
.danger { color: #f56c6c; }
.mt-4 { margin-top: 20px; }
</style>