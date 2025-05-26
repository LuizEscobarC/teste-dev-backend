#!/bin/bash
set -e

echo "=== CONTAINER QUEUE WORKERS STARTUP ==="

echo "Aguardando web container estar pronto..."
max_attempts=30
attempt=0

# Aguardar o web container estar completamente pronto
until [ $attempt -ge $max_attempts ]; do
    if nc -z app-estech 9000 2>/dev/null; then
        echo "Web container está pronto!"
        break
    fi
    
    attempt=$((attempt + 1))
    echo "Tentativa $attempt/$max_attempts - Web container não está pronto - aguardando..."
    sleep 5
done

if [ $attempt -ge $max_attempts ]; then
    echo "ERRO: Web container não ficou pronto após $max_attempts tentativas"
    exit 1
fi

# Aguardar um pouco mais para garantir que todas as inicializações foram feitas
echo "Aguardando inicializações do web container finalizarem..."
sleep 15

echo "Verificando conectividade com banco de dados..."
attempt=0
until [ $attempt -ge 10 ]; do
    if php artisan tinker --execute="DB::connection()->getPdo(); echo 'DB Connected';" 2>/dev/null | grep -q "DB Connected"; then
        echo "Banco de dados conectado!"
        break
    fi
    
    attempt=$((attempt + 1))
    echo "Tentativa $attempt/10 - Banco de dados não conectado - aguardando..."
    sleep 3
done

if [ $attempt -ge 10 ]; then
    echo "ERRO: Não foi possível conectar ao banco de dados"
    exit 1
fi

echo "Verificando se Redis está acessível..."
if ! php artisan tinker --execute="use Illuminate\Support\Facades\Redis; Redis::ping(); echo 'Redis Connected';" 2>/dev/null | grep -q "Redis Connected"; then
    echo "AVISO: Redis pode não estar totalmente acessível, mas continuando..."
fi

echo "=== QUEUE WORKERS PRONTOS ==="
echo "Iniciando workers das filas (sem executar migrations ou key generation)..."

# Limpar cache apenas se necessário
php artisan config:clear

exec /usr/bin/supervisord -n -c /etc/supervisor/supervisord.conf
