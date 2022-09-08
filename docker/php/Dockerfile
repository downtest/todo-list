FROM php:8.1.10-fpm-alpine3.16

ARG USER_ID=1000
ARG GROUP_ID=1000

WORKDIR /var/www/html

RUN apk update \
    && apk add --no-cache $PHPIZE_DEPS \

    # Создаём такого же пользователя, как и на хосте
    && addgroup -S $GROUP_ID && adduser -S $USER_ID -G $GROUP_ID \

    # Устанавливаем расширения php
    && apk --no-cache add postgresql-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql \
    && pecl install redis-5.3.7 mongodb-1.14.0 \
    # Включаем расширения
    && docker-php-ext-enable redis pdo pgsql \
    && echo "extension=mongodb.so" > $PHP_INI_DIR/conf.d/mongo.ini \

    # Git
    && apk add git \

    # Use the default production configuration
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
#    && mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini" \

    # Устанавливаем composer
    && echo "Устанавливаем composer" \
    && wget https://getcomposer.org/download/2.1.5/composer.phar -O /usr/local/bin/composer \
    && chmod +x /usr/local/bin/composer

USER $USER_ID
