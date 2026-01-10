#!/bin/bash

# 1. DOWNLOAD PHP (Static Binary)
# We download a standalone PHP 8.2 file because Vercel doesn't have one.
echo "🐘 Downloading Static PHP..."
mkdir -p bin
curl -L -o php.tar.gz https://dl.static-php.dev/static-php-cli/common/php-8.2-cli-linux-x86_64.tar.gz
tar -xzf php.tar.gz -C bin/
chmod +x bin/php

# 2. INSTALL COMPOSER
# We use our custom 'bin/php' to run the installer
echo "🎼 Installing Composer..."
curl -sS https://getcomposer.org/installer | bin/php

# 3. INSTALL BACKEND DEPS
# We use 'bin/php' to run 'composer.phar'
echo "📦 Installing Laravel Dependencies..."
bin/php composer.phar install --no-dev --optimize-autoloader --ignore-platform-reqs

# 4. BUILD FRONTEND
echo "🚀 Building Frontend..."
npm install
npm run build