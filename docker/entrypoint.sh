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

if [ "${APP_AUTO_INIT_DB:-1}" = "1" ]; then
  ATTEMPT=1
  MAX_ATTEMPTS=20
  DB_READY=0
  DB_SCHEMA_MISSING=0

  while [ "$ATTEMPT" -le "$MAX_ATTEMPTS" ]; do
    DB_CHECK_OUTPUT="$(php /var/www/html/scripts/check_db_ready.php 2>&1)" && DB_CHECK_EXIT=0 || DB_CHECK_EXIT=$?

    if [ "$DB_CHECK_EXIT" -eq 0 ]; then
      DB_READY=1
      break
    fi

    if [ "$DB_CHECK_EXIT" -eq 2 ]; then
      DB_SCHEMA_MISSING=1
      break
    fi

    echo "Aguardando banco ficar disponivel (tentativa ${ATTEMPT}/${MAX_ATTEMPTS})..."
    ATTEMPT=$((ATTEMPT + 1))
    sleep 2
  done

  if [ "$DB_SCHEMA_MISSING" -eq 1 ]; then
    echo "Schema nao encontrado. Inicializando base..."
    php /var/www/html/scripts/reset_demo_db.php
    php /var/www/html/scripts/reseed_minimal_demo.php
  elif [ "$DB_READY" -eq 0 ]; then
    echo "Falha ao conectar no banco apos ${MAX_ATTEMPTS} tentativas."
    echo "${DB_CHECK_OUTPUT}"
    exit 1
  fi
fi

exec "$@"
