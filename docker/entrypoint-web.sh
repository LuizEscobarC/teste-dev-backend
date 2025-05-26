#!/bin/bash
set -e

echo "=== CONTAINER WEB STARTUP ==="

echo "Aguardando banco de dados..."
max_attempts=30
attempt=0

# until [ $attempt -ge $max_attempts ]; do
#     if php artisan tinker --execute="DB::connection()->getPdo(); echo 'DB Connected';" 2>/dev/null | grep -q "DB Connected"; then
#         echo "Banco de dados conectado!"
#         break
#     fi
    
#     attempt=$((attempt + 1))
#     echo "Tentativa $attempt/$max_attempts - Banco de dados não está pronto - aguardando..."
#     sleep 2
# done

# if [ $attempt -ge $max_attempts ]; then
#     echo "ERRO: Não foi possível conectar ao banco de dados após $max_attempts tentativas"
#     exit 1
# fi

# Verificar se APP_KEY existe, se não, gerar
if [ -z "${APP_KEY}" ] || [ "${APP_KEY}" = "" ]; then
    echo "Gerando APP_KEY..."
    php artisan key:generate --force --no-interaction
else
    echo "APP_KEY já configurada."
fi

# Executar migrations
echo "Executando migrations..."
php artisan migrate --force

# Publicar assets do Telescope se necessário
echo "Publicando assets do Telescope..."
php artisan vendor:publish --tag=telescope-assets --force || true

# Otimizar configurações
echo "Otimizando configurações..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "=== WEB CONTAINER PRONTO ==="

# Iniciar PHP-FPM
exec php-fpm
