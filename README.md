# API Laravel Otimizada para Docker

Este projeto consiste em uma API Laravel otimizada para rodar em ambiente Docker, com limites de recursos configurados
e projetada para alta performance em ambientes de produção.

## 📋 Requisitos

- Docker e Docker Compose
- Git

## 🚀 Configuração Rápida

Para iniciar rapidamente, execute:

```bash
# Clone o repositório
git clone <seu-repositorio>
cd <diretorio-projeto>

# Execute o script de setup
chmod +x setup.sh
./setup.sh
```

O script irá configurar todo o ambiente Docker, instalar dependências, executar migrações e configurar o projeto.

## 🐳 Ambiente Docker

O projeto usa Docker com os seguintes serviços:

- **app-estech**: Contêiner PHP-FPM otimizado para APIs
- **nginx-estech**: Servidor web Nginx configurado para APIs REST
- **db-estech**: Banco de dados MySQL otimizado

### Limites de Recursos

Os contêineres têm limites de recursos configurados:

| Serviço | CPU | Memória |
|---------|-----|---------|
| PHP/Laravel | 0.5 CPU | 512MB |
| Nginx | 0.3 CPU | 128MB |
| MySQL | 1.0 CPU | 1GB |

## 📁 Estrutura de Arquivos

```
/docker
  /config           # Arquivos de configuração para os containers
    /mysql          # Configuração MySQL
    nginx.conf      # Configuração Nginx
    php.ini         # Configuração PHP
  Dockerfile        # Dockerfile otimizado para API
docker-compose.yml  # Compose com limites de recursos
setup.sh           # Script de inicialização
```

## 📡 Endpoints da API

- **Health Check**: `GET /api/health` - Verifica status da API e banco de dados
- **Ping**: `GET /api/ping` - Teste simples de conectividade
- **Test**: `GET /api/test` - Confirmação de funcionamento da API

## ⚙️ Configurações Avançadas

### Variáveis de Ambiente

Principais variáveis de ambiente configuráveis:

- `API_PORT`: Porta de exposição da API (padrão: 8000)
- `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`: Credenciais do banco de dados
- `DOCKER_*_CPU_LIMIT`, `DOCKER_*_MEMORY_LIMIT`: Limites de recursos dos containers

Consulte o arquivo `.env.example` para todas as opções disponíveis.

### Otimizações para Produção

Para ambientes de produção, recomendamos:

1. Configurar `APP_ENV=production` e `APP_DEBUG=false`
2. Otimizar o autoloader: `composer install --optimize-autoloader --no-dev`
3. Utilizar cache de configurações: `php artisan config:cache`
4. Utilizar cache de rotas: `php artisan route:cache`

## 🧪 Testes

Execute os testes utilizando:

```bash
docker compose exec app-estech php artisan test
```

## 🛠️ Comandos Úteis

```bash
# Acessar o terminal do container PHP
docker compose exec app-estech bash

# Visualizar logs
docker compose logs -f

# Verificar status dos containers
docker compose ps

# Reiniciar todos os containers
docker compose restart
```

## 📊 Monitoramento

O endpoint `/api/health` fornece informações sobre o status da aplicação e pode ser usado para monitoramento.

## 📄 Licença

Este projeto está licenciado sob a licença MIT.
