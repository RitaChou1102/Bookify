<template>
  <div class="admin-layout">
    <el-menu
      active-text-color="#ffd04b"
      background-color="#545c64"
      class="admin-menu"
      default-active="/admin/complaints"
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
      <h2>客訴與檢舉案件</h2>
      <el-card>
        <el-table :data="complaints" style="width: 100%">
          <el-table-column prop="id" label="案件ID" width="100" />
          <el-table-column prop="reporter" label="檢舉人" width="120" />
          <el-table-column prop="target" label="被檢舉對象/訂單" width="150" />
          <el-table-column prop="reason" label="原因" />
          <el-table-column prop="status" label="狀態" width="100">
             <template #default="scope">
               <el-tag :type="scope.row.status === 'pending' ? 'danger' : 'success'">
                 {{ scope.row.status === 'pending' ? '待處理' : '已結案' }}
               </el-tag>
             </template>
          </el-table-column>
          <el-table-column label="操作" width="180">
            <template #default="scope">
              <el-button size="small" @click="viewDetail(scope.row)">查看</el-button>
              <el-button 
                v-if="scope.row.status === 'pending'"
                size="small" 
                type="primary" 
                @click="resolveCase(scope.row)"
              >
                結案
              </el-button>
            </template>
          </el-table-column>
        </el-table>
      </el-card>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Odometer, User, Warning, SwitchButton } from '@element-plus/icons-vue'

const complaints = ref([
  { id: 'C001', reporter: 'UserA', target: 'Order #998', reason: '商品缺件，廠商不回應', status: 'pending' },
  { id: 'C002', reporter: 'UserB', target: 'Vendor X', reason: '販售盜版書籍', status: 'pending' },
  { id: 'C003', reporter: 'UserC', target: 'Review #123', reason: '評論含有人身攻擊', status: 'resolved' },
])

const viewDetail = (item) => {
  alert(`案件詳情：\n檢舉人：${item.reporter}\n原因：${item.reason}`)
}

const resolveCase = (item) => {
  if(confirm('確定要將此案件標記為已處理嗎？')) {
    item.status = 'resolved'
  }
}
</script>

<style scoped>
.admin-layout { display: flex; min-height: 100vh; }
.admin-menu { width: 250px; min-height: 100vh; display: flex; flex-direction: column; }
.admin-logo { height: 60px; line-height: 60px; text-align: center; color: white; font-size: 1.5rem; font-weight: bold; background-color: #434a50; }
.admin-content { flex: 1; padding: 20px; background-color: #f0f2f5; }
</style>