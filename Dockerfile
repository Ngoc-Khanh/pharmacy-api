# Sử dụng PHP 8.3 với Apache
FROM php:8.3-apache

# Cài đặt các thư viện cần thiết
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libonig-dev libxml2-dev \
    libcurl4-openssl-dev pkg-config libssl-dev \
    && docker-php-ext-install pdo mbstring exif pcntl bcmath gd

# Cài đặt MongoDB PHP Extension
RUN pecl install mongodb && echo "extension=mongodb.so" > /usr/local/etc/php/conf.d/mongodb.ini

# Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Thiết lập thư mục làm việc
WORKDIR /var/www/html

# Copy code Laravel vào container
COPY . .

# Cài đặt các package Laravel
RUN composer install --no-dev --optimize-autoloader

# Phân quyền thư mục storage và bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Expose cổng 80 cho Apache
EXPOSE 80

# Chạy Laravel server
CMD ["apache2-foreground"]
