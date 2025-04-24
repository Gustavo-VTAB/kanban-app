# Etapa 1: Construção dos ativos com Node.js
FROM node:18 AS node_builder

WORKDIR /app

# Copiar arquivos de dependências
COPY package*.json ./

# Instalar dependências do Node.js
RUN npm install

# Copiar o restante dos arquivos do projeto
COPY . .

# Executar o build do Vite
RUN npm run build

# Etapa 2: Configuração do ambiente PHP com Apache
FROM php:8.2-apache

# Instalar dependências do PHP
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    zip \
    git \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql

# Instalar o Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Definir o diretório de trabalho
WORKDIR /var/www/html

# Copiar arquivos PHP do projeto
COPY . .

# Copiar os arquivos compilados do Vite da etapa anterior
COPY --from=node_builder /app/public/build /var/www/html/public/build

# Instalar dependências do Composer
RUN composer install --no-interaction --prefer-dist

# Ajustar permissões
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Ativar o mod_rewrite do Apache
RUN a2enmod rewrite

# Configurar o Apache para apontar para o diretório 'public' do Laravel
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Expor a porta 80
EXPOSE 80

# Comandos para limpar e cachear configurações
RUN php artisan config:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache

# Executar as migrações e iniciar o Apache
CMD php artisan migrate --force && apache2-foreground
