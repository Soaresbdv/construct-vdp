# ==========================================
# Estágio 1: Build do Frontend (Vue + Tailwind)
# ==========================================
FROM node:20-alpine AS frontend
WORKDIR /app/frontend
COPY frontend/package*.json ./
RUN npm install
COPY frontend/ ./
RUN npm run build

# ==========================================
# Estágio 2: Build do Backend (Laravel)
# ==========================================
FROM php:8.4-cli AS backend
# Instala utilitários necessários para o Composer rodar no Estágio 2
RUN apt-get update && apt-get install -y git unzip
# Copia o Composer oficial mais recente
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY composer.json composer.lock ./
# O --ignore-platform-reqs é vital aqui para o Composer não travar pedindo extensões antes da hora
RUN composer install --no-dev --optimize-autoloader --no-scripts --ignore-platform-reqs
COPY . .

# ==========================================
# Estágio 3: Imagem Final de Produção (PHP + Apache)
# ==========================================
FROM php:8.4-apache

# Instala extensões necessárias do Laravel e o PDO para banco de dados Postgres
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_pgsql pgsql mbstring pcntl bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Habilita o mod_rewrite do Apache (essencial para as rotas do Laravel)
RUN a2enmod rewrite

# Muda a pasta raiz do Apache para a pasta "public" do Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html

# Copia o backend (já com as dependências do Composer) do Estágio 2
COPY --from=backend /app /var/www/html

# Copia o frontend compilado do Estágio 1 para dentro da pasta public do Laravel
COPY --from=frontend /app/frontend/dist /var/www/html/public

# Ajusta as permissões das pastas que o Laravel precisa escrever
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# O Render expõe a porta 80 por padrão
EXPOSE 80