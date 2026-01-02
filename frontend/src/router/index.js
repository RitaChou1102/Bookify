import { createRouter, createWebHistory } from 'vue-router'
import Home from '../page/Home.vue'
import Login from '../page/Login.vue'
import CloudinaryUploadTest from '../page/CloudinaryUploadTest.vue'
import BookDetail from '../page/BookDetail.vue'
import Register from '../page/Register.vue'
import Checkout from '../page/Checkout.vue'
import OrderSuccess from '../page/OrderSuccess.vue'
import ProductUpload from '../page/ProductUpload.vue'
import Cart from '../page/cart.vue'
import VendorProducts from '../page/VendorProducts.vue'
import UserProfile from '../page/UserProfile.vue'
import VendorOrders from '../page/VendorOrders.vue'
// import Orders from '../page/Orders.vue' // 可以註解掉或保留，下面已經用動態引入了
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
      path: '/upload-test',
      name: 'UploadTest',
      component: CloudinaryUploadTest
    },
    {
      path: '/checkout',
      name: 'checkout',
      component: Checkout
    },
    {
      path: '/order/success',
      name: 'order-success',
      component: OrderSuccess
    },
    {
      path: '/product/upload',
      name: 'product-upload',
      component: ProductUpload,
      meta: { requiresAuth: true }
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
      component: () => import('@/page/Orders.vue')
    },
    
    // [🔥保留這一個正確的] 對應你的按鈕連結 /order/2
    {
        path: '/order/:id',     
        name: 'order-detail',   
        component: OrderDetail,
        meta: { requiresAuth: true }
    },

    // [❌刪除這一個] 這是重複的，而且路徑是複數 (orders)，導致單數路徑失效
    /* {
      path: '/orders/:orderId',
      name: 'order-detail',
      component: OrderDetail
    },
    */

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