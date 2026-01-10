#!/bin/bash

# We are already in the 'App' directory because of Vercel Root settings.
# No need to 'cd App'.

echo "🚀 Building Frontend..."
npm install
npm run build

echo "🐘 Installing Composer Dependencies..."
# Use Vercel's built-in PHP if available, or download if missing
if ! command -v php &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php
    php composer.phar install --no-dev --optimize-autoloader --ignore-platform-reqs
else
    # If PHP exists (Vercel runtime), use it
    curl -sS https://getcomposer.org/installer | php
    php composer.phar install --no-dev --optimize-autoloader
fi