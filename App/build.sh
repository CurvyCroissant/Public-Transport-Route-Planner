#!/bin/bash

# 1. FIX DIRECTORY: Enter the App folder if we are not already in it
if [ -d "App" ]; then
  cd App
  echo "Changed directory to App/"
fi

# 2. FIX PHP DOWNLOAD: Use a reliable static binary source (tar.gz)
echo "Downloading Static PHP..."
mkdir -p bin
curl -o php.tar.gz https://dl.static-php.dev/static-php-cli/common/php-8.2-cli-linux-x86_64.tar.gz

# 3. EXTRACT: Extract the 'php' binary to the bin/ folder
tar -xzf php.tar.gz -C bin/
chmod +x bin/php

# 4. INSTALL COMPOSER & DEPENDENCIES
echo "Installing Composer..."
curl -sS https://getcomposer.org/installer | bin/php

echo "Installing Backend Deps..."
bin/php composer.phar install --no-dev --optimize-autoloader

echo "Building Frontend..."
npm install
npm run build