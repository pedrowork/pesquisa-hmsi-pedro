FROM php:8.2-fpm-alpine

# Instalar dependências do sistema
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    postgresql-dev \
    libzip-dev \
    zip \
    unzip \
    $PHPIZE_DEPS

# Instalar Node.js 22 LTS (requerido pelo Vite 7.x - Node 20.19+ ou 22.12+)
# Download direto do Node.js oficial para garantir versão compatível
RUN curl -fsSL https://unofficial-builds.nodejs.org/download/release/v22.12.0/node-v22.12.0-linux-x64-musl.tar.xz | tar -xJ -C /tmp && \
    mv /tmp/node-v22.12.0-linux-x64-musl /opt/node && \
    ln -sf /opt/node/bin/node /usr/local/bin/node && \
    ln -sf /opt/node/bin/npm /usr/local/bin/npm && \
    ln -sf /opt/node/bin/npx /usr/local/bin/npx && \
    rm -rf /tmp/node-* && \
    node --version && npm --version

# Instalar extensões PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_pgsql \
    pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    && pecl install redis \
    && docker-php-ext-enable redis

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar diretório de trabalho
WORKDIR /var/www/html

# Copiar arquivos da aplicação (arquivos wayfinder excluídos via .dockerignore)
COPY . .

# Instalar dependências PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Garantir remoção completa dos arquivos wayfinder (força regeneração limpa)
RUN rm -rf resources/js/routes resources/js/actions resources/js/wayfinder && \
    find . -type d -name "routes" -path "*/resources/js/routes" -exec rm -rf {} + 2>/dev/null || true && \
    find . -type d -name "actions" -path "*/resources/js/actions" -exec rm -rf {} + 2>/dev/null || true

# Instalar dependências Node.js
RUN npm install

# Gerar arquivos wayfinder manualmente (plugin desabilitado no vite.config.ts)
RUN php artisan wayfinder:generate --with-form

# Remover duplicação do 'export const update' (bug do wayfinder)
RUN sed -i '7,72d' resources/js/routes/user-password/index.ts || true

# Build dos assets
RUN npm run build

# Ajustar permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Expor porta PHP-FPM
EXPOSE 9000

# Comando padrão
CMD ["php-fpm"]
