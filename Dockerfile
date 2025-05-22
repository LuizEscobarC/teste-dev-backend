# Dockerfile para Laravel API Only
FROM php:8.2-fpm

# Instala dependências do sistema
RUN apt-get update \
    && apt-get install -y \
        libpng-dev \
        libonig-dev \
        libxml2-dev \
        zip \
        unzip \
        git \
        curl \
        sqlite3 \
        libsqlite3-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_sqlite mbstring exif pcntl bcmath gd

# Instala Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Define diretório de trabalho
WORKDIR /var/www

# Copia arquivos do projeto
COPY . .

# Instala dependências do PHP
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Permissões para storage e bootstrap/cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Porta padrão
EXPOSE 9000

CMD ["php-fpm"]
