# Sử dụng PHP 8.3 với Apache
FROM php:8.3-apache

# Cài đặt các thư viện cần thiết
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    curl \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql mbstring xml

# Cài đặt MongoDB PHP extension
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Thiết lập thư mục làm việc
WORKDIR /var/www/html

# Copy toàn bộ mã nguồn Laravel vào container
COPY . .

# Cài đặt Laravel dependencies
RUN composer install --no-dev --optimize-autoloader

# Thiết lập quyền cho Laravel storage và bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Kích hoạt mod_rewrite của Apache (cần thiết cho Laravel)
RUN a2enmod rewrite

# Mở cổng 80 (Apache)
EXPOSE 80

# Chạy Apache khi container khởi động
CMD ["apache2-foreground"]
