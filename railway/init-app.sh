#!/usr/bin/env sh
set -eu

php artisan migrate --force
