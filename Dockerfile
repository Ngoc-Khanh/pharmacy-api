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
# Chỉnh Apache chạy từ thư mục public
WORKDIR /var/www/html
# Copy code Laravel vào container
COPY . .
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Cài đặt các package Laravel
RUN composer install --no-dev --optimize-autoloader
# Sửa cấu hình Apache
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf
RUN echo "<Directory /var/www/html/public>\n\
    AllowOverride All\n\
</Directory>" >> /etc/apache2/apache2.conf

# Phân quyền thư mục storage và bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
# Bật mod_rewrite để hỗ trợ Laravel
RUN a2enmod rewrite

# Expose cổng 80 cho Apache
EXPOSE 80

# Chạy Laravel server
CMD ["apache2-foreground"]