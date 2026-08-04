# ============================================
# Stage 1: Builder
# Instalar dependências e preparar Laravel
# ============================================

FROM php:8.2-fpm AS builder

WORKDIR /var/www/html


# Dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    zip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    nodejs \
    npm \
    && rm -rf /var/lib/apt/lists/*


# Configurar GD
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg


# Extensões PHP
RUN docker-php-ext-install pdo_pgsql
RUN docker-php-ext-install mbstring
RUN docker-php-ext-install exif
RUN docker-php-ext-install pcntl
RUN docker-php-ext-install bcmath
RUN docker-php-ext-install gd
RUN docker-php-ext-install zip
RUN docker-php-ext-install intl


# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer


# Copiar aplicação
COPY . .


# Instalar dependências Laravel
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-progress


# ============================================
# Stage 2: Produção
# ============================================

FROM php:8.2-fpm-alpine


WORKDIR /var/www/html


# Dependências runtime
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    postgresql-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libxml2-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev


# Dependências temporárias para compilar PHP
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    autoconf \
    g++ \
    make


# Configurar GD
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg


# Instalar extensões PHP
RUN docker-php-ext-install \
    pdo_pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl


# Remover ferramentas de compilação
RUN apk del .build-deps


# Criar utilizador da aplicação
RUN addgroup -g 1000 -S www && \
    adduser -u 1000 -S www -G www


# Copiar aplicação preparada
COPY --from=builder /var/www/html /var/www/html


# ============================================
# Configurações Docker
# ============================================

COPY .docker/nginx.conf \
    /etc/nginx/http.d/default.conf

COPY .docker/www.conf \
    /usr/local/etc/php-fpm.d/www.conf

COPY .docker/php.ini \
    /usr/local/etc/php/php.ini

COPY .docker/supervisord.conf \
    /etc/supervisor/conf.d/supervisord.conf

COPY .docker/entrypoint.sh \
    /usr/local/bin/entrypoint.sh


# Permissões Laravel
RUN chmod +x /usr/local/bin/entrypoint.sh && \
    chown -R www:www /var/www/html && \
    chmod -R 775 /var/www/html/storage && \
    chmod -R 775 /var/www/html/bootstrap/cache


# Criar logs
RUN mkdir -p \
    /var/log/nginx \
    /var/log/supervisor


# Porta
EXPOSE 80


# Healthcheck
HEALTHCHECK \
    --interval=30s \
    --timeout=5s \
    --start-period=10s \
    --retries=3 \
    CMD curl -f http://localhost/health || exit 1


# Entrada
# Entrada
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

CMD ["supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]