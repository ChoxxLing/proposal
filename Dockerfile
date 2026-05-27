FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libcurl4-openssl-dev openssl \
    && docker-php-ext-install mysqli curl \
    && rm -rf /var/lib/apt/lists/*

COPY docker/apache/ssl-vhost.conf /etc/apache2/sites-available/project-ssl.conf
COPY docker/entrypoint/https-entrypoint.sh /usr/local/bin/https-entrypoint.sh

RUN chmod +x /usr/local/bin/https-entrypoint.sh

RUN a2enmod rewrite ssl

RUN sed -ri 's!/var/www/html!/var/www/html/project/public!g' /etc/apache2/sites-available/000-default.conf \
    && printf '%s\n' \
        'Alias /api /var/www/html/project/api' \
        '<Directory /var/www/html/project/public>' \
        '    Options Indexes FollowSymLinks' \
        '    AllowOverride All' \
        '    Require all granted' \
        '</Directory>' \
        '<Directory /var/www/html/project/api>' \
        '    Options FollowSymLinks' \
        '    AllowOverride None' \
        '    Require all granted' \
        '</Directory>' \
        > /etc/apache2/conf-available/project.conf \
    && a2enconf project \
    && a2ensite project-ssl

WORKDIR /var/www/html/project

ENTRYPOINT ["https-entrypoint.sh"]
