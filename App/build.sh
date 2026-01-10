#!/bin/bash
mkdir -p bin
curl -L https://github.com/vercel-community/php/releases/download/v8.2.14/php-linux-x64.zip -o php.zip
unzip -o php.zip -d bin
chmod +x bin/php
curl -sS https://getcomposer.org/installer | bin/php
bin/php composer.phar install --no-dev --optimize-autoloader
npm install
npm run build