#!/bin/sh
set -eu

SOURCE_CERT="/var/www/html/project/docker/certs/local-cert.pem"
SOURCE_KEY="/var/www/html/project/docker/certs/local-key.pem"
RUNTIME_SSL_DIR="/etc/apache2/runtime-ssl"
RUNTIME_CERT="$RUNTIME_SSL_DIR/local-cert.pem"
RUNTIME_KEY="$RUNTIME_SSL_DIR/local-key.pem"

mkdir -p "$RUNTIME_SSL_DIR"

if [ -s "$SOURCE_CERT" ] && [ -s "$SOURCE_KEY" ]; then
    cp "$SOURCE_CERT" "$RUNTIME_CERT"
    cp "$SOURCE_KEY" "$RUNTIME_KEY"
    echo "Using mkcert HTTPS certificate from docker/certs."
else
    echo "mkcert HTTPS certificate not found. Generating a self-signed Docker certificate."
    openssl req -x509 -nodes -newkey rsa:2048 -days 365 \
        -keyout "$RUNTIME_KEY" \
        -out "$RUNTIME_CERT" \
        -subj "/CN=localhost" \
        -addext "subjectAltName=DNS:localhost,IP:127.0.0.1" >/dev/null 2>&1
fi

chmod 600 "$RUNTIME_KEY"
chmod 644 "$RUNTIME_CERT"

exec docker-php-entrypoint apache2-foreground
