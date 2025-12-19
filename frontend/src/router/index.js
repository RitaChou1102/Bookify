import { createRouter, createWebHistory } from 'vue-router'
import Home from '../page/Home.vue'
import Login from '../page/Login.vue'
import CloudinaryUploadTest from '../page/CloudinaryUploadTest.vue'
import BookDetail from '../page/BookDetail.vue'
import Register from '../page/Register.vue'
import Checkout from '../page/Checkout.vue'
import ProductUpload from '../page/ProductUpload.vue'
import Cart from '../page/cart.vue'
import VendorProducts from '../page/VendorProducts.vue'
import UserProfile from '../page/UserProfile.vue'
import VendorOrders from '../page/VendorOrders.vue'

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
      path: '/register',
      name: 'register',
      component: Register
    },
    {
      path: '/book/:id',
      name: 'BookDetail',
      component: BookDetail
    },
    {
      path: '/cart',
      name: 'Cart',
      component: Cart
    },
    {
      path: '/upload-test', // <--- 您要訪問的 URL 路徑
      name: 'UploadTest',
      component: CloudinaryUploadTest // <--- 指向您的新元件
    },
    {
      path: '/checkout',
      name: 'checkout',
      component: Checkout
    },
    {
      path: '/product/upload',
      name: 'product-upload',
      component: ProductUpload
    },
    { 
      path: '/vendor/products', 
      name: 'vendor-products', 
      component: VendorProducts 
    },
    { 
      path: '/profile', 
      name: 'user-profile', 
      component: UserProfile 
    },
    { 
      path: '/vendor/orders', 
      name: 'vendor-orders', 
      component: VendorOrders 
    }
  ]
})

export default router