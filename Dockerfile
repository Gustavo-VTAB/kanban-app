# Etapa 1: Build dos assets com Vite
FROM node:23-slim AS node_builder

WORKDIR /app

COPY package*.json ./
RUN npm install

COPY . .
RUN npm run build

COPY --from=node_builder /app/public /var/www/html/public


# Etapa 2: App Laravel com PHP
FROM php:8.4-cli

# Instalar extensões e ferramentas necessárias
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    zip \
    git \
    unzip \
    curl \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql gd

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

# Copiar o projeto Laravel
COPY . .

# Copiar os arquivos compilados do Vite
COPY --from=node_builder /app/public/build /var/www/html/public/build

# Instalar dependências do PHP
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Ajustar permissões
RUN chmod -R 775 storage bootstrap/cache && chown -R www-data:www-data .

# Cache de configuração
RUN php artisan config:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache

RUN ls -la /var/www/html/public/build && cat /var/www/html/public/build/manifest.json

# Rodar as migrations na inicialização e subir o servidor embutido
CMD php artisan migrate --force && php -S 0.0.0.0:80 -t public

RUN chown -R www-data:www-data /var/www/html/public/build && \
    chmod -R 775 /var/www/html/public/build

