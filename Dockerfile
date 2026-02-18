# ใช้ PHP official image พร้อม Apache
FROM php:8.2.12-apache

# ติดตั้ง PHP extensions และ dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libgd-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mysqli gd

# ติดตั้ง Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# คัดลอก php.ini (หากมี)
COPY php.ini /usr/local/etc/php/

# เปิด mod_rewrite ถ้าจำเป็น
RUN a2enmod rewrite

# กำหนด working directory
WORKDIR /var/www/html

# คัดลอกโค้ดโปรเจกต์ทั้งหมดเข้า container
COPY . .

# ติดตั้ง PHP dependencies ด้วย Composer
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# เปิดพอร์ต 80 (Apache ใช้พอร์ตนี้อยู่แล้ว)
EXPOSE 80

# Apache จะรันโดย default ใน image นี้อยู่แล้ว
