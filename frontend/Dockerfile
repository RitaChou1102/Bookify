# 使用 Node 18
FROM node:20

# 設定工作目錄
WORKDIR /app

# 複製 package.json 與 package-lock.json
COPY package*.json ./

# 安裝依賴
RUN npm install

# 複製整個前端專案
COPY . .

# 暴露 Vite dev server 端口
EXPOSE 5173

# 預設啟動命令
CMD ["npm", "run", "dev", "--", "--host"]
