# Usa uma imagem pronta do PHP com tudo que o Laravel precisa
FROM php:8.2-cli

# 1. Instala utilitários do sistema e o Node.js (para o Vue)
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    curl \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# 2. Instala extensões do PHP necessárias para o Banco e Laravel
RUN docker-php-ext-install pdo pdo_pgsql zip bcmath

# 3. Instala o Composer (Gerenciador do PHP)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Define a pasta de trabalho
WORKDIR /var/www/html

# 5. Copia os arquivos do projeto para dentro do container
COPY . .

# 6. Roda as instalações (Back e Front)
RUN composer install --no-dev --optimize-autoloader
RUN npm install
RUN npm run build

# 7. Expõe a porta e roda o servidor
EXPOSE 10000
CMD php artisan serve --host=0.0.0.0 --port=10000