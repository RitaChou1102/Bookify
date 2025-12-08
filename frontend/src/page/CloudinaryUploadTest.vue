<template>
  <div class="upload-container">
    <h2>Cloudinary 圖片上傳測試</h2>
    <div class="image-preview">
      <h3>圖片預覽 (AdvancedImage)</h3>
      <AdvancedImage :cldImg="myImg" />
    </div>
  </div>
</template>

<script setup>
import { Cloudinary } from '@cloudinary/url-gen';
// 記得引入 AdvancedImage
import { AdvancedImage } from '@cloudinary/vue'; 

import { thumbnail } from '@cloudinary/url-gen/actions/resize';
import { focusOn } from '@cloudinary/url-gen/qualifiers/gravity';
import { face } from '@cloudinary/url-gen/qualifiers/focusOn';

// 初始化 Cloudinary
const cld = new Cloudinary({
  cloud: {
    cloudName: import.meta.env.VITE_CLOUDINARY_CLOUD_NAME
  }
});

// 設定圖片
const myImg = cld.image('sample'); 

// 加入變形效果
myImg.resize(thumbnail().width(300).height(300).gravity(focusOn(face())));
</script>

<style scoped>
.upload-container { padding: 20px; text-align: center; }
.image-preview { margin-top: 20px; border: 1px dashed #ccc; padding: 20px; display: inline-block; }
</style>