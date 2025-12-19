<template>
  <div class="vendor-products-container">
    <div class="page-header">
      <div class="title-section">
        <h1>商品管理</h1>
        <p>管理您上架的書籍資訊、庫存與狀態</p>
      </div>
      <el-button type="primary" @click="goToUpload">
        <el-icon class="el-icon--left"><Plus /></el-icon> 新增書籍
      </el-button>
    </div>

    <el-card class="table-card">
      <el-table :data="products" style="width: 100%" stripe>
        <el-table-column label="封面" width="100">
          <template #default="scope">
            <img :src="scope.row.image" alt="cover" class="product-thumb" />
          </template>
        </el-table-column>

        <el-table-column prop="name" label="書名" min-width="150" />
        <el-table-column prop="author" label="作者" width="120" />
        
        <el-table-column prop="price" label="價格" width="100">
          <template #default="scope">
            NT$ {{ scope.row.price }}
          </template>
        </el-table-column>
        <el-table-column prop="stock" label="庫存" width="100">
          <template #default="scope">
            <el-tag :type="scope.row.stock > 0 ? 'success' : 'danger'">
              {{ scope.row.stock }}
            </el-tag>
          </template>
        </el-table-column>

        <el-table-column prop="status" label="狀態" width="100">
          <template #default="scope">
            <el-tag :type="scope.row.status === 'active' ? 'primary' : 'info'" effect="dark">
              {{ scope.row.status === 'active' ? '上架中' : '已下架' }}
            </el-tag>
          </template>
        </el-table-column>

        <el-table-column label="操作" width="180" fixed="right">
          <template #default="scope">
            <el-button size="small" @click="handleEdit(scope.row)">編輯</el-button>
            <el-button 
              size="small" 
              type="danger" 
              @click="handleDelete(scope.row)"
            >
              刪除
            </el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { Plus } from '@element-plus/icons-vue' // 記得確認 main.js 是否有註冊 icon，若無可拿掉 icon 部分

const router = useRouter()

// 模擬廠商的商品資料 (未來從 API GET /api/vendor/products 取得)
const products = ref([
  {
    id: 1,
    name: '被討厭的勇氣',
    author: '岸見一郎',
    price: 300,
    stock: 50,
    status: 'active',
    image: 'https://via.placeholder.com/80'
  },
  {
    id: 2,
    name: '原子習慣',
    author: 'James Clear',
    price: 330,
    stock: 0,
    status: 'inactive', // 缺貨或下架
    image: 'https://via.placeholder.com/80'
  },
  {
    id: 3,
    name: '底層邏輯',
    author: '劉潤',
    price: 350,
    stock: 12,
    status: 'active',
    image: 'https://via.placeholder.com/80'
  }
])

const goToUpload = () => {
  router.push('/product/upload')
}

const handleEdit = (product) => {
  console.log('編輯商品:', product)
  // router.push(`/product/edit/${product.id}`) // 未來可做編輯頁
  alert(`編輯功能開發中：${product.name}`)
}

const handleDelete = (product) => {
  if(confirm(`確定要刪除 ${product.name} 嗎？`)) {
    console.log('刪除商品:', product.id)
    // 這裡呼叫 API 刪除，成功後從 products 移除
    products.value = products.value.filter(p => p.id !== product.id)
  }
}
</script>

<style scoped>
.vendor-products-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 40px 20px;
}
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}
.title-section h1 { margin: 0; color: #333; }
.title-section p { color: #666; margin: 5px 0 0; }

.product-thumb {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 4px;
}
.table-card { border-radius: 8px; }
</style>