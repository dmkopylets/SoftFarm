FROM webdevops/php-apache-dev:8.3-alpine AS build
MAINTAINER uncle.dima <dm.kopylets@gmail.com>
LABEL description="Alpine based image with php 8.3 apache"

# Environment variables
ENV PHP_INI_DIR=/usr/local/etc/php
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
ENV APACHE_LOG_DIR=/var/www/logs
ENV TZ="Europe/Kyiv"
ENV COMPOSER_MEMORY_LIMIT='-1'
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV LD_PRELOAD=/usr/lib/preloadable_libiconv.so

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

ADD ./conf/usr/local/etc/php/php.ini /usr/local/etc/php/
ADD ./apache2/conf.d/vhosts.conf /etc/apache2/conf.d/
ADD ./apache2/httpd.conf /etc/apache2/

COPY ./conf/ /opt/docker/

# update apk repositories
RUN apk update

# upgrade all
RUN apk upgrade

# add c build tools
RUN apk add build-base

# add git
RUN apk add git

# add development packages
RUN apk add --no-cache \
    autoconf \
    bash \
    build-base \
    bzip2-dev \
    coreutils \
    curl-dev \
    git \
    freetype-dev \
    icu-dev \
    imap-dev \
    krb5-dev \
    libavif-dev \
    libffi-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libsodium-dev \
    libxml2-dev \
    libwebp-dev \
    libxpm-dev \
    libzip-dev \
    postgresql-dev \
    nodejs \
    npm \
    zlib-dev

# install the PHP extensions we need

RUN docker-php-ext-configure pgsql --with-pgsql=/usr/local/pgsql && \
  docker-php-ext-install pgsql

RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
  docker-php-ext-install gd

RUN docker-php-ext-install intl opcache pdo_pgsql zip

COPY ./php/opcache-recommended.ini /usr/local/etc/php/conf.d/opcache-recommended.ini

RUN docker-php-ext-enable apcu pdo_pgsql sodium

RUN apk add --update linux-headers

ARG UID=1000
ARG GID=1000

RUN apk --no-cache add shadow && \
#    groupmod -o -g ${PGID} www-data && \
#    usermod -o -u ${PUID} -g www-data www-data
    (getent passwd ${UID} && deluser --remove-home $(getent passwd ${UID} | cut -d: -f1)) || true && \
    usermod -u ${UID} apache && \
    groupmod -g ${GID} apache

RUN chown -R apache:apache /var/www/localhost/htdocs/ && \
    chown -R apache:apache /var/www/logs/ && \
    chmod 755 /var/www/localhost/htdocs/

RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.alpine.sh' | bash

RUN apk add symfony-cli

WORKDIR /var/www/localhost/htdocs/

#####################################
# Composer:
#####################################

RUN curl -s http://getcomposer.org/installer | php && \
    echo "export PATH=${PATH}:/var/www/localhost/htdocs/vendor/bin" >> ~/.bashrc && \
    mv composer.phar /usr/local/bin/composer

# Change current user to www-data
USER apache

EXPOSE 9000 4343

ENTRYPOINT ["/entrypoint"]
CMD ["supervisord"]

ENV WEB_ALIAS_DOMAIN=*.local
ENV WEB_NO_CACHE_PATTERN=\.(css|js|gif|png|jpg|svg|json|xml)$
ENV WEB_PHP_SOCKET=127.0.0.1:9000