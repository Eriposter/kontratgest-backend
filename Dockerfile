# ============================================
# Backend Laravel Dockerfile (Simplificado e Robusto)
# ============================================
FROM php:8.2-fpm-alpine

# 1. Instalar dependências do sistema e extensões PHP
RUN apk add --no-cache \
    nginx \
    postgresql-dev \
    libpng-dev \
    libxml2-dev \
    libzip-dev \
    icu-dev \
    && docker-php-ext-install \
    pdo_pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl

# 2. Definir diretório de trabalho
WORKDIR /var/www/html

# 3. Copiar ficheiros do projeto
COPY . .

# 4. Instalar Composer e dependências
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# 5. Ajustar permissões (O Alpine usa o utilizador 'nginx' por defeito)
RUN chown -R nginx:nginx /var/www/html/storage \
    && chown -R nginx:nginx /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# 6. Configuração mínima do Nginx (inline, sem ficheiros externos)
RUN echo 'server { \
    listen 80; \
    server_name localhost; \
    root /var/www/html/public; \
    index index.php; \
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
}' > /etc/nginx/http.d/default.conf

# 7. Expor porta
EXPOSE 80

# 8. Comando de arranque (PHP-FPM em background + Nginx em foreground)
CMD sh -c "php-fpm -D && nginx -g 'daemon off;'"