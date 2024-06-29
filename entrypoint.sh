#!/bin/sh
set -e

# Run composer install if vendor directory is missing
if [ ! -d "vendor" ]; then
  composer install --no-interaction --prefer-dist --optimize-autoloader
fi

exec "$@"
