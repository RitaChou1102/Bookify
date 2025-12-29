<?php
$env = file_get_contents('.env');
// 精準替換密碼
$env = preg_replace('/^DB_PASSWORD=.*/m', 'DB_PASSWORD=rootpassword', $env);
file_put_contents('.env', $env);
echo "密碼已修正為正確的 rootpassword！\n";