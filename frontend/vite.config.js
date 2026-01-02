import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src')
    }
  },
  server: {
    host: '0.0.0.0', // 讓 Docker 外部也能存取
    port: 5173,      // 前端 Port
    proxy: {
      '/api': {
        target: 'http://localhost:8000', // 指向後端 (Docker Port 8000)
        changeOrigin: true,
        secure: false,
      },
      // 確保圖片也能正常讀取
      '/storage': {
        target: 'http://localhost:8000',
        changeOrigin: true,
        secure: false,
      }
    }
  }
})