import { createRouter, createWebHistory } from 'vue-router'
import Home from '../page/Home.vue'
import Login from '../page/Login.vue'
import CloudinaryUploadTest from '../page/CloudinaryUploadTest.vue'
const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: Home
    },
    {
      path: '/login',
      name: 'login',
      component: Login
    },
    {
      path: '/upload-test', // <--- 您要訪問的 URL 路徑
      name: 'UploadTest',
      component: CloudinaryUploadTest // <--- 指向您的新元件
    }
  ]
})

export default router