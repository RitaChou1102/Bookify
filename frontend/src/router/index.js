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
import Orders from '../page/Orders.vue'
import OrderDetail from '../page/OrderDetail.vue'
import AdminDashboard from '../page/AdminDashboard.vue'
import AdminUsers from '../page/AdminUsers.vue'
import AdminComplaints from '../page/AdminComplaints.vue'
import Search from '../page/Search.vue'


const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: Home
    },
    {
      path: '/search',
      name: 'search',
      component: Search
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
      path: '/orders',
      name: 'orders',
      component: Orders
    },
    {
      path: '/orders/:orderId',
      name: 'order-detail',
      component: OrderDetail
    },
    { 
      path: '/vendor/orders', 
      name: 'vendor-orders', 
      component: VendorOrders 
    },
    {
      path: '/admin/dashboard',
      name: 'admin-dashboard',
      component: AdminDashboard
    },
    {
      path: '/admin/users',
      name: 'admin-users',
      component: AdminUsers
    },
    {
      path: '/admin/complaints',
      name: 'admin-complaints',
      component: AdminComplaints
    }
  ]
})

export default router