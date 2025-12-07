<template>
  <div class="upload-container">
    <h2>Cloudinary 前端上傳測試</h2>
    <p>使用 Upload Preset: <strong>{{ uploadPreset }}</strong></p>

    <button @click="openWidget" :disabled="!isScriptLoaded" class="upload-button">
      {{ isScriptLoaded ? '點擊上傳圖片/文件' : '載入中...' }}
    </button>

    <div v-if="uploadStatus" class="status-box">
      <p :class="uploadStatus.type === 'success' ? 'success' : 'error'">
        {{ uploadStatus.message }}
      </p>
      <div v-if="uploadStatus.type === 'success'">
        <p>Public ID: <code>{{ uploadStatus.publicId }}</code></p>
        <a :href="uploadStatus.url" target="_blank">{{ uploadStatus.url }}</a>
        <img :src="uploadStatus.url" alt="上傳圖片預覽" class="uploaded-image">
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CloudinaryUploadTest',
  data() {
    return {
      // 您的 Cloudinary 配置
      cloudName: 'dze7hodsc',
      uploadPreset: 'bookify_unpreset_name',

      myWidget: null,
      isScriptLoaded: false,
      uploadStatus: null, // { type: 'success'|'error', message: '...', publicId: '...', url: '...' }
    };
  },
  mounted() {
    this.loadCloudinaryScript();
  },
  methods: {
    // 步驟一：動態載入 Cloudinary 上傳 Widget 腳本
    loadCloudinaryScript() {
      if (window.cloudinary) {
        this.isScriptLoaded = true;
        this.initializeWidget();
        return;
      }
      const script = document.createElement('script');
      script.src = 'https://upload-widget.cloudinary.com/global/all.js';
      script.onload = () => {
        this.isScriptLoaded = true;
        this.initializeWidget();
      };
      script.onerror = () => {
        this.uploadStatus = {
          type: 'error',
          message: '無法載入 Cloudinary 腳本。請檢查網路連線。'
        };
      };
      document.head.appendChild(script);
    },

    // 步驟二：初始化上傳 Widget
    initializeWidget() {
      if (!this.isScriptLoaded || this.myWidget) return;

      this.myWidget = window.cloudinary.createUploadWidget({
        cloudName: this.cloudName,
        uploadPreset: this.uploadPreset,
        sources: ['local', 'url'], // 允許從本地檔案或 URL 上傳
      }, (error, result) => {
        if (error) {
          this.uploadStatus = {
            type: 'error',
            message: `上傳發生錯誤: ${error.status} - ${error.statusText || error.status}`,
          };
        } else if (result.event === 'success') {
          console.log('上傳成功資訊:', result.info);
          this.uploadStatus = {
            type: 'success',
            message: '文件已成功上傳到 Cloudinary!',
            publicId: result.info.public_id,
            url: result.info.secure_url,
          };
        }
      });
    },

    // 步驟三：打開 Widget
    openWidget() {
      if (this.myWidget) {
        this.uploadStatus = null; // 清空狀態
        this.myWidget.open();
      }
    }
  }
};
</script>

<style scoped>
.upload-container {
  max-width: 600px;
  margin: 50px auto;
  padding: 20px;
  border: 1px solid #ccc;
  border-radius: 8px;
  text-align: center;
}
.upload-button {
  padding: 10px 20px;
  font-size: 16px;
  cursor: pointer;
  background-color: #42b983; /* Vue's green */
  color: white;
  border: none;
  border-radius: 4px;
}
.upload-button:disabled {
  background-color: #999;
  cursor: not-allowed;
}
.status-box {
  margin-top: 20px;
  padding: 15px;
  border-radius: 4px;
  text-align: left;
  background-color: #f9f9f9;
}
.success {
  color: green;
  font-weight: bold;
}
.error {
  color: red;
  font-weight: bold;
}
.uploaded-image {
  max-width: 100%;
  height: auto;
  margin-top: 15px;
  border: 1px solid #ddd;
}
</style>