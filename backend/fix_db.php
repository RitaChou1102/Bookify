<?php
$env = file_get_contents('.env');
// 1. 將 SQLite 改為 MySQL
$env = str_replace('DB_CONNECTION=sqlite', 'DB_CONNECTION=mysql', $env);
// 2. 設定 MySQL 連線資訊 (使用 Docker 預設值)
$env = preg_replace('/^#? ?DB_HOST=.*/m', 'DB_HOST=db', $env);
$env = preg_replace('/^#? ?DB_PORT=.*/m', 'DB_PORT=3306', $env);
$env = preg_replace('/^#? ?DB_DATABASE=.*/m', 'DB_DATABASE=bookify', $env);
$env = preg_replace('/^#? ?DB_USERNAME=.*/m', 'DB_USERNAME=root', $env);
$env = preg_replace('/^#? ?DB_PASSWORD=.*/m', 'DB_PASSWORD=root', $env);

file_put_contents('.env', $env);
echo "資料庫設定已切換為 MySQL (Host: db)！\n";