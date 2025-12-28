import { createApp } from 'vue'
import './style.css'
import App from './App.vue'
import router from './router'
import ElementPlus from 'element-plus'
import 'element-plus/dist/index.css'
import { createPinia } from 'pinia'

// 引入新版元件
import { AdvancedImage, AdvancedVideo } from '@cloudinary/vue';

const app = createApp(App)
const pinia = createPinia()

app.use(router)
app.use(ElementPlus)
app.use(pinia)

// 註冊全域元件
app.component('AdvancedImage', AdvancedImage);
app.component('AdvancedVideo', AdvancedVideo);

app.mount('#app')