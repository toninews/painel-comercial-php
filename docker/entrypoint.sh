#!/usr/bin/env sh
set -e

mkdir -p /var/www/html/tmp/sessions /var/www/html/tmp/rate_limits /var/www/html/App/Database
chown -R www-data:www-data /var/www/html/tmp /var/www/html/App/Database
chmod -R 775 /var/www/html/tmp /var/www/html/App/Database

if [ "${DB_TYPE:-sqlite}" = "sqlite" ]; then
  DB_PATH="${DB_NAME:-App/Database/painel_comercial.db}"
  case "$DB_PATH" in
    /*) ABS_DB_PATH="$DB_PATH" ;;
    *) ABS_DB_PATH="/var/www/html/$DB_PATH" ;;
  esac
  mkdir -p "$(dirname "$ABS_DB_PATH")"
  touch "$ABS_DB_PATH"
  chown www-data:www-data "$ABS_DB_PATH"
  chmod 664 "$ABS_DB_PATH"
fi

exec "$@"
