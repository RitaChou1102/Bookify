# 📚 Bookify

Bookify 是一個以 **Laravel + Docker** 建構的 Web 系統，本專案採用後端與前端分離架構，並且提供完整的容器化開發環境，讓開發者在任何平台都能立即啟動。

---

- **Docker 命令**：
  ```bash
  docker-compose up -d        # 啟動服務
  docker-compose down         # 停止服務
  docker-compose exec backend php artisan migrate    # 執行 migration
  docker-compose exec backend php artisan db:seed    # 執行 seeder
  ```

- **進入容器**：
  ```bash
  docker-compose exec backend bash    # 進入 backend 容器
  docker-compose exec db mysql -u bookify -p bookify    # 進入資料庫
  ```