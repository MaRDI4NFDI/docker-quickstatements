FROM composer:2 as composer

COPY /quickstatements /quickstatements

WORKDIR /quickstatements
RUN composer install --no-dev

FROM php:8-apache

# Install envsubst
RUN apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get install --yes --no-install-recommends gettext-base=0.19.* jq=1.5* \
    nano less && \
    rm -rf /var/lib/apt/lists/*

COPY --from=composer /quickstatements /var/www/html/quickstatements
COPY /magnustools /var/www/html/magnustools

COPY entrypoint.sh /entrypoint.sh

COPY config.json /templates/config.json
COPY oauth.ini /templates/oauth.ini
COPY php.ini /templates/php.ini

# patches
COPY quickstatements/public_html/api.php /var/www/html/quickstatements/public_html
COPY quickstatements/public_html/HandlerFactory.php /var/www/html/quickstatements/public_html
COPY quickstatements/public_html/quickstatements.php /var/www/html/quickstatements/public_html

ENV APACHE_DOCUMENT_ROOT /var/www/html/quickstatements/public_html
RUN sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf
RUN sed -ri -e "s!/var/www/!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

ENV MW_SITE_NAME=wikibase-docker\
    MW_SITE_LANG=en\
    PHP_TIMEZONE=UTC

RUN install -d -owww-data /var/log/quickstatements

ENTRYPOINT ["/bin/bash"]
CMD ["/entrypoint.sh"]
