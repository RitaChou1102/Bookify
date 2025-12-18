# 📚 Bookify

Bookify 是一個以 **Laravel + Docker** 建構的 Web 系統，本專案採用後端與前端分離架構，並且提供完整的容器化開發環境，讓開發者在任何平台都能立即啟動。

---

- **Docker 命令**：
  ```bash
  docker-compose up -d        # 啟動服務（開Docker容器）
  docker-compose down         # 停止服務（關Docker容器）
  docker-compose down -v      # 停止服務並刪除Volumn
  docker-compose exec backend php artisan migrate                # 執行 migration
  docker-compose exec backend php artisan migrate:fresh          # 重置並創建 migration
  docker-compose exec backend php artisan db:seed                # 執行 seeder
  docker-compose exec backend php artisan migrate:fresh --seed   # 重置資料庫並重新執行所有 migration 並執行 seeder
  ```

- **進入容器**：
  ```bash
  docker-compose exec backend sh    # 進入 backend 容器
  docker-compose exec frontend sh    # 進入 frontend 容器
  docker-compose exec db sh    # 進入 db 容器
  ```

- **資料庫**
  ```bash
  docker-compose exec db mysql -u bookify -p   # 進入資料庫（要輸入密碼）
  # 進入資料庫後正常使用SQL語法
  ```
  ### NOTICE！進入資料庫後查詢資料前先輸入以下指令更改編碼
  ```sql
  SET NAMES utf8mb4;
  ```