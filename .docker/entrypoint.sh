#!/bin/sh
set -e

echo "🚀 Iniciando KontratGest Backend..."

# Aguardar PostgreSQL estar pronto
echo "⏳ Aguardando base de dados..."
until php artisan db:show 2>/dev/null; do
    echo "   Base de dados ainda não disponível. Aguardando..."
    sleep 2
done
echo "✅ Base de dados conectada!"

# Permissões
echo " Ajustando permissões..."
chown -R www:www /var/www/html/storage
chown -R www:www /var/www/html/bootstrap/cache
chmod -R 755 /var/www/html/storage
chmod -R 755 /var/www/html/bootstrap/cache

# Otimizações (apenas em produção)
if [ "$APP_ENV" = "production" ]; then
    echo " Otimizando Laravel..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
fi

# Migrações (opcional - descomenta se quiseres auto-migrate)
# echo "🗄️  Executando migrações..."
# php artisan migrate --force --no-interaction

# Seeders (opcional)
# php artisan db:seed --force

echo "✅ KontratGest Backend pronto!"
echo "🌐 Acessível em http://localhost:8000"

# Executar o comando principal
exec "$@"