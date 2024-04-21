ARG ALPINE_VERSION
ARG COMPOSER_VERSION
FROM composer:${COMPOSER_VERSION} as composer
FROM alpine:${ALPINE_VERSION} as base

# ...

RUN apk add --update --no-cache  \
        php-mbstring~=${TARGET_PHP_VERSION} \
        php-phar~=${TARGET_PHP_VERSION} \

# ...

COPY --from=composer /usr/bin/composer /usr/local/bin/composer