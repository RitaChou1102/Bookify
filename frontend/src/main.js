import { createApp } from 'vue'
import './style.css'
import App from './App.vue'
import router from './router'
import ElementPlus from 'element-plus'
import 'element-plus/dist/index.css'
import Cloudinary, { CldImage, CldVideo } from '@cloudinary/vue';

const app = createApp(App)

app.use(router)
app.use(ElementPlus)

app.use(Cloudinary, {
    cloudName: import.meta.env.VITE_CLOUDINARY_CLOUD_NAME
});

app.component('CldImage', CldImage);
app.component('CldVideo', CldVideo);

app.mount('#app')