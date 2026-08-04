# ============================================
# Backend Laravel Dockerfile (Simplificado e Funcional)
# ============================================
FROM php:8.2-fpm

# 1. Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    git \
    curl \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    postgresql-client \
    && rm -rf /var/lib/apt/lists/*

# 2. Configurar e instalar extensões PHP
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl

# 3. Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Definir diretório de trabalho
WORKDIR /var/www/html

# 5. Copiar ficheiros do projeto
COPY . .

# 6. Instalar dependências Laravel
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-progress

# 7. Configurar Nginx (inline, sem ficheiros externos)
RUN echo 'server { \
    listen 80; \
    server_name localhost; \
    root /var/www/html/public; \
    index index.php; \
    charset utf-8; \
    location / { \
        try_files $uri $uri/ /index.php?$query_string; \
    } \
    location ~ \.php$ { \
        fastcgi_pass 127.0.0.1:9000; \
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name; \
        include fastcgi_params; \
    } \
    location ~ /\. { \
        deny all; \
    } \
}' > /etc/nginx/sites-available/default

# 8. Configurar Supervisor (inline)
RUN echo '[supervisord] \
nodaemon=true \
user=root \
logfile=/var/log/supervisor/supervisord.log \
pidfile=/var/run/supervisord.pid \
\
[program:nginx] \
command=nginx -g "daemon off;" \
autostart=true \
autorestart=true \
stdout_logfile=/var/log/supervisor/nginx.log \
stderr_logfile=/var/log/supervisor/nginx-error.log \
\
[program:php-fpm] \
command=php-fpm -F \
autostart=true \
autorestart=true \
stdout_logfile=/var/log/supervisor/php-fpm.log \
stderr_logfile=/var/log/supervisor/php-fpm-error.log' > /etc/supervisor/conf.d/supervisord.conf

# 9. Ajustar permissões
RUN chown -R www-data:www-data /var/www/html/storage \
    && chown -R www-data:www-data /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# 10. Criar diretórios de logs
RUN mkdir -p /var/log/nginx /var/log/supervisor

# 11. Expor porta
EXPOSE 80

# 12. Healthcheck
HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

# 13. Comando de arranque
CMD ["supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]