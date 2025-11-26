FROM php:8.2-fpm

# 安裝必要套件
RUN apt-get update && apt-get install -y \
    libzip-dev unzip git curl \
    && docker-php-ext-install pdo_mysql zip

# 安裝 Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# 複製 Laravel 專案到容器內
COPY . .

# 開放端口
EXPOSE 8000
