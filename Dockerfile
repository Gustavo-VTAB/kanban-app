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

# Corrigir permissões para as pastas de storage e cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Configurar mod_rewrite do Apache
RUN a2enmod rewrite

# Habilitar a escrita no log e outras pastas
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Configurar Apache para apontar para o diretório 'public' do Laravel
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Expor a porta 80
EXPOSE 80

# Comandos para garantir que o cache e configuração estão limpos
RUN php artisan config:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache

# Iniciar o Apache
CMD php artisan migrate --force && apache2-foreground
