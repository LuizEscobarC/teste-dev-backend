# Dockerfile otimizado para Laravel API
FROM php:8.2-fpm-alpine

# Instala extensões essenciais para API
RUN apk add --no-cache \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    sqlite \
    sqlite-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_sqlite mbstring exif pcntl bcmath

# Otimizações de PHP para API
COPY docker/config/php.ini /usr/local/etc/php/conf.d/custom.ini

# Instala Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Define diretório de trabalho
WORKDIR /var/www

# Primeiro copiamos apenas composer.* para aproveitar o cache em builds futuros
COPY composer.json composer.lock ./

# Instalamos dependências PHP em modo de produção
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --optimize-autoloader

# Então copiamos o resto dos arquivos
COPY . .

# Executamos os scripts necessários após a cópia de todos os arquivos
RUN composer dump-autoload --optimize && \
    # Configurações de permissão
    chmod -R 775 storage bootstrap/cache && \
    chown -R www-data:www-data storage bootstrap/cache

# Porta padrão
EXPOSE 9000

CMD ["php-fpm"]
