#!/bin/bash

# Navigate to the App directory
cd App

# 1. Install Frontend Dependencies & Build
echo "🚀 Building Frontend..."
npm install
npm run build

# 2. Install Backend (Composer)
# We rely on the system PHP or Vercel's environment here.
# If this fails, Vercel's fallback usually handles it, 
# but we want to be explicit.
if command -v php >/dev/null 2>&1; then
    echo "🐘 Installing Composer Dependencies..."
    curl -sS https://getcomposer.org/installer | php
    php composer.phar install --no-dev --optimize-autoloader --ignore-platform-reqs
else
    echo "⚠️ PHP command not found in build step. Relying on Vercel Runtime."
fi