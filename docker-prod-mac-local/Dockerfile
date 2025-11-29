# =============================================================================
# DOCKERFILE PRODUZIONE - Avatar 3D v1
# Multi-stage build per immagine ottimizzata
# Dominio: demo-avatar.trentaduebit.it
# =============================================================================

# -----------------------------------------------------------------------------
# STAGE 1: Composer Dependencies (prima, perché serve per Vite build)
# -----------------------------------------------------------------------------
FROM composer:2 AS composer-builder

WORKDIR /app

# Copia composer files
COPY composer.json composer.lock ./

# Installa dependencies senza dev
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --ignore-platform-reqs

# Copia tutto il codice per autoload
COPY . .

# Genera autoload ottimizzato
RUN composer dump-autoload --optimize --no-dev

# -----------------------------------------------------------------------------
# STAGE 2: Build Assets (Node.js) - dopo Composer per avere vendor/livewire
# -----------------------------------------------------------------------------
FROM node:22-alpine AS assets-builder

WORKDIR /app

# Copia package files
COPY package.json package-lock.json* ./

# Installa dependencies
RUN npm ci --production=false

# Copia sorgenti per build
COPY resources ./resources
COPY vite.config.js ./
COPY tailwind.config.js ./
COPY postcss.config.js ./

# Copia vendor da composer-builder (serve per livewire.esm)
COPY --from=composer-builder /app/vendor ./vendor

# Build assets produzione
RUN npm run build

# -----------------------------------------------------------------------------
# STAGE 3: Runtime (PHP-FPM + Nginx)
# -----------------------------------------------------------------------------
FROM php:8.3-fpm-alpine AS runtime

# Installa nginx e dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    libxml2-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    icu-dev \
    oniguruma-dev \
    libmemcached-dev \
    zlib-dev \
    mysql-client \
    # SSL per MongoDB Atlas
    openssl-dev \
    # Build dependencies per PECL
    autoconf \
    g++ \
    make \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd \
        mysqli \
        pdo_mysql \
        intl \
        zip \
        opcache \
        bcmath \
        pcntl \
    && pecl install memcached mongodb \
    && docker-php-ext-enable memcached mongodb \
    # Rimuovi build dependencies per ridurre dimensione immagine (ma mantieni openssl per runtime SSL)
    && apk del autoconf g++ make \
    && rm -rf /var/cache/apk/*

# Installa bash e Node.js (per Azure TTS con Visemi)
RUN apk add --no-cache bash nodejs npm

# Crea directory necessarie
RUN mkdir -p /var/www/html \
    && mkdir -p /var/log/supervisor \
    && mkdir -p /run/nginx \
    && mkdir -p /var/www/.cache \
    && chown -R www-data:www-data /var/www

# Copia configurazioni
COPY docker-prod-mac-local/php.ini /usr/local/etc/php/conf.d/zz-custom.ini
COPY docker-prod-mac-local/nginx.conf /etc/nginx/nginx.conf
COPY docker-prod-mac-local/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker-prod-mac-local/www.conf /usr/local/etc/php-fpm.d/www.conf

# Copia codice applicazione
WORKDIR /var/www/html

# Copia vendor da composer stage
COPY --from=composer-builder /app/vendor ./vendor

# Copia assets compilati da node stage
COPY --from=assets-builder /app/public/build ./public/build

# Copia tutto il codice sorgente
COPY --chown=www-data:www-data . .

# Installa dipendenze npm per TTS script (Azure Speech SDK)
RUN cd /var/www/html/src/Modules/Avatar3DReact/scripts \
    && npm init -y \
    && npm install microsoft-cognitiveservices-speech-sdk \
    && chown -R www-data:www-data /var/www/html/src/Modules/Avatar3DReact/scripts

# Crea directory per audio output
RUN mkdir -p /var/www/html/public/avatar3d/audio \
    && chown -R www-data:www-data /var/www/html/public/avatar3d

# Rimuovi file non necessari in produzione
RUN rm -rf \
    docker-dev-playwright \
    docker-prod \
    docker-prod-mac-local \
    tests \
    .git \
    .env.example \
    phpunit.xml \
    vite.config.js \
    tailwind.config.js \
    postcss.config.js \
    README.md \
    config/debugbar.php \
    bootstrap/cache/packages.php \
    bootstrap/cache/services.php

# Permessi corretti
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \; \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Nota: storage:link e cache commands vengono eseguiti nell'entrypoint
# perché richiedono le variabili d'ambiente corrette (APP_ENV, ecc.)

# Expose ports
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

# Entrypoint
COPY docker-prod-mac-local/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]