
# chmod +x run-tests.sh
# 使用方式：
#   ./run-tests.sh              -> 執行所有測試
#   ./run-tests.sh CartTest     -> 只執行 CartTest.php
#   ./run-tests.sh AuthTest     -> 只執行 AuthTest.php

if [ -z "$1" ]; then
    # 如果沒有傳入參數，跑全部測試
    echo "🚀 正在執行所有測試 (All Tests)..."
    docker compose exec backend php artisan test
else
    # 如果有傳入參數 (例如 CartTest)，只跑該檔案
    # 自動補上 tests/Feature/ 前綴與 .php 後綴 (如果不小心多打 .php 也能處理)
    TEST_NAME=$1
    
    # 移除使用者可能不小心打的 .php 後綴
    TEST_NAME=${TEST_NAME%.php}

    echo "🎯 正在執行測試: tests/Feature/$TEST_NAME.php"
    docker compose exec backend php artisan test tests/Feature/$TEST_NAME.php
fi