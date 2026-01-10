#!/bin/bash

# 1. DOWNLOAD PHP (From Vercel Community GitHub)
# This URL is stable and definitely exists.
echo "🐘 Downloading Static PHP..."
mkdir -p bin
curl -L -o php.zip https://github.com/vercel-community/php/releases/download/v8.2.11/php-linux-x64.zip

# 2. EXTRACT (Using Unzip)
echo "📂 Extracting PHP..."
unzip -o php.zip -d bin/
chmod +x bin/php

# 3. INSTALL COMPOSER
echo "🎼 Installing Composer..."
curl -sS https://getcomposer.org/installer | bin/php

# 4. INSTALL BACKEND DEPS
echo "📦 Installing Laravel Dependencies..."
bin/php composer.phar install --no-dev --optimize-autoloader --ignore-platform-reqs

# 5. BUILD FRONTEND
echo "🚀 Building Frontend..."
npm install
npm run build