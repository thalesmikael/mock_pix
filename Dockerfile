FROM php:8.3-cli

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    cron \
    && docker-php-ext-install pdo_mysql

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar diretório de trabalho
WORKDIR /var/www

# Copiar arquivos do projeto
COPY . /var/www

# Instalar dependências
RUN composer install --no-dev --optimize-autoloader

# Criar diretórios de storage
RUN mkdir -p /var/www/storage/mails /var/www/storage/reports

# Definir permissões
RUN chown -R www-data:www-data /var/www/storage

# Expor porta
EXPOSE 8080

# Comando padrão
CMD ["php", "-S", "0.0.0.0:8080", "-t", "/var/www/public"]
