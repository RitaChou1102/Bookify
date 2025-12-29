<?php
$content = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="app"></div>
</body>
</html>
HTML;

file_put_contents('resources/views/welcome.blade.php', $content);
echo "首頁已切換為 Bookify 書店入口！\n";