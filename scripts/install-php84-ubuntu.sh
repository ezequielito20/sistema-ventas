#!/usr/bin/env bash
# Instala PHP 8.4 en Ubuntu (requiere PPA ondrej/php en 22.04/24.04).
# Uso: bash scripts/install-php84-ubuntu.sh
set -euo pipefail

export DEBIAN_FRONTEND=noninteractive

echo "==> Actualizando índice de paquetes..."
sudo apt-get update -qq

echo "==> Instalando dependencias para añadir el PPA..."
sudo apt-get install -y software-properties-common ca-certificates lsb-release

echo "==> Añadiendo PPA ondrej/php..."
sudo add-apt-repository -y ppa:ondrej/php
sudo apt-get update -qq

echo "==> Instalando PHP 8.4 y extensiones habituales para Laravel + PostgreSQL..."
sudo apt-get install -y \
  php8.4 \
  php8.4-cli \
  php8.4-fpm \
  php8.4-common \
  php8.4-pgsql \
  php8.4-sqlite3 \
  php8.4-mysql \
  php8.4-xml \
  php8.4-curl \
  php8.4-mbstring \
  php8.4-zip \
  php8.4-bcmath \
  php8.4-intl \
  php8.4-readline \
  php8.4-gd \
  php8.4-opcache \
  php8.4-soap

echo "==> Configurando 'php' por defecto en el sistema (CLI)..."
if update-alternatives --query php &>/dev/null; then
  sudo update-alternatives --set php /usr/bin/php8.4
else
  sudo update-alternatives --install /usr/bin/php php /usr/bin/php8.4 84
fi

echo ""
echo "Listo. Versión activa:"
php -v

echo ""
echo "Siguiente paso en el proyecto: cd al repo y ejecutar 'composer install' y 'php artisan about'."
