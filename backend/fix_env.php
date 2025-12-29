<?php
$file = '.env';
if (!file_exists($file)) {
    copy('.env.example', '.env');
}
$content = file_get_contents($file);

// 1. 清除舊的、可能寫壞的 APP_KEY
$lines = explode("\n", $content);
$cleanLines = array_filter($lines, function($line) {
    return strpos($line, 'APP_KEY') === false;
});

// 2. 強制在第一行加入全新的 APP_KEY
array_unshift($cleanLines, 'APP_KEY=');

// 3. 存檔
file_put_contents($file, implode("\n", $cleanLines));
echo "設定檔已修復！APP_KEY 已重置於第一行。\n";