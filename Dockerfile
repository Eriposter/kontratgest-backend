# ============================================
# Stage 1: Builder (instalar dependências)
# ============================================
FROM php:8.2-fpm AS builder

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nodejs \
    npm \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensões PHP
RUN docker-php-ext-install \
    pdo_mysql \
    pdo_pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Definir diretório de trabalho
WORKDIR /var/www/html

# Copiar ficheiros do projeto
COPY . .

# Instalar dependências PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Otimizar Laravel
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# ============================================
# Stage 2: Produção (imagem final leve)
# ============================================
FROM php:8.2-fpm-alpine

# Instalar dependências mínimas
RUN apk add --no-cache \
    nginx \
    supervisor \
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

# Configurar permissões
RUN addgroup -g 1000 -S www && \
    adduser -u 1000 -S www -G www

# Copiar ficheiros do builder
COPY --from=builder /var/www/html /var/www/html

# Copiar configurações
COPY .docker/nginx.conf /etc/nginx/http.d/default.conf
COPY .docker/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY .docker/php.ini /usr/local/etc/php/php.ini
COPY .docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY .docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# Permissões
RUN chmod +x /usr/local/bin/entrypoint.sh && \
    chown -R www:www /var/www/html && \
    chmod -R 755 /var/www/html/storage && \
    chmod -R 755 /var/www/html/bootstrap/cache

# Criar diretórios necessários
RUN mkdir -p /var/log/nginx /var/log/supervisor

# Expor porta
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

# Ponto de entrada
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]