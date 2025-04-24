# Usar a imagem base do PHP com Apache
FROM php:8.2-apache

# Instalar dependências para Laravel e extensões do PHP
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    zip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql

# Definir o diretório de trabalho
WORKDIR /var/www/html

# Copiar os arquivos do projeto Laravel para o contêiner
COPY . .

# Instalar dependências do Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-interaction --prefer-dist

# Configurar permissões para o storage e cache do Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Habilitar mod_rewrite do Apache
RUN a2enmod rewrite

# Expor a porta 80
EXPOSE 80

# Iniciar o Apache
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8000 
